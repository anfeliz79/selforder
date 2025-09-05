<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;

class ProductController {
    private $model;
    private $catModel;

    public function __construct() {
        $this->model = new Product();
        $this->catModel = new Category();
    }

    // 🔹 GET /products
    public function index() {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            if (isset($_GET['id'])) {
                $product = $this->model->getById($_GET['id']);
                echo json_encode($product ?: []);
            } else {
                $all = $this->model->getAll();
                echo json_encode([ "data" => $all ]);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                "error" => "Error al obtener productos",
                "details" => $e->getMessage()
            ]);
        }
    }

    // 🔹 POST /products (crear)
    public function store() {
        $data = $_POST;

        // Imagen
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2 MB
                http_response_code(400);
                echo json_encode(["error" => "La imagen supera el tamaño máximo permitido (2 MB)"]);
                return;
            }

            $uploadDir = __DIR__ . "/../../public/uploads/products/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $filename = time() . "_" . basename($_FILES["image"]["name"]);
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
                $imagePath = "/uploads/products/" . $filename;
            }
        }

        $id = $this->model->create([
            "name"        => $data['name'],
            "description" => $data['description'] ?? '',
            "image"       => $imagePath,
            "base_price"  => $data['base_price'],
            "category"    => $data['category'] ?? ''
        ]);

        if (!empty($data['branches'])) $this->model->saveBranches($id, json_decode($data['branches'], true));
        if (!empty($data['variants'])) $this->model->saveVariants($id, json_decode($data['variants'], true));
        if (!empty($data['addons']))   $this->model->saveAddons($id,   json_decode($data['addons'], true));

        echo json_encode(["message" => "Producto creado", "id"=>$id]);
    }

    // 🔹 POST /products?id=XX (actualizar)
    public function update() {
        $data = $_POST;
        $id = $data['id'] ?? $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Falta id"]);
            return;
        }

        // Imagen (nueva o conservar actual)
        $imagePath = $data['current_image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(["error" => "La imagen supera el tamaño máximo permitido (2 MB)"]);
                return;
            }

            $uploadDir = __DIR__ . "/../../public/uploads/products/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $filename = time() . "_" . basename($_FILES["image"]["name"]);
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
                $imagePath = "/uploads/products/" . $filename;
            }
        }

        $this->model->update([
            "id"          => $id,
            "name"        => $data['name'],
            "description" => $data['description'] ?? '',
            "image"       => $imagePath,
            "base_price"  => $data['base_price'],
            "category"    => $data['category'] ?? ''
        ]);

        if (!empty($data['branches'])) $this->model->saveBranches($id, json_decode($data['branches'], true));
        if (!empty($data['variants'])) $this->model->saveVariants($id, json_decode($data['variants'], true));
        if (!empty($data['addons']))   $this->model->saveAddons($id,   json_decode($data['addons'], true));

        echo json_encode(["message" => "Producto actualizado"]);
    }

    // 🔹 DELETE /products?id=#
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->delete($id);
            echo json_encode(["message"=>"Producto eliminado"]);
        } else {
            http_response_code(400);
            echo json_encode(["error"=>"Falta id"]);
        }
    }

    // ================== 📌 CATEGORÍAS ==================

    // GET /categories
    public function categories() {
        header('Content-Type: application/json');
        echo json_encode([ "data" => $this->catModel->getAll() ]);
    }

    // POST /categories
    public function categoriesStore() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['name']) || strlen($data['name']) < 3) {
            http_response_code(400);
            echo json_encode(["error"=>"El nombre debe tener al menos 3 caracteres"]);
            return;
        }

        $this->catModel->create($data['name']);
        echo json_encode(["message"=>"Categoría creada correctamente"]);
    }

    // PUT /categories?id=#
    public function categoriesUpdate() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['id']) || empty($data['name'])) {
            http_response_code(400);
            echo json_encode(["error"=>"Faltan datos"]);
            return;
        }

        $this->catModel->update($data['id'], $data['name']);
        echo json_encode(["message"=>"Categoría actualizada"]);
    }

    // DELETE /categories?id=#
    public function categoriesDelete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error"=>"Falta id"]);
            return;
        }
        try {
            $this->catModel->delete($id);
            echo json_encode(["message"=>"Categoría eliminada"]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["error"=>"No se puede eliminar, categoría en uso"]);
        }
    }
}
