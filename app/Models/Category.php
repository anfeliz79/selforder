<?php
namespace App\Models;

use PDO;
use App\Config\Database;

class Category {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    private function ensureConnection() {
        if ($this->conn === null) {
            throw new \Exception('Conexión a la base de datos no establecida');
        }
    }

    // 🔹 Listar categorías
    public function getAll() {
        $this->ensureConnection();
        $stmt = $this->conn->query("SELECT id, name FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Crear categoría
    public function create($name) {
        $this->ensureConnection();
        $stmt = $this->conn->prepare("INSERT INTO categories (name) VALUES (:name)");
        $stmt->execute([":name" => $name]);
        return $this->conn->lastInsertId();
    }

    // 🔹 Actualizar categoría
    public function update($id, $name) {
        $this->ensureConnection();
        $stmt = $this->conn->prepare("UPDATE categories SET name = :name WHERE id = :id");
        return $stmt->execute([
            ":id"   => $id,
            ":name" => $name
        ]);
    }

    // 🔹 Eliminar categoría
    public function delete($id) {
        $this->ensureConnection();
        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([":id" => $id]);
    }
}
