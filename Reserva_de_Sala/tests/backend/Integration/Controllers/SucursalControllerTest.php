<?php
namespace Tests\Backend\Integration\Controllers;

use App\Controllers\SucursalController;
use App\Middleware\PermissionMiddleware;
use Tests\Backend\BaseTestCase;

/**
 * Tests de integración para SucursalController
 * 
 * Cubre: F-SUC-001 a F-SUC-011
 */
class SucursalControllerTest extends BaseTestCase
{
    private SucursalController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new SucursalController(self::getDatabase());
    }

    // ─── F-SUC-001: Admin crea sucursal ───

    /**
     * @test
     * @F-SUC-001
     */
    public function testAdminCreaSucursal(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'POST', '/api/sucursales')
        );
    }

    // ─── F-SUC-002: Admin lista sucursales ───

    /**
     * @test
     * @F-SUC-002
     */
    public function testAdminListaSucursales(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'GET', '/api/sucursales')
        );
    }

    // ─── F-SUC-003: Admin ve detalle ───

    /**
     * @test
     * @F-SUC-003
     */
    public function testAdminVeDetalleSucursal(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'GET', '/api/sucursales/1')
        );
    }

    // ─── F-SUC-004: Admin edita sucursal ───

    /**
     * @test
     * @F-SUC-004
     */
    public function testAdminEditaSucursal(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'PUT', '/api/sucursales/1')
        );
    }

    // ─── F-SUC-005: Admin elimina sucursal ───

    /**
     * @test
     * @F-SUC-005
     */
    public function testAdminEliminaSucursal(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'DELETE', '/api/sucursales/1')
        );
    }

    // ─── F-SUC-006: Validación nombre obligatorio ───

    /**
     * @test
     * @F-SUC-006
     */
    public function testCrearSucursalSinNombreEsRechazado(): void
    {
        $service = new \App\Services\SucursalService(self::getDatabase());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El nombre es obligatorio');

        $service->crear(['direccion' => 'Solo dirección']);
    }

    // ─── F-SUC-007: Coordinador lista sucursales ───

    /**
     * @test
     * @F-SUC-007
     */
    public function testCoordinadorListaSucursales(): void
    {
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'GET', '/api/sucursales')
        );
    }

    // ─── F-SUC-008: Coordinador ve detalle ───

    /**
     * @test
     * @F-SUC-008
     */
    public function testCoordinadorVeDetalleSucursal(): void
    {
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'GET', '/api/sucursales/1')
        );
    }

    // ─── F-SUC-009: Coordinador no puede crear ───

    /**
     * @test
     * @F-SUC-009
     */
    public function testCoordinadorNoPuedeCrearSucursal(): void
    {
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $this->assertFalse(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'POST', '/api/sucursales')
        );
    }

    // ─── F-SUC-010: Coordinador no puede editar ───

    /**
     * @test
     * @F-SUC-010
     */
    public function testCoordinadorNoPuedeEditarSucursal(): void
    {
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $this->assertFalse(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'PUT', '/api/sucursales/1')
        );
    }

    // ─── F-SUC-011: Coordinador no puede eliminar ───

    /**
     * @test
     * @F-SUC-011
     */
    public function testCoordinadorNoPuedeEliminarSucursal(): void
    {
        $this->createPermiso('coordinador', 'sucursales', true, false, false, false);

        $this->assertFalse(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'DELETE', '/api/sucursales/1')
        );
    }
}
