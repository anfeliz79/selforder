<?php
session_start();
require __DIR__ . "/../vendor/autoload.php";

use App\Config\Database;

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (!$username || !$password) {
        echo json_encode(["success"=>false, "message"=>"Faltan datos"]);
        exit;
    }

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
    $stmt->bindParam(":u", $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            "id" => $user['id'],
            "username" => $user['username'],
            "role" => $user['role']
        ];
        echo json_encode(["success"=>true]);
    } else {
        echo json_encode(["success"=>false, "message"=>"Usuario o contraseña inválidos"]);
    }
    exit;
}

if ($method === "GET") {
    // Verificar sesión activa
    echo json_encode(["logged_in"=>isset($_SESSION['user']), "user"=>$_SESSION['user'] ?? null]);
    exit;
}

if ($method === "DELETE") {
    // Cerrar sesión (logout)
    session_destroy();
    echo json_encode(["success"=>true]);
    exit;
}

http_response_code(405);
echo json_encode(["success"=>false, "message"=>"Método no permitido"]);
