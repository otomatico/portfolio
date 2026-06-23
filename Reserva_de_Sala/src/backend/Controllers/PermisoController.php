<?php
// Controllers/PermisoController.php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermisoService;

class PermisoController
{
    private PermisoService $service;

    public function __construct(Database $database)
    {
        $this->service = new PermisoService($database);
    }

    /**
     * GET /api/permisos
     */
    public function index(): void
    {
        try {
            $permisos = $this->service->listarTodos();
            echo json_encode($permisos);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/permisos/{rol}
     */
    public function show(string $rol): void
    {
        try {
            $permisos = $this->service->listarPorRol($rol);
            echo json_encode($permisos);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * PUT /api/permisos/{rol}/{componente}
     */
    public function update(string $rol, string $componente): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $permiso = $this->service->actualizar($rol, $componente, $input);
            echo json_encode($permiso);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
