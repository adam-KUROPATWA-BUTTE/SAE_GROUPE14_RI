<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class Database
{
    private static $instance = null;
    private $conn;

    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $charset;

    private function __construct()
    {
        try {
            // Charger les variables d'environnement
            $this->host     = $_ENV['DB_HOST'];
            $this->port     = $_ENV['DB_PORT'];
            $this->dbname   = $_ENV['DB_NAME'];
            $this->username = $_ENV['DB_USER'];
            $this->password = $_ENV['DB_PASSWORD'];
            $this->charset  = $_ENV['DB_CHARSET'];

            // Définir le fuseau horaire de PHP
            date_default_timezone_set('Europe/Paris');

            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

            // Définir le fuseau horaire MySQL
            $this->conn->exec("SET time_zone = 'Europe/Paris'");

            error_log("✅ Connexion à la base de données réussie");
        } catch (PDOException $e) {
            error_log("❌ DB Error: " . $e->getMessage());
            die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
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

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
