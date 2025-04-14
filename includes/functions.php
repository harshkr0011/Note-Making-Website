function createNote($title, $content, $folder_id = null) {
    global $conn;
    $user_id = $_SESSION['user_id'];
    
    // Validate folder_id if provided
    if ($folder_id !== null) {
        $stmt = $conn->prepare("SELECT id FROM folders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $folder_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Folder doesn't exist or doesn't belong to user
            $folder_id = null;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO notes (title, content, user_id, folder_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $title, $content, $user_id, $folder_id);
    return $stmt->execute();
}

function updateNote($id, $title, $content, $folder_id = null) {
    global $conn;
    $user_id = $_SESSION['user_id'];
    
    // Validate folder_id if provided
    if ($folder_id !== null) {
        $stmt = $conn->prepare("SELECT id FROM folders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $folder_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Folder doesn't exist or doesn't belong to user
            $folder_id = null;
        }
    }
    
    $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ?, folder_id = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssiii", $title, $content, $folder_id, $id, $user_id);
    return $stmt->execute();
} 