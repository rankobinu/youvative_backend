<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct() {
        // Get values from environment variables
        $this->host = getenv('DB_HOST') ?: 'mysql-1a06f84a-youvative.i.aivencloud.com';
        $this->db_name = getenv('DB_NAME') ?: 'defaultdb';
        $this->username = getenv('DB_USER') ?: 'avnadmin';
        $this->password = getenv('DB_PASS') ?: '';
        $this->port = getenv('DB_PORT') ?: '19652';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4", 
                      $this->username, 
                      $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            http_response_code(500);
            return null;
        }
        return $this->conn;
    }

    // Method to prepare SQL statements
    public function prepare($sql) {
        $connection = $this->getConnection();
        if ($connection) {
            return $connection->prepare($sql);
        }
        return null;
    }
}



