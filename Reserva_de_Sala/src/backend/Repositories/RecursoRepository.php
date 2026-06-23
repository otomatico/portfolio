<?php
// Repositories/RecursoRepository.php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Recurso;
use PDO;

class RecursoRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM recursos ORDER BY nombre ASC");
        $rows = $stmt->fetchAll();
        return array_map([Recurso::class, 'fromRow'], $rows);
    }

    public function findById(int $id): ?Recurso
    {
        $stmt = $this->db->prepare("SELECT * FROM recursos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Recurso::fromRow($row) : null;
    }

    public function create(array $data): Recurso
    {
        $stmt = $this->db->prepare(
            "INSERT INTO recursos (nombre, descripcion) VALUES (:nombre, :descripcion)"
        );
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? '',
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?Recurso
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nombre'])) {
            $fields[] = "nombre = :nombre";
            $params[':nombre'] = $data['nombre'];
        }
        if (isset($data['descripcion'])) {
            $fields[] = "descripcion = :descripcion";
            $params[':descripcion'] = $data['descripcion'];
        }

        if (empty($fields)) {
            return $this->findById($id);
        }

        $fields[] = "updated_at = datetime('now')";
        $sql = "UPDATE recursos SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        // Eliminar relaciones sala_recursos primero
        $this->db->prepare("DELETE FROM sala_recursos WHERE recurso_id = :id")->execute([':id' => $id]);
        
        $stmt = $this->db->prepare("DELETE FROM recursos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
