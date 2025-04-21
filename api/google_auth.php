<?php
session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php';

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$credential = $data['credential'] ?? null;

if (!$credential) {
    echo json_encode(['success' => false, 'message' => 'No credential provided']);
    exit;
}

try {
    // Initialize the Google Client
    $client = new Google_Client(['client_id' => 'YOUR_GOOGLE_CLIENT_ID']);
    $payload = $client->verifyIdToken($credential);

    if ($payload) {
        $email = $payload['email'];
        $name = $payload['name'];
        $google_id = $payload['sub'];

        // Check if user exists
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id, username FROM users WHERE google_id = :google_id OR email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":google_id", $google_id);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // User exists, log them in
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            
            echo json_encode(['success' => true]);
        } else {
            // Create new user
            $username = strtolower(str_replace(' ', '', $name));
            $query = "INSERT INTO users (username, email, google_id) VALUES (:username, :email, :google_id)";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":google_id", $google_id);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $db->lastInsertId();
                $_SESSION['username'] = $username;
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create user']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID token']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 