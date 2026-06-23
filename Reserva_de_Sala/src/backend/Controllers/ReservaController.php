<?php
// Controllers/ReservaController.php

namespace App\Controllers;

use App\Config\Database;
use App\Services\ReservaService;
use App\Middleware\JwtMiddleware;

class ReservaController
{
    private ReservaService $service;

    public function __construct(Database $database)
    {
        $this->service = new ReservaService($database);
    }

    /**
     * GET /api/reservas
     * Admin: todas las reservas
     * Coordinador: solo sus reservas
     */
    public function index(): void
    {
        try {
            $payload = JwtMiddleware::getPayload();
            $filters = [];

            // Si es coordinador, solo ve sus reservas (RN-06)
            if ($payload && $payload['rol'] === 'coordinador') {
                $filters['usuario_id'] = (int) $payload['sub'];
            }

            // Filtros opcionales (solo admin puede filtrar por otros criterios)
            if ($payload && $payload['rol'] === 'admin') {
                if (!empty($_GET['sala_id'])) {
                    $filters['sala_id'] = (int) $_GET['sala_id'];
                }
                if (!empty($_GET['sucursal_id'])) {
                    $filters['sucursal_id'] = (int) $_GET['sucursal_id'];
                }
                if (!empty($_GET['estado'])) {
                    $filters['estado'] = $_GET['estado'];
                }
                if (!empty($_GET['fecha_desde'])) {
                    $filters['fecha_desde'] = $_GET['fecha_desde'];
                }
                if (!empty($_GET['fecha_hasta'])) {
                    $filters['fecha_hasta'] = $_GET['fecha_hasta'];
                }
            }

            $reservas = $this->service->listar($filters);
            echo json_encode($reservas);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/reservas/{id}
     */
    public function show(int $id): void
    {
        try {
            $payload = JwtMiddleware::getPayload();
            $reserva = $this->service->obtenerPorId($id);

            // Coordinador solo puede ver sus propias reservas
            if ($payload && $payload['rol'] === 'coordinador' && (int) $reserva['usuario_id'] !== (int) $payload['sub']) {
                http_response_code(403);
                echo json_encode(['error' => 'No tienes permiso para ver esta reserva']);
                return;
            }

            echo json_encode($reserva);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/reservas
     */
    public function store(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $payload = JwtMiddleware::getPayload();
            $reserva = $this->service->crear($input, $payload);
            http_response_code(201);
            echo json_encode($reserva);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * PUT /api/reservas/{id}/cancelar
     */
    public function cancelar(int $id): void
    {
        try {
            $payload = JwtMiddleware::getPayload();
            $reserva = $this->service->cancelar($id, $payload);
            echo json_encode($reserva);
        } catch (\RuntimeException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/salas/{id}/disponibilidad
     */
    public function disponibilidad(int $salaId): void
    {
        try {
            $fecha = $_GET['fecha'] ?? date('Y-m-d');
            $disponibilidad = $this->service->getDisponibilidad($salaId, $fecha);
            echo json_encode($disponibilidad);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
