<?php
namespace App\Controllers;

use App\Models\Table;

// Importar librería phpqrcode
require_once __DIR__ . "/../Libraries/phpqrcode/qrlib.php";

class TableController {
    private $model;

    public function __construct() {
        $this->model = new Table();
    }

    // 🔹 GET /tables?branch_id=#
// GET /tables?branch_id=1
public function index() {
    header('Content-Type: application/json; charset=UTF-8');
    try {
        if (!isset($_GET['branch_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Falta branch_id"]);
            return;
        }

        $branchId = intval($_GET['branch_id']);
        $mesas = $this->model->getByBranch($branchId);

        // 🔹 forzar array aunque sea null
        if (!$mesas) $mesas = [];

        echo json_encode($mesas);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Error al obtener mesas",
            "details" => $e->getMessage()
        ]);
    }
}


    // 🔹 POST /tables
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['branch_id']) || empty($data['table_number'])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos"]);
            return;
        }

        // Generar QR
        $qrUrl = $this->generateQrFile($data['branch_id'], $data['table_number']);

        $success = $this->model->create($data['branch_id'], $data['table_number'], $qrUrl);

        if ($success) {
            echo json_encode([
                "message" => "Mesa creada correctamente",
                "qr_url"  => $qrUrl
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al crear la mesa"]);
        }
    }

    // 🔹 PUT /tables
    public function update() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['id']) || empty($data['branch_id']) || empty($data['table_number'])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos"]);
            return;
        }

        // Generar QR
        $qrUrl = $this->generateQrFile($data['branch_id'], $data['table_number']);

        $success = $this->model->update($data['id'], $data['branch_id'], $data['table_number'], $qrUrl);

        if ($success) {
            echo json_encode([
                "message" => "Mesa actualizada correctamente",
                "qr_url"  => $qrUrl
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al actualizar la mesa"]);
        }
    }

    // 🔹 DELETE /tables?id=#
    public function delete() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Falta id"]);
            return;
        }

        $success = $this->model->delete($id);

        if ($success) {
            echo json_encode(["message" => "Mesa eliminada correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al eliminar la mesa"]);
        }
    }

    // 🔹 GET /tables/qr?id=#
    public function generateQR() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Falta id"]);
            return;
        }

        // Buscar mesa en DB
        $table = $this->model->getById($id);
        if (!$table) {
            http_response_code(404);
            echo json_encode(["error" => "Mesa no encontrada"]);
            return;
        }

        // Generar QR
        $qrUrl = $this->generateQrFile($table['branch_id'], $table['table_number']);

        // Guardar en DB
        $this->model->updateQR($id, $qrUrl);

        echo json_encode(["qr" => $qrUrl]);
    }

    // 🔹 Función privada para generar QR
    private function generateQrFile($branchId, $tableNumber) {
        // Construir la URL base
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $baseUrl = $protocol . "://" . $host . $basePath;

        // Contenido del QR
        $qrContent = $baseUrl . "/order?sucursal={$branchId}&mesa={$tableNumber}";

        // Carpeta donde guardar los QR
        $uploadDir = __DIR__ . "/../../public/uploads/qrs/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = "table_{$branchId}_{$tableNumber}.png";
        $filePath = $uploadDir . $fileName;
        $publicPath = "/uploads/qrs/" . $fileName;

        // Generar el QR usando phpqrcode
        ob_clean(); // limpiar buffer para evitar que se mezclen headers
        \QRcode::png($qrContent, $filePath, QR_ECLEVEL_L, 6);

        return $publicPath;
    }
}
