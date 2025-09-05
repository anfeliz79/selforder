<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class AuthController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        session_start();
    }

    // POST /auth/login
    public function login() {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username=:u LIMIT 1");
        $stmt->execute([":u"=>$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                "id"=>$user['id'],
                "username"=>$user['username'],
                "role"=>$user['role']
            ];
            echo json_encode(["success"=>true]);
        } else {
            echo json_encode(["success"=>false,"message"=>"Credenciales inválidas"]);
        }
    }

    // GET /auth/logout
    public function logout() {
        session_destroy();
        header("Location: /login.php");
    }
}
