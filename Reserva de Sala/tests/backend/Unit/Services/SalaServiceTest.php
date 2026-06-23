<?php
namespace Tests\Backend\Unit\Services;

use App\Services\SalaService;
use Tests\Backend\BaseTestCase;

/**
 * Tests para SalaService
 * 
 * Cubre: F-SAL-001, F-SAL-002, F-SAL-003, F-SAL-004, F-SAL-005, F-SAL-006,
 *        F-SAL-007, F-SAL-008, F-SAL-009, F-SAL-010
 */
class SalaServiceTest extends BaseTestCase
{
    private SalaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SalaService(self::getDatabase());
    }

    // ─── F-SAL-001: Admin crea sala asociada a sucursal ───

    /**
     * @test
     * @F-SAL-001
     */
    public function testCrearSalaExitosamente(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');

        $result = $this->service->crear([
            'nombre' => 'Sala C',
            'aforo' => 25,
            'descripcion' => 'Planta baja',
            'sucursal_id' => $sucursal['id'],
        ]);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('Sala C', $result['nombre']);
        $this->assertEquals(25, $result['aforo']);
        $this->assertEquals('Planta baja', $result['descripcion']);
        $this->assertEquals($sucursal['id'], $result['sucursal_id']);
    }

    /**
     * @test
     * @F-SAL-001
     */
    public function testSalaApareceAsociadaASucursal(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $this->service->crear([
            'nombre' => 'Sala C',
            'aforo' => 25,
            'sucursal_id' => $sucursal['id'],
        ]);

        $listado = $this->service->listar();
        $this->assertCount(1, $listado);
        $this->assertEquals('Sala C', $listado[0]['nombre']);
        $this->assertEquals('Sucursal Centro', $listado[0]['sucursal_nombre']);
    }

    // ─── F-SAL-002: Admin lista todas las salas ───

    /**
     * @test
     * @F-SAL-002
     */
    public function testListarSalas(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $this->createSala('Sala A', 20, $sucursal['id']);
        $this->createSala('Sala B', 15, $sucursal['id']);

        $listado = $this->service->listar();

        $this->assertCount(2, $listado);
        $nombres = array_column($listado, 'nombre');
        $this->assertContains('Sala A', $nombres);
        $this->assertContains('Sala B', $nombres);
    }

    /**
     * @test
     * @F-SAL-002
     */
    public function testListarSalasConRecursos(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $recurso = $this->createRecurso('Proyector', 'Proyector HD');

        // Asignar recurso a sala
        $this->service->asignarRecurso($sala['id'], $recurso['id'], 1);

        $listado = $this->service->listar();
        $this->assertCount(1, $listado);
        $this->assertNotEmpty($listado[0]['recursos']);
    }

    // ─── F-SAL-003: Filtrar salas por sucursal ───

    /**
     * @test
     * @F-SAL-003
     */
    public function testFiltrarSalasPorSucursal(): void
    {
        $sucursalCentro = $this->createSucursal('Sucursal Centro');
        $sucursalNorte = $this->createSucursal('Sucursal Norte');
        $this->createSala('Sala A', 20, $sucursalCentro['id']);
        $this->createSala('Sala B', 15, $sucursalNorte['id']);

        $filtradas = $this->service->listar($sucursalCentro['id']);

        $this->assertCount(1, $filtradas);
        $this->assertEquals('Sala A', $filtradas[0]['nombre']);
    }

    // ─── F-SAL-004: Ver detalle de una sala con sus recursos ───

    /**
     * @test
     * @F-SAL-004
     */
    public function testVerDetalleSala(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $sala = $this->createSala('Sala A', 20, $sucursal['id'], 'Sala principal');

        $result = $this->service->obtenerPorId($sala['id']);

        $this->assertEquals('Sala A', $result['nombre']);
        $this->assertEquals(20, $result['aforo']);
        $this->assertEquals('Sala principal', $result['descripcion']);
        $this->assertEquals($sucursal['id'], $result['sucursal_id']);
        $this->assertEquals('Sucursal Centro', $result['sucursal_nombre']);
    }

    // ─── F-SAL-005: Admin edita una sala ───

    /**
     * @test
     * @F-SAL-005
     */
    public function testActualizarSala(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);

        $actualizada = $this->service->actualizar($sala['id'], ['aforo' => 30]);

        $this->assertEquals(30, $actualizada['aforo']);
        $this->assertEquals('Sala A', $actualizada['nombre']);
    }

    // ─── F-SAL-006: Admin elimina una sala ───

    /**
     * @test
     * @F-SAL-006
     */
    public function testEliminarSala(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $sala = $this->createSala('Sala B', 15, $sucursal['id']);

        $this->service->eliminar($sala['id']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sala no encontrada');
        $this->service->obtenerPorId($sala['id']);
    }

    /**
     * @test
     * @F-SAL-006
     */
    public function testSalaEliminadaNoApareceEnListado(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $sala = $this->createSala('Sala B', 15, $sucursal['id']);

        $this->service->eliminar($sala['id']);

        $listado = $this->service->listar();
        $this->assertCount(0, $listado);
    }

    // ─── F-SAL-007: Admin asigna recurso a sala ───

    /**
     * @test
     * @F-SAL-007
     */
    public function testAsignarRecursoASala(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $recurso = $this->createRecurso('Proyector', 'Proyector HD');

        $recursos = $this->service->asignarRecurso($sala['id'], $recurso['id'], 1);

        $this->assertCount(1, $recursos);
        $this->assertEquals($recurso['id'], $recursos[0]['recurso_id']);
        $this->assertEquals('Proyector', $recursos[0]['nombre']);
        $this->assertEquals(1, $recursos[0]['cantidad']);
    }

    /**
     * @test
     * @F-SAL-007
     */
    public function testRecursoApareceEnDetalleSala(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $recurso = $this->createRecurso('Proyector', 'Proyector HD');

        $this->service->asignarRecurso($sala['id'], $recurso['id'], 1);

        $detalle = $this->service->obtenerPorId($sala['id']);
        $this->assertNotEmpty($detalle['recursos']);
        $this->assertEquals('Proyector', $detalle['recursos'][0]['nombre']);
    }

    // ─── F-SAL-008: Admin desasigna recurso de sala ───

    /**
     * @test
     * @F-SAL-008
     */
    public function testDesasignarRecursoDeSala(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $recurso = $this->createRecurso('Proyector', 'Proyector HD');

        $this->service->asignarRecurso($sala['id'], $recurso['id'], 1);
        $recursos = $this->service->desasignarRecurso($sala['id'], $recurso['id']);

        $this->assertCount(0, $recursos);
    }

    /**
     * @test
     * @F-SAL-008
     */
    public function testRecursoDesasignadoNoApareceEnSala(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $recurso = $this->createRecurso('Proyector', 'Proyector HD');

        $this->service->asignarRecurso($sala['id'], $recurso['id'], 1);
        $this->service->desasignarRecurso($sala['id'], $recurso['id']);

        $detalle = $this->service->obtenerPorId($sala['id']);
        $this->assertEmpty($detalle['recursos']);
    }

    // ─── F-SAL-009: Validación sucursal obligatoria ───

    /**
     * @test
     * @F-SAL-009
     */
    public function testCrearSalaSinSucursalLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('La sucursal es obligatoria');

        $this->service->crear(['nombre' => 'Sala X']);
    }

    // ─── F-SAL-010: Validación nombre obligatorio ───

    /**
     * @test
     * @F-SAL-010
     */
    public function testCrearSalaSinNombreLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El nombre es obligatorio');

        $this->service->crear(['sucursal_id' => 1]);
    }

    // ─── Tests adicionales: casos borde ───

    /**
     * @test
     */
    public function testObtenerSalaInexistenteLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sala no encontrada');

        $this->service->obtenerPorId(999);
    }

    /**
     * @test
     */
    public function testAsignarRecursoASalaInexistenteLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sala no encontrada');

        $this->service->asignarRecurso(999, 1, 1);
    }

    /**
     * @test
     */
    public function testAsignarRecursoInexistenteLanzaExcepcion(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Recurso no encontrado');

        $this->service->asignarRecurso($sala['id'], 999, 1);
    }
}
