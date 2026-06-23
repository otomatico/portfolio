<?php
namespace Tests\Backend\Unit\Services;

use App\Services\SucursalService;
use Tests\Backend\BaseTestCase;

/**
 * Tests para SucursalService
 * 
 * Cubre: F-SUC-001, F-SUC-002, F-SUC-003, F-SUC-004, F-SUC-005, F-SUC-006
 */
class SucursalServiceTest extends BaseTestCase
{
    private SucursalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SucursalService(self::getDatabase());
    }

    // ─── F-SUC-001: Admin crea sucursal exitosamente ───

    /**
     * @test
     * @F-SUC-001
     */
    public function testCrearSucursalExitosamente(): void
    {
        $result = $this->service->crear([
            'nombre' => 'Sucursal Sur',
            'direccion' => 'Av. del Sur 789',
        ]);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('Sucursal Sur', $result['nombre']);
        $this->assertEquals('Av. del Sur 789', $result['direccion']);
    }

    /**
     * @test
     * @F-SUC-001
     */
    public function testNuevaSucursalApareceEnListado(): void
    {
        $this->service->crear([
            'nombre' => 'Sucursal Sur',
            'direccion' => 'Av. del Sur 789',
        ]);

        $listado = $this->service->listar();
        $nombres = array_column($listado, 'nombre');
        $this->assertContains('Sucursal Sur', $nombres);
    }

    // ─── F-SUC-002: Admin lista todas las sucursales ───

    /**
     * @test
     * @F-SUC-002
     */
    public function testListarSucursales(): void
    {
        $this->createSucursal('Sucursal Centro', 'Av. Principal 123');
        $this->createSucursal('Sucursal Norte', 'Calle Secundaria 456');

        $listado = $this->service->listar();

        $this->assertCount(2, $listado);
        $nombres = array_column($listado, 'nombre');
        $this->assertContains('Sucursal Centro', $nombres);
        $this->assertContains('Sucursal Norte', $nombres);
    }

    // ─── F-SUC-003: Admin ve detalle de una sucursal ───

    /**
     * @test
     * @F-SUC-003
     */
    public function testObtenerSucursalPorId(): void
    {
        $creada = $this->createSucursal('Sucursal Centro', 'Av. Principal 123');

        $result = $this->service->obtenerPorId($creada['id']);

        $this->assertEquals($creada['id'], $result['id']);
        $this->assertEquals('Sucursal Centro', $result['nombre']);
        $this->assertEquals('Av. Principal 123', $result['direccion']);
    }

    /**
     * @test
     */
    public function testObtenerSucursalInexistenteLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sucursal no encontrada');

        $this->service->obtenerPorId(999);
    }

    // ─── F-SUC-004: Admin edita una sucursal ───

    /**
     * @test
     * @F-SUC-004
     */
    public function testActualizarSucursal(): void
    {
        $creada = $this->createSucursal('Sucursal Centro', 'Av. Principal 123');

        $actualizada = $this->service->actualizar($creada['id'], [
            'nombre' => 'Sucursal Centro Renovada',
        ]);

        $this->assertEquals('Sucursal Centro Renovada', $actualizada['nombre']);
        $this->assertEquals('Av. Principal 123', $actualizada['direccion']);
    }

    /**
     * @test
     * @F-SUC-004
     */
    public function testCambiosReflejadosEnListado(): void
    {
        $creada = $this->createSucursal('Sucursal Centro', 'Av. Principal 123');

        $this->service->actualizar($creada['id'], ['nombre' => 'Sucursal Centro Renovada']);

        $listado = $this->service->listar();
        $nombres = array_column($listado, 'nombre');
        $this->assertContains('Sucursal Centro Renovada', $nombres);
        $this->assertNotContains('Sucursal Centro', $nombres);
    }

    // ─── F-SUC-005: Admin elimina una sucursal ───

    /**
     * @test
     * @F-SUC-005
     */
    public function testEliminarSucursal(): void
    {
        $creada = $this->createSucursal('Sucursal Norte', 'Calle Secundaria 456');

        $this->service->eliminar($creada['id']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sucursal no encontrada');
        $this->service->obtenerPorId($creada['id']);
    }

    /**
     * @test
     * @F-SUC-005
     */
    public function testSucursalEliminadaNoApareceEnListado(): void
    {
        $creada = $this->createSucursal('Sucursal Norte', 'Calle Secundaria 456');

        $this->service->eliminar($creada['id']);

        $listado = $this->service->listar();
        $nombres = array_column($listado, 'nombre');
        $this->assertNotContains('Sucursal Norte', $nombres);
    }

    // ─── F-SUC-006: Validación nombre obligatorio ───

    /**
     * @test
     * @F-SUC-006
     */
    public function testCrearSucursalSinNombreLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El nombre es obligatorio');

        $this->service->crear(['direccion' => 'Sin nombre']);
    }

    /**
     * @test
     */
    public function testCrearSucursalConNombreVacioLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El nombre es obligatorio');

        $this->service->crear(['nombre' => '', 'direccion' => 'Alguna dirección']);
    }
}
