<?php
namespace App\Models;

use PDO;
use App\Config\Database;

class Product {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Listar productos
    public function getAll() {
        $sql = "SELECT id, name, description, image, base_price, category, created_at
                FROM products
                ORDER BY created_at DESC";
        $stmt = $this->conn->query($sql);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$p) {
            $p['branches'] = $this->getBranches($p['id']);
            $p['variants'] = $this->getVariants($p['id']); // solo activas
            $p['addons']   = $this->getAddons($p['id']);
        }
        return $products;
    }

    public function getById($id) {
        $sql = "SELECT id, name, description, image, base_price, category, created_at
                FROM products
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id"=>$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $product['branches'] = $this->getBranches($id);
            $product['variants'] = $this->getVariants($id); // solo activas
            $product['addons']   = $this->getAddons($id);
        }
        return $product;
    }

    // Crear producto
    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO products (name, description, image, base_price, category) 
                                      VALUES (:name,:description,:image,:base_price,:category)");
        $stmt->execute([
            ":name" => $data['name'],
            ":description" => $data['description'] ?? '',
            ":image" => $data['image'] ?? null,
            ":base_price" => $data['base_price'],
            ":category" => $data['category'] ?? ''   // texto directo
        ]);
        return $this->conn->lastInsertId();
    }

    // Actualizar producto
    public function update($data) {
        $stmt = $this->conn->prepare("UPDATE products SET 
            name=:name, description=:description, image=:image, base_price=:base_price, category=:category 
            WHERE id=:id");
        return $stmt->execute([
            ":id" => $data['id'],
            ":name" => $data['name'],
            ":description" => $data['description'] ?? '',
            ":image" => $data['image'] ?? null,
            ":base_price" => $data['base_price'],
            ":category" => $data['category'] ?? ''
        ]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id=:id");
        return $stmt->execute([":id"=>$id]);
    }

    // -------- Relaciones ----------
    public function getBranches($productId) {
        $stmt = $this->conn->prepare("SELECT * FROM product_branch WHERE product_id=:pid");
        $stmt->execute([":pid"=>$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveBranches($productId, $branches) {
        $this->conn->prepare("DELETE FROM product_branch WHERE product_id=:pid")->execute([":pid"=>$productId]);
        foreach ($branches as $b) {
            $stmt = $this->conn->prepare(
                "INSERT INTO product_branch (product_id, branch_id, custom_price) 
                 VALUES (:pid,:bid,:price)"
            );
            $stmt->execute([
                ":pid"=>$productId, 
                ":bid"=>$b['branch_id'], 
                ":price"=>$b['custom_price'] ?? null
            ]);
        }
    }

    // Variantes (solo activas)
    public function getVariants($productId) {
        $stmt = $this->conn->prepare("SELECT id, name, price 
                                      FROM product_variants 
                                      WHERE product_id=:pid AND is_active=1");
        $stmt->execute([":pid"=>$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Soft delete de variantes
    public function saveVariants($productId, $variants) {
        // 🔹 Paso 1: marcar todas las variantes existentes como inactivas
        $this->conn->prepare("UPDATE product_variants SET is_active=0 WHERE product_id=:pid")
                   ->execute([":pid"=>$productId]);

        // 🔹 Paso 2: reactivar o insertar las variantes enviadas
        foreach ($variants as $v) {
            if (!empty($v['id'])) {
                // existe → actualizar y marcar activa
                $stmt = $this->conn->prepare(
                    "UPDATE product_variants 
                     SET name=:name, price=:price, is_active=1 
                     WHERE id=:id AND product_id=:pid"
                );
                $stmt->execute([
                    ":id"=>$v['id'],
                    ":pid"=>$productId,
                    ":name"=>$v['name'],
                    ":price"=>$v['price']
                ]);
            } else {
                // nueva → insertar
                $stmt = $this->conn->prepare(
                    "INSERT INTO product_variants (product_id, name, price, is_active) 
                     VALUES (:pid,:name,:price,1)"
                );
                $stmt->execute([
                    ":pid"=>$productId,
                    ":name"=>$v['name'],
                    ":price"=>$v['price']
                ]);
            }
        }
    }

public function getAddons($productId) {
    $stmt = $this->conn->prepare("SELECT id, name, price 
                                  FROM product_addons 
                                  WHERE product_id=:pid AND is_active=1");
    $stmt->execute([":pid"=>$productId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function saveAddons($productId, $addons) {
    // 🔹 Primero, desactivar todos los addons actuales
    $this->conn->prepare("UPDATE product_addons SET is_active = 0 WHERE product_id=:pid")
               ->execute([":pid"=>$productId]);

    foreach ($addons as $a) {
        if (!empty($a['id'])) {
            // 🔹 Actualizar existente
            $stmt = $this->conn->prepare(
                "UPDATE product_addons 
                 SET name=:name, price=:price, is_active=1 
                 WHERE id=:id AND product_id=:pid"
            );
            $stmt->execute([
                ":id"   => $a['id'],
                ":pid"  => $productId,
                ":name" => $a['name'],
                ":price"=> $a['price']
            ]);
        } else {
            // 🔹 Insertar nuevo
            $stmt = $this->conn->prepare(
                "INSERT INTO product_addons (product_id, name, price, is_active) 
                 VALUES (:pid,:name,:price,1)"
            );
            $stmt->execute([
                ":pid"  => $productId,
                ":name" => $a['name'],
                ":price"=> $a['price']
            ]);
        }
    }
}


    // 🔹 Devolver solo nombres de categorías
    public function getCategories() {
        $stmt = $this->conn->query("SELECT name FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // ["Bebidas","Comidas",...]
    }
}
