CREATE TABLE buttons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    button_number INTEGER NOT NULL UNIQUE,
    camion_value TEXT UNIQUE,
    FOREIGN KEY (camion_value) REFERENCES camion(value)
);

CREATE TABLE camion (
    value TEXT PRIMARY KEY,
    new_state TEXT CHECK(new_state IN ('Disponible', 'Plein', 'Sigma', 'TDR')) NOT NULL,
    ip_address TEXT
);

CREATE TABLE camion_changes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    button_id INTEGER NOT NULL,
    camion_value TEXT NOT NULL,
    new_state TEXT CHECK(new_state IN ('Disponible', 'Plein', 'Sigma', 'TDR')) NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address TEXT,
    FOREIGN KEY (button_id) REFERENCES buttons(id),
    FOREIGN KEY (camion_value) REFERENCES camion(value)
);
