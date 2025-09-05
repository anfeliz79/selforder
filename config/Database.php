<?php
namespace App\Config;

use PDO;
use PDOException;
use RuntimeException;

class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;

        $host = getenv('DB_HOST');
        $dbName = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');

        try {
            if ($host === false || $dbName === false || $username === false || $password === false) {
                throw new RuntimeException('Faltan variables de entorno para la conexión a la base de datos.');
            }

            $this->conn = new PDO(
                "mysql:host={$host};dbname={$dbName}",
                $username,
                $password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (RuntimeException $e) {
            echo "⚠️ Configuración: " . $e->getMessage();
        } catch (PDOException $e) {
            echo "❌ Error de conexión: " . $e->getMessage();
        }

        return $this->conn;
    }
}
