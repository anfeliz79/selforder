<?php
namespace App\Models;

use PDO;
use App\Config\Database;

class Table {
    private $conn;
    private $table = "tables";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // 🔹 Listar mesas de una sucursal
    public function getByBranch($branchId) {
        $query = "SELECT * FROM " . $this->table . " WHERE branch_id = :branch_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":branch_id", $branchId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Crear mesa
    public function create($branchId, $tableNumber, $qrCode = null) {
        $query = "INSERT INTO " . $this->table . " (branch_id, table_number, qr_code) 
                  VALUES (:branch_id, :table_number, :qr_code)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":branch_id", $branchId, PDO::PARAM_INT);
        $stmt->bindParam(":table_number", $tableNumber);
        $stmt->bindParam(":qr_code", $qrCode);
        return $stmt->execute();
    }

    // 🔹 Actualizar mesa
    public function update($id, $branchId, $tableNumber, $qrCode = null) {
        $query = "UPDATE " . $this->table . " 
                  SET table_number = :table_number, qr_code = :qr_code 
                  WHERE id = :id AND branch_id = :branch_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":branch_id", $branchId, PDO::PARAM_INT);
        $stmt->bindParam(":table_number", $tableNumber);
        $stmt->bindParam(":qr_code", $qrCode);
        return $stmt->execute();
    }

    // 🔹 Eliminar mesa
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // 🔹 Obtener mesa por ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Solo actualizar la ruta del QR
    public function updateQR($id, $path) {
        $query = "UPDATE " . $this->table . " SET qr_code = :qr WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([":qr" => $path, ":id" => $id]);
    }
}
