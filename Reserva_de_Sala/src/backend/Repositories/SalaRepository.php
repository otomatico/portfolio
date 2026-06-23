<?php
// Repositories/SalaRepository.php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Sala;
use PDO;

class SalaRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findAll(?int $sucursalId = null): array
    {
        $sql = "SELECT s.*, sc.nombre AS sucursal_nombre 
                FROM salas s 
                LEFT JOIN sucursales sc ON s.sucursal_id = sc.id";

        $params = [];
        if ($sucursalId !== null) {
            $sql .= " WHERE s.sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = $sucursalId;
        }

        $sql .= " ORDER BY s.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $salas = array_map([Sala::class, 'fromRow'], $rows);

        // Cargar recursos para cada sala
        foreach ($salas as $sala) {
            $sala->recursos = $this->getRecursosBySalaId($sala->id);
        }

        return $salas;
    }

    public function findById(int $id): ?Sala
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, sc.nombre AS sucursal_nombre 
             FROM salas s 
             LEFT JOIN sucursales sc ON s.sucursal_id = sc.id 
             WHERE s.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $sala = Sala::fromRow($row);
        $sala->recursos = $this->getRecursosBySalaId($sala->id);
        return $sala;
    }

    public function create(array $data): Sala
    {
        $stmt = $this->db->prepare(
            "INSERT INTO salas (nombre, aforo, descripcion, sucursal_id) 
             VALUES (:nombre, :aforo, :descripcion, :sucursal_id)"
        );
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':aforo' => (int) ($data['aforo'] ?? 1),
            ':descripcion' => $data['descripcion'] ?? '',
            ':sucursal_id' => (int) $data['sucursal_id'],
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?Sala
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nombre'])) {
            $fields[] = "nombre = :nombre";
            $params[':nombre'] = $data['nombre'];
        }
        if (isset($data['aforo'])) {
            $fields[] = "aforo = :aforo";
            $params[':aforo'] = (int) $data['aforo'];
        }
        if (isset($data['descripcion'])) {
            $fields[] = "descripcion = :descripcion";
            $params[':descripcion'] = $data['descripcion'];
        }
        if (isset($data['sucursal_id'])) {
            $fields[] = "sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = (int) $data['sucursal_id'];
        }

        if (empty($fields)) {
            return $this->findById($id);
        }

        $fields[] = "updated_at = datetime('now')";
        $sql = "UPDATE salas SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        // Eliminar relaciones sala_recursos primero
        $this->db->prepare("DELETE FROM sala_recursos WHERE sala_id = :id")->execute([':id' => $id]);
        
        $stmt = $this->db->prepare("DELETE FROM salas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function getRecursosBySalaId(int $salaId): array
    {
        $stmt = $this->db->prepare(
            "SELECT sr.*, r.nombre AS recurso_nombre 
             FROM sala_recursos sr 
             LEFT JOIN recursos r ON sr.recurso_id = r.id 
             WHERE sr.sala_id = :sala_id 
             ORDER BY r.nombre ASC"
        );
        $stmt->execute([':sala_id' => $salaId]);
        $rows = $stmt->fetchAll();

        $recursos = [];
        foreach ($rows as $row) {
            $recursos[] = [
                'recurso_id' => (int) $row['recurso_id'],
                'nombre' => $row['recurso_nombre'],
                'cantidad' => (int) ($row['cantidad'] ?? 1),
            ];
        }
        return $recursos;
    }

    public function asignarRecurso(int $salaId, int $recursoId, int $cantidad = 1): bool
    {
        // Verificar si ya existe
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM sala_recursos WHERE sala_id = :sala_id AND recurso_id = :recurso_id"
        );
        $stmt->execute([':sala_id' => $salaId, ':recurso_id' => $recursoId]);
        $exists = (bool) $stmt->fetchColumn();

        if ($exists) {
            // Actualizar cantidad
            $stmt = $this->db->prepare(
                "UPDATE sala_recursos SET cantidad = :cantidad WHERE sala_id = :sala_id AND recurso_id = :recurso_id"
            );
        } else {
            // Insertar
            $stmt = $this->db->prepare(
                "INSERT INTO sala_recursos (sala_id, recurso_id, cantidad) VALUES (:sala_id, :recurso_id, :cantidad)"
            );
        }

        return $stmt->execute([
            ':sala_id' => $salaId,
            ':recurso_id' => $recursoId,
            ':cantidad' => $cantidad,
        ]);
    }

    public function desasignarRecurso(int $salaId, int $recursoId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM sala_recursos WHERE sala_id = :sala_id AND recurso_id = :recurso_id"
        );
        $stmt->execute([':sala_id' => $salaId, ':recurso_id' => $recursoId]);
        return $stmt->rowCount() > 0;
    }
}
