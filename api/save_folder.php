<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $name = $_POST['name'] ?? '';

    if (empty($name)) {
        throw new Exception('Folder name is required');
    }

    // Check if folder with same name already exists for this user
    $query = "SELECT id FROM folders WHERE user_id = :user_id AND name = :name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':name', $name);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        throw new Exception('A folder with this name already exists');
    }

    // Insert new folder
    $query = "INSERT INTO folders (user_id, name) VALUES (:user_id, :name)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':name', $name);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Folder created successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 