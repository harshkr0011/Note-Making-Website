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

    // If folder_id is provided, verify it exists and belongs to the user
    if ($folder_id !== null && $folder_id !== '') {
        $query = "SELECT id FROM folders WHERE id = :folder_id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':folder_id', $folder_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new Exception('Invalid folder selected');
        }
    } else {
        $folder_id = null; // Set to NULL if empty string or null
    }

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
        foreach ($tags as $tag_name) {
            // Check if tag exists
            $query = "SELECT id FROM tags WHERE name = :name AND user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $tag_name);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $tag = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tag) {
                // Create new tag
                $query = "INSERT INTO tags (name, user_id) VALUES (:name, :user_id)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $tag_name);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $tag_id = $db->lastInsertId();
            } else {
                $tag_id = $tag['id'];
            }

            // Check if note-tag relationship already exists
            $query = "SELECT 1 FROM note_tags WHERE note_id = :note_id AND tag_id = :tag_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':note_id', $note_id);
            $stmt->bindParam(':tag_id', $tag_id);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                // Link tag to note only if relationship doesn't exist
                $query = "INSERT INTO note_tags (note_id, tag_id) VALUES (:note_id, :tag_id)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':note_id', $note_id);
                $stmt->bindParam(':tag_id', $tag_id);
                $stmt->execute();
            }
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