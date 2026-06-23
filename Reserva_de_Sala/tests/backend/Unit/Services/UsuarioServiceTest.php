<?php
namespace Tests\Backend\Unit\Services;

use App\Services\UsuarioService;
use Tests\Backend\BaseTestCase;

/**
 * Tests para UsuarioService
 * 
 * Cubre: F-USU-001, F-USU-002, F-USU-003, F-USU-004, F-USU-005, F-USU-006, F-USU-007, F-USU-008
 */
class UsuarioServiceTest extends BaseTestCase
{
    private UsuarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UsuarioService(self::getDatabase());
    }

    // ─── F-USU-001: Admin lista todos los usuarios ───

    /**
     * @test
     * @F-USU-001
     */
    public function testListarUsuarios(): void
    {
        $this->createUsuario('Admin 1', 'admin1@test.com', 'admin');
        $sucursal = $this->createSucursal('Sucursal Centro');
        $this->createUsuario('Coord 1', 'coord1@test.com', 'coordinador', $sucursal['id']);

        $listado = $this->service->listar();

        $this->assertCount(2, $listado);
        $emails = array_column($listado, 'email');
        $this->assertContains('admin1@test.com', $emails);
        $this->assertContains('coord1@test.com', $emails);
    }

    /**
     * @test
     * @F-USU-001
     */
    public function testListadoUsuariosIncluyeRolYSucursal(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $this->createUsuario('Coord 1', 'coord1@test.com', 'coordinador', $sucursal['id']);

        $listado = $this->service->listar();

        $this->assertCount(1, $listado);
        $this->assertEquals('coordinador', $listado[0]['rol']);
        $this->assertEquals($sucursal['id'], $listado[0]['sucursal_id']);
    }

    // ─── F-USU-002: Admin ve detalle de un usuario ───

    /**
     * @test
     * @F-USU-002
     */
    public function testObtenerUsuarioPorId(): void
    {
        $sucursal = $this->createSucursal('Sucursal Centro');
        $creado = $this->createUsuario('Coord 1', 'coord1@test.com', 'coordinador', $sucursal['id']);

        $result = $this->service->obtenerPorId($creado['id']);

        $this->assertEquals($creado['id'], $result['id']);
        $this->assertEquals('coord1@test.com', $result['email']);
        $this->assertEquals('coordinador', $result['rol']);
        $this->assertEquals($sucursal['id'], $result['sucursal_id']);
    }

    // ─── F-USU-003: Admin crea un nuevo administrador ───

    /**
     * @test
     * @F-USU-003
     */
    public function testCrearAdminSinSucursal(): void
    {
        $result = $this->service->crear([
            'nombre' => 'Admin 2',
            'email' => 'admin2@example.com',
            'password' => 'Password123',
            'rol' => 'admin',
        ]);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('admin', $result['rol']);
        $this->assertNull($result['sucursal_id']);
    }

    // ─── F-USU-004: Admin crea coordinador con sucursal ───

    /**
     * @test
     * @F-USU-004
     */
    public function testCrearCoordinadorConSucursal(): void
    {
        $sucursal = $this->createSucursal('Sucursal Norte');

        $result = $this->service->crear([
            'nombre' => 'Coord 2',
            'email' => 'coord2@example.com',
            'password' => 'Password123',
            'rol' => 'coordinador',
            'sucursal_id' => $sucursal['id'],
        ]);

        $this->assertEquals('coordinador', $result['rol']);
        $this->assertEquals($sucursal['id'], $result['sucursal_id']);
    }

    // ─── F-USU-005: Validación coordinador sin sucursal ───

    /**
     * @test
     * @F-USU-005
     */
    public function testCrearCoordinadorSinSucursalLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El coordinador debe estar asociado a una sucursal');

        $this->service->crear([
            'nombre' => 'Coord Sin Sucursal',
            'email' => 'coord_sin@test.com',
            'password' => 'Password123',
            'rol' => 'coordinador',
        ]);
    }

    // ─── F-USU-006: Validación email duplicado ───

    /**
     * @test
     * @F-USU-006
     */
    public function testCrearUsuarioConEmailDuplicadoLanzaExcepcion(): void
    {
        $this->createUsuario('Admin', 'admin@example.com', 'admin');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El email ya está registrado');

        $this->service->crear([
            'nombre' => 'Admin Duplicado',
            'email' => 'admin@example.com',
            'password' => 'Password123',
            'rol' => 'admin',
        ]);
    }

    // ─── F-USU-007: Admin edita un usuario ───

    /**
     * @test
     * @F-USU-007
     */
    public function testActualizarUsuario(): void
    {
        $creado = $this->createUsuario('Coord 1', 'coord1@example.com', 'coordinador');

        $actualizado = $this->service->actualizar($creado['id'], [
            'nombre' => 'Coordinador Nuevo',
        ]);

        $this->assertEquals('Coordinador Nuevo', $actualizado['nombre']);
        $this->assertEquals('coord1@example.com', $actualizado['email']);
    }

    // ─── F-USU-008: Admin elimina un usuario ───

    /**
     * @test
     * @F-USU-008
     */
    public function testEliminarUsuario(): void
    {
        $creado = $this->createUsuario('Coord 1', 'coord1@example.com', 'coordinador');

        $this->service->eliminar($creado['id']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Usuario no encontrado');
        $this->service->obtenerPorId($creado['id']);
    }

    /**
     * @test
     * @F-USU-008
     */
    public function testUsuarioEliminadoNoApareceEnListado(): void
    {
        $creado = $this->createUsuario('Coord 1', 'coord1@example.com', 'coordinador');

        $this->service->eliminar($creado['id']);

        $listado = $this->service->listar();
        $this->assertCount(0, $listado);
    }

    // ─── Validaciones adicionales ───

    /**
     * @test
     */
    public function testCrearUsuarioSinNombreLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El nombre es obligatorio');

        $this->service->crear([
            'email' => 'test@test.com',
            'password' => 'Password123',
            'rol' => 'admin',
        ]);
    }

    /**
     * @test
     */
    public function testCrearUsuarioSinEmailLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El email es obligatorio');

        $this->service->crear([
            'nombre' => 'Test',
            'password' => 'Password123',
            'rol' => 'admin',
        ]);
    }

    /**
     * @test
     */
    public function testCrearUsuarioSinPasswordLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('La contraseña es obligatoria');

        $this->service->crear([
            'nombre' => 'Test',
            'email' => 'test@test.com',
            'rol' => 'admin',
        ]);
    }
}
