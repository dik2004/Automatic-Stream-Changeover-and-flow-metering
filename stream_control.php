<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

try {
    $pdo->beginTransaction();
    $response = ['success' => true];
    
    switch ($data['action']) {
        case 'manual_override':
            if (!isset($data['stream_id'])) {
                throw new Exception('Stream ID is required');
            }
            
            // Update all streams to standby
            $stmt = $pdo->prepare("UPDATE streams SET status = 'standby'");
            $stmt->execute();
            
            // Set selected stream to active
            $stmt = $pdo->prepare("UPDATE streams SET status = 'active' WHERE stream_id = ?");
            $stmt->execute([$data['stream_id']]);
            
            // Get stream name
            $stmt = $pdo->prepare("SELECT name FROM streams WHERE stream_id = ?");
            $stmt->execute([$data['stream_id']]);
            $stream = $stmt->fetch();
            
            $response['message'] = "Manual override successful - Switched to {$stream['name']}";
            break;
            
        case 'auto_mode':
            // Get current measurements
            $stmt = $pdo->query("
                SELECT s.stream_id, s.name, s.status,
                       m.flow_rate, m.pressure, m.temperature
                FROM streams s
                LEFT JOIN measurements m ON s.stream_id = m.stream_id
                WHERE m.timestamp = (
                    SELECT MAX(timestamp)
                    FROM measurements
                )
            ");
            $streams = $stmt->fetchAll();
            
            // Get settings
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            $changeover = false;
            
            // Check conditions for each stream
            foreach ($streams as $stream) {
                if ($stream['status'] === 'active' && (
                    $stream['flow_rate'] > $settings['changeover_threshold'] ||
                    $stream['pressure'] > $settings['pressure_limit'] ||
                    $stream['temperature'] > $settings['temperature_limit']
                )) {
                    // Find another stream to switch to
                    $newStream = null;
                    foreach ($streams as $s) {
                        if ($s['stream_id'] !== $stream['stream_id'] && $s['status'] !== 'maintenance') {
                            $newStream = $s;
                            break;
                        }
                    }
                    
                    if ($newStream) {
                        // Update stream statuses
                        $stmt = $pdo->prepare("UPDATE streams SET status = 'standby' WHERE stream_id = ?");
                        $stmt->execute([$stream['stream_id']]);
                        
                        $stmt = $pdo->prepare("UPDATE streams SET status = 'active' WHERE stream_id = ?");
                        $stmt->execute([$newStream['stream_id']]);
                        
                        $response['message'] = 'Auto mode changeover successful';
                        $response['from_stream'] = $stream['name'];
                        $response['to_stream'] = $newStream['name'];
                        $changeover = true;
                        break;
                    }
                }
            }
            
            if (!$changeover) {
                $response['message'] = 'No changeover needed';
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    $pdo->commit();
    http_response_code(200);
    echo json_encode($response);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?> 