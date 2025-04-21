<?php
session_start();
require_once '../config/gemini_api.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';

if (!$message) {
    echo json_encode(['success' => false, 'reply' => 'No message provided.']);
    exit();
}

try {
    $reply = gemini_summarise($message);
    echo json_encode(['success' => true, 'reply' => $reply]);
} catch (Exception $e) {
    error_log("Chatbot Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'reply' => 'Sorry, I encountered an error. Please try again later.']);
}
?>