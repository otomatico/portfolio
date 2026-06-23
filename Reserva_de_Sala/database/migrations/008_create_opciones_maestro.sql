CREATE TABLE IF NOT EXISTS opciones_maestro (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    maestro_codigo TEXT NOT NULL,
    codigo TEXT NOT NULL,
    nombre TEXT NOT NULL,
    orden INTEGER NOT NULL DEFAULT 0,
    activo INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (maestro_codigo) REFERENCES maestros(codigo),
    UNIQUE (maestro_codigo, codigo)
);
