<?php
// Repositories/ReservaRepository.php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Reserva;
use PDO;

class ReservaRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findAll(array $filters = []): array
    {
        $sql = "SELECT r.*, s.nombre AS sala_nombre, u.nombre AS usuario_nombre, 
                       u.email AS usuario_email, sc.id AS sucursal_id, sc.nombre AS sucursal_nombre
                FROM reservas r 
                LEFT JOIN salas s ON r.sala_id = s.id 
                LEFT JOIN usuarios u ON r.usuario_id = u.id 
                LEFT JOIN sucursales sc ON s.sucursal_id = sc.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['usuario_id'])) {
            $conditions[] = "r.usuario_id = :usuario_id";
            $params[':usuario_id'] = $filters['usuario_id'];
        }

        if (!empty($filters['sala_id'])) {
            $conditions[] = "r.sala_id = :sala_id";
            $params[':sala_id'] = $filters['sala_id'];
        }

        if (!empty($filters['sucursal_id'])) {
            $conditions[] = "s.sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = $filters['sucursal_id'];
        }

        if (!empty($filters['estado'])) {
            $conditions[] = "r.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        if (!empty($filters['fecha_desde'])) {
            $conditions[] = "r.fecha_inicio >= :fecha_desde";
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $conditions[] = "r.fecha_fin <= :fecha_hasta";
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY r.fecha_inicio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map([Reserva::class, 'fromRow'], $rows);
    }

    public function findById(int $id): ?Reserva
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, s.nombre AS sala_nombre, u.nombre AS usuario_nombre, 
                    u.email AS usuario_email, sc.id AS sucursal_id, sc.nombre AS sucursal_nombre
             FROM reservas r 
             LEFT JOIN salas s ON r.sala_id = s.id 
             LEFT JOIN usuarios u ON r.usuario_id = u.id 
             LEFT JOIN sucursales sc ON s.sucursal_id = sc.id 
             WHERE r.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Reserva::fromRow($row) : null;
    }

    /**
     * Verifica si hay conflictos de horario para una sala
     * RN-05: nueva.fecha_inicio < existente.fecha_fin AND nueva.fecha_fin > existente.fecha_inicio
     */
    public function hasConflict(int $salaId, string $fechaInicio, string $fechaFin, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM reservas 
                WHERE sala_id = :sala_id 
                AND estado = 'confirmada'
                AND fecha_inicio < :fecha_fin 
                AND fecha_fin > :fecha_inicio";

        $params = [
            ':sala_id' => $salaId,
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin,
        ];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Obtiene la disponibilidad de una sala para una fecha específica
     */
    public function getDisponibilidad(int $salaId, string $fecha): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, fecha_inicio, fecha_fin, estado 
             FROM reservas 
             WHERE sala_id = :sala_id 
             AND date(fecha_inicio) = :fecha 
             AND estado = 'confirmada'
             ORDER BY fecha_inicio ASC"
        );
        $stmt->execute([
            ':sala_id' => $salaId,
            ':fecha' => $fecha,
        ]);
        return $stmt->fetchAll();
    }

    public function create(array $data): Reserva
    {
        $stmt = $this->db->prepare(
            "INSERT INTO reservas (sala_id, usuario_id, fecha_inicio, fecha_fin, estado) 
             VALUES (:sala_id, :usuario_id, :fecha_inicio, :fecha_fin, :estado)"
        );
        $stmt->execute([
            ':sala_id' => (int) $data['sala_id'],
            ':usuario_id' => (int) $data['usuario_id'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':estado' => $data['estado'] ?? 'confirmada',
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function updateEstado(int $id, string $estado): ?Reserva
    {
        $stmt = $this->db->prepare(
            "UPDATE reservas SET estado = :estado, updated_at = datetime('now') WHERE id = :id"
        );
        $stmt->execute([':id' => $id, ':estado' => $estado]);
        return $this->findById($id);
    }
}
