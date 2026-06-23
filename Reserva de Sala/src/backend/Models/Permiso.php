<?php
// Models/Permiso.php

namespace App\Models;

class Permiso
{
    public ?int $id = null;
    public string $rol = '';
    public string $componente = '';
    public bool $permiso_lectura = false;
    public bool $permiso_creacion = false;
    public bool $permiso_actualizacion = false;
    public bool $permiso_eliminacion = false;
    public string $created_at = '';
    public string $updated_at = '';

    public static function fromRow(array $row): self
    {
        $model = new self();
        $model->id = (int) $row['id'];
        $model->rol = $row['rol'];
        $model->componente = $row['componente'];
        $model->permiso_lectura = (bool) ($row['permiso_lectura'] ?? false);
        $model->permiso_creacion = (bool) ($row['permiso_creacion'] ?? false);
        $model->permiso_actualizacion = (bool) ($row['permiso_actualizacion'] ?? false);
        $model->permiso_eliminacion = (bool) ($row['permiso_eliminacion'] ?? false);
        $model->created_at = $row['created_at'] ?? '';
        $model->updated_at = $row['updated_at'] ?? '';
        return $model;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'rol' => $this->rol,
            'componente' => $this->componente,
            'permiso_lectura' => $this->permiso_lectura,
            'permiso_creacion' => $this->permiso_creacion,
            'permiso_actualizacion' => $this->permiso_actualizacion,
            'permiso_eliminacion' => $this->permiso_eliminacion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
