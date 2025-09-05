<?php
namespace App\Models;

use PDO;
use App\Config\Database;

class Setting {
    private $conn;
    private $table = "settings";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function get() {
        $q = "SELECT * FROM " . $this->table . " ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($q);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
