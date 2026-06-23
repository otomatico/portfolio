<?php
namespace Tests\Backend\Unit\Services;

use App\Services\RecursoService;
use Tests\Backend\BaseTestCase;

/**
 * Tests para RecursoService
 * 
 * Cubre: F-REC-001, F-REC-002, F-REC-003, F-REC-004, F-REC-005
 */
class RecursoServiceTest extends BaseTestCase
{
    private RecursoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RecursoService(self::getDatabase());
    }

    // ─── F-REC-001: Admin crea un recurso ───

    /**
     * @test
     * @F-REC-001
     */
    public function testCrearRecursoExitosamente(): void
    {
        $result = $this->service->crear([
            'nombre' => 'Equipo de Audio',
            'descripcion' => 'Sistema de sonido profesional',
        ]);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('Equipo de Audio', $result['nombre']);
        $this->assertEquals('Sistema de sonido profesional', $result['descripcion']);
    }

    /**
     * @test
     * @F-REC-001
     */
    public function testNuevoRecursoApareceEnListado(): void
    {
        $this->service->crear([
            'nombre' => 'Equipo de Audio',
            'descripcion' => 'Sistema de sonido profesional',
        ]);

        $listado = $this->service->listar();
        $nombres = array_column($listado, 'nombre');
        $this->assertContains('Equipo de Audio', $nombres);
    }

    // ─── F-REC-002: Admin lista todos los recursos ───

    /**
     * @test
     * @F-REC-002
     */
    public function testListarRecursos(): void
    {
        $this->createRecurso('Proyector');
        $this->createRecurso('Pizarra');
        $this->createRecurso('TV');

        $listado = $this->service->listar();

        $this->assertCount(3, $listado);
        $nombres = array_column($listado, 'nombre');
        $this->assertContains('Proyector', $nombres);
        $this->assertContains('Pizarra', $nombres);
        $this->assertContains('TV', $nombres);
    }

    // ─── F-REC-003: Admin ve detalle de un recurso ───

    /**
     * @test
     * @F-REC-003
     */
    public function testObtenerRecursoPorId(): void
    {
        $creado = $this->createRecurso('Proyector', 'Proyector HD 1080p');

        $result = $this->service->obtenerPorId($creado['id']);

        $this->assertEquals($creado['id'], $result['id']);
        $this->assertEquals('Proyector', $result['nombre']);
        $this->assertEquals('Proyector HD 1080p', $result['descripcion']);
    }

    /**
     * @test
     */
    public function testObtenerRecursoInexistenteLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Recurso no encontrado');

        $this->service->obtenerPorId(999);
    }

    // ─── F-REC-004: Admin edita un recurso ───

    /**
     * @test
     * @F-REC-004
     */
    public function testActualizarRecurso(): void
    {
        $creado = $this->createRecurso('Proyector', 'Proyector HD 1080p');

        $actualizado = $this->service->actualizar($creado['id'], [
            'descripcion' => 'Proyector 4K',
        ]);

        $this->assertEquals('Proyector 4K', $actualizado['descripcion']);
        $this->assertEquals('Proyector', $actualizado['nombre']);
    }

    // ─── F-REC-005: Admin elimina un recurso ───

    /**
     * @test
     * @F-REC-005
     */
    public function testEliminarRecurso(): void
    {
        $creado = $this->createRecurso('TV', 'TV 55 pulgadas');

        $this->service->eliminar($creado['id']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Recurso no encontrado');
        $this->service->obtenerPorId($creado['id']);
    }

    /**
     * @test
     * @F-REC-005
     */
    public function testRecursoEliminadoNoApareceEnListado(): void
    {
        $creado = $this->createRecurso('TV', 'TV 55 pulgadas');

        $this->service->eliminar($creado['id']);

        $listado = $this->service->listar();
        $this->assertCount(0, $listado);
    }

    // ─── Validaciones ───

    /**
     * @test
     */
    public function testCrearRecursoSinNombreLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El nombre es obligatorio');

        $this->service->crear(['descripcion' => 'Sin nombre']);
    }
}
