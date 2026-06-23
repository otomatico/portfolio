CREATE TABLE IF NOT EXISTS salas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    aforo INTEGER NOT NULL DEFAULT 1,
    descripcion TEXT DEFAULT '',
    sucursal_id INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
);
