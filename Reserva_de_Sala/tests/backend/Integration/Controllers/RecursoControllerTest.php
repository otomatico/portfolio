<?php
namespace Tests\Backend\Integration\Controllers;

use App\Controllers\RecursoController;
use App\Middleware\PermissionMiddleware;
use Tests\Backend\BaseTestCase;

/**
 * Tests de integración para RecursoController
 * 
 * Cubre: F-REC-001 a F-REC-010
 */
class RecursoControllerTest extends BaseTestCase
{
    private RecursoController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new RecursoController(self::getDatabase());
    }

    // ─── F-REC-001: Admin crea recurso ───

    /**
     * @test
     * @F-REC-001
     */
    public function testAdminCreaRecurso(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'POST', '/api/recursos')
        );
    }

    // ─── F-REC-002: Admin lista recursos ───

    /**
     * @test
     * @F-REC-002
     */
    public function testAdminListaRecursos(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'GET', '/api/recursos')
        );
    }

    // ─── F-REC-003: Admin ve detalle ───

    /**
     * @test
     * @F-REC-003
     */
    public function testAdminVeDetalleRecurso(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'GET', '/api/recursos/1')
        );
    }

    // ─── F-REC-004: Admin edita recurso ───

    /**
     * @test
     * @F-REC-004
     */
    public function testAdminEditaRecurso(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'PUT', '/api/recursos/1')
        );
    }

    // ─── F-REC-005: Admin elimina recurso ───

    /**
     * @test
     * @F-REC-005
     */
    public function testAdminEliminaRecurso(): void
    {
        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'admin', 'sub' => 1], 'DELETE', '/api/recursos/1')
        );
    }

    // ─── F-REC-006: Coordinador lista recursos ───

    /**
     * @test
     * @F-REC-006
     */
    public function testCoordinadorListaRecursos(): void
    {
        $this->createPermiso('coordinador', 'recursos', true, false, false, false);

        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'GET', '/api/recursos')
        );
    }

    // ─── F-REC-007: Coordinador ve detalle ───

    /**
     * @test
     * @F-REC-007
     */
    public function testCoordinadorVeDetalleRecurso(): void
    {
        $this->createPermiso('coordinador', 'recursos', true, false, false, false);

        $this->assertTrue(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'GET', '/api/recursos/1')
        );
    }

    // ─── F-REC-008: Coordinador no puede crear ───

    /**
     * @test
     * @F-REC-008
     */
    public function testCoordinadorNoPuedeCrearRecurso(): void
    {
        $this->createPermiso('coordinador', 'recursos', true, false, false, false);

        $this->assertFalse(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'POST', '/api/recursos')
        );
    }

    // ─── F-REC-009: Coordinador no puede editar ───

    /**
     * @test
     * @F-REC-009
     */
    public function testCoordinadorNoPuedeEditarRecurso(): void
    {
        $this->createPermiso('coordinador', 'recursos', true, false, false, false);

        $this->assertFalse(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'PUT', '/api/recursos/1')
        );
    }

    // ─── F-REC-010: Coordinador no puede eliminar ───

    /**
     * @test
     * @F-REC-010
     */
    public function testCoordinadorNoPuedeEliminarRecurso(): void
    {
        $this->createPermiso('coordinador', 'recursos', true, false, false, false);

        $this->assertFalse(
            PermissionMiddleware::check(['rol' => 'coordinador', 'sub' => 1], 'DELETE', '/api/recursos/1')
        );
    }
}
