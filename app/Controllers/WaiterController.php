<?php
namespace App\Controllers;

use App\Models\Branch;

class WaiterController {
    private $branchModel;

    public function __construct() {
        $this->branchModel = new Branch();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // 🔹 Página de login
    public function loginPage() {
        if (isset($_SESSION['branch_id'])) {
            header("Location: /waiter/orders.php");
            exit;
        }
        include __DIR__ . "/../../public/waiter/login.php";
    }

    // 🔹 Procesar login
    public function login() {
        $branchId = $_POST['branch_id'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$branchId || !$password) {
            $_SESSION['error'] = "Debes seleccionar sucursal y clave";
            header("Location: /waiter/login.php");
            exit;
        }

        $branch = $this->branchModel->getById($branchId);

        if (!$branch) {
            $_SESSION['error'] = "Sucursal no encontrada";
            header("Location: /waiter/login.php");
            exit;
        }

        if (!isset($branch['access_key']) || !password_verify($password, $branch['access_key'])) {
            $_SESSION['error'] = "Clave incorrecta";
            header("Location: /waiter/login.php");
            exit;
        }

        // Guardar sesión
        session_regenerate_id(true);
        $_SESSION['branch_id']   = $branch['id'];
        $_SESSION['branch_name'] = $branch['name'];

        header("Location: /waiter/orders.php");
        exit;
    }

    // 🔹 Página de pedidos
    public function ordersPage() {
        if (!isset($_SESSION['branch_id'])) {
            header("Location: /waiter/login.php");
            exit;
        }
        include __DIR__ . "/../../public/waiter/orders.php";
    }

    // 🔹 Logout
    public function logout() {
        session_start();
        session_destroy();
        header("Location: /waiter/login.php");
        exit;
    }
}
