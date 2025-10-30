<?php
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "Dropping and recreating database...\n";
    
    // Drop and recreate database
    $pdo->exec("DROP DATABASE IF EXISTS " . DB_NAME);
    $pdo->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE " . DB_NAME);
    
    echo "✓ Database reset successfully\n";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}