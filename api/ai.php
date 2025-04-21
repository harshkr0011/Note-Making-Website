<?php
session_start();
require_once '../includes/AIService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$aiService = new AIService();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['action'])) {
            switch ($data['action']) {
                case 'summarize':
                    if (isset($data['note_id']) && isset($data['content'])) {
                        $summary = $aiService->generateSummary(
                            $data['note_id'],
                            $data['content'],
                            $data['length'] ?? 'medium'
                        );
                        echo json_encode(['summary' => $summary]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Missing required parameters']);
                    }
                    break;

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
?> 