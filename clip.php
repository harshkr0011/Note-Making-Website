<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$url = $_POST['url'] ?? '';

// Get user's folders
$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM folders WHERE user_id = :user_id ORDER BY name";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$folders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clip to Nexus Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .container { max-width: 800px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Clip to Nexus Notes</h1>
        <form id="clipForm" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($content); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="folder" class="form-label">Folder</label>
                <select class="form-select" id="folder" name="folder_id">
                    <option value="">No Folder</option>
                    <?php foreach($folders as $folder): ?>
                        <option value="<?php echo $folder['id']; ?>"><?php echo htmlspecialchars($folder['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="tags" class="form-label">Tags (comma-separated)</label>
                <input type="text" class="form-control" id="tags" name="tags" placeholder="tag1, tag2, tag3">
            </div>
            <input type="hidden" id="url" name="url" value="<?php echo htmlspecialchars($url); ?>">
            <button type="submit" class="btn btn-primary">Save Note</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('clipForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                title: document.getElementById('title').value,
                content: document.getElementById('content').value,
                url: document.getElementById('url').value,
                folder_id: document.getElementById('folder').value,
                tags: document.getElementById('tags').value.split(',').map(tag => tag.trim()).filter(tag => tag)
            };

            fetch('api/clip_content.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Content clipped successfully!');
                    window.close();
                } else {
                    throw new Error(data.message || 'Failed to save content');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });
    </script>
</body>
</html> 