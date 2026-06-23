<?php
// Controllers/SalaController.php

namespace App\Controllers;

use App\Config\Database;
use App\Services\SalaService;

class SalaController
{
    private SalaService $service;

    public function __construct(Database $database)
    {
        $this->service = new SalaService($database);
    }

    /**
     * GET /api/salas
     */
    public function index(): void
    {
        try {
            $sucursalId = isset($_GET['sucursal_id']) ? (int) $_GET['sucursal_id'] : null;
            $salas = $this->service->listar($sucursalId);
            echo json_encode($salas);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/salas/{id}
     */
    public function show(int $id): void
    {
        try {
            $sala = $this->service->obtenerPorId($id);
            echo json_encode($sala);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/salas
     */
    public function store(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $sala = $this->service->crear($input);
            http_response_code(201);
            echo json_encode($sala);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * PUT /api/salas/{id}
     */
    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $sala = $this->service->actualizar($id, $input);
            echo json_encode($sala);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /api/salas/{id}
     */
    public function destroy(int $id): void
    {
        try {
            $this->service->eliminar($id);
            http_response_code(200);
            echo json_encode(['message' => 'Sala eliminada exitosamente']);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/salas/{id}/recursos
     */
    public function recursos(int $id): void
    {
        try {
            $recursos = $this->service->getRecursos($id);
            echo json_encode($recursos);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/salas/{id}/recursos
     */
    public function asignarRecurso(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $recursoId = (int) ($input['recurso_id'] ?? 0);
        $cantidad = (int) ($input['cantidad'] ?? 1);

        try {
            $recursos = $this->service->asignarRecurso($id, $recursoId, $cantidad);
            http_response_code(201);
            echo json_encode($recursos);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /api/salas/{id}/recursos/{recursoId}
     */
    public function desasignarRecurso(int $id, int $recursoId): void
    {
        try {
            $recursos = $this->service->desasignarRecurso($id, $recursoId);
            echo json_encode($recursos);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
