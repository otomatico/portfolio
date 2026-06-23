<?php
// Models/Usuario.php

namespace App\Models;

class Usuario
{
    public ?int $id = null;
    public string $nombre = '';
    public string $email = '';
    public string $password = '';
    public string $rol = 'coordinador';
    public ?int $sucursal_id = null;
    public string $created_at = '';
    public string $updated_at = '';

    // Joined fields
    public ?string $sucursal_nombre = null;

    public static function fromRow(array $row): self
    {
        $model = new self();
        $model->id = (int) $row['id'];
        $model->nombre = $row['nombre'];
        $model->email = $row['email'];
        $model->password = $row['password'];
        $model->rol = $row['rol'];
        $model->sucursal_id = isset($row['sucursal_id']) && $row['sucursal_id'] !== null ? (int) $row['sucursal_id'] : null;
        $model->created_at = $row['created_at'] ?? '';
        $model->updated_at = $row['updated_at'] ?? '';
        $model->sucursal_nombre = $row['sucursal_nombre'] ?? null;
        return $model;
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'rol' => $this->rol,
            'sucursal_id' => $this->sucursal_id,
            'sucursal_nombre' => $this->sucursal_nombre,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        return $data;
    }
}
