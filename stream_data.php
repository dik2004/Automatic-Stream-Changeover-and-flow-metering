<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getStreamData();
        break;
    case 'POST':
        handlePostRequest();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function getStreamData() {
    $data = getStreamData();
    
    if ($data) {
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No data available'
        ]);
    }
}

function handlePostRequest() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }
    
    try {
        // Start transaction
        global $pdo;
        $pdo->beginTransaction();
        
        // Update stream status
        if (isset($data['stream_id']) && isset($data['status'])) {
            $success = updateStreamStatus($data['stream_id'], $data['status']);
            if (!$success) {
                throw new Exception('Failed to update stream status');
            }
        }
        
        // Insert new measurement
        if (isset($data['measurement'])) {
            $success = insertMeasurement(
                $data['measurement']['stream_id'],
                $data['measurement']['flow_rate'],
                $data['measurement']['pressure'],
                $data['measurement']['temperature']
            );
            if (!$success) {
                throw new Exception('Failed to insert measurement');
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Data updated successfully'
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        
        http_response_code(500);
        echo json_encode([
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 