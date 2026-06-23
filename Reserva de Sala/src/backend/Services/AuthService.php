<?php
// Services/AuthService.php

namespace App\Services;

use App\Config\Database;
use App\Log\Logger;
use App\Repositories\UsuarioRepository;

class AuthService
{
    private UsuarioRepository $usuarioRepository;
    private Logger $logger;
    private array $config;

    public function __construct(Database $database)
    {
        $this->usuarioRepository = new UsuarioRepository($database);
        $this->logger = new Logger();
        $this->config = require __DIR__ . '/../Config/app.php';
    }

    /**
     * Intenta autenticar un usuario con email y contraseña
     */
    public function login(string $email, string $password): array
    {
        $usuario = $this->usuarioRepository->findByEmail($email);

        if (!$usuario || !password_verify($password, $usuario->password)) {
            $this->logger->warning('Credenciales inválidas', [
                'email' => $email,
                'caller' => Logger::getCaller(),
            ]);
            throw new \RuntimeException('Credenciales inválidas');
        }

        $token = $this->generateJWT($usuario);

        $this->logger->info('Inicio de sesión exitoso', [
            'usuario_id' => $usuario->id,
            'email' => $usuario->email,
            'rol' => $usuario->rol,
        ]);

        return [
            'token' => $token,
            'user' => $usuario->toArray(),
        ];
    }

    /**
     * Obtiene el usuario autenticado a partir del payload del JWT
     */
    public function me(array $jwtPayload): array
    {
        $usuario = $this->usuarioRepository->findById((int) $jwtPayload['sub']);
        if (!$usuario) {
            throw new \RuntimeException('Usuario no encontrado');
        }
        return $usuario->toArray();
    }

    /**
     * Genera un token JWT
     */
    private function generateJWT(\App\Models\Usuario $usuario): string
    {
        $jwtConfig = $this->config['jwt'];
        $issuedAt = time();
        $expiresAt = $issuedAt + $jwtConfig['expiration'];

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'iss' => $jwtConfig['issuer'],
            'sub' => $usuario->id,
            'email' => $usuario->email,
            'rol' => $usuario->rol,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ];

        $base64Header = $this->base64UrlEncode(json_encode($header));
        $base64Payload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "{$base64Header}.{$base64Payload}", $jwtConfig['secret'], true);
        $base64Signature = $this->base64UrlEncode($signature);

        return "{$base64Header}.{$base64Payload}.{$base64Signature}";
    }

    /**
     * Valida y decodifica un token JWT
     */
    public function validateJWT(string $token): ?array
    {
        $jwtConfig = $this->config['jwt'];
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        // Verificar firma
        $signature = $this->base64UrlDecode($base64Signature);
        $expectedSignature = hash_hmac('sha256', "{$base64Header}.{$base64Payload}", $jwtConfig['secret'], true);

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($base64Payload), true);

        if (!$payload || !isset($payload['exp'])) {
            return null;
        }

        // Verificar expiración
        if ($payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
