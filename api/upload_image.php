<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    // Debug information
    error_log("Starting image upload process");
    
    // Check if file was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error: " . print_r($_FILES, true));
        throw new Exception('No image uploaded or upload error');
    }

    $file = $_FILES['image'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        error_log("Invalid file type: " . $file['type']);
        throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
    }

    // Set up paths using Windows-compatible directory separators
    $upload_dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'images';
    
    error_log("Upload directory: " . $upload_dir);

    // Generate unique filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . DIRECTORY_SEPARATOR . $filename;

    error_log("Attempting to move file to: " . $filepath);

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        error_log("Failed to move uploaded file. PHP error: " . error_get_last()['message']);
        throw new Exception('Failed to save image');
    }

    // Generate URL (use forward slashes for URLs)
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $relative_path = '/Nexus%20Notes/uploads/images/' . $filename;
    $image_url = $base_url . $relative_path;

    error_log("Successfully uploaded image. URL: " . $image_url);

    echo json_encode([
        'success' => true,
        'url' => $image_url
    ]);

} catch (Exception $e) {
    error_log("Image upload error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 