<?php
class Migration {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Method to create the database
    public function createDatabase($dbName) {
        $sql = "CREATE DATABASE $dbName";

        if (pg_query($this->db, $sql)) {
            echo "Database '$dbName' created successfully";
        } else {
            echo "Error creating database: " . pg_last_error($this->db);
        }
    }

    // Method to check if a database exists
    public function databaseExists($dbName) {
        $sql    = "SELECT datname FROM pg_database WHERE datname = $1";
        $stmt   = pg_prepare($this->db, "", $sql);
        $result = pg_execute($this->db, "", array($dbName));

        return ($result && pg_num_rows($result) > 0);
    }

    // Method to check if a table exists
    public function tableExists($tableName) {
        $sql    = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = $1";
        $stmt   = pg_prepare($this->db, "", $sql);
        $result = pg_execute($this->db, "", array($tableName));

        return ($result && pg_num_rows($result) > 0);
    }

    // Method to create the 'mails' table
    public function createMailsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS mails (
            id SERIAL PRIMARY KEY,
            recipient VARCHAR(64) NOT NULL,
            mail_subject VARCHAR(64) NOT NULL,
            mail_body TEXT NOT NULL,
            status VARCHAR(50) DEFAULT 'pending' NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if (pg_query($this->db, $sql)) {
            echo "Table Mails created successfully";
        } else {
            echo "Error creating table: " . pg_last_error($this->db);
        }
    }
}
?>
