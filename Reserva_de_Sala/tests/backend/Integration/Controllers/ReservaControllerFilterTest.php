<?php
namespace Tests\Backend\Integration\Controllers;

use App\Controllers\ReservaController;
use Tests\Backend\BaseTestCase;

/**
 * Tests complementarios de filtros para ReservaController
 * 
 * Cubre partes adicionales de: F-RES-012, F-RES-013, F-RES-014, F-RES-015
 */
class ReservaControllerFilterTest extends BaseTestCase
{
    private ReservaController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ReservaController(self::getDatabase());
    }

    /**
     * @test
     * @F-RES-016
     */
    public function testDisponibilidadMuestraSlotsCorrectos(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $admin = $this->createUsuario('Admin', 'admin@test.com', 'admin');

        $this->createReserva($sala['id'], $admin['id'], '2026-07-10 09:00:00', '2026-07-10 11:00:00');

        $service = new \App\Services\ReservaService(self::getDatabase());
        $disp = $service->getDisponibilidad($sala['id'], '2026-07-10');

        // Verificar slots específicos
        $slots09 = current(array_filter($disp['slots'], fn($s) => $s['hora_inicio'] === '09:00'));
        $slots10 = current(array_filter($disp['slots'], fn($s) => $s['hora_inicio'] === '10:00'));
        $slots11 = current(array_filter($disp['slots'], fn($s) => $s['hora_inicio'] === '11:00'));

        $this->assertFalse($slots09['disponible'], 'Slot 09:00 debe estar ocupado');
        $this->assertFalse($slots10['disponible'], 'Slot 10:00 debe estar ocupado');
        $this->assertTrue($slots11['disponible'], 'Slot 11:00 debe estar disponible');
    }

    /**
     * @test
     * @F-RES-020
     */
    public function testRecursosSalaAntesDeReservar(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $recurso = $this->createRecurso('Proyector', 'Proyector HD');
        $pizarra = $this->createRecurso('Pizarra', 'Pizarra blanca');

        $service = new \App\Services\SalaService(self::getDatabase());
        $service->asignarRecurso($sala['id'], $recurso['id'], 1);
        $service->asignarRecurso($sala['id'], $pizarra['id'], 2);

        $recursos = $service->getRecursos($sala['id']);

        $this->assertCount(2, $recursos);
        $nombres = array_column($recursos, 'nombre');
        $this->assertContains('Proyector', $nombres);
        $this->assertContains('Pizarra', $nombres);
    }
}
