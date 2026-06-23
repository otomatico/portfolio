<?php
// Repositories/UsuarioRepository.php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Usuario;
use PDO;

class UsuarioRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            "SELECT u.*, s.nombre AS sucursal_nombre 
             FROM usuarios u 
             LEFT JOIN sucursales s ON u.sucursal_id = s.id 
             ORDER BY u.nombre ASC"
        );
        $rows = $stmt->fetchAll();
        return array_map([Usuario::class, 'fromRow'], $rows);
    }

    public function findById(int $id): ?Usuario
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, s.nombre AS sucursal_nombre 
             FROM usuarios u 
             LEFT JOIN sucursales s ON u.sucursal_id = s.id 
             WHERE u.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Usuario::fromRow($row) : null;
    }

    public function findByEmail(string $email): ?Usuario
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, s.nombre AS sucursal_nombre 
             FROM usuarios u 
             LEFT JOIN sucursales s ON u.sucursal_id = s.id 
             WHERE u.email = :email"
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ? Usuario::fromRow($row) : null;
    }

    public function create(array $data): Usuario
    {
        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (nombre, email, password, rol, sucursal_id) 
             VALUES (:nombre, :email, :password, :rol, :sucursal_id)"
        );
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':email' => $data['email'],
            ':password' => $data['password'],
            ':rol' => $data['rol'] ?? 'coordinador',
            ':sucursal_id' => $data['sucursal_id'] ?? null,
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?Usuario
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nombre'])) {
            $fields[] = "nombre = :nombre";
            $params[':nombre'] = $data['nombre'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (isset($data['password'])) {
            $fields[] = "password = :password";
            $params[':password'] = $data['password'];
        }
        if (isset($data['rol'])) {
            $fields[] = "rol = :rol";
            $params[':rol'] = $data['rol'];
        }
        if (array_key_exists('sucursal_id', $data)) {
            $fields[] = "sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = $data['sucursal_id'];
        }

        if (empty($fields)) {
            return $this->findById($id);
        }

        $fields[] = "updated_at = datetime('now')";
        $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
