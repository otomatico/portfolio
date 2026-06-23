<?php
// Bootstrap para tests de backend
require_once __DIR__ . '/../../src/backend/Config/Database.php';
require_once __DIR__ . '/../../src/backend/Config/app.php';
require_once __DIR__ . '/../../src/backend/Database/MigrationManager.php';

// Configurar autoloading manual para tests
spl_autoload_register(function (string $class) {
    // App\Config\* -> src/backend/Config/
    // App\Models\* -> src/backend/Models/
    // App\Services\* -> src/backend/Services/
    // App\Controllers\* -> src/backend/Controllers/
    // App\Repositories\* -> src/backend/Repositories/
    // App\Middleware\* -> src/backend/Middleware/
    // App\Log\* -> src/backend/Log/
    // App\Database\* -> src/backend/Database/

    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../../src/backend/';

    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
});
