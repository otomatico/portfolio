<?php
// Services/RecursoService.php

namespace App\Services;

use App\Config\Database;
use App\Log\Logger;
use App\Repositories\RecursoRepository;
use RuntimeException;

class RecursoService
{
    private RecursoRepository $repository;
    private Logger $logger;

    public function __construct(Database $database)
    {
        $this->repository = new RecursoRepository($database);
        $this->logger = new Logger();
    }

    public function listar(): array
    {
        $recursos = $this->repository->findAll();
        return array_map(fn($r) => $r->toArray(), $recursos);
    }

    public function obtenerPorId(int $id): array
    {
        $recurso = $this->repository->findById($id);
        if (!$recurso) {
            throw new RuntimeException('Recurso no encontrado');
        }
        return $recurso->toArray();
    }

    public function crear(array $data): array
    {
        if (empty($data['nombre'])) {
            throw new RuntimeException('El nombre es obligatorio');
        }

        $recurso = $this->repository->create($data);

        $this->logger->info('Recurso creado', [
            'id' => $recurso->id,
            'nombre' => $recurso->nombre,
        ]);

        return $recurso->toArray();
    }

    public function actualizar(int $id, array $data): array
    {
        $recurso = $this->repository->findById($id);
        if (!$recurso) {
            throw new RuntimeException('Recurso no encontrado');
        }

        $recurso = $this->repository->update($id, $data);

        $this->logger->info('Recurso actualizado', [
            'id' => $recurso->id,
            'nombre' => $recurso->nombre,
        ]);

        return $recurso->toArray();
    }

    public function eliminar(int $id): void
    {
        $recurso = $this->repository->findById($id);
        if (!$recurso) {
            throw new RuntimeException('Recurso no encontrado');
        }

        $this->repository->delete($id);

        $this->logger->info('Recurso eliminado', [
            'id' => $id,
            'nombre' => $recurso->nombre,
        ]);
    }
}
