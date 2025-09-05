<?php
namespace App\Models;

use PDO;
use App\Config\Database;

class Order {
    private $conn;
    private $table = "orders";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ================== CREAR ==================
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (branch_id, table_id, customer_id, status) 
                  VALUES (:branch_id, :table_id, :customer_id, 'pendiente')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":branch_id", $data['branch_id']);
        $stmt->bindParam(":table_id", $data['table_id']);
        $stmt->bindParam(":customer_id", $data['customer_id']);
        $stmt->execute();

        $orderId = $this->conn->lastInsertId();

        foreach ($data['items'] as $item) {
            $q = "INSERT INTO order_details (order_id, product_id, variant_id, addons, quantity, price, status, comment)
                  VALUES (:order_id, :product_id, :variant_id, :addons, :quantity, :price, 'pendiente', :comment)";
            $s = $this->conn->prepare($q);
            $s->bindParam(":order_id", $orderId);
            $s->bindParam(":product_id", $item['product_id']);
            $s->bindParam(":variant_id", $item['variant_id']);
            $addons = json_encode($item['addons']);
            $s->bindParam(":addons", $addons);
            $s->bindParam(":quantity", $item['quantity']);
            $s->bindParam(":price", $item['price']);
            $s->bindParam(":comment", $item['comment']);
            $s->execute();
        }

        return $orderId;
    }

    // ================== CONSULTAS ==================

    // 📌 General por sucursal (puede filtrar por status único o array)
    public function getByBranch($branchId, $status = null) {
        $query = "
            SELECT o.id, o.table_id, o.customer_id, o.status, o.created_at,
                   c.name as customer_name, c.phone as customer_phone,
                   t.table_number, b.name as branch_name,
                   (SELECT SUM(od.price * od.quantity) FROM order_details od WHERE od.order_id=o.id) as total
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN tables t ON o.table_id = t.id
            JOIN branches b ON o.branch_id = b.id
            WHERE o.branch_id = :branch_id
        ";

        if ($status) {
            if (is_array($status)) {
                $placeholders = implode(",", array_fill(0, count($status), "?"));
                $query .= " AND o.status IN ($placeholders)";
            } else {
                $query .= " AND o.status = ?";
            }
        }

        $query .= " ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $params = [$branchId];
        if ($status) {
            $params = array_merge($params, is_array($status) ? $status : [$status]);
        }
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as &$order) {
            $order['details'] = $this->getDetails($order['id']);
        }

        return $orders;
    }

    // 📌 Detalle único
    public function getById($id) {
        $q = "
            SELECT o.id, o.branch_id, o.table_id, o.customer_id, o.status, o.created_at,
                   c.name as customer_name, c.phone as customer_phone,
                   t.table_number, b.name as branch_name,
                   (SELECT SUM(od.price * od.quantity) FROM order_details od WHERE od.order_id=o.id) as total
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN tables t ON o.table_id = t.id
            JOIN branches b ON o.branch_id = b.id
            WHERE o.id = :id
        ";
        $stmt = $this->conn->prepare($q);
        $stmt->execute([":id"=>$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $order['details'] = $this->getDetails($order['id']);
        }
        return $order;
    }

    private function getDetails($orderId) {
        $q = "
            SELECT od.id, od.product_id, p.name as product_name,
                   od.variant_id, v.name as variant_name,
                   od.addons, od.quantity, od.price, od.status, od.comment
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            LEFT JOIN product_variants v ON od.variant_id = v.id
            WHERE od.order_id = :order_id
        ";
        $s = $this->conn->prepare($q);
        $s->execute([":order_id"=>$orderId]);
        $details = $s->fetchAll(PDO::FETCH_ASSOC);

        foreach ($details as &$d) {
            $d['addons'] = $d['addons'] ? json_decode($d['addons'], true) : [];
        }
        return $details;
    }

    // ================== ESTADOS ==================

    public function updateStatus($orderId, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([":status"=>$status, ":id"=>$orderId]);
    }

    public function cancelItem($detailId, $customerId) {
        $query = "UPDATE order_details od
                  JOIN orders o ON od.order_id = o.id
                  SET od.status = 'cancelado'
                  WHERE od.id = :detail_id 
                    AND o.customer_id = :customer_id 
                    AND od.status = 'pendiente'";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([":detail_id"=>$detailId, ":customer_id"=>$customerId]);
    }
}
