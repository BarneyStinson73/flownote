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
$userId = $_SESSION['user_id'];

// Retrieve file from database
$stmt = $conn->prepare("SELECT original_name, file_type, file_content FROM uploaded_files WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $fileId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $fileName = $row['original_name'];
    $fileType = $row['file_type'];
    $fileContent = base64_decode($row['file_content']);
    
    // Set appropriate headers
    header('Content-Type: ' . $fileType);
    header('Content-Disposition: inline; filename="' . $fileName . '"');
    header('Content-Length: ' . strlen($fileContent));
    
    // Output file content
    echo $fileContent;
} else {
    http_response_code(404);
    die('File not found');
}

$stmt->close();
$conn->close();
?>