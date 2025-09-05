<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class DashboardController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // GET /dashboard?filter=month
public function index() {
    header("Content-Type: application/json; charset=UTF-8");

    try {
        $filter = $_GET['filter'] ?? 'today';
        $start  = $_GET['start'] ?? null;
        $end    = $_GET['end'] ?? null;

        $dateCondition = $this->getDateCondition($filter, $start, $end);

        $branches    = (int) $this->conn->query("SELECT COUNT(*) FROM branches")->fetchColumn();
        $products    = (int) $this->conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $customers   = (int) $this->conn->query("SELECT COUNT(*) FROM customers")->fetchColumn();
        $orders      = (int) $this->conn->query("SELECT COUNT(*) FROM orders WHERE $dateCondition")->fetchColumn();
        $totalIncome = (float) $this->conn->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE $dateCondition")->fetchColumn();

        // Ventas por día
        $stmt = $this->conn->query("
            SELECT DATE(created_at) as d,
                   COUNT(id) as orders,
                   SUM(total) as income
            FROM orders
            WHERE $dateCondition
            GROUP BY DATE(created_at)
            ORDER BY d ASC
        ");
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$sales) {
            $sales = [
                ["d" => date("Y-m-d"), "orders" => 0, "income" => 0]
            ];
        }

        // Top productos
        $stmt = $this->conn->query("
            SELECT p.name, COUNT(od.id) as total 
            FROM order_details od 
            JOIN products p ON p.id=od.product_id 
            JOIN orders o ON o.id=od.order_id 
            WHERE $dateCondition
            GROUP BY p.id 
            ORDER BY total DESC 
            LIMIT 5
        ");
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Top clientes
        $stmt = $this->conn->query("
            SELECT c.name, COUNT(o.id) as total 
            FROM customers c 
            JOIN orders o ON o.customer_id=c.id 
            WHERE $dateCondition
            GROUP BY c.id 
            ORDER BY total DESC 
            LIMIT 5
        ");
        $topCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        echo json_encode([
            "branches"      => $branches,
            "products"      => $products,
            "customers"     => $customers,
            "orders"        => $orders,
            "income"        => $totalIncome,
            "sales"         => [
                "labels" => array_column($sales, "d"),
                "orders" => array_map("intval", array_column($sales, "orders")),
                "income" => array_map("floatval", array_column($sales, "income"))
            ],
            "top_products"  => $topProducts,
            "top_customers" => $topCustomers
        ]);

    } catch (\Throwable $e) {
        echo json_encode([
            "branches"      => 0,
            "products"      => 0,
            "customers"     => 0,
            "orders"        => 0,
            "income"        => 0,
            "sales"         => [
                "labels" => [date("Y-m-d")],
                "orders" => [0],
                "income" => [0]
            ],
            "top_products"  => [],
            "top_customers" => [],
            "error"         => $e->getMessage()
        ]);
    }
}

    private function getDateCondition($filter, $start, $end) {
        switch ($filter) {
            case "today": return "DATE(created_at)=CURDATE()";
            case "yesterday": return "DATE(created_at)=CURDATE()-INTERVAL 1 DAY";
            case "week": return "YEARWEEK(created_at)=YEARWEEK(CURDATE())";
            case "month": return "YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())";
            case "last_month": return "YEAR(created_at)=YEAR(CURDATE() - INTERVAL 1 MONTH) 
                                       AND MONTH(created_at)=MONTH(CURDATE() - INTERVAL 1 MONTH)";
            case "year": return "YEAR(created_at)=YEAR(CURDATE())";
            case "custom":
                if ($start && $end) return "DATE(created_at) BETWEEN '$start' AND '$end'";
                return "1=1";
            default: return "1=1";
        }
    }
}
