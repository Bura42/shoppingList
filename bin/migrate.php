<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    echo "Connecting to database...\n";
    $pdo = Database::getConnection();

    echo "Connection successful. Running migrations...\n";

    $sql = "
    CREATE TABLE IF NOT EXISTS items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name TEXT NOT NULL,
        is_checked BOOLEAN NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=INNODB;
    ";

    $pdo->exec($sql);

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
