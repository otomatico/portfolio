<?php
// Services/ReservaService.php

namespace App\Services;

use App\Config\Database;
use App\Log\Logger;
use App\Repositories\ReservaRepository;
use App\Repositories\SalaRepository;
use App\Repositories\SucursalRepository;
use DateTime;
use RuntimeException;

class ReservaService
{
    private ReservaRepository $repository;
    private SalaRepository $salaRepository;
    private SucursalRepository $sucursalRepository;
    private Logger $logger;

    public function __construct(Database $database)
    {
        $this->repository = new ReservaRepository($database);
        $this->salaRepository = new SalaRepository($database);
        $this->sucursalRepository = new SucursalRepository($database);
        $this->logger = new Logger();
    }

    public function listar(array $filters = []): array
    {
        $reservas = $this->repository->findAll($filters);
        return array_map(fn($r) => $r->toArray(), $reservas);
    }

    public function obtenerPorId(int $id): array
    {
        $reserva = $this->repository->findById($id);
        if (!$reserva) {
            throw new RuntimeException('Reserva no encontrada');
        }
        return $reserva->toArray();
    }

    public function crear(array $data, array $jwtPayload): array
    {
        // Validar campos obligatorios
        if (empty($data['sala_id'])) {
            throw new RuntimeException('La sala es obligatoria');
        }
        if (empty($data['fecha_inicio'])) {
            throw new RuntimeException('La fecha de inicio es obligatoria');
        }
        if (empty($data['fecha_fin'])) {
            throw new RuntimeException('La fecha de fin es obligatoria');
        }

        // Verificar que la sala exista
        $sala = $this->salaRepository->findById((int) $data['sala_id']);
        if (!$sala) {
            throw new RuntimeException('Sala no encontrada');
        }

        // Validar que la fecha de inicio sea anterior a la fecha de fin
        $inicio = new DateTime($data['fecha_inicio']);
        $fin = new DateTime($data['fecha_fin']);

        if ($inicio >= $fin) {
            throw new RuntimeException('La fecha de inicio debe ser anterior a la fecha de fin');
        }

        // Validar que no sea en el pasado (RN-08)
        $now = new DateTime();
        if ($inicio < $now) {
            throw new RuntimeException('No se pueden crear reservas en el pasado');
        }

        // Verificar disponibilidad (RN-05)
        if ($this->repository->hasConflict((int) $data['sala_id'], $data['fecha_inicio'], $data['fecha_fin'])) {
            throw new RuntimeException('La sala no está disponible en el horario solicitado');
        }

        // Asignar usuario autenticado
        $data['usuario_id'] = (int) $jwtPayload['sub'];

        $reserva = $this->repository->create($data);

        $this->logger->info('Reserva creada', [
            'id' => $reserva->id,
            'sala_id' => $reserva->sala_id,
            'usuario_id' => $reserva->usuario_id,
            'fecha_inicio' => $reserva->fecha_inicio,
            'fecha_fin' => $reserva->fecha_fin,
        ]);

        return $reserva->toArray();
    }

    public function cancelar(int $id, array $jwtPayload): array
    {
        $reserva = $this->repository->findById($id);
        if (!$reserva) {
            throw new RuntimeException('Reserva no encontrada');
        }

        // Si es coordinador, verificar que la reserva le pertenece (RN-06)
        if ($jwtPayload['rol'] === 'coordinador' && (int) $reserva->usuario_id !== (int) $jwtPayload['sub']) {
            throw new RuntimeException('No tienes permiso para cancelar esta reserva');
        }

        // Validar que sea una reserva futura (RN-08)
        $fechaInicio = new DateTime($reserva->fecha_inicio);
        $now = new DateTime();
        if ($fechaInicio <= $now) {
            throw new RuntimeException('Solo se pueden cancelar reservas futuras');
        }

        $reserva = $this->repository->updateEstado($id, 'cancelada');

        $this->logger->info('Reserva cancelada', [
            'id' => $reserva->id,
            'sala_id' => $reserva->sala_id,
        ]);

        return $reserva->toArray();
    }

    public function getDisponibilidad(int $salaId, string $fecha): array
    {
        $sala = $this->salaRepository->findById($salaId);
        if (!$sala) {
            throw new RuntimeException('Sala no encontrada');
        }

        $ocupados = $this->repository->getDisponibilidad($salaId, $fecha);

        // Generar slots de 1 hora para el día completo
        $slots = [];
        $horaInicio = 7; // 07:00
        $horaFin = 22;   // 22:00

        for ($h = $horaInicio; $h < $horaFin; $h++) {
            $start = sprintf('%s %02d:00:00', $fecha, $h);
            $end = sprintf('%s %02d:00:00', $fecha, $h + 1);

            $ocupado = false;
            foreach ($ocupados as $ocupado_r) {
                if ($start < $ocupado_r['fecha_fin'] && $end > $ocupado_r['fecha_inicio']) {
                    $ocupado = true;
                    break;
                }
            }

            $slots[] = [
                'hora_inicio' => sprintf('%02d:00', $h),
                'hora_fin' => sprintf('%02d:00', $h + 1),
                'disponible' => !$ocupado,
            ];
        }

        return [
            'sala_id' => $salaId,
            'sala_nombre' => $sala->nombre,
            'fecha' => $fecha,
            'ocupados' => $ocupados,
            'slots' => $slots,
        ];
    }
}
