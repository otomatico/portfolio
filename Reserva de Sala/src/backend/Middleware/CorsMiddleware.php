<?php
// Middleware/CorsMiddleware.php

namespace App\Middleware;

class CorsMiddleware
{
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../Config/app.php';
    }

    public function handle(): void
    {
        $corsConfig = $this->config['cors'];
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        if (in_array($origin, $corsConfig['allowed_origins'])) {
            header("Access-Control-Allow-Origin: {$origin}");
        } else {
            header('Access-Control-Allow-Origin: *');
        }

        header('Access-Control-Allow-Methods: ' . implode(', ', $corsConfig['allowed_methods']));
        header('Access-Control-Allow-Headers: ' . implode(', ', $corsConfig['allowed_headers']));
        header('Access-Control-Max-Age: ' . $corsConfig['max_age']);
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json; charset=utf-8');

        // Responder a preflight OPTIONS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
