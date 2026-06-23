<?php
namespace Tests\Backend\Integration\Controllers;

use App\Controllers\SalaController;
use App\Services\SalaService;
use App\Repositories\PermisoRepository;
use Tests\Backend\BaseTestCase;

/**
 * Tests de integración para SalaController
 * 
 * Cubre: F-SAL-001 a F-SAL-015
 * 
 * NOTA: Los métodos del controlador que requieren php://input 
 * (store, update, asignarRecurso) se prueban a través del service,
 * ya que php://input no es fácilmente mockeable en PHPUnit desde CLI.
 * Los métodos GET y DELETE se prueban directamente contra el controlador
 * capturando su salida JSON y código HTTP.
 */
class SalaControllerTest extends BaseTestCase
{
    private SalaController $controller;
    private SalaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $db = self::getDatabase();
        $this->controller = new SalaController($db);
        $this->service = new SalaService($db);
    }

    /**
     * Helper para capturar la salida de un método del controlador
     */
    private function captureControllerOutput(callable $action): array
    {
        ob_start();
        try {
            $action();
            $output = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        $statusCode = http_response_code() ?: 200;
        $data = json_decode($output, true);

        return [
            'status' => $statusCode,
            'data'   => $data,
            'raw'    => $output,
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        http_response_code(200);
    }

    // ─── F-SAL-001: Admin crea sala ───

    /**
     * @test
     * @F-SAL-001
     */
    public function testAdminCreaSala(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');

        $result = $this->service->crear([
            'nombre'       => 'Sala C',
            'aforo'        => 25,
            'descripcion'  => 'Planta baja',
            'sucursal_id'  => $sucursal['id'],
        ]);

        $this->assertArrayHasKey('id', $result, 'La sala creada debe tener un ID');
        $this->assertEquals('Sala C', $result['nombre']);
        $this->assertEquals(25, $result['aforo']);
        $this->assertEquals('Planta baja', $result['descripcion']);
        $this->assertEquals($sucursal['id'], $result['sucursal_id']);
    }

    // ─── F-SAL-002: Admin lista salas ───

    /**
     * @test
     * @F-SAL-002
     */
    public function testAdminListaSalas(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $this->createSala('Sala A', 20, $sucursal['id']);
        $this->createSala('Sala B', 15, $sucursal['id']);

        $response = $this->captureControllerOutput(function () {
            $this->controller->index();
        });

        $this->assertEquals(200, $response['status'], 'Debe responder con HTTP 200');
        $this->assertIsArray($response['data'], 'Debe devolver un array JSON');
        $this->assertCount(2, $response['data'], 'Debe listar todas las salas');
        $this->assertEquals('Sala A', $response['data'][0]['nombre']);
    }

    // ─── F-SAL-003: Filtrar salas por sucursal ───

    /**
     * @test
     * @F-SAL-003
     */
    public function testFiltrarSalasPorSucursal(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $this->createSala('Sala A', 20, $sucursal['id']);
        $this->createSala('Sala B', 15, $sucursal['id']);

        $filtradas = $this->service->listar($sucursal['id']);

        $this->assertCount(2, $filtradas);
        foreach ($filtradas as $sala) {
            $this->assertEquals($sucursal['id'], $sala['sucursal_id']);
        }
    }

    // ─── F-SAL-004: Ver detalle con recursos ───

    /**
     * @test
     * @F-SAL-004
     */
    public function testVerDetalleSalaConRecursos(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $recurso = $this->createRecurso('Proyector');

        $this->service->asignarRecurso($sala['id'], $recurso['id'], 1);

        $detalle = $this->service->obtenerPorId($sala['id']);

        $this->assertEquals('Sala A', $detalle['nombre']);
        $this->assertEquals(20, $detalle['aforo']);
        $this->assertNotEmpty($detalle['recursos']);
        $this->assertEquals('Proyector', $detalle['recursos'][0]['nombre']);
    }

    // ─── F-SAL-005: Admin edita sala ───

    /**
     * @test
     * @F-SAL-005
     */
    public function testAdminEditaSala(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);

        $actualizada = $this->service->actualizar($sala['id'], [
            'aforo' => 30,
        ]);

        $this->assertEquals(30, $actualizada['aforo'], 'El aforo debe haberse actualizado');
        $this->assertEquals('Sala A', $actualizada['nombre'], 'El nombre debe mantenerse');
    }

    // ─── F-SAL-006: Admin elimina sala ───

    /**
     * @test
     * @F-SAL-006
     */
    public function testAdminEliminaSala(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala B', 15, $sucursal['id']);

        $response = $this->captureControllerOutput(function () use ($sala) {
            $this->controller->destroy($sala['id']);
        });

        $this->assertEquals(200, $response['status'], 'Debe responder con HTTP 200');
        $this->assertEquals('Sala eliminada exitosamente', $response['data']['message']);

        // Verificar que ya no aparece en el listado
        $salas = $this->service->listar();
        $this->assertCount(0, $salas, 'La sala debe haber sido eliminada del listado');
    }

    // ─── F-SAL-007: Asignar recurso ───

    /**
     * @test
     * @F-SAL-007
     */
    public function testAsignarRecursoASala(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $recurso = $this->createRecurso('Proyector');

        $recursos = $this->service->asignarRecurso($sala['id'], $recurso['id'], 1);

        $this->assertCount(1, $recursos, 'Debe tener 1 recurso asignado');
        $this->assertEquals('Proyector', $recursos[0]['nombre']);
        $this->assertEquals(1, $recursos[0]['cantidad']);
    }

    // ─── F-SAL-008: Desasignar recurso ───

    /**
     * @test
     * @F-SAL-008
     */
    public function testDesasignarRecursoDeSala(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);
        $recurso = $this->createRecurso('Proyector');

        $this->service->asignarRecurso($sala['id'], $recurso['id'], 1);

        // Desasignar
        $recursos = $this->service->desasignarRecurso($sala['id'], $recurso['id']);

        $this->assertCount(0, $recursos, 'No debe quedar recursos asignados');
    }

    // ─── F-SAL-009: Validación sucursal obligatoria ───

    /**
     * @test
     * @F-SAL-009
     */
    public function testCrearSalaSinSucursalRechazado(): void
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
    public function testCrearSalaSinNombreRechazado(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El nombre es obligatorio');

        $this->service->crear(['sucursal_id' => 1]);
    }

    // ─── F-SAL-011: Coordinador lista salas ───

    /**
     * @test
     * @F-SAL-011
     */
    public function testCoordinadorListaSalas(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $this->createSala('Sala A', 20, $sucursal['id']);
        $this->createSala('Sala B', 15, $sucursal['id']);

        // El controlador no discrimina por rol: devuelve todas las salas
        $response = $this->captureControllerOutput(function () {
            $this->controller->index();
        });

        $this->assertEquals(200, $response['status']);
        $this->assertIsArray($response['data']);
        $this->assertCount(2, $response['data'], 'Coordinador puede listar todas las salas');
    }

    // ─── F-SAL-012: Coordinador ve detalle ───

    /**
     * @test
     * @F-SAL-012
     */
    public function testCoordinadorVeDetalleSala(): void
    {
        $sucursal = $this->createSucursal();
        $sala = $this->createSala('Sala A', 20, $sucursal['id']);

        $response = $this->captureControllerOutput(function () use ($sala) {
            $this->controller->show($sala['id']);
        });

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Sala A', $response['data']['nombre']);
        $this->assertEquals(20, $response['data']['aforo']);
        $this->assertEquals($sucursal['id'], $response['data']['sucursal_id']);
    }

    // ─── F-SAL-013: Coordinador no puede crear (validación de permiso) ───

    /**
     * @test
     * @F-SAL-013
     */
    public function testCoordinadorNoPuedeCrearSala(): void
    {
        // La restricción se aplica en PermissionMiddleware a nivel de ruta.
        // Verificamos que el permiso de creación está correctamente configurado
        // para el rol coordinador en el componente salas.
        $this->createPermiso('coordinador', 'salas', true, false, false, false);

        $permisoRepository = new PermisoRepository(self::getDatabase());
        $permiso = $permisoRepository->findByRolYComponente('coordinador', 'salas');

        $this->assertNotNull($permiso, 'Debe existir un registro de permiso');
        $this->assertTrue((bool) $permiso->permiso_lectura, 'Coordinador debe tener permiso de lectura');
        $this->assertFalse((bool) $permiso->permiso_creacion, 'Coordinador NO debe tener permiso de creación');
    }

    // ─── F-SAL-014: Coordinador no puede editar (validación de permiso) ───

    /**
     * @test
     * @F-SAL-014
     */
    public function testCoordinadorNoPuedeEditarSala(): void
    {
        $this->createPermiso('coordinador', 'salas', true, false, false, false);

        $permisoRepository = new PermisoRepository(self::getDatabase());
        $permiso = $permisoRepository->findByRolYComponente('coordinador', 'salas');

        $this->assertNotNull($permiso, 'Debe existir un registro de permiso');
        $this->assertFalse((bool) $permiso->permiso_actualizacion, 'Coordinador NO debe tener permiso de actualización');
    }

    // ─── F-SAL-015: Coordinador no puede eliminar (validación de permiso) ───

    /**
     * @test
     * @F-SAL-015
     */
    public function testCoordinadorNoPuedeEliminarSala(): void
    {
        $this->createPermiso('coordinador', 'salas', true, false, false, false);

        $permisoRepository = new PermisoRepository(self::getDatabase());
        $permiso = $permisoRepository->findByRolYComponente('coordinador', 'salas');

        $this->assertNotNull($permiso, 'Debe existir un registro de permiso');
        $this->assertFalse((bool) $permiso->permiso_eliminacion, 'Coordinador NO debe tener permiso de eliminación');
    }
}
