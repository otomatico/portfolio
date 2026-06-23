<?php
// Controllers/RecursoController.php

namespace App\Controllers;

use App\Config\Database;
use App\Services\RecursoService;

class RecursoController
{
    private RecursoService $service;

    public function __construct(Database $database)
    {
        $this->service = new RecursoService($database);
    }

    /**
     * GET /api/recursos
     */
    public function index(): void
    {
        try {
            $recursos = $this->service->listar();
            echo json_encode($recursos);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/recursos/{id}
     */
    public function show(int $id): void
    {
        try {
            $recurso = $this->service->obtenerPorId($id);
            echo json_encode($recurso);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/recursos
     */
    public function store(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $recurso = $this->service->crear($input);
            http_response_code(201);
            echo json_encode($recurso);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * PUT /api/recursos/{id}
     */
    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $recurso = $this->service->actualizar($id, $input);
            echo json_encode($recurso);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /api/recursos/{id}
     */
    public function destroy(int $id): void
    {
        try {
            $this->service->eliminar($id);
            http_response_code(200);
            echo json_encode(['message' => 'Recurso eliminado exitosamente']);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
