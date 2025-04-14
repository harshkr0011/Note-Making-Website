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
    $pdf->Write(0, $note['content'], '', 0, 'L', true, 0, false, false, 0);

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
