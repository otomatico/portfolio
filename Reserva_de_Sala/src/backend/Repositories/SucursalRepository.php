<?php
// Repositories/SucursalRepository.php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Sucursal;
use PDO;

class SucursalRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM sucursales ORDER BY nombre ASC");
        $rows = $stmt->fetchAll();
        return array_map([Sucursal::class, 'fromRow'], $rows);
    }

    public function findById(int $id): ?Sucursal
    {
        $stmt = $this->db->prepare("SELECT * FROM sucursales WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Sucursal::fromRow($row) : null;
    }

    public function create(array $data): Sucursal
    {
        $stmt = $this->db->prepare(
            "INSERT INTO sucursales (nombre, direccion) VALUES (:nombre, :direccion)"
        );
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':direccion' => $data['direccion'] ?? '',
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?Sucursal
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nombre'])) {
            $fields[] = "nombre = :nombre";
            $params[':nombre'] = $data['nombre'];
        }
        if (isset($data['direccion'])) {
            $fields[] = "direccion = :direccion";
            $params[':direccion'] = $data['direccion'];
        }

        if (empty($fields)) {
            return $this->findById($id);
        }

        $fields[] = "updated_at = datetime('now')";
        $sql = "UPDATE sucursales SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM sucursales WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
