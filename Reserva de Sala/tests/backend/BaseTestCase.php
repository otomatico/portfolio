<?php
namespace Tests\Backend;

use App\Config\Database;
use App\Database\MigrationManager;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected static ?Database $db = null;
    protected static bool $migrated = false;

    /**
     * Obtiene la conexión de base de datos de testing
     */
    protected static function getDatabase(): Database
    {
        if (self::$db === null) {
            self::$db = Database::forTest();
        }
        return self::$db;
    }

    /**
     * Ejecuta las migraciones una vez por suite
     */
    protected static function runMigrations(): void
    {
        if (self::$migrated) {
            return;
        }

        $db = self::getDatabase();
        $migrationsPath = __DIR__ . '/../../database/migrations';
        $manager = new MigrationManager($db, $migrationsPath);
        $manager->run();
        self::$migrated = true;
    }

    /**
     * Limpia todas las tablas entre tests
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::runMigrations();
        $this->truncateTables();
    }

    /**
     * Trunca (vacia) todas las tablas del sistema respetando FK
     */
    protected function truncateTables(): void
    {
        $db = self::getDatabase()->getConnection();
        $db->exec('PRAGMA foreign_keys=OFF');

        $tables = [
            'sala_recursos',
            'reservas',
            'opciones_maestro',
            'maestros',
            'permisos',
            'usuarios',
            'recursos',
            'salas',
            'sucursales',
        ];

        foreach ($tables as $table) {
            $db->exec("DELETE FROM {$table}");
        }

        // Resetear autoincrement
        $db->exec("DELETE FROM sqlite_sequence");

        $db->exec('PRAGMA foreign_keys=ON');
    }

    /**
     * Crea una sucursal de prueba
     */
    protected function createSucursal(string $nombre = 'Sucursal Centro', string $direccion = 'Av. Principal 123'): array
    {
        $db = self::getDatabase()->getConnection();
        $stmt = $db->prepare("INSERT INTO sucursales (nombre, direccion) VALUES (:nombre, :direccion)");
        $stmt->execute([':nombre' => $nombre, ':direccion' => $direccion]);
        $id = (int) $db->lastInsertId();
        return ['id' => $id, 'nombre' => $nombre, 'direccion' => $direccion];
    }

    /**
     * Crea un recurso de prueba
     */
    protected function createRecurso(string $nombre = 'Proyector', string $descripcion = 'Proyector HD'): array
    {
        $db = self::getDatabase()->getConnection();
        $stmt = $db->prepare("INSERT INTO recursos (nombre, descripcion) VALUES (:nombre, :descripcion)");
        $stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion]);
        $id = (int) $db->lastInsertId();
        return ['id' => $id, 'nombre' => $nombre, 'descripcion' => $descripcion];
    }

    /**
     * Crea una sala de prueba
     */
    protected function createSala(string $nombre = 'Sala A', int $aforo = 20, int $sucursalId = 1, string $descripcion = ''): array
    {
        $db = self::getDatabase()->getConnection();
        $stmt = $db->prepare("INSERT INTO salas (nombre, aforo, descripcion, sucursal_id) VALUES (:nombre, :aforo, :descripcion, :sucursal_id)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':aforo' => $aforo,
            ':descripcion' => $descripcion,
            ':sucursal_id' => $sucursalId,
        ]);
        $id = (int) $db->lastInsertId();
        return ['id' => $id, 'nombre' => $nombre, 'aforo' => $aforo, 'sucursal_id' => $sucursalId];
    }

    /**
     * Crea un usuario de prueba (contraseña hasheada para 'Password123')
     */
    protected function createUsuario(
        string $nombre = 'Admin Test',
        string $email = 'admin@test.com',
        string $rol = 'admin',
        ?int $sucursalId = null
    ): array {
        $db = self::getDatabase()->getConnection();
        $password = password_hash('Password123', PASSWORD_BCRYPT);
        $stmt = $db->prepare(
            "INSERT INTO usuarios (nombre, email, password, rol, sucursal_id) VALUES (:nombre, :email, :password, :rol, :sucursal_id)"
        );
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password' => $password,
            ':rol' => $rol,
            ':sucursal_id' => $sucursalId,
        ]);
        $id = (int) $db->lastInsertId();
        return ['id' => $id, 'nombre' => $nombre, 'email' => $email, 'rol' => $rol, 'sucursal_id' => $sucursalId];
    }

    /**
     * Crea una reserva de prueba
     */
    protected function createReserva(
        int $salaId,
        int $usuarioId,
        string $fechaInicio = '2026-07-10 09:00:00',
        string $fechaFin = '2026-07-10 11:00:00',
        string $estado = 'confirmada'
    ): array {
        $db = self::getDatabase()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO reservas (sala_id, usuario_id, fecha_inicio, fecha_fin, estado) 
             VALUES (:sala_id, :usuario_id, :fecha_inicio, :fecha_fin, :estado)"
        );
        $stmt->execute([
            ':sala_id' => $salaId,
            ':usuario_id' => $usuarioId,
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin,
            ':estado' => $estado,
        ]);
        $id = (int) $db->lastInsertId();
        return [
            'id' => $id, 'sala_id' => $salaId, 'usuario_id' => $usuarioId,
            'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'estado' => $estado,
        ];
    }

    /**
     * Crea un grupo maestro de prueba
     */
    protected function createMaestro(string $codigo = 'user_role', string $nombre = 'Roles de Usuario'): array
    {
        $db = self::getDatabase()->getConnection();
        $stmt = $db->prepare("INSERT INTO maestros (codigo, nombre) VALUES (:codigo, :nombre)");
        $stmt->execute([':codigo' => $codigo, ':nombre' => $nombre]);
        return ['codigo' => $codigo, 'nombre' => $nombre];
    }

    /**
     * Crea una opción de maestro de prueba
     */
    protected function createOpcionMaestro(
        string $maestroCodigo,
        string $codigo = 'admin',
        string $nombre = 'Administrador',
        int $orden = 1,
        bool $activo = true
    ): array {
        $db = self::getDatabase()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO opciones_maestro (maestro_codigo, codigo, nombre, orden, activo) 
             VALUES (:maestro_codigo, :codigo, :nombre, :orden, :activo)"
        );
        $stmt->execute([
            ':maestro_codigo' => $maestroCodigo,
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':orden' => $orden,
            ':activo' => (int) $activo,
        ]);
        $id = (int) $db->lastInsertId();
        return [
            'id' => $id, 'maestro_codigo' => $maestroCodigo, 'codigo' => $codigo,
            'nombre' => $nombre, 'orden' => $orden, 'activo' => $activo,
        ];
    }

    /**
     * Crea un permiso de prueba
     */
    protected function createPermiso(
        string $rol,
        string $componente,
        bool $lectura = true,
        bool $creacion = false,
        bool $actualizacion = false,
        bool $eliminacion = false
    ): array {
        $db = self::getDatabase()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) 
             VALUES (:rol, :componente, :permiso_lectura, :permiso_creacion, :permiso_actualizacion, :permiso_eliminacion)"
        );
        $stmt->execute([
            ':rol' => $rol,
            ':componente' => $componente,
            ':permiso_lectura' => (int) $lectura,
            ':permiso_creacion' => (int) $creacion,
            ':permiso_actualizacion' => (int) $actualizacion,
            ':permiso_eliminacion' => (int) $eliminacion,
        ]);
        $id = (int) $db->lastInsertId();
        return [
            'id' => $id, 'rol' => $rol, 'componente' => $componente,
            'permiso_lectura' => $lectura, 'permiso_creacion' => $creacion,
            'permiso_actualizacion' => $actualizacion, 'permiso_eliminacion' => $eliminacion,
        ];
    }
}
