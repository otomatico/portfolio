-- Seed: Maestros (grupos de datos maestros)
INSERT OR IGNORE INTO maestros (codigo, nombre) VALUES ('user_role', 'Roles de Usuario');
INSERT OR IGNORE INTO maestros (codigo, nombre) VALUES ('reserva_estado', 'Estados de Reserva');
INSERT OR IGNORE INTO maestros (codigo, nombre) VALUES ('tipo_recurso', 'Tipos de Recurso');

-- Seed: Opciones de Maestro para user_role
INSERT OR IGNORE INTO opciones_maestro (maestro_codigo, codigo, nombre, orden, activo) VALUES ('user_role', 'admin', 'Administrador', 1, 1);
INSERT OR IGNORE INTO opciones_maestro (maestro_codigo, codigo, nombre, orden, activo) VALUES ('user_role', 'coordinador', 'Coordinador', 2, 1);

-- Seed: Opciones de Maestro para reserva_estado
INSERT OR IGNORE INTO opciones_maestro (maestro_codigo, codigo, nombre, orden, activo) VALUES ('reserva_estado', 'confirmada', 'Confirmada', 1, 1);
INSERT OR IGNORE INTO opciones_maestro (maestro_codigo, codigo, nombre, orden, activo) VALUES ('reserva_estado', 'cancelada', 'Cancelada', 2, 1);

-- Seed: Opciones de Maestro para tipo_recurso
INSERT OR IGNORE INTO opciones_maestro (maestro_codigo, codigo, nombre, orden, activo) VALUES ('tipo_recurso', 'proyector', 'Proyector', 1, 1);
INSERT OR IGNORE INTO opciones_maestro (maestro_codigo, codigo, nombre, orden, activo) VALUES ('tipo_recurso', 'pizarra', 'Pizarra', 2, 1);
INSERT OR IGNORE INTO opciones_maestro (maestro_codigo, codigo, nombre, orden, activo) VALUES ('tipo_recurso', 'tv', 'TV', 3, 1);
INSERT OR IGNORE INTO opciones_maestro (maestro_codigo, codigo, nombre, orden, activo) VALUES ('tipo_recurso', 'equipo_audio', 'Equipo de Audio', 4, 1);

-- Seed: Permisos para admin (todo true en todos componentes)
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('admin', 'auth', 1, 1, 1, 0);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('admin', 'sucursales', 1, 1, 1, 1);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('admin', 'salas', 1, 1, 1, 1);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('admin', 'recursos', 1, 1, 1, 1);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('admin', 'reservas', 1, 1, 1, 1);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('admin', 'usuarios', 1, 1, 1, 1);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('admin', 'maestros', 1, 1, 1, 1);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('admin', 'permisos', 1, 0, 1, 0);

-- Seed: Permisos para coordinador (lectura en sucursales/salas/recursos, lectura+creacion en reservas, delete para cancelar)
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('coordinador', 'sucursales', 1, 0, 0, 0);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('coordinador', 'salas', 1, 0, 0, 0);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('coordinador', 'recursos', 1, 0, 0, 0);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('coordinador', 'reservas', 1, 1, 0, 1);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('coordinador', 'usuarios', 0, 0, 0, 0);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('coordinador', 'maestros', 0, 0, 0, 0);
INSERT OR IGNORE INTO permisos (rol, componente, permiso_lectura, permiso_creacion, permiso_actualizacion, permiso_eliminacion) VALUES ('coordinador', 'permisos', 0, 0, 0, 0);

-- Seed: Sucursal por defecto
INSERT OR IGNORE INTO sucursales (nombre, direccion) VALUES ('Sucursal Centro', 'Calle Principal 123');

-- Seed: Usuarios por defecto
-- Password: Password123 (bcrypt hash)
INSERT OR IGNORE INTO usuarios (nombre, email, password, rol, sucursal_id) VALUES ('Administrador', 'admin@example.com', '$2y$12$TXjUt/j17zimaxUc8NG5fu6wcZlXQY.DHWYkezYFPmq8Qd039Btnu', 'admin', NULL);
INSERT OR IGNORE INTO usuarios (nombre, email, password, rol, sucursal_id) VALUES ('Coordinador Centro', 'coordinador@example.com', '$2y$12$jsIYsBni22bplq/qROCrHOUIOggI9BZaov.724q.cc2PckS8j2lo6', 'coordinador', (SELECT id FROM sucursales WHERE nombre = 'Sucursal Centro'));
