<?php
namespace Tests\Backend\Unit\Services;

use App\Services\MaestroService;
use Tests\Backend\BaseTestCase;

/**
 * Tests para MaestroService
 * 
 * Cubre: F-MAE-001, F-MAE-002, F-MAE-003, F-MAE-004, F-MAE-005, F-MAE-006,
 *        F-MAE-007, F-MAE-008, F-MAE-009, F-MAE-010, F-MAE-011, F-MAE-012
 */
class MaestroServiceTest extends BaseTestCase
{
    private MaestroService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MaestroService(self::getDatabase());
    }

    // ─── F-MAE-001: Lista grupos maestros ───

    /**
     * @test
     * @F-MAE-001
     */
    public function testListarGruposMaestros(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');
        $this->createMaestro('reserva_estado', 'Estados de Reserva');

        $grupos = $this->service->listarGrupos();

        $this->assertCount(2, $grupos);
        $codigos = array_column($grupos, 'codigo');
        $this->assertContains('user_role', $codigos);
        $this->assertContains('reserva_estado', $codigos);
    }

    // ─── F-MAE-002: Crea grupo maestro ───

    /**
     * @test
     * @F-MAE-002
     */
    public function testCrearGrupoMaestro(): void
    {
        $result = $this->service->crearGrupo([
            'codigo' => 'tipo_recurso',
            'nombre' => 'Tipos de Recurso',
        ]);

        $this->assertEquals('tipo_recurso', $result['codigo']);
        $this->assertEquals('Tipos de Recurso', $result['nombre']);
    }

    /**
     * @test
     * @F-MAE-002
     */
    public function testNuevoGrupoApareceEnListado(): void
    {
        $this->service->crearGrupo([
            'codigo' => 'tipo_recurso',
            'nombre' => 'Tipos de Recurso',
        ]);

        $grupos = $this->service->listarGrupos();
        $codigos = array_column($grupos, 'codigo');
        $this->assertContains('tipo_recurso', $codigos);
    }

    // ─── F-MAE-003: Edita grupo maestro ───

    /**
     * @test
     * @F-MAE-003
     */
    public function testActualizarGrupoMaestro(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');

        $actualizado = $this->service->actualizarGrupo('user_role', [
            'nombre' => 'Roles del Sistema',
        ]);

        $this->assertEquals('Roles del Sistema', $actualizado['nombre']);
    }

    // ─── F-MAE-004: Elimina grupo sin opciones ───

    /**
     * @test
     * @F-MAE-004
     */
    public function testEliminarGrupoSinOpciones(): void
    {
        $this->createMaestro('canal_formacion', 'Canal de Formación');

        $this->service->eliminarGrupo('canal_formacion');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Grupo maestro no encontrado');
        $this->service->obtenerGrupo('canal_formacion');
    }

    /**
     * @test
     * @F-MAE-004
     */
    public function testGrupoEliminadoNoApareceEnListado(): void
    {
        $this->createMaestro('canal_formacion', 'Canal de Formación');

        $this->service->eliminarGrupo('canal_formacion');

        $grupos = $this->service->listarGrupos();
        $codigos = array_column($grupos, 'codigo');
        $this->assertNotContains('canal_formacion', $codigos);
    }

    // ─── F-MAE-005: No elimina grupo con opciones ───

    /**
     * @test
     * @F-MAE-005
     */
    public function testNoEliminarGrupoConOpciones(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');
        $this->createOpcionMaestro('user_role', 'admin', 'Administrador');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No se puede eliminar un grupo que tiene opciones asociadas');

        $this->service->eliminarGrupo('user_role');
    }

    // ─── F-MAE-006: Código duplicado ───

    /**
     * @test
     * @F-MAE-006
     */
    public function testCrearGrupoConCodigoDuplicadoLanzaExcepcion(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El código ya existe');

        $this->service->crearGrupo([
            'codigo' => 'user_role',
            'nombre' => 'Roles del Sistema',
        ]);
    }

    // ─── F-MAE-007: Lista opciones de un grupo ───

    /**
     * @test
     * @F-MAE-007
     */
    public function testListarOpcionesDeGrupo(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');
        $this->createOpcionMaestro('user_role', 'admin', 'Administrador');
        $this->createOpcionMaestro('user_role', 'coordinador', 'Coordinador');

        $opciones = $this->service->listarOpciones('user_role');

        $this->assertCount(2, $opciones);
        $codigos = array_column($opciones, 'codigo');
        $this->assertContains('admin', $codigos);
        $this->assertContains('coordinador', $codigos);
    }

    // ─── F-MAE-008: Crea opción en grupo ───

    /**
     * @test
     * @F-MAE-008
     */
    public function testCrearOpcionEnGrupo(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');

        $result = $this->service->crearOpcion('user_role', [
            'codigo' => 'supervisor',
            'nombre' => 'Supervisor',
            'orden' => 3,
            'activo' => true,
        ]);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('supervisor', $result['codigo']);
        $this->assertEquals('Supervisor', $result['nombre']);
        $this->assertEquals(3, $result['orden']);
        $this->assertTrue($result['activo']);
    }

    /**
     * @test
     * @F-MAE-008
     */
    public function testNuevaOpcionApareceEnListado(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');
        $this->service->crearOpcion('user_role', [
            'codigo' => 'supervisor',
            'nombre' => 'Supervisor',
            'orden' => 3,
            'activo' => true,
        ]);

        $opciones = $this->service->listarOpciones('user_role');
        $codigos = array_column($opciones, 'codigo');
        $this->assertContains('supervisor', $codigos);
    }

    // ─── F-MAE-009: Edita opción ───

    /**
     * @test
     * @F-MAE-009
     */
    public function testActualizarOpcion(): void
    {
        $this->createMaestro('user_role', 'Roles de Usuario');
        $opcion = $this->createOpcionMaestro('user_role', 'coordinador', 'Coordinador');

        $actualizada = $this->service->actualizarOpcion($opcion['id'], [
            'nombre' => 'Coordinador de Sucursal',
        ]);

        $this->assertEquals('Coordinador de Sucursal', $actualizada['nombre']);
    }

    // ─── F-MAE-010: Elimina opción ───

    /**
     * @test
     * @F-MAE-010
     */
    public function testEliminarOpcion(): void
    {
        $this->createMaestro('reserva_estado', 'Estados de Reserva');
        $opcion = $this->createOpcionMaestro('reserva_estado', 'cancelada', 'Cancelada');

        $this->service->eliminarOpcion($opcion['id']);

        $opciones = $this->service->listarOpciones('reserva_estado');
        $codigos = array_column($opciones, 'codigo');
        $this->assertNotContains('cancelada', $codigos);
    }

    // ─── F-MAE-011: Opción desactivada no se muestra en dropdowns ───

    /**
     * @test
     * @F-MAE-011
     */
    public function testOpcionDesactivadaNoApareceEnDropdown(): void
    {
        $this->createMaestro('reserva_estado', 'Estados de Reserva');
        $this->createOpcionMaestro('reserva_estado', 'confirmada', 'Confirmada', 1, true);
        $this->createOpcionMaestro('reserva_estado', 'cancelada', 'Cancelada', 2, false); // desactivada

        // onlyActivas = true
        $opciones = $this->service->listarOpciones('reserva_estado', true);

        $codigos = array_column($opciones, 'codigo');
        $this->assertContains('confirmada', $codigos);
        $this->assertNotContains('cancelada', $codigos);
    }

    // ─── F-MAE-012: Opción desactivada sigue válida en registros existentes ───

    /**
     * @test
     * @F-MAE-012
     */
    public function testOpcionDesactivadaSigueValidaEnRegistros(): void
    {
        $this->createMaestro('reserva_estado', 'Estados de Reserva');
        $this->createOpcionMaestro('reserva_estado', 'cancelada', 'Cancelada', 2, false);

        // Con onlyActivas=false, debe seguir apareciendo
        $opciones = $this->service->listarOpciones('reserva_estado', false);

        $codigos = array_column($opciones, 'codigo');
        $this->assertContains('cancelada', $codigos);
    }

    // ─── Validaciones adicionales ───

    /**
     * @test
     */
    public function testCrearGrupoSinCodigoLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El código es obligatorio');

        $this->service->crearGrupo(['nombre' => 'Test']);
    }

    /**
     * @test
     */
    public function testCrearGrupoSinNombreLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El nombre es obligatorio');

        $this->service->crearGrupo(['codigo' => 'test']);
    }

    /**
     * @test
     */
    public function testObtenerGrupoInexistenteLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Grupo maestro no encontrado');

        $this->service->obtenerGrupo('no_existe');
    }

    /**
     * @test
     */
    public function testCrearOpcionSinCodigoLanzaExcepcion(): void
    {
        $this->createMaestro('test', 'Test');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El código de la opción es obligatorio');

        $this->service->crearOpcion('test', ['nombre' => 'Test']);
    }

    /**
     * @test
     */
    public function testCrearOpcionEnGrupoInexistenteLanzaExcepcion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Grupo maestro no encontrado');

        $this->service->crearOpcion('no_existe', ['codigo' => 'test', 'nombre' => 'Test']);
    }
}
