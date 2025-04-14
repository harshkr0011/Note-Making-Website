<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit('Not authenticated');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Invalid request method');
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $note_id = $_GET['id'] ?? null;
    if (!$note_id) {
        throw new Exception('Note ID is required');
    }

    // Get note details
    $query = "SELECT n.*, f.name as folder_name, GROUP_CONCAT(t.name) as tags 
              FROM notes n 
              LEFT JOIN folders f ON n.folder_id = f.id 
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
        throw new Exception('Note not found or unauthorized');
    }

    // Convert HTML to Markdown
    $content = $note['content'];

    $content = preg_replace_callback('/<h([1-6])>(.*?)<\/h\1>/', function($matches) {
        return str_repeat('#', $matches[1]) . ' ' . trim($matches[2]);
    }, $content);
    $content = preg_replace('/<strong>(.*?)<\/strong>/', '**$1**', $content);
    $content = preg_replace('/<em>(.*?)<\/em>/', '*$1*', $content);
    $content = preg_replace('/<ul>(.*?)<\/ul>/s', '$1', $content);
    $content = preg_replace('/<ol>(.*?)<\/ol>/s', '$1', $content);
    $content = preg_replace('/<li>(.*?)<\/li>/', '- $1' . PHP_EOL, $content);
    $content = preg_replace('/<p>(.*?)<\/p>/', '$1' . PHP_EOL . PHP_EOL, $content);
    $content = preg_replace('/<br\s*\/?>/', PHP_EOL, $content);
    $content = strip_tags($content);

    // Set headers for file download
    header('Content-Type: text/markdown');
    header('Content-Disposition: attachment; filename="' . $note['title'] . '.md"');

    // Output the markdown content
    echo "# " . $note['title'] . PHP_EOL . PHP_EOL;
    if ($note['folder_name']) {
        echo "**Folder:** " . $note['folder_name'] . PHP_EOL . PHP_EOL;
    }
    if ($note['tags']) {
        echo "**Tags:** " . $note['tags'] . PHP_EOL . PHP_EOL;
    }
    echo "**Created:** " . date('Y-m-d H:i:s', strtotime($note['created_at'])) . PHP_EOL . PHP_EOL;
    echo "---" . PHP_EOL . PHP_EOL;
    echo $content;

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo $e->getMessage();
}
?>
