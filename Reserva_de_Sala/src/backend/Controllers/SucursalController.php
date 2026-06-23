<?php
// Controllers/SucursalController.php

namespace App\Controllers;

use App\Config\Database;
use App\Services\SucursalService;

class SucursalController
{
    private SucursalService $service;

    public function __construct(Database $database)
    {
        $this->service = new SucursalService($database);
    }

    /**
     * GET /api/sucursales
     */
    public function index(): void
    {
        try {
            $sucursales = $this->service->listar();
            echo json_encode($sucursales);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/sucursales/{id}
     */
    public function show(int $id): void
    {
        try {
            $sucursal = $this->service->obtenerPorId($id);
            echo json_encode($sucursal);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/sucursales
     */
    public function store(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $sucursal = $this->service->crear($input);
            http_response_code(201);
            echo json_encode($sucursal);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * PUT /api/sucursales/{id}
     */
    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $sucursal = $this->service->actualizar($id, $input);
            echo json_encode($sucursal);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /api/sucursales/{id}
     */
    public function destroy(int $id): void
    {
        try {
            $this->service->eliminar($id);
            http_response_code(200);
            echo json_encode(['message' => 'Sucursal eliminada exitosamente']);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
