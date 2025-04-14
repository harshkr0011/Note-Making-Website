<?php
session_start();
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get user's folders
$query = "SELECT * FROM folders WHERE user_id = :user_id ORDER BY name";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$folders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's tags
$query = "SELECT DISTINCT t.* FROM tags t 
          JOIN note_tags nt ON t.id = nt.tag_id 
          JOIN notes n ON nt.note_id = n.id 
          WHERE n.user_id = :user_id 
          ORDER BY t.name";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's notes
$query = "SELECT n.*, GROUP_CONCAT(t.name) as tag_names 
          FROM notes n 
          LEFT JOIN note_tags nt ON n.id = nt.note_id 
          LEFT JOIN tags t ON nt.tag_id = t.id 
          WHERE n.user_id = :user_id 
          GROUP BY n.id 
          ORDER BY n.updated_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nexus Notes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Nexus Notes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button id="toggleAIChat" class="btn btn-outline-light me-2">
                            <i class="fas fa-robot"></i> AI Assistant
                        </button>
                    </li>
                    <li class="nav-item">
                        <div class="theme-toggle-container">
                            <button class="theme-toggle-btn" id="theme-toggle">
                                <span class="theme-icon sun"><i class="fas fa-sun"></i></span>
                                <span class="theme-icon moon"><i class="fas fa-moon"></i></span>
                                <span class="theme-text">Toggle Theme</span>
                            </button>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="mb-4">
                    <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#newNoteModal">
                        <i class="fas fa-plus"></i> New Note
                    </button>
                </div>

                <!-- Web Clipper Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Web Clipper</h5>
                        <p class="card-text">Save content from any webpage directly to your notes.</p>
                        <a href="bookmarklet.html" class="btn btn-success w-100">
                            <i class="fas fa-paperclip"></i> Get Web Clipper
                        </a>
                    </div>
                </div>

                <!-- Folders Section -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="text-primary">Folders</h5>
                        <div class="folder-actions">
                            <button id="createFolder" class="btn btn-primary" title="New Folder">
                                <i class="fas fa-folder-plus"></i>
                                <span class="btn-text">New</span>
                            </button>
                            <button id="deleteFolder" class="btn btn-danger" title="Delete Folder">
                                <i class="fas fa-trash"></i>
                                <span class="btn-text">Delete</span>
                            </button>
                        </div>
                    </div>
                    <div id="folderList" class="folder-list">
                        <!-- Folders will be loaded dynamically here -->
                    </div>
                </div>

                <!-- Tags Section -->
                <div class="mb-4">
                    <h5 class="sidebar-header">Tags</h5>
                    <div class="tag-list">
                        <?php foreach($tags as $tag): ?>
                            <div class="tag-item">
                                <i class="fas fa-tag"></i>
                                <span class="tag-name"><?php echo htmlspecialchars($tag['name']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="row">
                    <!-- Notes Content -->
                    <div class="col-md-8">
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Search notes..." id="searchInput">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="notesContainer">
                            <?php foreach($notes as $note): ?>
                                <div class="col-md-6 mb-4 note-card" data-folder-id="<?php echo $note['folder_id'] ?? 'all'; ?>">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($note['title']); ?></h5>
                                            <p class="card-text"><?php echo substr(strip_tags($note['content']), 0, 100) . '...'; ?></p>
                                            <?php if($note['tag_names']): ?>
                                                <div class="mb-2">
                                                    <?php foreach(explode(',', $note['tag_names']) as $tag): ?>
                                                        <span class="badge bg-primary me-1"><?php echo htmlspecialchars($tag); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            <small class="text-muted">Last updated: <?php echo date('M d, Y', strtotime($note['updated_at'])); ?></small>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-sm btn-primary edit-note" data-id="<?php echo $note['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-note" data-id="<?php echo $note['id']; ?>">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-download"></i> Export
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item export-pdf" href="#" data-id="<?php echo $note['id']; ?>">Export as PDF</a></li>
                                                    <li><a class="dropdown-item export-md" href="#" data-id="<?php echo $note['id']; ?>">Export as Markdown</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- AI Chat Container -->
                    <div class="col-md-4">
                        <div class="ai-chat-container" id="aiChatContainer">
                            <div class="ai-chat-header">
                                <h5>AI Assistant</h5>
                                <div class="ai-chat-actions">
                                    <button id="aiSettingsButton" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button id="toggleAIChat" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="ai-chat-messages" id="aiChatMessages"></div>
                            <div class="ai-chat-input">
                                <input type="text" id="aiChatInput" placeholder="Type your message...">
                                <button id="aiChatSend" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Settings Modal -->
            <div class="modal fade" id="aiSettingsModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">AI Settings</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="autoSummarize">
                                    <label class="form-check-label" for="autoSummarize">
                                        Auto-generate summaries for new notes
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="summaryLength" class="form-label">Summary Length</label>
                                <select class="form-select" id="summaryLength">
                                    <option value="short">Short</option>
                                    <option value="medium">Medium</option>
                                    <option value="long">Long</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="aiModel" class="form-label">AI Model</label>
                                <select class="form-select" id="aiModel">
                                    <option value="gemini-pro">Gemini Pro</option>
                                    <option value="gemini-pro-vision">Gemini Pro Vision</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="aiChat.saveSettings()">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Note Modal -->
    <div class="modal fade" id="newNoteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="noteForm">
                        <div class="mb-3">
                            <label for="noteTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="noteTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="noteContent" class="form-label">Content</label>
                            <div id="editor"></div>
                        </div>
                        <div class="mb-3">
                            <label for="noteFolder" class="form-label">Folder</label>
                            <select class="form-select" id="noteFolder">
                                <option value="">No Folder</option>
                                <?php foreach($folders as $folder): ?>
                                    <option value="<?php echo $folder['id']; ?>"><?php echo htmlspecialchars($folder['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <input type="text" class="form-control" id="noteTags" placeholder="Enter tags separated by commas">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveNote">Save Note</button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Folder Modal -->
    <div class="modal fade" id="newFolderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newFolderForm">
                        <div class="mb-3">
                            <label for="folderName" class="form-label">Folder Name</label>
                            <input type="text" class="form-control" id="folderName" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="createFolder">Create</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Note Modal -->
    <div class="modal fade" id="editNoteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editNoteForm">
                        <input type="hidden" id="editNoteId">
                        <div class="mb-3">
                            <label for="editNoteTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editNoteTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="editNoteContent" class="form-label">Content</label>
                            <div id="editEditor"></div>
                        </div>
                        <div class="mb-3">
                            <label for="editNoteFolder" class="form-label">Folder</label>
                            <select class="form-select" id="editNoteFolder">
                                <option value="">No Folder</option>
                                <?php foreach($folders as $folder): ?>
                                    <option value="<?php echo $folder['id']; ?>"><?php echo htmlspecialchars($folder['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <input type="text" class="form-control" id="editNoteTags" placeholder="Enter tags separated by commas">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateNote">Update Note</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/ai.js"></script>
    <script>
        // Initialize AI Chat
        document.addEventListener('DOMContentLoaded', function() {
            window.aiChat = new AIChat();
        });
    </script>
</body>
</html> 