<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\BranchController;
use App\Controllers\TableController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;
use App\Controllers\CustomerController;
use App\Controllers\SettingController;
use App\Controllers\DashboardController;
use App\Controllers\WaiterController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($uri) {
    // 🔹 Home
    case '/':
    case '/index.php':
        (new HomeController())->index();
        break;

    // 🔹 Branches
    case '/branches':
        $controller = new BranchController();
        if     ($method === 'GET')    $controller->index();
        elseif ($method === 'POST')   $controller->store();
        elseif ($method === 'PUT')    $controller->update();
        elseif ($method === 'DELETE') $controller->delete();
        break;

    // 🔹 Tables
    case '/tables':
        $controller = new TableController();
        if     ($method === 'GET')    $controller->index();
        elseif ($method === 'POST')   $controller->store();
        elseif ($method === 'PUT')    $controller->update();
        elseif ($method === 'DELETE') $controller->delete();
        break;

    case '/tables/qr':
        (new TableController())->generateQR();
        break;

    // 🔹 Orders
    case '/orders':
        $controller = new OrderController();
        if     ($method === 'GET')    $controller->index();
        elseif ($method === 'POST')   $controller->store();
        elseif ($method === 'PUT')    $controller->updateStatus();
        break;

    case '/orders/cancel':
        if ($method === 'PUT') {
            (new OrderController())->cancelItem();
        }
        break;

    // 🔹 Customers
    case '/customers':
        if ($method === 'POST') {
            (new CustomerController())->store();
        }
        break;

    // 🔹 Products
    case '/products':
        $controller = new ProductController();
        if     ($method === 'GET')    $controller->index();
        elseif ($method === 'POST') {
            if (isset($_GET['id'])) {
                $controller->update();   // POST + id → actualizar
            } else {
                $controller->store();    // POST sin id → crear
            }
        }
        elseif ($method === 'DELETE') $controller->delete();
        break;

    // 🔹 Categories (CRUD dentro de ProductController)
    case '/categories':
        $controller = new ProductController();
        if     ($method === 'GET')    $controller->categories();
        elseif ($method === 'POST')   $controller->categoriesStore();
        elseif ($method === 'PUT')    $controller->categoriesUpdate();
        elseif ($method === 'DELETE') $controller->categoriesDelete();
        break;

    // 🔹 Settings
    case '/settings':
        $controller = new SettingController();
        if     ($method === 'GET')    $controller->index();
        elseif ($method === 'POST')   $controller->update();
        break;

    // 🔹 Dashboard
    case '/dashboard':
        $controller = new DashboardController();
        if ($method === 'GET') {
            $controller->index();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

        // 🔹 Waiter Login & Orders
case '/waiter/login':
    $controller = new WaiterController();
    if ($method === 'GET') $controller->loginPage();
    elseif ($method === 'POST') $controller->login();
    break;
    
// 🔹 Orders para meseros
case '/orders/waiter':
    $controller = new OrderController();
    if ($method === 'GET') $controller->waiter();
    break;


case '/waiter/logout':
    $controller = new WaiterController();
    $controller->logout();
    break;

    // 🔹 Default
    default:
        http_response_code(404);
        echo "404 - Página no encontrada";
}
