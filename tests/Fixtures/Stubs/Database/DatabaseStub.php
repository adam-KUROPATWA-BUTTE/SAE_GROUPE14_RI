<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class Database
{
    private static $instance = null;
    private $pdo = null;

    public static function getInstance()
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

    public function getConnection()
    {
        return $this->pdo;
    }

    public static function reset()
    {
        self::$instance = null;
    }
}
