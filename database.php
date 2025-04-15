<?php
// Database configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => 'flow_metering',
    'username' => 'root',
    'password' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']}",
        $db_config['username'],
        $db_config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper functions for database operations
function getStreamData($stream_id = null) {
    global $pdo;
    
    try {
        $sql = "
            SELECT s.stream_id, s.name, s.status,
                   m.flow_rate, m.pressure, m.temperature,
                   m.timestamp
            FROM streams s
            LEFT JOIN measurements m ON s.stream_id = m.stream_id
            WHERE m.timestamp = (
                SELECT MAX(timestamp)
                FROM measurements
            )
        ";
        
        if ($stream_id) {
            $sql .= " AND s.stream_id = :stream_id";
        }
        
        $stmt = $pdo->prepare($sql);
        if ($stream_id) {
            $stmt->execute([':stream_id' => $stream_id]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting stream data: " . $e->getMessage());
        return false;
    }
}

function updateStreamStatus($stream_id, $status) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE streams
            SET status = :status
            WHERE stream_id = :stream_id
        ");
        
        return $stmt->execute([
            ':status' => $status,
            ':stream_id' => $stream_id
        ]);
    } catch (PDOException $e) {
        error_log("Error updating stream status: " . $e->getMessage());
        return false;
    }
}

function insertMeasurement($stream_id, $flow_rate, $pressure, $temperature) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO measurements (
                stream_id, flow_rate, pressure, temperature, timestamp
            ) VALUES (
                :stream_id, :flow_rate, :pressure, :temperature, NOW()
            )
        ");
        
        return $stmt->execute([
            ':stream_id' => $stream_id,
            ':flow_rate' => $flow_rate,
            ':pressure' => $pressure,
            ':temperature' => $temperature
        ]);
    } catch (PDOException $e) {
        error_log("Error inserting measurement: " . $e->getMessage());
        return false;
    }
}

function getSettings() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        error_log("Error getting settings: " . $e->getMessage());
        return false;
    }
}

function updateSetting($key, $value) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE settings
            SET setting_value = :value
            WHERE setting_key = :key
        ");
        
        return $stmt->execute([
            ':value' => $value,
            ':key' => $key
        ]);
    } catch (PDOException $e) {
        error_log("Error updating setting: " . $e->getMessage());
        return false;
    }
}
?> 