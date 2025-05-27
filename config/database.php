<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Get values from environment variables
        $this->host = getenv('DB_HOST') ?: 'default-host';
        $this->db_name = getenv('DB_NAME') ?: 'defaultdb';
        $this->username = getenv('DB_USER') ?: 'defaultuser';
        $this->password = getenv('DB_PASS') ?: 'defaultpass';

    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4", 
                      $this->username, 
                      $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage()); // 🔥 Logs error instead of exposing it
            http_response_code(500); // Sends a server error response
            return null; // Prevents further execution if connection fails
        }
        return $this->conn;
    }

    // Method to prepare SQL statements
    public function prepare($sql) {
        return $this->getConnection()->prepare($sql);
    }
}



