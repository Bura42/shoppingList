<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Manages the database connection using PDO.
 * Implements a singleton pattern to ensure a single connection instance.
 */
class Database
{
    /**
     * @var PDO|null The single instance of the PDO connection.
     */
    private static ?PDO $pdo = null;

    /**
     * The constructor is private to prevent direct instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Gets the single PDO connection instance.
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../../config/database.php';

            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$pdo = new PDO($dsn, $config['user'], $config['password'], $options);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}