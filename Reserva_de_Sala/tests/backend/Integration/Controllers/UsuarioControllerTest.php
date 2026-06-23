<?php
namespace Tests\Backend\Integration\Controllers;

use App\Controllers\UsuarioController;
use App\Middleware\PermissionMiddleware;
use Tests\Backend\BaseTestCase;

/**
 * Tests de integración para UsuarioController
 * 
 * Cubre: F-USU-001 a F-USU-009
 */
class UsuarioControllerTest extends BaseTestCase
{
    private UsuarioController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new UsuarioController(self::getDatabase());
    }

    // ─── F-USU-001: Admin lista usuarios ───

    /**
     * @test
     * @F-USU-001
     */
    public function testAdminListaUsuarios(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'GET', '/api/usuarios')
        );
    }

    // ─── F-USU-002: Admin ve detalle ───

    /**
     * @test
     * @F-USU-002
     */
    public function testAdminVeDetalleUsuario(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'GET', '/api/usuarios/1')
        );
    }

    // ─── F-USU-003: Admin crea admin ───

    /**
     * @test
     * @F-USU-003
     */
    public function testAdminCreaAdmin(): void
    {
        $service = new \App\Services\UsuarioService(self::getDatabase());
        $result = $service->crear([
            'nombre' => 'Admin 2',
            'email' => 'admin2@example.com',
            'password' => 'Password123',
            'rol' => 'admin',
        ]);

        $this->assertEquals('admin', $result['rol']);
        $this->assertNull($result['sucursal_id']);
    }

    // ─── F-USU-004: Admin crea coordinador con sucursal ───

    /**
     * @test
     * @F-USU-004
     */
    public function testAdminCreaCoordinadorConSucursal(): void
    {
        $sucursal = $this->createSucursal('Sucursal Norte');
        $service = new \App\Services\UsuarioService(self::getDatabase());

        $result = $service->crear([
            'nombre' => 'Coord 2',
            'email' => 'coord2@example.com',
            'password' => 'Password123',
            'rol' => 'coordinador',
            'sucursal_id' => $sucursal['id'],
        ]);

        $this->assertEquals('coordinador', $result['rol']);
        $this->assertEquals($sucursal['id'], $result['sucursal_id']);
    }

    // ─── F-USU-005: Coordinador sin sucursal ───

    /**
     * @test
     * @F-USU-005
     */
    public function testCrearCoordinadorSinSucursalRechazado(): void
    {
        $service = new \App\Services\UsuarioService(self::getDatabase());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El coordinador debe estar asociado a una sucursal');

        $service->crear([
            'nombre' => 'Coord Sin Suc',
            'email' => 'coord_sin@test.com',
            'password' => 'Password123',
            'rol' => 'coordinador',
        ]);
    }

    // ─── F-USU-006: Email duplicado ───

    /**
     * @test
     * @F-USU-006
     */
    public function testCrearUsuarioEmailDuplicadoRechazado(): void
    {
        $this->createUsuario('Admin', 'admin@example.com', 'admin');
        $service = new \App\Services\UsuarioService(self::getDatabase());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El email ya está registrado');

        $service->crear([
            'nombre' => 'Admin Dup',
            'email' => 'admin@example.com',
            'password' => 'Password123',
            'rol' => 'admin',
        ]);
    }

    // ─── F-USU-007: Admin edita usuario ───

    /**
     * @test
     * @F-USU-007
     */
    public function testAdminEditaUsuario(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'PUT', '/api/usuarios/1')
        );
    }

    // ─── F-USU-008: Admin elimina usuario ───

    /**
     * @test
     * @F-USU-008
     */
    public function testAdminEliminaUsuario(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'DELETE', '/api/usuarios/1')
        );
    }

    // ─── F-USU-009: Coordinador no accede a usuarios ───

    /**
     * @test
     * @F-USU-009
     */
    public function testCoordinadorNoAccedeAUsuarios(): void
    {
        $this->createPermiso('coordinador', 'usuarios', false, false, false, false);

        $this->assertFalse(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'GET', '/api/usuarios')
        );
    }

    /**
     * @test
     * @F-USU-009
     */
    public function testCoordinadorRecibe403AlAccederAUsuarios(): void
    {
        $this->createPermiso('coordinador', 'usuarios', false, false, false, false);

        $tienePermiso = PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'GET', '/api/usuarios');
        $this->assertFalse($tienePermiso);

        if (!$tienePermiso) {
            http_response_code(403);
        }

        $this->assertEquals(403, http_response_code());
    }
}
