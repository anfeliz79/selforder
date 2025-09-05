<?php
namespace App\Models;

use PDO;
use App\Config\Database;

class Customer {
    private $conn;
    private $table = "customers";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($name, $phone, $tableId) {
        $q = "INSERT INTO " . $this->table . " (name, phone, table_id) VALUES (:name, :phone, :table_id)";
        $stmt = $this->conn->prepare($q);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":table_id", $tableId);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }
}
