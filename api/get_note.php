<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $note_id = $_GET['id'] ?? null;
    if (empty($note_id)) {
        throw new Exception('Note ID is required');
    }

    // Get note with tags
    $query = "SELECT n.*, GROUP_CONCAT(DISTINCT t.name) as tags 
              FROM notes n 
              LEFT JOIN note_tags nt ON n.id = nt.note_id 
              LEFT JOIN tags t ON nt.tag_id = t.id 
              WHERE n.id = :note_id AND n.user_id = :user_id 
              GROUP BY n.id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':note_id', $note_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();

    $note = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$note) {
        throw new Exception('Note not found or access denied');
    }

    // Convert tags string to array
    $note['tags'] = $note['tags'] ? explode(',', $note['tags']) : [];

    echo json_encode([
        'success' => true,
        'note' => $note
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 