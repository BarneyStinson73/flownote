<?php
session_start();
include 'config.php'; // Your database configuration

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

if ($_POST['action'] === 'upload' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $userId = $_SESSION['user_id']; // Updated session variable
    $noteType = $_POST['note_type'] ?? 'personal'; // personal or shared
    $category = $_POST['category'] ?? 'general'; // math, physics, etc.
    $description = $_POST['description'] ?? ''; // optional description
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'File upload error']);
        exit();
    }
    
    // File type validation
    $allowed = ['pdf', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed']);
        exit();
    }
    
    // File size validation (10MB limit)
    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
        exit();
    }
    
    // File information
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    $tempPath = $file['tmp_name'];
    
    // Read file content for database storage
    $fileContent = file_get_contents($tempPath);
    $fileContentBase64 = base64_encode($fileContent);
    
    // Save to database with file content
    $stmt = $conn->prepare("INSERT INTO uploaded_files (user_id, original_name, file_size, file_type, note_type, category, description, file_content, upload_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    if ($stmt) {
        $stmt->bind_param("isssssss", $userId, $fileName, $fileSize, $fileType, $noteType, $category, $description, $fileContentBase64);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'File uploaded successfully',
                'file_id' => $conn->insert_id,
                'category' => $category,
                'note_type' => $noteType
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database preparation error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>