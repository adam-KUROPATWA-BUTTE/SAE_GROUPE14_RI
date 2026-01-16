<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class Database
{
    private static ?self $instance = null;
    private ?\PDO $pdo = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        // Create in-memory SQLite database for testing
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function getConnection(): \PDO
    {
        if ($this->pdo === null) {
            throw new \RuntimeException('PDO connection not initialized');
        }
        return $this->pdo;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
