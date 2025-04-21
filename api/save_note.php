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

    // Get POST data
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $folder_id = $_POST['folder_id'] ?? null;
    $tags = json_decode($_POST['tags'] ?? '[]', true);

    if (empty($title)) {
        throw new Exception('Title is required');
    }

    // Start transaction
    $db->beginTransaction();

    // Insert note
    $query = "INSERT INTO notes (user_id, folder_id, title, content) VALUES (:user_id, :folder_id, :title, :content)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':folder_id', $folder_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->execute();

    $note_id = $db->lastInsertId();

    // Handle tags
    if (!empty($tags)) {
        $tagStmt = $db->prepare("INSERT INTO tags (name, user_id) VALUES (?, ?)");
        $tagIdStmt = $db->prepare("INSERT INTO note_tags (note_id, tag_id) VALUES (?, ?)");
        
        foreach ($tags as $tagName) {
            // Insert tag
            $tagStmt->bindParam(1, $tagName);
            $tagStmt->bindParam(2, $_SESSION['user_id']);
            $tagStmt->execute();
            $tagId = $db->lastInsertId();
            
            // Link tag to note
            $tagIdStmt->bindParam(1, $note_id);
            $tagIdStmt->bindParam(2, $tagId);
            $tagIdStmt->execute();
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Note saved successfully']);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 