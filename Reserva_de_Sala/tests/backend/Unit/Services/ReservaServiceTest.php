<?php
namespace Tests\Backend\Unit\Services;

use App\Services\ReservaService;
use Tests\Backend\BaseTestCase;

/**
 * Tests para ReservaService
 * 
 * Cubre: F-RES-001, F-RES-002, F-RES-003, F-RES-004, F-RES-005, F-RES-006,
 *        F-RES-007, F-RES-008, F-RES-009, F-RES-010, F-RES-011, F-RES-012,
 *        F-RES-013, F-RES-014, F-RES-015, F-RES-016, F-RES-017, F-RES-018,
 *        F-RES-019, F-RES-020
 */
class ReservaServiceTest extends BaseTestCase
{
    private ReservaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReservaService(self::getDatabase());
    }

    private function getAdminPayload(int $userId): array
    {
        return ['sub' => $userId, 'rol' => 'admin', 'email' => 'admin@test.com'];
    }

    private function getCoordPayload(int $userId): array
    {
        return ['sub' => $userId, 'rol' => 'coordinador', 'email' => 'coord@test.com'];
    }

    // ─── F-RES-001: Admin lista todas las reservas ───

    /**
     * @test
     * @F-RES-001
     */
    public function testAdminListaTodasLasReservas(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');
        $coord1 = $this->createUsuario('Coord1', 'coord1@test.com', 'coordinador');
        $coord2 = $this->createUsuario('Coord2', 'coord2@test.com', 'coordinador');

        $this->createReserva($sala['id'], $coord1['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00');
        $this->createReserva($sala['id'], $coord2['id'], '2026-07-11 09:00:00', '2026-07-11 11:00:00');

        $reservas = $this->service->listar();

        $this->assertCount(2, $reservas);
    }

    // ─── F-RES-002: Coordinador lista solo sus reservas ───

    /**
     * @test
     * @F-RES-002
     */
    public function testCoordinadorListaSoloSusReservas(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $coord1 = $this->createUsuario('Coord1', 'coord1@test.com', 'coordinador');
        $coord2 = $this->createUsuario('Coord2', 'coord2@test.com', 'coordinador');

        $this->createReserva($sala['id'], $coord1['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00');
        $this->createReserva($sala['id'], $coord2['id'], '2026-07-11 09:00:00', '2026-07-11 11:00:00');

        $reservas = $this->service->listar(['usuario_id' => $coord1['id']]);

        $this->assertCount(1, $reservas);
        $this->assertEquals($coord1['id'], $reservas[0]['usuario_id']);
    }

    // ─── F-RES-003: Admin crea una reserva ───

    /**
     * @test
     * @F-RES-003
     */
    public function testAdminCreaReservaExitosamente(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $result = $this->service->crear([
            'sala_id' => $sala['id'],
            'fecha_inicio' => '2026-07-10 09:00:00',
            'fecha_fin' => '2026-07-10 11:00:00',
        ], $this->getAdminPayload($admin['id']));

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('confirmada', $result['estado']);
        $this->assertEquals($sala['id'], $result['sala_id']);
        $this->assertEquals($admin['id'], $result['usuario_id']);
    }

    // ─── F-RES-004: Coordinador crea una reserva ───

    /**
     * @test
     * @F-RES-004
     */
    public function testCoordinadorCreaReservaExitosamente(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $coord = $this->createUsuario('Coord1', 'coord1@test.com', 'coordinador');

        $result = $this->service->crear([
            'sala_id' => $sala['id'],
            'fecha_inicio' => '2026-07-10 14:00:00',
            'fecha_fin' => '2026-07-10 16:00:00',
        ], $this->getCoordPayload($coord['id']));

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('confirmada', $result['estado']);
        $this->assertEquals($coord['id'], $result['usuario_id']);
    }

    // ─── F-RES-005: No permite solapamiento ───

    /**
     * @test
     * @F-RES-005
     */
    public function testNoPermiteReservaSolapada(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->createReserva($sala['id'], $admin['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('La sala no está disponible en el horario solicitado');

        $this->service->crear([
            'sala_id' => $sala['id'],
            'fecha_inicio' => '2026-07-10 10:00:00',
            'fecha_fin' => '2026-07-10 12:00:00',
        ], $this->getAdminPayload($admin['id']));
    }

    // ─── F-RES-006: Permite reserva no solapada en misma sala ───

    /**
     * @test
     * @F-RES-006
     */
    public function testPermiteReservaNoSolapadaMismaSala(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->createReserva($sala['id'], $admin['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00');

        $result = $this->service->crear([
            'sala_id' => $sala['id'],
            'fecha_inicio' => '2026-07-10 11:00:00',
            'fecha_fin' => '2026-07-10 13:00:00',
        ], $this->getAdminPayload($admin['id']));

        $this->assertArrayHasKey('id', $result);
    }

    // ─── F-RES-007: Permite reserva misma fecha sala diferente ───

    /**
     * @test
     * @F-RES-007
     */
    public function testPermiteReservaMismaFechaSalaDiferente(): void
    {
        $sucursal = $this->createSucursal();
        $salaA = $this->createSala('Sala A', 20, $sucursal['id']);
        $salaB = $this->createSala('Sala B', 15, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->createReserva($salaA['id'], $admin['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00');

        $result = $this->service->crear([
            'sala_id' => $salaB['id'],
            'fecha_inicio' => '2026-07-10 09:00:00',
            'fecha_fin' => '2026-07-10 11:00:00',
        ], $this->getAdminPayload($admin['id']));

        $this->assertArrayHasKey('id', $result);
    }

    // ─── F-RES-008: Cancelar reserva futura ───

    /**
     * @test
     * @F-RES-008
     */
    public function testCancelarReservaFutura(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $reserva = $this->createReserva(
            $sala['id'], $admin['id'],
            '2026-08-01 09:00:00', '2026-08-01 11:00:00'
        );

        $result = $this->service->cancelar($reserva['id'], $this->getAdminPayload($admin['id']));

        $this->assertEquals('cancelada', $result['estado']);
    }

    // ─── F-RES-009: No cancelar reserva pasada ───

    /**
     * @test
     * @F-RES-009
     */
    public function testNoPermiteCancelarReservaPasada(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $reserva = $this->createReserva(
            $sala['id'], $admin['id'],
            '2025-01-01 09:00:00', '2025-01-01 11:00:00'
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Solo se pueden cancelar reservas futuras');

        $this->service->cancelar($reserva['id'], $this->getAdminPayload($admin['id']));
    }

    // ─── F-RES-010: Coordinador no cancela reserva de otro ───

    /**
     * @test
     * @F-RES-010
     */
    public function testCoordinadorNoCancelaReservaDeOtro(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $coord1 = $this->createUsuario('Coord1', 'coord1@test.com', 'coordinador');
        $coord2 = $this->createUsuario('Coord2', 'coord2@test.com', 'coordinador');

        $reserva = $this->createReserva(
            $sala['id'], $coord2['id'],
            '2026-08-01 09:00:00', '2026-08-01 11:00:00'
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No tienes permiso para cancelar esta reserva');

        $this->service->cancelar($reserva['id'], $this->getCoordPayload($coord1['id']));
    }

    // ─── F-RES-011: Admin cancela reserva de cualquier usuario ───

    /**
     * @test
     * @F-RES-011
     */
    public function testAdminCancelaReservaDeCualquierUsuario(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');
        $coord = $this->createUsuario('Coord', 'coord@test.com', 'coordinador');

        $reserva = $this->createReserva(
            $sala['id'], $coord['id'],
            '2026-08-01 14:00:00', '2026-08-01 16:00:00'
        );

        $result = $this->service->cancelar($reserva['id'], $this->getAdminPayload($admin['id']));

        $this->assertEquals('cancelada', $result['estado']);
    }

    // ─── F-RES-012: Filtrar reservas por sala ───

    /**
     * @test
     * @F-RES-012
     */
    public function testFiltrarReservasPorSala(): void
    {
        $sucursal = $this->createSucursal();
        $salaA = $this->createSala('Sala A', 20, $sucursal['id']);
        $salaB = $this->createSala('Sala B', 15, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->createReserva($salaA['id'], $admin['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00');
        $this->createReserva($salaB['id'], $admin['id'], '2026-07-10 14:00:00', '2026-07-10 16:00:00');

        $reservas = $this->service->listar(['sala_id' => $salaA['id']]);

        $this->assertCount(1, $reservas);
        $this->assertEquals($salaA['id'], $reservas[0]['sala_id']);
    }

    // ─── F-RES-013: Filtrar reservas por sucursal ───

    /**
     * @test
     * @F-RES-013
     */
    public function testFiltrarReservasPorSucursal(): void
    {
        $sucursalCentro = $this->createSucursal('Sucursal Centro');
        $sucursalNorte = $this->createSucursal('Sucursal Norte');
        $salaA = $this->createSala('Sala A', 20, $sucursalCentro['id']);
        $salaB = $this->createSala('Sala B', 15, $sucursalNorte['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->createReserva($salaA['id'], $admin['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00');
        $this->createReserva($salaB['id'], $admin['id'], '2026-07-10 14:00:00', '2026-07-10 16:00:00');

        $reservas = $this->service->listar(['sucursal_id' => $sucursalCentro['id']]);

        $this->assertCount(1, $reservas);
    }

    // ─── F-RES-014: Filtrar reservas por estado ───

    /**
     * @test
     * @F-RES-014
     */
    public function testFiltrarReservasPorEstado(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->createReserva($sala['id'], $admin['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00', 'confirmada');
        $this->createReserva($sala['id'], $admin['id'], '2026-07-11 09:00:00', '2026-07-11 11:00:00', 'cancelada');

        $reservas = $this->service->listar(['estado' => 'cancelada']);

        $this->assertCount(1, $reservas);
        $this->assertEquals('cancelada', $reservas[0]['estado']);
    }

    // ─── F-RES-015: Filtrar reservas por rango de fechas ───

    /**
     * @test
     * @F-RES-015
     */
    public function testFiltrarReservasPorRangoFechas(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->createReserva($sala['id'], $admin['id'], '2026-07-05 09:00:00', '2026-07-05 11:00:00');
        $this->createReserva($sala['id'], $admin['id'], '2026-07-15 09:00:00', '2026-07-15 11:00:00');

        $reservas = $this->service->listar([
            'fecha_desde' => '2026-07-01',
            'fecha_hasta' => '2026-07-10',
        ]);

        $this->assertCount(1, $reservas);
    }

    // ─── F-RES-016: Consultar disponibilidad ───

    /**
     * @test
     * @F-RES-016
     */
    public function testConsultarDisponibilidad(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->createReserva($sala['id'], $admin['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00');

        $disponibilidad = $this->service->getDisponibilidad($sala['id'], '2026-07-10');

        $this->assertArrayHasKey('sala_id', $disponibilidad);
        $this->assertArrayHasKey('sala_nombre', $disponibilidad);
        $this->assertArrayHasKey('fecha', $disponibilidad);
        $this->assertArrayHasKey('ocupados', $disponibilidad);
        $this->assertArrayHasKey('slots', $disponibilidad);
        $this->assertEquals('2026-07-10', $disponibilidad['fecha']);
        $this->assertCount(1, $disponibilidad['ocupados']);

        // Verificar slots (07:00 - 22:00 = 15 slots)
        $this->assertCount(15, $disponibilidad['slots']);
    }

    // ─── F-RES-017: Admin ve detalle de cualquier reserva ───

    /**
     * @test
     * @F-RES-017
     */
    public function testAdminVeDetalleReserva(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $reserva = $this->createReserva($sala['id'], $admin['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00');

        $result = $this->service->obtenerPorId($reserva['id']);

        $this->assertEquals($reserva['id'], $result['id']);
        $this->assertEquals($sala['id'], $result['sala_id']);
        $this->assertEquals($admin['id'], $result['usuario_id']);
        $this->assertEquals('confirmada', $result['estado']);
    }

    // ─── Validaciones adicionales ───

    /**
     * @test
     */
    public function testCrearReservaSinSalaLanzaExcepcion(): void
    {
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('La sala es obligatoria');

        $this->service->crear([
            'fecha_inicio' => '2026-07-10 09:00:00',
            'fecha_fin' => '2026-07-10 11:00:00',
        ], $this->getAdminPayload($admin['id']));
    }

    /**
     * @test
     */
    public function testCrearReservaConFechaInicioMayorAFechaLanzaExcepcion(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('La fecha de inicio debe ser anterior a la fecha de fin');

        $this->service->crear([
            'sala_id' => $sala['id'],
            'fecha_inicio' => '2026-07-10 14:00:00',
            'fecha_fin' => '2026-07-10 12:00:00',
        ], $this->getAdminPayload($admin['id']));
    }
}
