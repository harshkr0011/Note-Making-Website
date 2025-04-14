<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get POST data
$noteId = $_POST['note_id'] ?? '';

if (empty($noteId)) {
    echo json_encode(['success' => false, 'message' => 'Note ID is required']);
    exit;
}

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Verify note belongs to user and delete
    $stmt = $db->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$noteId, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        // Also delete any associated metadata
        $metaStmt = $db->prepare("DELETE FROM note_metadata WHERE note_id = ?");
        $metaStmt->execute([$noteId]);
        
        echo json_encode(['success' => true, 'message' => 'Note deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Note not found or you do not have permission to delete it']);
    }
} catch (PDOException $e) {
    error_log("Note deletion error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error deleting note. Please try again.']);
}
?> 