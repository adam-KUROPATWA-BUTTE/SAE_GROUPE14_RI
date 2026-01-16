<?php

// phpcs:disable Generic.Files.LineLength
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?self $instance = null;
    private PDO $conn;

    private function __construct()
    {
        try {
            // CORRECTION LEVEL 9 : Cast explicite strval() pour $_ENV (type mixed)
            $host     = strval($_ENV['DB_HOST'] ?? 'localhost');
            $port     = strval($_ENV['DB_PORT'] ?? '3306');
            $dbname   = strval($_ENV['DB_NAME'] ?? '');
            $username = strval($_ENV['DB_USER'] ?? '');
            $password = strval($_ENV['DB_PASSWORD'] ?? '');
            $charset  = strval($_ENV['DB_CHARSET'] ?? 'utf8mb4');

            date_default_timezone_set('Europe/Paris');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];

            $this->conn = new PDO($dsn, $username, $password, $options);
            $this->conn->exec("SET time_zone = 'Europe/Paris'");
        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }

    private function __clone()
    {
    }
    public function __wakeup()
    {
        throw new Exception("Singleton cannot be serialized");
    }
}
