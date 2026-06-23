CREATE TABLE IF NOT EXISTS maestros (
    codigo TEXT PRIMARY KEY,
    nombre TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT NOT NULL DEFAULT (datetime('now'))
);
