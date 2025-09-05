<?php
namespace App\Controllers;

use App\Models\Branch;
use Exception;

class BranchController {
    private $model;

    public function __construct() {
        $this->model = new Branch();
        header('Content-Type: application/json');
    }

    // 🔹 GET /branches
    public function index() {
        try {
            $branches = $this->model->getAll();
            echo json_encode(["data" => $branches]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // 🔹 POST /branches
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['name']) || empty($data['address']) || empty($data['phone']) || empty($data['access_key'])) {
            http_response_code(400);
            echo json_encode(["success"=>false, "error"=>"Faltan datos"]);
            return;
        }

        try {
            $hashedKey = password_hash($data['access_key'], PASSWORD_DEFAULT);
            $success = $this->model->create($data['name'], $data['address'], $data['phone'], $hashedKey);
            echo json_encode(["success"=>$success, "message"=>"Sucursal creada correctamente"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["success"=>false, "error"=>$e->getMessage()]);
        }
    }

    // 🔹 PUT /branches?id=#
    public function update() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['id']) || empty($data['name']) || empty($data['address']) || empty($data['phone']) || empty($data['access_key'])) {
            http_response_code(400);
            echo json_encode(["success"=>false, "error"=>"Faltan datos"]);
            return;
        }

        try {
            $hashedKey = password_hash($data['access_key'], PASSWORD_DEFAULT);
            $success = $this->model->update($data['id'], $data['name'], $data['address'], $data['phone'], $hashedKey);
            echo json_encode(["success"=>$success, "message"=>"Sucursal actualizada correctamente"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["success"=>false, "error"=>$e->getMessage()]);
        }
    }

    // 🔹 DELETE /branches?id=#
    public function delete() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(["success"=>false, "error"=>"ID requerido"]);
            return;
        }

        try {
            $success = $this->model->delete($id);
            echo json_encode(["success"=>$success, "message"=>"Sucursal eliminada correctamente"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["success"=>false, "error"=>$e->getMessage()]);
        }
    }
}
