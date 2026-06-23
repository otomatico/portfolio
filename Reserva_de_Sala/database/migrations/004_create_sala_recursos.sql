CREATE TABLE IF NOT EXISTS sala_recursos (
    sala_id INTEGER NOT NULL,
    recurso_id INTEGER NOT NULL,
    cantidad INTEGER NOT NULL DEFAULT 1,
    PRIMARY KEY (sala_id, recurso_id),
    FOREIGN KEY (sala_id) REFERENCES salas(id),
    FOREIGN KEY (recurso_id) REFERENCES recursos(id)
);
