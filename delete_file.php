<?php
ob_start(); // Start output buffering
error_reporting(0);
ini_set('display_errors', 0);

session_start();
include 'config.php';

ob_clean(); // Clear any output from included files
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        exit();
    }

    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit();
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['file_id'])) {
        echo json_encode(['success' => false, 'message' => 'File ID not provided']);
        exit();
    }

    $fileId = intval($input['file_id']);
    $userId = $_SESSION['user_id'];

    // Check if file exists and belongs to user
    $checkStmt = $conn->prepare("SELECT id, original_name FROM uploaded_files WHERE id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $fileId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'File not found or access denied']);
        exit();
    }

    $file = $result->fetch_assoc();
    $checkStmt->close();

    // Delete the file
    $deleteStmt = $conn->prepare("DELETE FROM uploaded_files WHERE id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $fileId, $userId);

    if ($deleteStmt->execute() && $deleteStmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete file']);
    }

    $deleteStmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
    
$conn->close();
?>