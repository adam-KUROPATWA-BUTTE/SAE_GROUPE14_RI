<?php
class Database
{
    private static $instance = null;
    private $conn;

    private $host = 'localhost';
    private $dbname = 'SAE_RI';
    private $username = 'ri_user';
    private $password = 'AdminDataRI5434';

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
            
        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            die("Erreur de connexion à la base de données.");
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
