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
$data = json_decode(file_get_contents('php://input'), true);
$folderId = $data['folder_id'] ?? '';

if (empty($folderId)) {
    echo json_encode(['success' => false, 'message' => 'Folder ID is required']);
    exit;
}

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    // First, delete all notes in the folder
    $deleteNotesStmt = $db->prepare("DELETE FROM notes WHERE folder_id = ? AND user_id = ?");
    $deleteNotesStmt->execute([$folderId, $_SESSION['user_id']]);
    
    // Then delete the folder
    $deleteFolderStmt = $db->prepare("DELETE FROM folders WHERE id = ? AND user_id = ?");
    $deleteFolderStmt->execute([$folderId, $_SESSION['user_id']]);
    
    if ($deleteFolderStmt->rowCount() > 0) {
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Folder deleted successfully']);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Folder not found or you do not have permission to delete it']);
    }
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Folder deletion error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error deleting folder. Please try again.']);
} 