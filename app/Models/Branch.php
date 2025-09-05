<?php
namespace App\Models;

use PDO;
use App\Config\Database;

class Branch {
    private $conn;
    private $table = "branches";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Obtener todas las sucursales
    public function getAll() {
        $query = "SELECT id, name, address, phone, access_key 
                  FROM {$this->table} 
                  ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear sucursal
    public function create($name, $address, $phone, $accessKey) {
        $query = "INSERT INTO {$this->table} (name, address, phone, access_key) 
                  VALUES (:name, :address, :phone, :access_key)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ":name" => $name,
            ":address" => $address,
            ":phone" => $phone,
            ":access_key" => $accessKey
        ]);
    }

    // Actualizar sucursal
    public function update($id, $name, $address, $phone, $accessKey) {
        $query = "UPDATE {$this->table} 
                  SET name = :name, address = :address, phone = :phone, access_key = :access_key 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ":id" => $id,
            ":name" => $name,
            ":address" => $address,
            ":phone" => $phone,
            ":access_key" => $accessKey
        ]);
    }

    // Eliminar sucursal
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([":id" => $id]);
    }

    public function getById($id) {
    $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

}
