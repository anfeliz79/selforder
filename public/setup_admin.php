<?php
require __DIR__ . "/../vendor/autoload.php";

use App\Config\Database;

$db = new Database();
$conn = $db->getConnection();

$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";

$stmt = $conn->prepare("INSERT INTO users (username,password,role) VALUES (:u,:p,:r)");
$stmt->bindParam(":u", $username);
$stmt->bindParam(":p", $password);
$stmt->bindParam(":r", $role);
$stmt->execute();

echo "✅ Usuario admin creado con contraseña admin123";
