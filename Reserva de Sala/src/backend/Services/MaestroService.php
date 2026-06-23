<?php
// Services/MaestroService.php

namespace App\Services;

use App\Config\Database;
use App\Log\Logger;
use App\Repositories\MaestroRepository;
use RuntimeException;

class MaestroService
{
    private MaestroRepository $repository;
    private Logger $logger;

    public function __construct(Database $database)
    {
        $this->repository = new MaestroRepository($database);
        $this->logger = new Logger();
    }

    // --- Grupos Maestros ---

    public function listarGrupos(): array
    {
        $grupos = $this->repository->findAll();
        return array_map(fn($g) => $g->toArray(), $grupos);
    }

    public function obtenerGrupo(string $codigo): array
    {
        $grupo = $this->repository->findByCodigo($codigo);
        if (!$grupo) {
            throw new RuntimeException('Grupo maestro no encontrado');
        }
        return $grupo->toArray();
    }

    public function crearGrupo(array $data): array
    {
        if (empty($data['codigo'])) {
            throw new RuntimeException('El código es obligatorio');
        }
        if (empty($data['nombre'])) {
            throw new RuntimeException('El nombre es obligatorio');
        }

        // Verificar código duplicado
        $existing = $this->repository->findByCodigo($data['codigo']);
        if ($existing) {
            throw new RuntimeException('El código ya existe');
        }

        $grupo = $this->repository->create($data);

        $this->logger->info('Grupo maestro creado', [
            'codigo' => $grupo->codigo,
            'nombre' => $grupo->nombre,
        ]);

        return $grupo->toArray();
    }

    public function actualizarGrupo(string $codigo, array $data): array
    {
        $grupo = $this->repository->findByCodigo($codigo);
        if (!$grupo) {
            throw new RuntimeException('Grupo maestro no encontrado');
        }

        $grupo = $this->repository->update($codigo, $data);

        $this->logger->info('Grupo maestro actualizado', [
            'codigo' => $grupo->codigo,
            'nombre' => $grupo->nombre,
        ]);

        return $grupo->toArray();
    }

    public function eliminarGrupo(string $codigo): void
    {
        $grupo = $this->repository->findByCodigo($codigo);
        if (!$grupo) {
            throw new RuntimeException('Grupo maestro no encontrado');
        }

        // Verificar que no tenga opciones asociadas
        if ($this->repository->hasOptions($codigo)) {
            throw new RuntimeException('No se puede eliminar un grupo que tiene opciones asociadas');
        }

        $this->repository->delete($codigo);

        $this->logger->info('Grupo maestro eliminado', [
            'codigo' => $codigo,
        ]);
    }

    // --- Opciones ---

    public function listarOpciones(string $maestroCodigo, bool $onlyActivas = false): array
    {
        $grupo = $this->repository->findByCodigo($maestroCodigo);
        if (!$grupo) {
            throw new RuntimeException('Grupo maestro no encontrado');
        }

        $opciones = $this->repository->findOpcionesByMaestro($maestroCodigo, $onlyActivas);
        return array_map(fn($o) => $o->toArray(), $opciones);
    }

    public function crearOpcion(string $maestroCodigo, array $data): array
    {
        $grupo = $this->repository->findByCodigo($maestroCodigo);
        if (!$grupo) {
            throw new RuntimeException('Grupo maestro no encontrado');
        }

        if (empty($data['codigo'])) {
            throw new RuntimeException('El código de la opción es obligatorio');
        }
        if (empty($data['nombre'])) {
            throw new RuntimeException('El nombre de la opción es obligatorio');
        }

        $data['maestro_codigo'] = $maestroCodigo;

        $opcion = $this->repository->createOpcion($data);

        $this->logger->info('Opción de maestro creada', [
            'id' => $opcion->id,
            'codigo' => $opcion->codigo,
            'maestro_codigo' => $opcion->maestro_codigo,
        ]);

        return $opcion->toArray();
    }

    public function actualizarOpcion(int $id, array $data): array
    {
        $opcion = $this->repository->findOpcionById($id);
        if (!$opcion) {
            throw new RuntimeException('Opción no encontrada');
        }

        $opcion = $this->repository->updateOpcion($id, $data);

        $this->logger->info('Opción de maestro actualizada', [
            'id' => $opcion->id,
            'codigo' => $opcion->codigo,
        ]);

        return $opcion->toArray();
    }

    public function eliminarOpcion(int $id): void
    {
        $opcion = $this->repository->findOpcionById($id);
        if (!$opcion) {
            throw new RuntimeException('Opción no encontrada');
        }

        $this->repository->deleteOpcion($id);

        $this->logger->info('Opción de maestro eliminada', [
            'id' => $id,
            'codigo' => $opcion->codigo,
        ]);
    }
}
