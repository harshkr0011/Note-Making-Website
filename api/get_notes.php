<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get folder ID from query parameter
$folderId = isset($_GET['folder_id']) ? $_GET['folder_id'] : null;

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Build query based on folder ID
    if ($folderId && $folderId !== 'all') {
        $query = "SELECT n.*, f.name as folder_name, GROUP_CONCAT(DISTINCT t.name) as tags 
                 FROM notes n 
                 LEFT JOIN folders f ON n.folder_id = f.id
                 LEFT JOIN note_tags nt ON n.id = nt.note_id 
                 LEFT JOIN tags t ON nt.tag_id = t.id 
                 WHERE n.user_id = ? AND n.folder_id = ?
                 GROUP BY n.id 
                 ORDER BY n.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id'], $folderId]);
    } else {
        $query = "SELECT n.*, f.name as folder_name, GROUP_CONCAT(DISTINCT t.name) as tags 
                 FROM notes n 
                 LEFT JOIN folders f ON n.folder_id = f.id
                 LEFT JOIN note_tags nt ON n.id = nt.note_id 
                 LEFT JOIN tags t ON nt.tag_id = t.id 
                 WHERE n.user_id = ?
                 GROUP BY n.id 
                 ORDER BY n.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
    }
    
    $notes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['tags'] = $row['tags'] ? explode(',', $row['tags']) : [];
        $notes[] = $row;
    }
    
    echo json_encode($notes);
} catch (PDOException $e) {
    error_log("Error fetching notes: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching notes']);
} 