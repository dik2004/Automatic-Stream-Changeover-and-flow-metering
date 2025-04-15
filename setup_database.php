<?php
// Database configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => ''
];

require_once '../config/database.php';

try {
    // Create connection without database
    $pdo = new PDO(
        "mysql:host={$db_config['host']}",
        $db_config['username'],
        $db_config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS flow_metering");
    echo "Database 'flow_metering' created successfully.<br>";
    
    // Select the database
    $pdo->exec("USE flow_metering");
    
    // Drop tables in correct order to avoid foreign key constraints
    $pdo->exec("DROP TABLE IF EXISTS contact_messages");
    $pdo->exec("DROP TABLE IF EXISTS measurements");
    $pdo->exec("DROP TABLE IF EXISTS settings");
    $pdo->exec("DROP TABLE IF EXISTS streams");
    $pdo->exec("DROP TABLE IF EXISTS users");
    
    // Create users table
    $pdo->exec("CREATE TABLE users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'operator') NOT NULL DEFAULT 'operator',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'users' created successfully.<br>";

    // Create streams table
    $pdo->exec("CREATE TABLE streams (
        stream_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        status ENUM('active', 'standby') NOT NULL DEFAULT 'standby',
        current_flow_rate DECIMAL(10,2) DEFAULT 0.00,
        current_pressure DECIMAL(10,2) DEFAULT 0.00,
        current_temperature DECIMAL(10,2) DEFAULT 0.00,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Table 'streams' created successfully.<br>";

    // Create settings table
    $pdo->exec("CREATE TABLE settings (
        setting_id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Table 'settings' created successfully.<br>";

    // Create measurements table
    $pdo->exec("CREATE TABLE measurements (
        measurement_id INT AUTO_INCREMENT PRIMARY KEY,
        stream_id INT NOT NULL,
        flow_rate DECIMAL(10,2) NOT NULL,
        pressure DECIMAL(10,2) NOT NULL,
        temperature DECIMAL(10,2) NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (stream_id) REFERENCES streams(stream_id)
    )");
    echo "Table 'measurements' created successfully.<br>";

    // Create contact_messages table
    $pdo->exec("CREATE TABLE contact_messages (
        message_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('new', 'read', 'replied') DEFAULT 'new'
    )");
    echo "Table 'contact_messages' created successfully.<br>";

    // Insert default admin user
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (username, password, role) 
                VALUES ('admin', '$hashedPassword', 'admin')");
    echo "Default admin user created.<br>";

    // Insert default streams
    $pdo->exec("INSERT INTO streams (name, status) VALUES 
        ('Stream A', 'active'),
        ('Stream B', 'standby')");
    echo "Default streams created.<br>";

    // Insert default settings
    $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES 
        ('changeover_threshold', 100.00),
        ('pressure_limit', 10.00),
        ('temperature_limit', 50.00)");
    echo "Default settings created.<br>";

    echo "<br>Database setup completed successfully!<br>";
    echo "<a href='../index.php'>Go to Dashboard</a>";

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?> 