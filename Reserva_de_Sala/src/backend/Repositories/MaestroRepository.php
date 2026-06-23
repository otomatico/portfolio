<?php
// Repositories/MaestroRepository.php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Maestro;
use App\Models\OpcionMaestro;
use PDO;

class MaestroRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM maestros ORDER BY codigo ASC");
        $rows = $stmt->fetchAll();
        return array_map([Maestro::class, 'fromRow'], $rows);
    }

    public function findByCodigo(string $codigo): ?Maestro
    {
        $stmt = $this->db->prepare("SELECT * FROM maestros WHERE codigo = :codigo");
        $stmt->execute([':codigo' => $codigo]);
        $row = $stmt->fetch();
        return $row ? Maestro::fromRow($row) : null;
    }

    public function create(array $data): Maestro
    {
        $stmt = $this->db->prepare(
            "INSERT INTO maestros (codigo, nombre) VALUES (:codigo, :nombre)"
        );
        $stmt->execute([
            ':codigo' => $data['codigo'],
            ':nombre' => $data['nombre'],
        ]);
        return $this->findByCodigo($data['codigo']);
    }

    public function update(string $codigo, array $data): ?Maestro
    {
        $fields = [];
        $params = [':codigo' => $codigo];

        if (isset($data['nombre'])) {
            $fields[] = "nombre = :nombre";
            $params[':nombre'] = $data['nombre'];
        }

        if (empty($fields)) {
            return $this->findByCodigo($codigo);
        }

        $fields[] = "updated_at = datetime('now')";
        $sql = "UPDATE maestros SET " . implode(', ', $fields) . " WHERE codigo = :codigo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findByCodigo($codigo);
    }

    public function delete(string $codigo): bool
    {
        $stmt = $this->db->prepare("DELETE FROM maestros WHERE codigo = :codigo");
        $stmt->execute([':codigo' => $codigo]);
        return $stmt->rowCount() > 0;
    }

    public function hasOptions(string $codigo): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM opciones_maestro WHERE maestro_codigo = :codigo");
        $stmt->execute([':codigo' => $codigo]);
        return (bool) $stmt->fetchColumn();
    }

    // --- Opciones ---

    public function findOpcionesByMaestro(string $maestroCodigo, bool $onlyActivas = false): array
    {
        $sql = "SELECT * FROM opciones_maestro WHERE maestro_codigo = :maestro_codigo";
        $params = [':maestro_codigo' => $maestroCodigo];

        if ($onlyActivas) {
            $sql .= " AND activo = 1";
        }

        $sql .= " ORDER BY orden ASC, nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return array_map([OpcionMaestro::class, 'fromRow'], $rows);
    }

    public function findOpcionById(int $id): ?OpcionMaestro
    {
        $stmt = $this->db->prepare("SELECT * FROM opciones_maestro WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? OpcionMaestro::fromRow($row) : null;
    }

    public function createOpcion(array $data): OpcionMaestro
    {
        $stmt = $this->db->prepare(
            "INSERT INTO opciones_maestro (maestro_codigo, codigo, nombre, orden, activo) 
             VALUES (:maestro_codigo, :codigo, :nombre, :orden, :activo)"
        );
        $stmt->execute([
            ':maestro_codigo' => $data['maestro_codigo'],
            ':codigo' => $data['codigo'],
            ':nombre' => $data['nombre'],
            ':orden' => (int) ($data['orden'] ?? 0),
            ':activo' => isset($data['activo']) ? (int) $data['activo'] : 1,
        ]);
        return $this->findOpcionById((int) $this->db->lastInsertId());
    }

    public function updateOpcion(int $id, array $data): ?OpcionMaestro
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['codigo'])) {
            $fields[] = "codigo = :codigo";
            $params[':codigo'] = $data['codigo'];
        }
        if (isset($data['nombre'])) {
            $fields[] = "nombre = :nombre";
            $params[':nombre'] = $data['nombre'];
        }
        if (isset($data['orden'])) {
            $fields[] = "orden = :orden";
            $params[':orden'] = (int) $data['orden'];
        }
        if (isset($data['activo'])) {
            $fields[] = "activo = :activo";
            $params[':activo'] = (int) $data['activo'];
        }

        if (empty($fields)) {
            return $this->findOpcionById($id);
        }

        $fields[] = "updated_at = datetime('now')";
        $sql = "UPDATE opciones_maestro SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findOpcionById($id);
    }

    public function deleteOpcion(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM opciones_maestro WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
