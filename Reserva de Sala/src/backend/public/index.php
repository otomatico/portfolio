<?php
// public/index.php — Front Controller + Boot

// Autoload de clases (PSR-4 simple)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Config\Database;
use App\Database\MigrationManager;
use App\Middleware\CorsMiddleware;
use App\Middleware\JwtMiddleware;
use App\Middleware\PermissionMiddleware;

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// ─── CORS ───
$cors = new CorsMiddleware();
$cors->handle();

// ─── Migraciones ───
try {
    $db = new Database();
    $migrator = new MigrationManager($db, __DIR__ . '/../../../database/migrations/');
    $result = $migrator->run();

    if ($migrator->hasErrors()) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Error en migraciones',
            'details' => $migrator->getErrors(),
        ]);
        exit;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error de conexión a base de datos',
        'message' => $e->getMessage(),
    ]);
    exit;
}

// ─── Router ───
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    // Eliminar query string de la URI
    $uriPath = parse_url($uri, PHP_URL_PATH);

    // Cargar rutas
    $routes = require __DIR__ . '/../Routes/api.php';

    // Buscar ruta exacta primero
    $routeKey = "{$method} {$uriPath}";

    if (isset($routes[$routeKey])) {
        $handler = $routes[$routeKey];
        handleRoute($handler, $db, []);
        exit;
    }

    // Buscar ruta con parámetros
    foreach ($routes as $pattern => $handler) {
        $params = matchRoute($method, $uriPath, $pattern);
        if ($params !== null) {
            handleRoute($handler, $db, $params);
            exit;
        }
    }

    // Ruta no encontrada
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada', 'uri' => $uriPath, 'method' => $method]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}

// ─── Helper Functions ───

/**
 * Intenta hacer match de una URI contra un patrón de ruta
 */
function matchRoute(string $method, string $uri, string $pattern): ?array
{
    // Separar método y patrón
    $parts = explode(' ', $pattern, 2);
    if (count($parts) !== 2) {
        return null;
    }

    $patternMethod = $parts[0];
    $patternPath = $parts[1];

    if ($method !== $patternMethod) {
        return null;
    }

    // Convertir patrón a regex
    $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $patternPath);
    $regex = '#^' . $regex . '$#';

    if (preg_match($regex, $uri, $matches)) {
        // Extraer solo los parámetros nombrados
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        return $params;
    }

    return null;
}

/**
 * Ejecuta el handler de una ruta
 */
function handleRoute(array $handler, Database $db, array $params): void
{
    [$controllerClass, $methodName] = $handler;

    // Crear instancia del controlador
    $controller = new $controllerClass($db);

    // Verificar autenticación para rutas protegidas (excepto login)
    $requiresAuth = true;
    $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($uriPath === '/api/auth/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $requiresAuth = false;
    }

    if ($requiresAuth) {
        $payload = JwtMiddleware::validate();
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido o expirado']);
            return;
        }

        // Verificar permisos
        if (!PermissionMiddleware::check($payload, $_SERVER['REQUEST_METHOD'], $uriPath)) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso para esta acción']);
            return;
        }
    }

    // Llamar al método del controlador con los parámetros
    if (!empty($params)) {
        $controller->$methodName(...array_values($params));
    } else {
        $controller->$methodName();
    }
}
