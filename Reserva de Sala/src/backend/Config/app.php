<?php
// Config/app.php — Configuración general del sistema

return [
    'jwt' => [
        'secret'     => getenv('JWT_SECRET') ?: 'S3cr3t0JWT_SalasFormacion_2026!',
        'issuer'     => 'salas-formacion',
        'expiration' => 28800, // 8 horas en segundos
    ],
    'cors' => [
        'allowed_origins' => ['http://localhost:5173', 'http://localhost:5000', 'http://127.0.0.1:5173'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'Accept'],
        'max_age'         => 86400,
    ],
    'log' => [
        'path'      => __DIR__ . '/../../../logs/',
        'level'     => 'debug',   // debug | info | warning | error
        'max_files' => 7,
        'app_name'  => 'salas-formacion',
    ],
    'database' => [
        'dev'  => __DIR__ . '/../../../database/database.sqlite',
        'test' => __DIR__ . '/../../../tests/backend/test_db.sqlite',
    ],
    'debug' => true,
];
