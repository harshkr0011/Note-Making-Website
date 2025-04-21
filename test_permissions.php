<?php
$upload_dir = __DIR__ . '\\uploads\\images';
echo "<h2>Testing Directory Permissions</h2>";
echo "Upload directory: " . $upload_dir . "<br>";
echo "Directory exists: " . (file_exists($upload_dir) ? 'Yes' : 'No') . "<br>";
echo "Directory is writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . "<br>";

try {
    // Try to create a test file
    $test_file = $upload_dir . '\\test.txt';
    if (file_put_contents($test_file, 'test')) {
        echo "Successfully created test file<br>";
        // Try to delete it
        if (unlink($test_file)) {
            echo "Successfully deleted test file<br>";
        } else {
            echo "Failed to delete test file<br>";
        }
    } else {
        echo "Failed to create test file<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?> 