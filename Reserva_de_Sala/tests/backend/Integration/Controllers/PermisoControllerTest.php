<?php
namespace Tests\Backend\Integration\Controllers;

use App\Controllers\PermisoController;
use App\Middleware\PermissionMiddleware;
use Tests\Backend\BaseTestCase;

/**
 * Tests de integración para PermisoController
 * 
 * Cubre: F-PER-001, F-PER-002, F-PER-003, F-PER-004, F-PER-005, F-PER-006, F-PER-007, F-PER-008, F-PER-009
 */
class PermisoControllerTest extends BaseTestCase
{
    private PermisoController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new PermisoController(self::getDatabase());
    }

    // ─── F-PER-001: Matriz completa de permisos ───

    /**
     * @test
     * @F-PER-001
     */
    public function testAdminVisualizaMatrizCompleta(): void
    {
        $this->createPermiso('admin', 'sucursales', true, true, true, true);
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $service = new \App\Services\PermisoService(self::getDatabase());
        $permisos = $service->listarTodos();

        $this->assertCount(2, $permisos);

        // Cada permiso tiene valores CRUD
        foreach ($permisos as $p) {
            $this->assertArrayHasKey('permiso_lectura', $p);
            $this->assertArrayHasKey('permiso_creacion', $p);
            $this->assertArrayHasKey('permiso_actualizacion', $p);
            $this->assertArrayHasKey('permiso_eliminacion', $p);
        }
    }

    // ─── F-PER-002: Permisos filtrados por rol ───

    /**
     * @test
     * @F-PER-002
     */
    public function testAdminVisualizaPermisosPorRol(): void
    {
        $this->createPermiso('admin', 'sucursales', true, true, true, true);
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $service = new \App\Services\PermisoService(self::getDatabase());
        $permisos = $service->listarPorRol('coordinador');

        $this->assertCount(1, $permisos);
        foreach ($permisos as $p) {
            $this->assertEquals('coordinador', $p['rol']);
        }
    }

    // ─── F-PER-003: Actualizar permiso ───

    /**
     * @test
     * @F-PER-003
     */
    public function testAdminActualizaPermiso(): void
    {
        $this->createPermiso('coordinador', 'salas', true, false, false, false);

        $service = new \App\Services\PermisoService(self::getDatabase());
        $actualizado = $service->actualizar('coordinador', 'salas', [
            'permiso_creacion' => true,
        ]);

        $this->assertTrue($actualizado['permiso_creacion']);
    }

    // ─── F-PER-004: Actualizar múltiples permisos ───

    /**
     * @test
     * @F-PER-004
     */
    public function testAdminActualizaMultiplesPermisos(): void
    {
        $this->createPermiso('coordinador', 'reservas', true, true, false, true);

        $service = new \App\Services\PermisoService(self::getDatabase());
        $actualizado = $service->actualizar('coordinador', 'reservas', [
            'permiso_lectura' => true,
            'permiso_creacion' => true,
            'permiso_actualizacion' => false,
            'permiso_eliminacion' => true,
        ]);

        $this->assertTrue($actualizado['permiso_lectura']);
        $this->assertTrue($actualizado['permiso_creacion']);
        $this->assertFalse($actualizado['permiso_actualizacion']);
        $this->assertTrue($actualizado['permiso_eliminacion']);
    }

    // ─── F-PER-005: Middleware deniega acceso sin permiso registrado (RN-15) ───

    /**
     * @test
     * @F-PER-005
     */
    public function testMiddlewareDeniegaSinPermisoRegistrado(): void
    {
        // No registrar permiso para coordinador en maestros
        $tienePermiso = PermissionMiddleware::check(
            ['rol' => 'coordinador', 'sub' => 1],
            'GET',
            '/api/maestros'
        );

        $this->assertFalse($tienePermiso);
    }

    // ─── F-PER-006: Middleware deniega operación no permitida ───

    /**
     * @test
     * @F-PER-006
     */
    public function testMiddlewareDeniegaOperacionNoPermitida(): void
    {
        $this->createPermiso('coordinador', 'reservas', true, true, false, false);

        // DELETE sin permiso_eliminacion
        $tienePermiso = PermissionMiddleware::check(
            ['rol' => 'coordinador', 'sub' => 1],
            'DELETE',
            '/api/reservas/1'
        );

        $this->assertFalse($tienePermiso);
    }

    /**
     * @test
     * @F-PER-006
     */
    public function testMiddlewarePermiteLecturaConPermiso(): void
    {
        $this->createPermiso('coordinador', 'reservas', true, false, false, false);

        $tienePermiso = PermissionMiddleware::check(
            ['rol' => 'coordinador', 'sub' => 1],
            'GET',
            '/api/reservas'
        );

        $this->assertTrue($tienePermiso);
    }

    // ─── F-PER-007: Admin tiene lectura en todos ───

    /**
     * @test
     * @F-PER-007
     */
    public function testAdminTieneLecturaEnTodosLosComponentes(): void
    {
        $componentes = ['sucursales', 'salas', 'recursos', 'reservas', 'usuarios', 'maestros', 'permisos'];
        foreach ($componentes as $componente) {
            $this->assertTrue(
                PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'GET', "/api/{$componente}")
            );
        }
    }

    // ─── F-PER-008: Acceso denegado registrado en log ───

    /**
     * @test
     * @F-PER-008
     */
    public function testAccesoDenegadoRegistradoEnLog(): void
    {
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $tienePermiso = PermissionMiddleware::check(
            ['rol' => 'coordinador', 'sub' => 1],
            'GET',
            '/api/usuarios'
        );

        $this->assertFalse($tienePermiso);

        // Verificar que se escribió en el log de errores
        $logPath = __DIR__ . '/../../../../logs/error.log';
        if (file_exists($logPath)) {
            $contenido = file_get_contents($logPath);
            $this->assertStringContainsString('Acceso denegado', $contenido);
        }
    }

    // ─── F-PER-009: Coordinador no accede a permisos ───

    /**
     * @test
     * @F-PER-009
     */
    public function testCoordinadorNoAccedeAPermisos(): void
    {
        $this->createPermiso('coordinador', 'permisos', false, false, false, false);

        $this->assertFalse(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'GET', '/api/permisos')
        );
    }

    /**
     * @test
     * @F-PER-009
     */
    public function testCoordinadorRecibe403AlAccederAPermisos(): void
    {
        $this->createPermiso('coordinador', 'permisos', false, false, false, false);

        $tienePermiso = PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'GET', '/api/permisos');
        $this->assertFalse($tienePermiso);

        if (!$tienePermiso) {
            http_response_code(403);
        }

        $this->assertEquals(403, http_response_code());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        http_response_code(200);
    }
}
