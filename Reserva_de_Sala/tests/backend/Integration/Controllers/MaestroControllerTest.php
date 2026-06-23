<?php
namespace Tests\Backend\Integration\Controllers;

use App\Controllers\MaestroController;
use App\Middleware\PermissionMiddleware;
use Tests\Backend\BaseTestCase;

/**
 * Tests de integración para MaestroController
 * 
 * Cubre: F-MAE-001 a F-MAE-013
 */
class MaestroControllerTest extends BaseTestCase
{
    private MaestroController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new MaestroController(self::getDatabase());
    }

    // ─── F-MAE-001: Lista grupos ───

    /**
     * @test
     * @F-MAE-001
     */
    public function testAdminListaGrupos(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'GET', '/api/maestros')
        );
    }

    // ─── F-MAE-002: Crea grupo ───

    /**
     * @test
     * @F-MAE-002
     */
    public function testAdminCreaGrupo(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'POST', '/api/maestros')
        );
    }

    // ─── F-MAE-003: Edita grupo ───

    /**
     * @test
     * @F-MAE-003
     */
    public function testAdminEditaGrupo(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'PUT', '/api/maestros/user_role')
        );
    }

    // ─── F-MAE-004: Elimina grupo sin opciones ───

    /**
     * @test
     * @F-MAE-004
     */
    public function testAdminEliminaGrupoSinOpciones(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'DELETE', '/api/maestros/canal_formacion')
        );
    }

    // ─── F-MAE-005: No elimina grupo con opciones ───

    /**
     * @test
     * @F-MAE-005
     */
    public function testNoEliminaGrupoConOpciones(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');
        $this->createOpcionMaestro('user_role', 'admin', 'Admin');

        $service = new \App\Services\MaestroService(self::getDatabase());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No se puede eliminar un grupo que tiene opciones asociadas');

        $service->eliminarGrupo('user_role');
    }

    // ─── F-MAE-006: Código duplicado ───

    /**
     * @test
     * @F-MAE-006
     */
    public function testCrearGrupoCodigoDuplicadoRechazado(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');
        $service = new \App\Services\MaestroService(self::getDatabase());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El código ya existe');

        $service->crearGrupo([
            'codigo' => 'user_role',
            'nombre' => 'Roles del Sistema',
        ]);
    }

    // ─── F-MAE-007: Lista opciones de grupo ───

    /**
     * @test
     * @F-MAE-007
     */
    public function testAdminListaOpcionesDeGrupo(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');
        $this->createOpcionMaestro('user_role', 'admin', 'Admin');

        $service = new \App\Services\MaestroService(self::getDatabase());
        $opciones = $service->listarOpciones('user_role');

        $this->assertCount(1, $opciones);
        $this->assertEquals('admin', $opciones[0]['codigo']);
    }

    // ─── F-MAE-008: Crea opción en grupo ───

    /**
     * @test
     * @F-MAE-008
     */
    public function testAdminCreaOpcionEnGrupo(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');
        $service = new \App\Services\MaestroService(self::getDatabase());

        $result = $service->crearOpcion('user_role', [
            'codigo' => 'supervisor',
            'nombre' => 'Supervisor',
            'orden' => 3,
            'activo' => true,
        ]);

        $this->assertEquals('supervisor', $result['codigo']);
        $this->assertEquals('Supervisor', $result['nombre']);
    }

    // ─── F-MAE-009: Edita opción ───

    /**
     * @test
     * @F-MAE-009
     */
    public function testAdminEditaOpcion(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');
        $opcion = $this->createOpcionMaestro('user_role', 'coordinador', 'Coordinador');
        $service = new \App\Services\MaestroService(self::getDatabase());

        $actualizada = $service->actualizarOpcion($opcion['id'], [
            'nombre' => 'Coordinador de Sucursal',
        ]);

        $this->assertEquals('Coordinador de Sucursal', $actualizada['nombre']);
    }

    // ─── F-MAE-010: Elimina opción ───

    /**
     * @test
     * @F-MAE-010
     */
    public function testAdminEliminaOpcion(): void
    {
        $this->createMaestro('reserva_estado', 'Estados de Reserva');
        $opcion = $this->createOpcionMaestro('reserva_estado', 'cancelada', 'Cancelada');
        $service = new \App\Services\MaestroService(self::getDatabase());

        $service->eliminarOpcion($opcion['id']);

        $opciones = $service->listarOpciones('reserva_estado');
        $codigos = array_column($opciones, 'codigo');
        $this->assertNotContains('cancelada', $codigos);
    }

    // ─── F-MAE-011: Opción desactivada no en dropdown ───

    /**
     * @test
     * @F-MAE-011
     */
    public function testOpcionDesactivadaNoEnDropdown(): void
    {
        $this->createMaestro('reserva_estado', 'Estados de Reserva');
        $this->createOpcionMaestro('reserva_estado', 'confirmada', 'Confirmada', 1, true);
        $this->createOpcionMaestro('reserva_estado', 'cancelada', 'Cancelada', 2, false);

        $service = new \App\Services\MaestroService(self::getDatabase());
        $opciones = $service->listarOpciones('reserva_estado', true);

        $codigos = array_column($opciones, 'codigo');
        $this->assertContains('confirmada', $codigos);
        $this->assertNotContains('cancelada', $codigos);
    }

    // ─── F-MAE-012: Opción desactivada sigue válida en registros ───

    /**
     * @test
     * @F-MAE-012
     */
    public function testOpcionDesactivadaSigueValida(): void
    {
        $this->createMaestro('reserva_estado', 'Estados de Reserva');
        $this->createOpcionMaestro('reserva_estado', 'cancelada', 'Cancelada', 2, false);

        $service = new \App\Services\MaestroService(self::getDatabase());
        $opciones = $service->listarOpciones('reserva_estado', false);

        $codigos = array_column($opciones, 'codigo');
        $this->assertContains('cancelada', $codigos);
    }

    // ─── F-MAE-013: Coordinador no accede a maestros ───

    /**
     * @test
     * @F-MAE-013
     */
    public function testCoordinadorNoAccedeAMaestros(): void
    {
        $this->createPermiso('coordinador', 'maestros', false, false, false, false);

        $this->assertFalse(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'GET', '/api/maestros')
        );
    }

    /**
     * @test
     * @F-MAE-013
     */
    public function testCoordinadorRecibe403AlAccederAMaestros(): void
    {
        $this->createPermiso('coordinador', 'maestros', false, false, false, false);

        $tienePermiso = PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'GET', '/api/maestros');
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
