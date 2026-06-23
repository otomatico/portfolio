<?php
// Middleware/JwtMiddleware.php

namespace App\Middleware;

use App\Services\AuthService;
use App\Config\Database;

class JwtMiddleware
{
    /**
     * Valida el token JWT de la petición
     * @return array|null Payload del JWT si es válido, null si no
     */
    public static function validate(): ?array
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

        if (empty($authHeader)) {
            return null;
        }

        // Extraer token del header "Bearer xxx"
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        } else {
            return null;
        }

        // También permitir token en query param (para compatibilidad)
        if (empty($token)) {
            $token = $_GET['token'] ?? '';
        }

        if (empty($token)) {
            return null;
        }

        try {
            $db = new Database();
            $authService = new AuthService($db);
            $payload = $authService->validateJWT($token);
            return $payload;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Obtiene el payload del JWT si existe (sin validar nuevamente)
     */
    public static function getPayload(): ?array
    {
        return self::validate();
    }
}
