<?php
namespace App\Controllers;

use App\Models\Customer;

class CustomerController {
    private $model;

    public function __construct() {
        $this->model = new Customer();
    }

    // POST /customers
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name']) || !isset($data['phone']) || !isset($data['table_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos"]);
            return;
        }

        $id = $this->model->create($data['name'], $data['phone'], $data['table_id']);
        echo json_encode(["message" => "Cliente registrado", "customer_id" => $id]);
    }
}
