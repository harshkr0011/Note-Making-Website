<?php
session_start();
require_once '../config/database.php';
require_once '../config/gemini_api.php'; // Store your Gemini API key here

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$note_id = $_GET['id'] ?? null;
if (empty($note_id)) {
    echo json_encode(['success' => false, 'message' => 'Note ID is required']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT content FROM notes WHERE id = :note_id AND user_id = :user_id");
    $stmt->bindParam(':note_id', $note_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();

    $note = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$note) {
        throw new Exception('Note not found or access denied');
    }

    // Call Gemini API to summarise
    $summary = gemini_summarise($note['content']);
    echo json_encode(['success' => true, 'summary' => $summary]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>