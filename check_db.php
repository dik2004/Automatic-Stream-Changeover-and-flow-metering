<?php
require_once 'config/database.php';

try {
    // Check database connection
    $pdo->query("SELECT 1");
    echo "Database connection successful!<br>";

    // Check if tables exist
    $tables = ['users', 'streams', 'settings', 'measurements'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "Table '$table' exists<br>";
        } else {
            echo "Table '$table' does not exist<br>";
        }
    }

    // Check if admin user exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        echo "Admin user exists<br>";
    } else {
        echo "Admin user does not exist<br>";
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
} 