<?php
session_start();
require_once __DIR__ . '/../includes/AIService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $aiService = new AIService();
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['action'])) {
                switch ($data['action']) {
                    case 'chat':
                        if (isset($data['message'])) {
                            $response = $aiService->chat($_SESSION['user_id'], $data['message']);
                            echo json_encode(['response' => $response]);
                        } else {
                            http_response_code(400);
                            echo json_encode(['error' => 'Message is required']);
                        }
                        break;
                    
                    case 'update_settings':
                        if (isset($data['settings'])) {
                            $success = $aiService->updateAISettings($_SESSION['user_id'], $data['settings']);
                            echo json_encode(['success' => $success]);
                        } else {
                            http_response_code(400);
                            echo json_encode(['error' => 'Settings are required']);
                        }
                        break;
                    
                    default:
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid action']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Action is required']);
            }
            break;
            
        case 'GET':
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'settings':
                        $settings = $aiService->getAISettings($_SESSION['user_id']);
                        echo json_encode(['settings' => $settings]);
                        break;
                        
                    default:
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid action']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Action is required']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?> 