<?php

namespace App;

/**
 * SQLite Create Table Demo
 */
class SQLiteCreateTable {

    /**
     * PDO object
     * @var \PDO
     */
    private $pdo;

    /**
     * connect to the SQLite database
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * create tables 
     */
    public function createTables() {
        $commands = [
            'CREATE TABLE IF NOT EXISTS camion (
                value TEXT PRIMARY KEY,
                state TEXT CHECK(state IN ("Fini", "En cours", "Disponible", "Plein", "Sigma", "TDR")) NOT NULL
            )',
            
            'CREATE TABLE IF NOT EXISTS buttons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                camion_value TEXT,
                state TEXT CHECK(state IN ("Disponible", "Plein", "Sigma", "TDR")) NOT NULL,
                FOREIGN KEY (camion_value) REFERENCES camion(value)
            )',
            
            'CREATE TABLE IF NOT EXISTS camion_changes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                button_id INTEGER NOT NULL,
                camion_value TEXT NOT NULL,
                new_state TEXT CHECK(new_state IN ("Disponible", "Plein", "Sigma", "TDR")) NOT NULL,
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ip_address TEXT,
                FOREIGN KEY (button_id) REFERENCES buttons(id),
                FOREIGN KEY (camion_value) REFERENCES camion(value)
            )'
        ];
        
        // execute the sql commands to create new tables
        foreach ($commands as $command) {
            $this->pdo->exec($command);
        }
    }

    /**
     * Initialize buttons table with default values
     */
    public function initButtons() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM buttons");
        $buttonCount = $stmt->fetchColumn();

        if ($buttonCount < 40) {
            for ($i = $buttonCount + 1; $i <= 40; $i++) {
                $stmt = $this->pdo->prepare("INSERT INTO buttons (camion_value, state) VALUES ('None', 'Disponible');");
                $stmt->execute();
            }
        }
    }

    /**
     * Fetch buttons from the database
     */
    public function fetchButtons() {
        $stmt = $this->pdo->prepare("SELECT id, state FROM buttons;");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get the table list in the database
     */
    public function getTableList() {
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name");
        $tables = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tables[] = $row['name'];
        }

        return $tables;
    }
}
