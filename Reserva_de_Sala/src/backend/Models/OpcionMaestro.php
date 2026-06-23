<?php
// Models/OpcionMaestro.php

namespace App\Models;

class OpcionMaestro
{
    public ?int $id = null;
    public string $maestro_codigo = '';
    public string $codigo = '';
    public string $nombre = '';
    public int $orden = 0;
    public bool $activo = true;
    public string $created_at = '';
    public string $updated_at = '';

    public static function fromRow(array $row): self
    {
        $model = new self();
        $model->id = (int) $row['id'];
        $model->maestro_codigo = $row['maestro_codigo'];
        $model->codigo = $row['codigo'];
        $model->nombre = $row['nombre'];
        $model->orden = (int) ($row['orden'] ?? 0);
        $model->activo = (bool) ($row['activo'] ?? true);
        $model->created_at = $row['created_at'] ?? '';
        $model->updated_at = $row['updated_at'] ?? '';
        return $model;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'maestro_codigo' => $this->maestro_codigo,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'orden' => $this->orden,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
