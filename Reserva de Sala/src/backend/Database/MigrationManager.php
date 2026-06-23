<?php
// Database/MigrationManager.php — Gestor de migraciones SQL

namespace App\Database;

use App\Config\Database;
use App\Log\Logger;
use PDO;

class MigrationManager
{
    private PDO $db;
    private string $migrationsPath;
    private array $errors = [];
    private Logger $logger;

    public function __construct(Database $database, string $migrationsPath)
    {
        $this->db = $database->getConnection();
        $this->migrationsPath = rtrim($migrationsPath, '/') . '/';
        $this->logger = new Logger();
    }

    /**
     * Ejecuta el pipeline completo de migraciones
     */
    public function run(): bool
    {
        $this->ensureMigrationsTable();

        $pendings = $this->getPendingMigrations();

        if (empty($pendings)) {
            $this->logger->info('No hay migraciones pendientes');
            return true;
        }

        foreach ($pendings as $filename) {
            $success = $this->executeMigration($filename);
            if (!$success) {
                $this->logger->error("Falló migración {$filename}", ['filename' => $filename]);
            }
        }

        return !$this->hasErrors();
    }

    /**
     * Asegura que existe la tabla de control de migraciones
     */
    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename TEXT NOT NULL UNIQUE,
            file_hash TEXT NOT NULL,
            executed_at TEXT NOT NULL DEFAULT (datetime('now')),
            status TEXT NOT NULL DEFAULT 'ok',
            error_log TEXT NULL
        )";
        $this->db->exec($sql);
    }

    /**
     * Retorna los archivos SQL pendientes de ejecutar
     */
    public function getPendingMigrations(): array
    {
        $executed = $this->getExecutedMigrations();
        $files = glob($this->migrationsPath . '*.sql');
        sort($files);

        $pending = [];
        foreach ($files as $filepath) {
            $filename = basename($filepath);
            if (!in_array($filename, $executed)) {
                $pending[] = $filename;
            }
        }

        return $pending;
    }

    /**
     * Ejecuta un archivo SQL y registra el resultado
     */
    public function executeMigration(string $filename): bool
    {
        $filepath = $this->migrationsPath . $filename;

        if (!file_exists($filepath)) {
            $this->errors[] = "Archivo no encontrado: {$filename}";
            return false;
        }

        $sql = file_get_contents($filepath);
        $hash = md5($sql);

        try {
            // Dividir por puntos y coma para ejecutar múltiples sentencias
            $statements = explode(';', $sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $this->db->exec($statement);
                }
            }

            // Registrar migración exitosa
            $stmt = $this->db->prepare(
                "INSERT INTO migrations (filename, file_hash, status) VALUES (:filename, :hash, 'ok')"
            );
            $stmt->execute([':filename' => $filename, ':hash' => $hash]);

            $this->logger->info("Migración ejecutada: {$filename}");
            return true;

        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();

            // Registrar migración fallida
            $stmt = $this->db->prepare(
                "INSERT INTO migrations (filename, file_hash, status, error_log) VALUES (:filename, :hash, 'error', :error)"
            );
            $stmt->execute([
                ':filename' => $filename,
                ':hash' => $hash,
                ':error' => $errorMsg,
            ]);

            $this->errors[] = "Error en {$filename}: {$errorMsg}";
            $this->logger->error("Falló migración {$filename}", [
                'filename' => $filename,
                'error' => $errorMsg,
            ]);

            return false;
        }
    }

    /**
     * Retorna los nombres de migraciones ya ejecutadas
     */
    public function getExecutedMigrations(): array
    {
        $stmt = $this->db->query("SELECT filename FROM migrations WHERE status = 'ok'");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
