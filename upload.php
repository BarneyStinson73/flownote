<?php
session_start();
include 'config.php';

// Handle sign-out BEFORE any output
if (isset($_POST['signout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$uploadMsg = '';
$previewData = null;
$fileInfo = null;
$showPreview = false;

// Handle final upload with categorization
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_upload'])) {
    $tempFile = $_POST['temp_file'];
    $originalName = $_POST['original_name'];
    $fileSize = $_POST['file_size'];
    $fileType = $_POST['file_type'];
    $noteType = $_POST['note_type']; // 'personal' or 'shared'
    $category = $_POST['category']; // math, physics, etc.
    $description = $_POST['description'] ?? '';
    
    if (file_exists($tempFile)) {
        // Read file content to store in database
        $fileContent = file_get_contents($tempFile);
        $fileContentBase64 = base64_encode($fileContent);
        
        $userId = $_SESSION['user_id'] ?? 1; // Use your actual session variable
        
        // Insert into database with file content
        $stmt = $conn->prepare("INSERT INTO uploaded_files (user_id, original_name, file_size, file_type, note_type, category, description, file_content, upload_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        if ($stmt) {
            $stmt->bind_param("isssssss", $userId, $originalName, $fileSize, $fileType, $noteType, $category, $description, $fileContentBase64);
            
            if ($stmt->execute()) {
                // Delete temp file after successful database insert
                unlink($tempFile);
                
                // Redirect to dashboard
                if ($noteType === 'personal') {
                    header('Location: my_notes.php');
                } else {
                    header('Location: shared_notes.php');
                }
                exit();
            } else {
                $uploadMsg = '<div class="alert alert-danger">Database error: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        } else {
            $uploadMsg = '<div class="alert alert-danger">Database preparation error: ' . $conn->error . '</div>';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $allowed = ['pdf', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        $uploadMsg = '<div class="alert alert-danger">❌ File type not allowed.</div>';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadMsg = '<div class="alert alert-danger">❌ File upload error.</div>';
    } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        $uploadMsg = '<div class="alert alert-danger">❌ File size exceeds 10MB limit.</div>';
    } else {
        $uploadDir = 'temp_uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $uniqueName = 'temp_' . time() . '_' . uniqid() . '.' . $ext;
        $uploadPath = $uploadDir . $uniqueName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $uploadMsg = '<div class="alert alert-success">✅ File ready for upload!</div>';
            $showPreview = true;
            
            // Store file info
            $fileInfo = [
                'name' => $file['name'],
                'size' => $file['size'],
                'type' => $file['type'],
                'path' => $uploadPath,
                'temp_path' => $uploadPath,
                'ext' => $ext,
                'modified' => date('Y-m-d H:i:s', filemtime($uploadPath))
            ];
            
            // Generate preview data
            $previewData = generatePreview($uploadPath, $ext, $file['name']);
        } else {
            $uploadMsg = '<div class="alert alert-danger">❌ Failed to save file.</div>';
        }
    }
}

function generatePreview($path, $ext, $filename) {
    switch ($ext) {
        case 'pdf':
            return [
                'type' => 'pdf',
                'content' => '<div style="width: 100%; height: 450px;"><embed src="' . $path . '" type="application/pdf" width="100%" height="100%" style="border-radius:12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);"></div>'
            ];
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return [
                'type' => 'image',
                'content' => '<div style="text-align: center;"><img src="' . $path . '" alt="Preview" class="img-fluid" style="max-height: 400px; max-width: 100%; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); object-fit: contain;"></div>'
            ];
        case 'txt':
            $content = htmlspecialchars(file_get_contents($path));
            $preview = strlen($content) > 2000 ? substr($content, 0, 2000) . '...' : $content;
            return [
                'type' => 'text',
                'content' => '<div style="width: 100%;"><pre style="background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; max-height: 400px; overflow-y: auto; font-family: \'Courier New\', monospace; font-size: 13px; line-height: 1.5; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); margin: 0;">' . $preview . '</pre></div>'
            ];
        default:
            $iconMap = [
                'doc' => '📝', 'docx' => '📝',
                'ppt' => '📊', 'pptx' => '📊',
                'xls' => '📈', 'xlsx' => '📈'
            ];
            $icon = isset($iconMap[$ext]) ? $iconMap[$ext] : '📄';
            return [
                'type' => 'generic',
                'content' => '<div style="text-align: center; padding: 40px;"><div style="font-size: 64px; margin-bottom: 20px; color: #f4623a;">' . $icon . '</div><h6>' . htmlspecialchars($filename) . '</h6><p style="color: #6c757d;">Preview not available for this file type.</p><small style="color: #6c757d;">File is ready and uploaded successfully.</small></div>'
            ];
    }
}

function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Upload with Preview</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/upload.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'navigation_bar.php'; ?>

    <div class="container-fluid main-container">
        <div class="row">
            <!-- Left Side: File Upload -->
            <div class="col-lg-6 mb-4">
                <div class="upload-section">
                    <?php if ($showPreview): ?>
                        <div class="status-indicator">✅ Ready</div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div class="upload-container" id="uploadContainer">
                            <div class="progress-circle" id="progressCircle">
                                <div class="upload-content">
                                    <div class="upload-text" id="uploadText">
                                        <?php if ($showPreview): ?>
                                            ✅ Ready<br><?php echo htmlspecialchars(substr($fileInfo['name'], 0, 20)) . (strlen($fileInfo['name']) > 20 ? '...' : ''); ?>
                                        <?php else: ?>
                                            📁 Click or Drop<br>file to upload
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <input type="file" class="file-input" id="fileInput" name="file" 
                                   accept=".txt,.pdf,.jpg,.jpeg,.png,.gif,.doc,.docx" 
                                   <?php echo $showPreview ? '' : 'required'; ?> />
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Side: File Preview -->
            <div class="col-lg-6 mb-4">
                <div class="preview-section">
                    <div class="preview-header">
                        <h5>📋 File Preview & Information</h5>
                    </div>

                    <!-- Upload Status -->
                    <?php if ($uploadMsg): ?>
                        <div id="uploadStatus"><?php echo $uploadMsg; ?></div>
                    <?php endif; ?>

                    <!-- File Info Section -->
                    <?php if ($fileInfo): ?>
                    <div id="fileInfoSection">
                        <div class="file-info">
                            <h6>📄 File Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($fileInfo['name']); ?></p>
                            <p><strong>Size:</strong> <?php echo formatFileSize($fileInfo['size']); ?></p>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($fileInfo['type'] ?: 'Unknown'); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Preview Content -->
                    <div class="preview-content <?php echo $previewData ? 'has-content' : ''; ?>" id="previewContent">
                        <?php if ($previewData): ?>
                            <div style="width: 100%; height: 100%;">
                                <?php echo $previewData['content']; ?>
                            </div>
                        <?php else: ?>
                            <div>
                                <div class="file-icon default-icon">📁</div>
                                <h5 style="color: #6c757d; font-weight: 500;">No File Selected</h5>
                                <p>Choose a file to upload and see preview here<br>
                                <small>Supports: PDF, Images, Text files, Documents<br>
                                Maximum file size: 10MB</small></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bottom-section">
                        <div class="d-flex justify-content-between">
                            <?php if ($showPreview): ?>
                                <a href="?" class="btn btn-outline-secondary">📤 Upload Another</a>
                                <div>
                                    <a href="<?php echo $fileInfo['path']; ?>" target="_blank" class="btn btn-outline-primary me-2">👁️ View Full</a>
                                    <button type="button" class="btn upload-btn" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                        💾 Save File
                                    </button>
                                </div>
                            <?php else: ?>
                                <div></div>
                                <button type="submit" form="uploadForm" class="btn upload-btn" id="submitBtn" disabled>
                                    ⬆️ Upload File
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Configuration Modal -->
    <?php if ($showPreview): ?>
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">💾 Save File Configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="finalUploadForm">
                    <div class="modal-body">
                        <input type="hidden" name="final_upload" value="1">
                        <input type="hidden" name="temp_file" value="<?php echo $fileInfo['temp_path']; ?>">
                        <input type="hidden" name="original_name" value="<?php echo htmlspecialchars($fileInfo['name']); ?>">
                        <input type="hidden" name="file_size" value="<?php echo $fileInfo['size']; ?>">
                        <input type="hidden" name="file_type" value="<?php echo htmlspecialchars($fileInfo['type']); ?>">
                        
                        <!-- File Summary -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="mb-2">📄 File: <?php echo htmlspecialchars($fileInfo['name']); ?></h6>
                            <small class="text-muted">Size: <?php echo formatFileSize($fileInfo['size']); ?></small>
                        </div>

                        <!-- Note Type Selection -->
                        <div class="mb-4">
                            <label class="form-label">📝 Note Type</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="note_type" id="personal" value="personal" checked>
                                    <label class="btn btn-outline-primary w-100" for="personal">
                                        🔒 Personal Notes<br>
                                        <small>Only you can see</small>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="note_type" id="shared" value="shared">
                                    <label class="btn btn-outline-success w-100" for="shared">
                                        🌍 Shared Notes<br>
                                        <small>Others can access</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Category Selection -->
                        <div class="mb-4">
                            <label class="form-label">📚 Category</label>
                            <input type="hidden" name="category" id="selectedCategory" value="general">
                            <div class="category-grid">
                                <div class="category-option selected" data-category="general">
                                    <div class="category-icon">📋</div>
                                    <div>General</div>
                                </div>
                                <div class="category-option" data-category="math">
                                    <div class="category-icon">🧮</div>
                                    <div>Math</div>
                                </div>
                                <div class="category-option" data-category="physics">
                                    <div class="category-icon">⚛️</div>
                                    <div>Physics</div>
                                </div>
                                <div class="category-option" data-category="chemistry">
                                    <div class="category-icon">🧪</div>
                                    <div>Chemistry</div>
                                </div>
                                <div class="category-option" data-category="biology">
                                    <div class="category-icon">🧬</div>
                                    <div>Biology</div>
                                </div>
                                <div class="category-option" data-category="history">
                                    <div class="category-icon">📜</div>
                                    <div>History</div>
                                </div>
                                <div class="category-option" data-category="literature">
                                    <div class="category-icon">📚</div>
                                    <div>Literature</div>
                                </div>
                                <div class="category-option" data-category="other">
                                    <div class="category-icon">📁</div>
                                    <div>Other</div>
                                </div>
                            </div>
                        </div>

                        <!-- Optional Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">📝 Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Add a description for your file..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn upload-btn">💾 Save File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/upload.js"></script>
</body>
</html>