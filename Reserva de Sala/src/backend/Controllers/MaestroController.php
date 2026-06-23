<?php
// Controllers/MaestroController.php

namespace App\Controllers;

use App\Config\Database;
use App\Services\MaestroService;

class MaestroController
{
    private MaestroService $service;

    public function __construct(Database $database)
    {
        $this->service = new MaestroService($database);
    }

    /**
     * GET /api/maestros
     */
    public function index(): void
    {
        try {
            $grupos = $this->service->listarGrupos();
            echo json_encode($grupos);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/maestros/{codigo}
     */
    public function show(string $codigo): void
    {
        try {
            $grupo = $this->service->obtenerGrupo($codigo);
            echo json_encode($grupo);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/maestros
     */
    public function store(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $grupo = $this->service->crearGrupo($input);
            http_response_code(201);
            echo json_encode($grupo);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * PUT /api/maestros/{codigo}
     */
    public function update(string $codigo): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $grupo = $this->service->actualizarGrupo($codigo, $input);
            echo json_encode($grupo);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /api/maestros/{codigo}
     */
    public function destroy(string $codigo): void
    {
        try {
            $this->service->eliminarGrupo($codigo);
            http_response_code(200);
            echo json_encode(['message' => 'Grupo maestro eliminado exitosamente']);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // --- Opciones ---

    /**
     * GET /api/maestros/{codigo}/opciones
     */
    public function opciones(string $codigo): void
    {
        try {
            $onlyActivas = isset($_GET['activas']) && $_GET['activas'] === 'true';
            $opciones = $this->service->listarOpciones($codigo, $onlyActivas);
            echo json_encode($opciones);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/maestros/{codigo}/opciones
     */
    public function storeOpcion(string $codigo): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $opcion = $this->service->crearOpcion($codigo, $input);
            http_response_code(201);
            echo json_encode($opcion);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * PUT /api/maestros/opciones/{id}
     */
    public function updateOpcion(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $opcion = $this->service->actualizarOpcion($id, $input);
            echo json_encode($opcion);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /api/maestros/opciones/{id}
     */
    public function destroyOpcion(int $id): void
    {
        try {
            $this->service->eliminarOpcion($id);
            http_response_code(200);
            echo json_encode(['message' => 'Opción eliminada exitosamente']);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
