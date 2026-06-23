<?php
// Middleware/PermissionMiddleware.php

namespace App\Middleware;

use App\Config\Database;
use App\Log\Logger;
use App\Repositories\PermisoRepository;

class PermissionMiddleware
{
    /**
     * Verifica que el rol tenga permiso para la operación solicitada
     * @param array $jwtPayload Payload del JWT
     * @param string $method Método HTTP (GET, POST, PUT, DELETE)
     * @param string $uri URI de la petición
     * @return bool True si tiene permiso
     */
    public static function check(array $jwtPayload, string $method, string $uri): bool
    {
        $rol = $jwtPayload['rol'] ?? '';
        $componente = self::resolveComponente($uri);
        $logger = new Logger();

        // Cualquier usuario autenticado puede leer su propia matriz de permisos
        if ($componente === 'permisos' && $method === 'GET') {
            return true;
        }

        try {
            $db = new Database();
            $permisoRepository = new PermisoRepository($db);
            $permiso = $permisoRepository->findByRolYComponente($rol, $componente);

            if (!$permiso) {
                $logger->warning('Acceso denegado - permiso no encontrado', [
                    'rol' => $rol,
                    'componente' => $componente,
                    'metodo' => $method,
                ]);
                return false;
            }

            $tienePermiso = self::tienePermiso($permiso, $method);

            if (!$tienePermiso) {
                $logger->warning('Acceso denegado - sin permiso para operación', [
                    'rol' => $rol,
                    'componente' => $componente,
                    'metodo' => $method,
                ]);
            }

            return $tienePermiso;

        } catch (\Throwable $e) {
            $logger->error('Error al verificar permiso', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private static function tienePermiso(\App\Models\Permiso $permiso, string $method): bool
    {
        return match ($method) {
            'GET' => (bool) $permiso->permiso_lectura,
            'POST' => (bool) $permiso->permiso_creacion,
            'PUT' => (bool) $permiso->permiso_actualizacion,
            'DELETE' => (bool) $permiso->permiso_eliminacion,
            default => false,
        };
    }

    /**
     * Resuelve el componente a partir de la URI
     * /api/salas/123/recursos → 'salas'
     * /api/reservas → 'reservas'
     */
    private static function resolveComponente(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $parts = explode('/', trim($path, '/'));

        // Las rutas tienen formato: api/{componente}/...
        if (isset($parts[0]) && $parts[0] === 'api' && isset($parts[1])) {
            $componente = $parts[1];

            // Mapear endpoints especiales
            $map = [
                'auth' => 'auth',
                'sucursales' => 'sucursales',
                'salas' => 'salas',
                'recursos' => 'recursos',
                'reservas' => 'reservas',
                'usuarios' => 'usuarios',
                'maestros' => 'maestros',
                'permisos' => 'permisos',
            ];

            return $map[$componente] ?? $componente;
        }

        return '';
    }
}
