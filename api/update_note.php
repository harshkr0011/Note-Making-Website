<?php
session_start();
require_once '../config/database.php';

// Set JSON header at the very beginning
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
    $note_id = $_POST['note_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $folder_id = $_POST['folder_id'] ?? null;
    $tags = json_decode($_POST['tags'] ?? '[]', true);

    if (empty($note_id) || empty($title)) {
        throw new Exception('Note ID and title are required');
    }

    // Process images in content
    $content = processImages($content, $_SESSION['user_id']);

    // Start transaction
    $db->beginTransaction();

    // Verify note belongs to user
    $query = "SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':note_id', $note_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        throw new Exception('Note not found or access denied');
    }

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

    // Update note
    $query = "UPDATE notes SET title = :title, content = :content, folder_id = :folder_id WHERE id = :note_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':folder_id', $folder_id);
    $stmt->bindParam(':note_id', $note_id);
    $stmt->execute();

    // Delete existing tags
    $query = "DELETE FROM note_tags WHERE note_id = :note_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':note_id', $note_id);
    $stmt->execute();

    // Add new tags
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

            // Link tag to note
            $query = "INSERT INTO note_tags (note_id, tag_id) VALUES (:note_id, :tag_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':note_id', $note_id);
            $stmt->bindParam(':tag_id', $tag_id);
            $stmt->execute();
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Note updated successfully']);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function processImages($content, $user_id) {
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/' . $user_id . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Process base64 images
    $content = preg_replace_callback('/<img[^>]+src="data:image\/([^;]+);base64,([^"]+)"[^>]*>/', 
        function($matches) use ($upload_dir, $user_id) {
            $image_type = $matches[1];
            $image_data = base64_decode($matches[2]);
            
            // Generate unique filename
            $filename = uniqid() . '.' . $image_type;
            $filepath = $upload_dir . $filename;
            
            // Save image
            if (file_put_contents($filepath, $image_data)) {
                // Return new img tag with file path
                return '<img src="uploads/' . $user_id . '/' . $filename . '" alt="Uploaded image">';
            } else {
                // If saving fails, return original image
                return $matches[0];
            }
        }, 
        $content
    );

    return $content;
}
?> 