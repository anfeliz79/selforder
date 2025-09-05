<?php
namespace App\Controllers;

use App\Models\Order;
use App\Models\Branch;

class OrderController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new Order();
        $this->authenticate();
    }

    /**
     * Verifica que la petición provenga de una sucursal válida.
     * - Usa la sesión (branch_id) para peticiones de meseros
     * - O un token Bearer que coincida con el access_key de la sucursal
     * Si no se cumple, responde 401 y detiene la ejecución.
     */
    private function authenticate(): void {
        if (!empty($_SESSION['branch_id'])) {
            return; // Autenticado por sesión
        }

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
            $branchModel = new Branch();
            $branch = $branchModel->getByAccessKey($token);
            if ($branch) {
                // Guardar en sesión para uso posterior
                $_SESSION['branch_id'] = $branch['id'];
                return;
            }
        }

        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    // ================== LISTADOS ==================

    // GET /orders?branch_id=1  (cliente o admin)
    public function index() {
        header('Content-Type: application/json');

        // 📌 Si viene id → detalle
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $order = $this->model->getById($id);
            echo json_encode($order ?: []);
            return;
        }

        if (!isset($_GET['branch_id'])) {
            echo json_encode(["error" => "Falta branch_id"]);
            return;
        }

        $branchId = intval($_GET['branch_id']);
        $status   = $_GET['status'] ?? null;

        $orders = $this->model->getByBranch($branchId, $status);
        echo json_encode(["data" => $orders]);
    }

    // GET /orders/waiter?branch_id=1&status=pendiente
    public function waiter() {
        header('Content-Type: application/json');

        if (!isset($_GET['branch_id'])) {
            echo json_encode(["error" => "Falta branch_id"]);
            return;
        }

        $branchId = intval($_GET['branch_id']);
        $status   = $_GET['status'] ?? null;
        if ($status === '') {
            $status = null;
        }

        if ($status) {
            $orders = $this->model->getByBranch($branchId, $status);
        } else {
            // Solo estados activos por defecto
            $statuses = ["pendiente","preparacion","listo"];
            $orders = $this->model->getByBranch($branchId, $statuses);
        }

        echo json_encode(["data" => $orders]);
    }

    // ================== CREAR ==================

    // POST /orders
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['branch_id']) || !isset($data['table_id']) || !isset($data['customer_id']) || !isset($data['items'])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos"]);
            return;
        }

        $orderId = $this->model->create($data);
        echo json_encode(["message" => "Pedido creado", "order_id" => $orderId]);
    }

    // ================== ESTADOS ==================

    // PUT /orders?id=1
    public function updateStatus() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($_GET['id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos"]);
            return;
        }

        $success = $this->model->updateStatus($_GET['id'], $data['status']);
        if ($success) {
            echo json_encode(["message" => "Estado actualizado"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al actualizar estado"]);
        }
    }

    // PUT /orders/cancel?id=XX&customer=YY
    public function cancelItem() {
        if (!isset($_GET['id']) || !isset($_GET['customer'])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos"]);
            return;
        }
        $success = $this->model->cancelItem($_GET['id'], $_GET['customer']);
        if ($success) {
            echo json_encode(["message" => "Item cancelado"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo cancelar"]);
        }
    }
}
