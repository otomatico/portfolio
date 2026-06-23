<?php
// Controllers/UsuarioController.php

namespace App\Controllers;

use App\Config\Database;
use App\Services\UsuarioService;

class UsuarioController
{
    private UsuarioService $service;

    public function __construct(Database $database)
    {
        $this->service = new UsuarioService($database);
    }

    /**
     * GET /api/usuarios
     */
    public function index(): void
    {
        try {
            $usuarios = $this->service->listar();
            echo json_encode($usuarios);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/usuarios/{id}
     */
    public function show(int $id): void
    {
        try {
            $usuario = $this->service->obtenerPorId($id);
            echo json_encode($usuario);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/usuarios
     */
    public function store(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $usuario = $this->service->crear($input);
            http_response_code(201);
            echo json_encode($usuario);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * PUT /api/usuarios/{id}
     */
    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $usuario = $this->service->actualizar($id, $input);
            echo json_encode($usuario);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /api/usuarios/{id}
     */
    public function destroy(int $id): void
    {
        try {
            $this->service->eliminar($id);
            http_response_code(200);
            echo json_encode(['message' => 'Usuario eliminado exitosamente']);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
