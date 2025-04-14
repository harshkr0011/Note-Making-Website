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
$folderName = $data['name'] ?? '';

if (empty($folderName)) {
    echo json_encode(['success' => false, 'message' => 'Folder name is required']);
    exit;
}

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if folder already exists for this user
    $checkStmt = $db->prepare("SELECT id FROM folders WHERE user_id = ? AND name = ?");
    $checkStmt->execute([$_SESSION['user_id'], $folderName]);
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'A folder with this name already exists']);
        exit;
    }
    
    // Insert new folder
    $stmt = $db->prepare("INSERT INTO folders (user_id, name) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $folderName]);
    
    echo json_encode(['success' => true, 'message' => 'Folder created successfully']);
} catch (PDOException $e) {
    error_log("Folder creation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error creating folder. Please try again.']);
} 