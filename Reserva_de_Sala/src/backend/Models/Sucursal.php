<?php
// Models/Sucursal.php

namespace App\Models;

class Sucursal
{
    public ?int $id = null;
    public string $nombre = '';
    public string $direccion = '';
    public string $created_at = '';
    public string $updated_at = '';

    public static function fromRow(array $row): self
    {
        $model = new self();
        $model->id = (int) $row['id'];
        $model->nombre = $row['nombre'];
        $model->direccion = $row['direccion'] ?? '';
        $model->created_at = $row['created_at'] ?? '';
        $model->updated_at = $row['updated_at'] ?? '';
        return $model;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
