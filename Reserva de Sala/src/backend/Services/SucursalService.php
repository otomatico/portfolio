<?php
// Services/SucursalService.php

namespace App\Services;

use App\Config\Database;
use App\Log\Logger;
use App\Repositories\SucursalRepository;
use RuntimeException;

class SucursalService
{
    private SucursalRepository $repository;
    private Logger $logger;

    public function __construct(Database $database)
    {
        $this->repository = new SucursalRepository($database);
        $this->logger = new Logger();
    }

    public function listar(): array
    {
        $sucursales = $this->repository->findAll();
        return array_map(fn($s) => $s->toArray(), $sucursales);
    }

    public function obtenerPorId(int $id): array
    {
        $sucursal = $this->repository->findById($id);
        if (!$sucursal) {
            throw new RuntimeException('Sucursal no encontrada');
        }
        return $sucursal->toArray();
    }

    public function crear(array $data): array
    {
        if (empty($data['nombre'])) {
            throw new RuntimeException('El nombre es obligatorio');
        }

        $sucursal = $this->repository->create($data);

        $this->logger->info('Sucursal creada', [
            'id' => $sucursal->id,
            'nombre' => $sucursal->nombre,
        ]);

        return $sucursal->toArray();
    }

    public function actualizar(int $id, array $data): array
    {
        $sucursal = $this->repository->findById($id);
        if (!$sucursal) {
            throw new RuntimeException('Sucursal no encontrada');
        }

        $sucursal = $this->repository->update($id, $data);

        $this->logger->info('Sucursal actualizada', [
            'id' => $sucursal->id,
            'nombre' => $sucursal->nombre,
        ]);

        return $sucursal->toArray();
    }

    public function eliminar(int $id): void
    {
        $sucursal = $this->repository->findById($id);
        if (!$sucursal) {
            throw new RuntimeException('Sucursal no encontrada');
        }

        $this->repository->delete($id);

        $this->logger->info('Sucursal eliminada', [
            'id' => $id,
            'nombre' => $sucursal->nombre,
        ]);
    }
}
