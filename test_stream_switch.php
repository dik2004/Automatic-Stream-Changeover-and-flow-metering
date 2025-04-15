<?php
// Start session and login before any output
session_start();
$_SESSION['user_id'] = 1; // Set admin user ID
$_SESSION['username'] = 'admin';

try {
    // Database configuration
    $db_config = [
        'host' => 'localhost',
        'dbname' => 'flow_metering',
        'username' => 'root',
        'password' => ''
    ];

    // Create connection
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']}",
        $db_config['username'],
        $db_config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Test manual override
    function testManualOverride() {
        global $pdo;
        
        try {
            // Get initial stream status
            $stmt = $pdo->query("SELECT stream_id, name, status FROM streams ORDER BY stream_id");
            $initialStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Initial Stream Status:\n";
            foreach ($initialStatus as $stream) {
                echo "{$stream['name']}: {$stream['status']}\n";
            }
            
            // Test switching to Stream B (if A is active) or A (if B is active)
            $currentActive = array_filter($initialStatus, function($s) { return $s['status'] === 'active'; });
            $currentActive = reset($currentActive);
            $newStreamId = ($currentActive['stream_id'] == 1) ? 2 : 1;
            
            $ch = curl_init('http://localhost/project/api/stream_control.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'action' => 'manual_override',
                'stream_id' => $newStreamId
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Cookie: PHPSESSID=' . session_id()
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "\nManual Override Response (HTTP Code: $httpCode):\n";
            echo $response . "\n";
            
            // Get updated stream status
            $stmt = $pdo->query("SELECT stream_id, name, status FROM streams ORDER BY stream_id");
            $updatedStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\nUpdated Stream Status:\n";
            foreach ($updatedStatus as $stream) {
                echo "{$stream['name']}: {$stream['status']}\n";
            }
            
            return $httpCode === 200;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    // Test auto mode
    function testAutoMode() {
        global $pdo;
        
        try {
            // Insert test measurements that exceed thresholds
            $stmt = $pdo->prepare("
                INSERT INTO measurements (stream_id, flow_rate, pressure, temperature, timestamp)
                VALUES (1, 150, 15, 60, NOW())
            ");
            $stmt->execute();
            
            // Test auto mode
            $ch = curl_init('http://localhost/project/api/stream_control.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'action' => 'auto_mode'
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Cookie: PHPSESSID=' . session_id()
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "\nAuto Mode Response (HTTP Code: $httpCode):\n";
            echo $response . "\n";
            
            // Get final stream status
            $stmt = $pdo->query("SELECT stream_id, name, status FROM streams ORDER BY stream_id");
            $finalStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\nFinal Stream Status:\n";
            foreach ($finalStatus as $stream) {
                echo "{$stream['name']}: {$stream['status']}\n";
            }
            
            return $httpCode === 200;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    // Run tests
    echo "Testing Stream Switching Functionality\n";
    echo "=====================================\n\n";

    echo "Test 1: Manual Override\n";
    echo "----------------------\n";
    $manualResult = testManualOverride();
    echo ($manualResult ? "✓" : "✗") . " Manual override test " . ($manualResult ? "passed" : "failed") . "\n";

    echo "\nTest 2: Auto Mode\n";
    echo "----------------\n";
    $autoResult = testAutoMode();
    echo ($autoResult ? "✓" : "✗") . " Auto mode test " . ($autoResult ? "passed" : "failed") . "\n";

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?> 