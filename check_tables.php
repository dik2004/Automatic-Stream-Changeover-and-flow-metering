<?php
require_once 'config/database.php';

try {
    // Check settings table
    $stmt = $pdo->query("DESCRIBE settings");
    echo "<h3>Settings Table Structure:</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

    // Check settings data
    $stmt = $pdo->query("SELECT * FROM settings");
    echo "<h3>Settings Data:</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

    // Check streams table
    $stmt = $pdo->query("DESCRIBE streams");
    echo "<h3>Streams Table Structure:</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

    // Check streams data
    $stmt = $pdo->query("SELECT * FROM streams");
    echo "<h3>Streams Data:</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

    // Check users table
    $stmt = $pdo->query("DESCRIBE users");
    echo "<h3>Users Table Structure:</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

    // Check users data (without showing passwords)
    $stmt = $pdo->query("SELECT user_id, username, role, created_at FROM users");
    echo "<h3>Users Data:</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 