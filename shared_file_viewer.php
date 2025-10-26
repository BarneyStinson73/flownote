<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Access denied');
}

if (!isset($_GET['id'])) {
    die('File ID not provided');
}

$fileId = intval($_GET['id']);

// Retrieve shared file from database (no user restriction for shared files)
$stmt = $conn->prepare("SELECT original_name, file_type, file_content FROM uploaded_files WHERE id = ? AND note_type = 'shared'");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $fileName = $row['original_name'];
    $fileType = $row['file_type'];
    $fileContent = base64_decode($row['file_content']);
    
    // Check if download is requested
    if (isset($_GET['download']) && $_GET['download'] == '1') {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
    } else {
        // Set appropriate headers for viewing
        header('Content-Type: ' . $fileType);
        header('Content-Disposition: inline; filename="' . $fileName . '"');
    }
    
    header('Content-Length: ' . strlen($fileContent));
    
    // Output file content
    echo $fileContent;
} else {
    http_response_code(404);
    die('Shared file not found');
}

$stmt->close();
$conn->close();
?>