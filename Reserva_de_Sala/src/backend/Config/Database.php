<?php
// Config/Database.php — Conexión SQLite mediante PDO

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private ?PDO $connection = null;
    private string $dbPath;

    public function __construct(?string $dbPath = null)
    {
        if ($dbPath === null) {
            $config = require __DIR__ . '/app.php';
            $dbPath = $config['database']['dev'];
        }
        $this->dbPath = $dbPath;
        $this->connect();
    }

    public static function forTest(): self
    {
        $config = require __DIR__ . '/app.php';
        return new self($config['database']['test']);
    }

    private function connect(): void
    {
        $dir = dirname($this->dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        try {
            $this->connection = new PDO('sqlite:' . $this->dbPath);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->connection->exec('PRAGMA journal_mode=WAL');
            $this->connection->exec('PRAGMA foreign_keys=ON');
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function getDbPath(): string
    {
        return $this->dbPath;
    }
}
