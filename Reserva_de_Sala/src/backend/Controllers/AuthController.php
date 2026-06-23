<?php
// Controllers/AuthController.php

namespace App\Controllers;

use App\Config\Database;
use App\Services\AuthService;
use App\Middleware\JwtMiddleware;

class AuthController
{
    private AuthService $authService;

    public function __construct(Database $database)
    {
        $this->authService = new AuthService($database);
    }

    /**
     * POST /api/auth/login
     */
    public function login(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email y contraseña son requeridos']);
            return;
        }

        try {
            $result = $this->authService->login($email, $password);
            echo json_encode([
                'message' => 'Autenticación exitosa',
                'token' => $result['token'],
                'user' => $result['user'],
            ]);
        } catch (\RuntimeException $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(): void
    {
        // En JWT stateless, el logout se maneja desde el frontend eliminando el token
        // Aquí simplemente confirmamos
        echo json_encode(['message' => 'Sesión cerrada exitosamente']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(): void
    {
        $payload = JwtMiddleware::getPayload();
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Token no proporcionado']);
            return;
        }

        try {
            $user = $this->authService->me($payload);
            echo json_encode($user);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
