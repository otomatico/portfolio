<?php
// Repositories/PermisoRepository.php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Permiso;
use PDO;

class PermisoRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM permisos ORDER BY rol, componente");
        $rows = $stmt->fetchAll();
        return array_map([Permiso::class, 'fromRow'], $rows);
    }

    public function findByRol(string $rol): array
    {
        $stmt = $this->db->prepare("SELECT * FROM permisos WHERE rol = :rol ORDER BY componente");
        $stmt->execute([':rol' => $rol]);
        $rows = $stmt->fetchAll();
        return array_map([Permiso::class, 'fromRow'], $rows);
    }

    public function findByRolYComponente(string $rol, string $componente): ?Permiso
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM permisos WHERE rol = :rol AND componente = :componente"
        );
        $stmt->execute([':rol' => $rol, ':componente' => $componente]);
        $row = $stmt->fetch();
        return $row ? Permiso::fromRow($row) : null;
    }

    public function upsert(string $rol, string $componente, array $data): Permiso
    {
        // Verificar si existe
        $existing = $this->findByRolYComponente($rol, $componente);

        if ($existing) {
            $fields = [];
            $params = [':id' => $existing->id];

            if (isset($data['permiso_lectura'])) {
                $fields[] = "permiso_lectura = :permiso_lectura";
                $params[':permiso_lectura'] = (int) $data['permiso_lectura'];
            }
            if (isset($data['permiso_creacion'])) {
                $fields[] = "permiso_creacion = :permiso_creacion";
                $params[':permiso_creacion'] = (int) $data['permiso_creacion'];
            }
            if (isset($data['permiso_actualizacion'])) {
                $fields[] = "permiso_actualizacion = :permiso_actualizacion";
                $params[':permiso_actualizacion'] = (int) $data['permiso_actualizacion'];
            }
            if (isset($data['permiso_eliminacion'])) {
                $fields[] = "permiso_eliminacion = :permiso_eliminacion";
                $params[':permiso_eliminacion'] = (int) $data['permiso_eliminacion'];
            }

            if (!empty($fields)) {
                $fields[] = "updated_at = datetime('now')";
                $sql = "UPDATE permisos SET " . implode(', ', $fields) . " WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }

            return $this->findByRolYComponente($rol, $componente);
        } else {
            // Insertar nuevo
            $stmt = $this->db->prepare(
                "INSERT INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) 
                 VALUES (:rol, :componente, :permiso_lectura, :permiso_creacion, :permiso_actualizacion, :permiso_eliminacion)"
            );
            $stmt->execute([
                ':rol' => $rol,
                ':componente' => $componente,
                ':permiso_lectura' => (int) ($data['permiso_lectura'] ?? 0),
                ':permiso_creacion' => (int) ($data['permiso_creacion'] ?? 0),
                ':permiso_actualizacion' => (int) ($data['permiso_actualizacion'] ?? 0),
                ':permiso_eliminacion' => (int) ($data['permiso_eliminacion'] ?? 0),
            ]);
            return $this->findByRolYComponente($rol, $componente);
        }
    }
}
