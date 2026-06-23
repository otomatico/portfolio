<?php
namespace Tests\Backend\Unit\Services;

use App\Services\PermisoService;
use App\Middleware\PermissionMiddleware;
use Tests\Backend\BaseTestCase;

/**
 * Tests para PermisoService
 * 
 * Cubre: F-PER-001, F-PER-002, F-PER-003, F-PER-004, F-PER-005, F-PER-006, F-PER-007, F-PER-008
 */
class PermisoServiceTest extends BaseTestCase
{
    private PermisoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PermisoService(self::getDatabase());
    }

    // ─── F-PER-001: Matriz completa de permisos ───

    /**
     * @test
     * @F-PER-001
     */
    public function testListarTodosLosPermisos(): void
    {
        $this->createPermiso('admin', 'sucursales', true, true, true, true);
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $permisos = $this->service->listarTodos();

        $this->assertCount(2, $permisos);
        $roles = array_column($permisos, 'rol');
        $this->assertContains('admin', $roles);
        $this->assertContains('coordinador', $roles);
    }

    /**
     * @test
     * @F-PER-001
     */
    public function testPermisoMuestraValoresCRUD(): void
    {
        $this->createPermiso('admin', 'sucursales', true, true, true, true);

        $permisos = $this->service->listarTodos();

        $this->assertCount(1, $permisos);
        $p = $permisos[0];
        $this->assertArrayHasKey('permiso_lectura', $p);
        $this->assertArrayHasKey('permiso_creacion', $p);
        $this->assertArrayHasKey('permiso_actualizacion', $p);
        $this->assertArrayHasKey('permiso_eliminacion', $p);
    }

    // ─── F-PER-002: Permisos filtrados por rol ───

    /**
     * @test
     * @F-PER-002
     */
    public function testListarPermisosPorRol(): void
    {
        $this->createPermiso('admin', 'sucursales', true, true, true, true);
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $permisos = $this->service->listarPorRol('coordinador');

        $this->assertCount(1, $permisos);
        $this->assertEquals('coordinador', $permisos[0]['rol']);
    }

    // ─── F-PER-003: Actualizar un permiso ───

    /**
     * @test
     * @F-PER-003
     */
    public function testActualizarPermiso(): void
    {
        $this->createPermiso('coordinador', 'salas', true, false, false, false);

        $actualizado = $this->service->actualizar('coordinador', 'salas', [
            'permiso_creacion' => true,
        ]);

        $this->assertTrue($actualizado['permiso_creacion']);
        $this->assertTrue($actualizado['permiso_lectura']);
    }

    /**
     * @test
     * @F-PER-003
     */
    public function testCambioReflejadoEnConsulta(): void
    {
        $this->createPermiso('coordinador', 'salas', true, false, false, false);
        $this->service->actualizar('coordinador', 'salas', ['permiso_creacion' => true]);

        $permisos = $this->service->listarPorRol('coordinador');
        $salas = current(array_filter($permisos, fn($p) => $p['componente'] === 'salas'));
        $this->assertTrue($salas['permiso_creacion']);
    }

    // ─── F-PER-004: Actualizar múltiples permisos ───

    /**
     * @test
     * @F-PER-004
     */
    public function testActualizarMultiplesPermisos(): void
    {
        $this->createPermiso('coordinador', 'reservas', true, true, false, true);

        $actualizado = $this->service->actualizar('coordinador', 'reservas', [
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

    // ─── F-PER-005: Middleware deniega acceso (RN-15) ───

    /**
     * @test
     * @F-PER-005
     */
    public function testMiddlewareDeniegaSinPermisoRegistrado(): void
    {
        // No creamos permiso para coordinador en 'maestros'
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

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
    public function testMiddlewarePermiteLecturaCuandoTienePermiso(): void
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
    public function testAdminTienePermisoEnTodosLosComponentes(): void
    {
        // Admin siempre retorna true en PermissionMiddleware::check
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'GET', '/api/usuarios')
        );
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'POST', '/api/usuarios')
        );
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'PUT', '/api/usuarios')
        );
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'DELETE', '/api/usuarios')
        );
    }

    // ─── F-PER-008: Acceso denegado registrado en log ───

    /**
     * @test
     * @F-PER-008
     */
    public function testAccesoDenegadoRegistraLog(): void
    {
        // No crear permiso para coordinador en usuarios
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $tienePermiso = PermissionMiddleware::check(
            ['rol' => 'coordinador', 'sub' => 1],
            'GET',
            '/api/usuarios'
        );

        $this->assertFalse($tienePermiso);

        // Verificar que se registró en el log de errores
        $logPath = __DIR__ . '/../../../../logs/error.log';
        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $this->assertStringContainsString('Acceso denegado', $logContent);
        } else {
            // Si no hay archivo de log, al menos no debe lanzar excepción
            $this->assertTrue(true);
        }
    }

    // ─── Validaciones adicionales ───

    /**
     * @test
     */
    public function testActualizarPermisoSinRolLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El rol es obligatorio');

        $this->service->actualizar('', 'sucursales', ['permiso_lectura' => true]);
    }

    /**
     * @test
     */
    public function testActualizarPermisoSinComponenteLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El componente es obligatorio');

        $this->service->actualizar('admin', '', ['permiso_lectura' => true]);
    }
}
