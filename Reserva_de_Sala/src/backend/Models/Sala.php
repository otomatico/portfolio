<?php
// Models/Sala.php

namespace App\Models;

class Sala
{
    public ?int $id = null;
    public string $nombre = '';
    public int $aforo = 1;
    public string $descripcion = '';
    public int $sucursal_id;
    public string $created_at = '';
    public string $updated_at = '';

    // Joined fields
    public ?string $sucursal_nombre = null;
    public array $recursos = [];

    public static function fromRow(array $row): self
    {
        $model = new self();
        $model->id = (int) $row['id'];
        $model->nombre = $row['nombre'];
        $model->aforo = (int) ($row['aforo'] ?? 1);
        $model->descripcion = $row['descripcion'] ?? '';
        $model->sucursal_id = (int) $row['sucursal_id'];
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
            'aforo' => $this->aforo,
            'descripcion' => $this->descripcion,
            'sucursal_id' => $this->sucursal_id,
            'sucursal_nombre' => $this->sucursal_nombre,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        if (!empty($this->recursos)) {
            $data['recursos'] = $this->recursos;
        }
        return $data;
    }
}
