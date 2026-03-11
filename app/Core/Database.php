<?php


class Database
{
    private static ?Database $instance = null;
    private ?PDO $conn = null;

    private string $host;
    private string $port;
    private string $dbname;
    private string $username;
    private string $password;
    private string $charset;

    private function __construct()
    {
        try {
            // Charger les variables d'environnement (avec valeurs par défaut pour éviter les crashs immédiats)
            $this->host     = $_ENV['DB_HOST'] ?? 'localhost';
            $this->port     = $_ENV['DB_PORT'] ?? '3306';
            $this->dbname   = $_ENV['DB_NAME'] ?? '';
            $this->username = $_ENV['DB_USER'] ?? '';
            $this->password = $_ENV['DB_PASSWORD'] ?? '';
            $this->charset  = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

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
            $this->conn->exec("SET time_zone = 'Europe/Paris'");

            error_log("✅ Connexion à la base de données réussie");
        } catch (PDOException $e) {
            error_log("❌ DB Error: " . $e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Garantit le retour d'un objet PDO valide.
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if ($this->conn === null) {
            throw new \RuntimeException("La connexion base de données n'est pas initialisée.");
        }
        return $this->conn;
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}