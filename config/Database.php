<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private $host = "127.0.0.1";
    private $db_name = "selforder";
    private $username = "root";
    private $password = "Anfeliz112322";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "❌ Error de conexión: " . $e->getMessage();
        }
        return $this->conn;
    }
}
