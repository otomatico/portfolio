<?php
// Log/Logger.php — Sistema de logging ligero

namespace App\Log;

class Logger
{
    private string $logDir;
    private string $level;
    private string $appName;

    private const LEVELS = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];

    public function __construct()
    {
        $config = require __DIR__ . '/../Config/app.php';
        $logConfig = $config['log'];
        $this->logDir = rtrim($logConfig['path'], '/') . '/';
        $this->level = $logConfig['level'] ?? 'debug';
        $this->appName = $logConfig['app_name'] ?? 'app';

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0775, true);
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        $levelLower = strtolower($level);
        if (self::LEVELS[$levelLower] < self::LEVELS[$this->level]) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextJson = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = "[{$timestamp}] [{$level}] [{$this->appName}] {$message}{$contextJson}" . PHP_EOL;

        // Escribir en app.log
        $this->writeToFile('app.log', $line);

        // Si es error, también escribir en error.log
        if ($level === 'ERROR' || $level === 'WARNING') {
            $this->writeToFile('error.log', $line);
        }
    }

    private function writeToFile(string $filename, string $line): void
    {
        $filepath = $this->logDir . $filename;
        try {
            file_put_contents($filepath, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // No podemos loguear el error del logger, silenciamos
        }
    }

    /**
     * Obtiene el origen de la llamada (clase::método)
     */
    public static function getCaller(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = $trace[2] ?? [];
        $class = $caller['class'] ?? 'unknown';
        $function = $caller['function'] ?? 'unknown';
        return $class . '::' . $function;
    }
}
