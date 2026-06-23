<?php
// Models/SalaRecurso.php

namespace App\Models;

class SalaRecurso
{
    public int $sala_id;
    public int $recurso_id;
    public int $cantidad = 1;

    // Joined fields
    public ?string $recurso_nombre = null;

    public static function fromRow(array $row): self
    {
        $model = new self();
        $model->sala_id = (int) $row['sala_id'];
        $model->recurso_id = (int) $row['recurso_id'];
        $model->cantidad = (int) ($row['cantidad'] ?? 1);
        $model->recurso_nombre = $row['recurso_nombre'] ?? null;
        return $model;
    }

    public function toArray(): array
    {
        return [
            'sala_id' => $this->sala_id,
            'recurso_id' => $this->recurso_id,
            'cantidad' => $this->cantidad,
            'recurso_nombre' => $this->recurso_nombre,
        ];
    }
}
