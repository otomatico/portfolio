<?php
// Services/PermisoService.php

namespace App\Services;

use App\Config\Database;
use App\Log\Logger;
use App\Repositories\PermisoRepository;
use RuntimeException;

class PermisoService
{
    private PermisoRepository $repository;
    private Logger $logger;

    public function __construct(Database $database)
    {
        $this->repository = new PermisoRepository($database);
        $this->logger = new Logger();
    }

    public function listarTodos(): array
    {
        $permisos = $this->repository->findAll();
        return array_map(fn($p) => $p->toArray(), $permisos);
    }

    public function listarPorRol(string $rol): array
    {
        $permisos = $this->repository->findByRol($rol);
        return array_map(fn($p) => $p->toArray(), $permisos);
    }

    public function actualizar(string $rol, string $componente, array $data): array
    {
        if (empty($rol)) {
            throw new RuntimeException('El rol es obligatorio');
        }
        if (empty($componente)) {
            throw new RuntimeException('El componente es obligatorio');
        }

        $permiso = $this->repository->upsert($rol, $componente, $data);

        $this->logger->info('Permiso actualizado', [
            'rol' => $rol,
            'componente' => $componente,
            'data' => $data,
        ]);

        return $permiso->toArray();
    }
}
