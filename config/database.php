<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'youva_project';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Use PDO to connect to the database
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->db_name", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }

    // Method to prepare SQL statements
    public function prepare($sql) {
        return $this->getConnection()->prepare($sql);
    }
}



