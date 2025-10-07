<?php
class Database
{
    private static $instance = null;
    private $conn;

    private $host = 'db-sae-ri-do-user-18319910-0.f.db.ondigitalocean.com';
    private $port = '25060';
    private $dbname = 'defaultdb';
    private $username = 'doadmin';
    private $password = 'AVNS_GRx9GzxHWjKfJkBwcQY';
    private $charset = 'utf8mb4';

    private function __construct()
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

            error_log("✅ Connexion à la base de données réussie");

        } catch (PDOException $e) {
            error_log("❌ DB Error: " . $e->getMessage());
            error_log("DSN utilisé: mysql:host={$this->host};port={$this->port};dbname={$this->dbname}");

            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
