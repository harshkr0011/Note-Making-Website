<?php
ob_start(); // Start output buffering

require_once '../vendor/autoload.php';
require_once '../config/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access');
}

$database = new Database();
$pdo = $database->getConnection();

$note_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$note_id) {
    die('Invalid note ID');
}

try {
    $stmt = $pdo->prepare("SELECT title, content, created_at FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$note_id, $_SESSION['user_id']]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$note) {
        die('Note not found');
    }

    // Convert HTML to plain text while preserving formatting and images
    $content = $note['content'];
    
    // Create a temporary directory for images
    $temp_dir = sys_get_temp_dir() . '/nexus_notes_images';
    if (!file_exists($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    // Extract and save images
    preg_match_all('/<img[^>]+src="([^">]+)"/', $content, $matches);
    $image_paths = [];
    
    foreach ($matches[1] as $index => $src) {
        // Handle base64 encoded images
        if (strpos($src, 'data:image') === 0) {
            $data = explode(',', $src);
            $image_data = base64_decode($data[1]);
            $image_info = getimagesizefromstring($image_data);
            $extension = image_type_to_extension($image_info[2]);
            $temp_path = $temp_dir . '/image_' . $index . $extension;
            file_put_contents($temp_path, $image_data);
            $image_paths[] = $temp_path;
            
            // Replace base64 image with placeholder
            $content = str_replace($src, 'IMAGE_PLACEHOLDER_' . $index, $content);
        }
        // Handle regular image URLs
        else {
            $temp_path = $temp_dir . '/image_' . $index . '.jpg';
            $image_data = file_get_contents($src);
            if ($image_data !== false) {
                file_put_contents($temp_path, $image_data);
                $image_paths[] = $temp_path;
                
                // Replace image URL with placeholder
                $content = str_replace($src, 'IMAGE_PLACEHOLDER_' . $index, $content);
            }
        }
    }
    
    // Replace <p> tags with double newlines
    $content = preg_replace('/<p[^>]*>/', "\n\n", $content);
    $content = str_replace('</p>', '', $content);
    
    // Replace <br> tags with single newlines
    $content = preg_replace('/<br\s*\/?>/', "\n", $content);
    
    // Replace <h1> to <h6> tags with newlines and bold text
    $content = preg_replace('/<h([1-6])[^>]*>(.*?)<\/h\1>/', "\n\n$2\n\n", $content);
    
    // Replace <strong> and <b> tags with bold text
    $content = preg_replace('/<(strong|b)[^>]*>(.*?)<\/(strong|b)>/', "$2", $content);
    
    // Replace <em> and <i> tags with italic text
    $content = preg_replace('/<(em|i)[^>]*>(.*?)<\/(em|i)>/', "$2", $content);
    
    // Replace <ul> and <ol> lists with proper formatting
    $content = preg_replace('/<ul[^>]*>(.*?)<\/ul>/s', "\n$1\n", $content);
    $content = preg_replace('/<ol[^>]*>(.*?)<\/ol>/s', "\n$1\n", $content);
    $content = preg_replace('/<li[^>]*>(.*?)<\/li>/', "\n• $1", $content);
    
    // Remove remaining HTML tags
    $content = strip_tags($content);
    
    // Convert HTML entities to their corresponding characters
    $content = html_entity_decode($content);
    
    // Remove ** markers
    $content = str_replace('**', '', $content);
    
    // Clean up multiple newlines
    $content = preg_replace('/\n{3,}/', "\n\n", $content);
    
    // Trim whitespace
    $content = trim($content);

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Nexus Notes');
    $pdf->SetTitle($note['title']);

    $pdf->SetHeaderData(
        PDF_HEADER_LOGO,
        PDF_HEADER_LOGO_WIDTH,
        $note['title'],
        "Created on: " . date('Y-m-d H:i:s', strtotime($note['created_at']))
    );

    $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
    $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    
    // Split content by image placeholders
    $parts = explode('IMAGE_PLACEHOLDER_', $content);
    
    // Write first part of content
    $pdf->Write(0, $parts[0], '', 0, 'L', true, 0, false, false, 0);
    
    // Add images and remaining content
    for ($i = 1; $i < count($parts); $i++) {
        $image_index = intval($parts[$i]);
        if (isset($image_paths[$image_index])) {
            // Add image
            $pdf->Image($image_paths[$image_index], '', '', 0, 0, '', '', 'L', false, 300, '', false, false, 0, false, false, false);
            $pdf->Ln(10);
        }
        
        // Write remaining content
        $remaining_content = substr($parts[$i], strpos($parts[$i], ' ') + 1);
        $pdf->Write(0, $remaining_content, '', 0, 'L', true, 0, false, false, 0);
    }

    // Clean up temporary files
    foreach ($image_paths as $path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }
    if (file_exists($temp_dir)) {
        rmdir($temp_dir);
    }

    ob_end_clean(); // Clear the output buffer before sending PDF
    $pdf->Output($note['title'] . '.pdf', 'D');

} catch(PDOException $e) {
    ob_end_clean(); // Ensure buffer is cleared on error
    die('Database error: ' . $e->getMessage());
} catch(Exception $e) {
    ob_end_clean(); // Ensure buffer is cleared on error
    die('Error: ' . $e->getMessage());
}
?>
