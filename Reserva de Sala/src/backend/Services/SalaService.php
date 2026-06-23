<?php
// Services/SalaService.php

namespace App\Services;

use App\Config\Database;
use App\Log\Logger;
use App\Repositories\SalaRepository;
use App\Repositories\SucursalRepository;
use App\Repositories\RecursoRepository;
use RuntimeException;

class SalaService
{
    private SalaRepository $repository;
    private SucursalRepository $sucursalRepository;
    private RecursoRepository $recursoRepository;
    private Logger $logger;

    public function __construct(Database $database)
    {
        $this->repository = new SalaRepository($database);
        $this->sucursalRepository = new SucursalRepository($database);
        $this->recursoRepository = new RecursoRepository($database);
        $this->logger = new Logger();
    }

    public function listar(?int $sucursalId = null): array
    {
        $salas = $this->repository->findAll($sucursalId);
        return array_map(fn($s) => $s->toArray(), $salas);
    }

    public function obtenerPorId(int $id): array
    {
        $sala = $this->repository->findById($id);
        if (!$sala) {
            throw new RuntimeException('Sala no encontrada');
        }
        return $sala->toArray();
    }

    public function crear(array $data): array
    {
        if (empty($data['nombre'])) {
            throw new RuntimeException('El nombre es obligatorio');
        }
        if (empty($data['sucursal_id'])) {
            throw new RuntimeException('La sucursal es obligatoria');
        }

        $sucursal = $this->sucursalRepository->findById((int) $data['sucursal_id']);
        if (!$sucursal) {
            throw new RuntimeException('La sucursal especificada no existe');
        }

        $sala = $this->repository->create($data);

        $this->logger->info('Sala creada', [
            'id' => $sala->id,
            'nombre' => $sala->nombre,
            'sucursal_id' => $sala->sucursal_id,
        ]);

        return $sala->toArray();
    }

    public function actualizar(int $id, array $data): array
    {
        $sala = $this->repository->findById($id);
        if (!$sala) {
            throw new RuntimeException('Sala no encontrada');
        }

        if (isset($data['sucursal_id'])) {
            $sucursal = $this->sucursalRepository->findById((int) $data['sucursal_id']);
            if (!$sucursal) {
                throw new RuntimeException('La sucursal especificada no existe');
            }
        }

        $sala = $this->repository->update($id, $data);

        $this->logger->info('Sala actualizada', [
            'id' => $sala->id,
            'nombre' => $sala->nombre,
        ]);

        return $sala->toArray();
    }

    public function eliminar(int $id): void
    {
        $sala = $this->repository->findById($id);
        if (!$sala) {
            throw new RuntimeException('Sala no encontrada');
        }

        $this->repository->delete($id);

        $this->logger->info('Sala eliminada', [
            'id' => $id,
            'nombre' => $sala->nombre,
        ]);
    }

    public function getRecursos(int $salaId): array
    {
        $sala = $this->repository->findById($salaId);
        if (!$sala) {
            throw new RuntimeException('Sala no encontrada');
        }
        return $sala->recursos;
    }

    public function asignarRecurso(int $salaId, int $recursoId, int $cantidad = 1): array
    {
        $sala = $this->repository->findById($salaId);
        if (!$sala) {
            throw new RuntimeException('Sala no encontrada');
        }

        $recurso = $this->recursoRepository->findById($recursoId);
        if (!$recurso) {
            throw new RuntimeException('Recurso no encontrado');
        }

        $this->repository->asignarRecurso($salaId, $recursoId, $cantidad);

        $this->logger->info('Recurso asignado a sala', [
            'sala_id' => $salaId,
            'recurso_id' => $recursoId,
            'cantidad' => $cantidad,
        ]);

        return $this->getRecursos($salaId);
    }

    public function desasignarRecurso(int $salaId, int $recursoId): array
    {
        $sala = $this->repository->findById($salaId);
        if (!$sala) {
            throw new RuntimeException('Sala no encontrada');
        }

        $this->repository->desasignarRecurso($salaId, $recursoId);

        $this->logger->info('Recurso desasignado de sala', [
            'sala_id' => $salaId,
            'recurso_id' => $recursoId,
        ]);

        return $this->getRecursos($salaId);
    }
}
