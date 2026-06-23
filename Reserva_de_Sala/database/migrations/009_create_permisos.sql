CREATE TABLE IF NOT EXISTS permisos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    rol TEXT NOT NULL,
    componente TEXT NOT NULL,
    permiso_lectura INTEGER NOT NULL DEFAULT 0,
    permiso_creacion INTEGER NOT NULL DEFAULT 0,
    permiso_actualizacion INTEGER NOT NULL DEFAULT 0,
    permiso_eliminacion INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (rol, componente)
);
