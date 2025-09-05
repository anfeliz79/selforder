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

        $validFilters = ['today', 'yesterday', 'week', 'month', 'last_month', 'year', 'custom'];
        if (!in_array($filter, $validFilters, true)) {
            $filter = 'today';
        }

        [$dateCondition, $params] = $this->getDateCondition($filter, $start, $end);

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM branches");
        $stmt->execute();
        $branches = (int) $stmt->fetchColumn();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM products");
        $stmt->execute();
        $products = (int) $stmt->fetchColumn();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM customers");
        $stmt->execute();
        $customers = (int) $stmt->fetchColumn();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE $dateCondition");
        $stmt->execute($params);
        $orders = (int) $stmt->fetchColumn();

        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(total),0) FROM orders WHERE $dateCondition");
        $stmt->execute($params);
        $totalIncome = (float) $stmt->fetchColumn();

        // Ventas por día
        $stmt = $this->conn->prepare("
            SELECT DATE(created_at) as d,
                   COUNT(id) as orders,
                   SUM(total) as income
            FROM orders
            WHERE $dateCondition
            GROUP BY DATE(created_at)
            ORDER BY d ASC
        ");
        $stmt->execute($params);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$sales) {
            $sales = [
                ["d" => date("Y-m-d"), "orders" => 0, "income" => 0]
            ];
        }

        // Top productos
        $stmt = $this->conn->prepare("
            SELECT p.name, COUNT(od.id) as total
            FROM order_details od
            JOIN products p ON p.id=od.product_id
            JOIN orders o ON o.id=od.order_id
            WHERE $dateCondition
            GROUP BY p.id
            ORDER BY total DESC
            LIMIT 5
        ");
        $stmt->execute($params);
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Top clientes
        $stmt = $this->conn->prepare("
            SELECT c.name, COUNT(o.id) as total
            FROM customers c
            JOIN orders o ON o.customer_id=c.id
            WHERE $dateCondition
            GROUP BY c.id
            ORDER BY total DESC
            LIMIT 5
        ");
        $stmt->execute($params);
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
            case "today":
                return ["DATE(created_at)=CURDATE()", []];
            case "yesterday":
                return ["DATE(created_at)=CURDATE()-INTERVAL 1 DAY", []];
            case "week":
                return ["YEARWEEK(created_at)=YEARWEEK(CURDATE())", []];
            case "month":
                return ["YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())", []];
            case "last_month":
                return ["YEAR(created_at)=YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(created_at)=MONTH(CURDATE() - INTERVAL 1 MONTH)", []];
            case "year":
                return ["YEAR(created_at)=YEAR(CURDATE())", []];
            case "custom":
                if ($start && $end) {
                    return ["DATE(created_at) BETWEEN :start AND :end", [':start' => $start, ':end' => $end]];
                }
                return ["1=1", []];
            default:
                return ["1=1", []];
        }
    }
}
