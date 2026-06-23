<?php
// Services/UsuarioService.php

namespace App\Services;

use App\Config\Database;
use App\Log\Logger;
use App\Repositories\UsuarioRepository;
use App\Repositories\SucursalRepository;
use RuntimeException;

class UsuarioService
{
    private UsuarioRepository $repository;
    private SucursalRepository $sucursalRepository;
    private Logger $logger;

    public function __construct(Database $database)
    {
        $this->repository = new UsuarioRepository($database);
        $this->sucursalRepository = new SucursalRepository($database);
        $this->logger = new Logger();
    }

    public function listar(): array
    {
        $usuarios = $this->repository->findAll();
        return array_map(fn($u) => $u->toArray(), $usuarios);
    }

    public function obtenerPorId(int $id): array
    {
        $usuario = $this->repository->findById($id);
        if (!$usuario) {
            throw new RuntimeException('Usuario no encontrado');
        }
        return $usuario->toArray();
    }

    public function crear(array $data): array
    {
        if (empty($data['nombre'])) {
            throw new RuntimeException('El nombre es obligatorio');
        }
        if (empty($data['email'])) {
            throw new RuntimeException('El email es obligatorio');
        }
        if (empty($data['password'])) {
            throw new RuntimeException('La contraseña es obligatoria');
        }

        // Verificar email duplicado
        $existing = $this->repository->findByEmail($data['email']);
        if ($existing) {
            throw new RuntimeException('El email ya está registrado');
        }

        // Si es coordinador, sucursal es obligatoria
        $rol = $data['rol'] ?? 'coordinador';
        if ($rol === 'coordinador' && (empty($data['sucursal_id']))) {
            throw new RuntimeException('El coordinador debe estar asociado a una sucursal');
        }

        // Si tiene sucursal, verificar que exista
        if (!empty($data['sucursal_id'])) {
            $sucursal = $this->sucursalRepository->findById((int) $data['sucursal_id']);
            if (!$sucursal) {
                throw new RuntimeException('La sucursal especificada no existe');
            }
        }

        // Hashear contraseña
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $usuario = $this->repository->create($data);

        $this->logger->info('Usuario creado', [
            'id' => $usuario->id,
            'email' => $usuario->email,
            'rol' => $usuario->rol,
        ]);

        return $usuario->toArray();
    }

    public function actualizar(int $id, array $data): array
    {
        $usuario = $this->repository->findById($id);
        if (!$usuario) {
            throw new RuntimeException('Usuario no encontrado');
        }

        // Si se cambia el email, verificar que no esté duplicado
        if (isset($data['email']) && $data['email'] !== $usuario->email) {
            $existing = $this->repository->findByEmail($data['email']);
            if ($existing) {
                throw new RuntimeException('El email ya está registrado');
            }
        }

        // Si se cambia la contraseña, hashearla
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $usuario = $this->repository->update($id, $data);

        $this->logger->info('Usuario actualizado', [
            'id' => $usuario->id,
            'email' => $usuario->email,
        ]);

        return $usuario->toArray();
    }

    public function eliminar(int $id): void
    {
        $usuario = $this->repository->findById($id);
        if (!$usuario) {
            throw new RuntimeException('Usuario no encontrado');
        }

        $this->repository->delete($id);

        $this->logger->info('Usuario eliminado', [
            'id' => $id,
            'email' => $usuario->email,
        ]);
    }
}
