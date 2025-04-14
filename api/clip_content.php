<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['title']) || !isset($data['content']) || !isset($data['url'])) {
        throw new Exception('Missing required fields');
    }

    // Sanitize and prepare data
    $title = htmlspecialchars($data['title']);
    $content = $data['content'];
    $url = filter_var($data['url'], FILTER_SANITIZE_URL);
    $folder_id = isset($data['folder_id']) ? (int)$data['folder_id'] : null;
    $tags = isset($data['tags']) ? $data['tags'] : [];

    // Insert the clipped note
    $query = "INSERT INTO notes (user_id, title, content, folder_id, created_at, updated_at) 
              VALUES (:user_id, :title, :content, :folder_id, NOW(), NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':folder_id', $folder_id);
    $stmt->execute();

    $note_id = $db->lastInsertId();

    // Add source URL as metadata
    $query = "INSERT INTO note_metadata (note_id, meta_key, meta_value) 
              VALUES (:note_id, 'source_url', :url)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':note_id', $note_id);
    $stmt->bindParam(':url', $url);
    $stmt->execute();

    // Handle tags if provided
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
                $query = "INSERT INTO tags (user_id, name) VALUES (:user_id, :name)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':name', $tag_name);
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

    echo json_encode([
        'success' => true,
        'message' => 'Content clipped successfully',
        'note_id' => $note_id
    ]);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 