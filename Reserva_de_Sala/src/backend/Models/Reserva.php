<?php
// Models/Reserva.php

namespace App\Models;

class Reserva
{
    public ?int $id = null;
    public int $sala_id;
    public int $usuario_id;
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public string $estado = 'confirmada';
    public string $created_at = '';
    public string $updated_at = '';

    // Joined fields
    public ?string $sala_nombre = null;
    public ?string $usuario_nombre = null;
    public ?string $usuario_email = null;
    public ?int $sucursal_id = null;
    public ?string $sucursal_nombre = null;

    public static function fromRow(array $row): self
    {
        $model = new self();
        $model->id = (int) $row['id'];
        $model->sala_id = (int) $row['sala_id'];
        $model->usuario_id = (int) $row['usuario_id'];
        $model->fecha_inicio = $row['fecha_inicio'];
        $model->fecha_fin = $row['fecha_fin'];
        $model->estado = $row['estado'];
        $model->created_at = $row['created_at'] ?? '';
        $model->updated_at = $row['updated_at'] ?? '';
        $model->sala_nombre = $row['sala_nombre'] ?? null;
        $model->usuario_nombre = $row['usuario_nombre'] ?? null;
        $model->usuario_email = $row['usuario_email'] ?? null;
        $model->sucursal_id = isset($row['sucursal_id']) ? (int) $row['sucursal_id'] : null;
        $model->sucursal_nombre = $row['sucursal_nombre'] ?? null;
        return $model;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sala_id' => $this->sala_id,
            'usuario_id' => $this->usuario_id,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'estado' => $this->estado,
            'sala_nombre' => $this->sala_nombre,
            'usuario_nombre' => $this->usuario_nombre,
            'usuario_email' => $this->usuario_email,
            'sucursal_id' => $this->sucursal_id,
            'sucursal_nombre' => $this->sucursal_nombre,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
