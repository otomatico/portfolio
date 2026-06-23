<?php
// Routes/api.php — Definición de rutas de la API

use App\Controllers\AuthController;
use App\Controllers\SucursalController;
use App\Controllers\SalaController;
use App\Controllers\RecursoController;
use App\Controllers\ReservaController;
use App\Controllers\UsuarioController;
use App\Controllers\MaestroController;
use App\Controllers\PermisoController;

/**
 * Mapa de rutas: [method] => [path_pattern => handler]
 */
return [
    // Autenticación
    'POST /api/auth/login' => [AuthController::class, 'login'],
    'POST /api/auth/logout' => [AuthController::class, 'logout'],
    'GET /api/auth/me' => [AuthController::class, 'me'],

    // Sucursales
    'GET /api/sucursales' => [SucursalController::class, 'index'],
    'GET /api/sucursales/{id}' => [SucursalController::class, 'show'],
    'POST /api/sucursales' => [SucursalController::class, 'store'],
    'PUT /api/sucursales/{id}' => [SucursalController::class, 'update'],
    'DELETE /api/sucursales/{id}' => [SucursalController::class, 'destroy'],

    // Salas
    'GET /api/salas' => [SalaController::class, 'index'],
    'GET /api/salas/{id}' => [SalaController::class, 'show'],
    'POST /api/salas' => [SalaController::class, 'store'],
    'PUT /api/salas/{id}' => [SalaController::class, 'update'],
    'DELETE /api/salas/{id}' => [SalaController::class, 'destroy'],

    // Salas - Recursos
    'GET /api/salas/{id}/recursos' => [SalaController::class, 'recursos'],
    'POST /api/salas/{id}/recursos' => [SalaController::class, 'asignarRecurso'],
    'DELETE /api/salas/{id}/recursos/{recursoId}' => [SalaController::class, 'desasignarRecurso'],

    // Recursos
    'GET /api/recursos' => [RecursoController::class, 'index'],
    'GET /api/recursos/{id}' => [RecursoController::class, 'show'],
    'POST /api/recursos' => [RecursoController::class, 'store'],
    'PUT /api/recursos/{id}' => [RecursoController::class, 'update'],
    'DELETE /api/recursos/{id}' => [RecursoController::class, 'destroy'],

    // Usuarios
    'GET /api/usuarios' => [UsuarioController::class, 'index'],
    'GET /api/usuarios/{id}' => [UsuarioController::class, 'show'],
    'POST /api/usuarios' => [UsuarioController::class, 'store'],
    'PUT /api/usuarios/{id}' => [UsuarioController::class, 'update'],
    'DELETE /api/usuarios/{id}' => [UsuarioController::class, 'destroy'],

    // Reservas
    'GET /api/reservas' => [ReservaController::class, 'index'],
    'GET /api/reservas/{id}' => [ReservaController::class, 'show'],
    'POST /api/reservas' => [ReservaController::class, 'store'],
    'PUT /api/reservas/{id}/cancelar' => [ReservaController::class, 'cancelar'],

    // Disponibilidad
    'GET /api/salas/{id}/disponibilidad' => [ReservaController::class, 'disponibilidad'],

    // Maestros
    'GET /api/maestros' => [MaestroController::class, 'index'],
    'GET /api/maestros/{codigo}' => [MaestroController::class, 'show'],
    'POST /api/maestros' => [MaestroController::class, 'store'],
    'PUT /api/maestros/{codigo}' => [MaestroController::class, 'update'],
    'DELETE /api/maestros/{codigo}' => [MaestroController::class, 'destroy'],

    // Maestros - Opciones
    'GET /api/maestros/{codigo}/opciones' => [MaestroController::class, 'opciones'],
    'POST /api/maestros/{codigo}/opciones' => [MaestroController::class, 'storeOpcion'],
    'PUT /api/maestros/opciones/{id}' => [MaestroController::class, 'updateOpcion'],
    'DELETE /api/maestros/opciones/{id}' => [MaestroController::class, 'destroyOpcion'],

    // Permisos
    'GET /api/permisos' => [PermisoController::class, 'index'],
    'GET /api/permisos/{rol}' => [PermisoController::class, 'show'],
    'PUT /api/permisos/{rol}/{componente}' => [PermisoController::class, 'update'],
];
