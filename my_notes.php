<?php
session_start();
include 'config.php';

if (isset($_POST['signout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$selectedCategory = $_GET['category'] ?? 'all';

// Get all categories for this user's personal notes
$categoryQuery = $conn->prepare("SELECT category, COUNT(*) as file_count FROM uploaded_files WHERE user_id = ? AND note_type = 'personal' GROUP BY category ORDER BY category");
$categoryQuery->bind_param("i", $userId);
$categoryQuery->execute();
$categories = $categoryQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Get files based on selected category
if ($selectedCategory === 'all') {
    $filesQuery = $conn->prepare("SELECT id, original_name, file_size, file_type, category, description, upload_date FROM uploaded_files WHERE user_id = ? AND note_type = 'personal' ORDER BY upload_date DESC");
    $filesQuery->bind_param("i", $userId);
} else {
    $filesQuery = $conn->prepare("SELECT id, original_name, file_size, file_type, category, description, upload_date FROM uploaded_files WHERE user_id = ? AND note_type = 'personal' AND category = ? ORDER BY upload_date DESC");
    $filesQuery->bind_param("is", $userId, $selectedCategory);
}
$filesQuery->execute();
$files = $filesQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Category icons mapping
$categoryIcons = [
    'general' => '📋',
    'math' => '🧮',
    'physics' => '⚛️',
    'chemistry' => '🧪',
    'biology' => '🧬',
    'history' => '📜',
    'literature' => '📚',
    'other' => '📑'
];

function formatFileSize($bytes)
{
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

function getFileIcon($fileType)
{
    $type = strtolower($fileType);
    if (strpos($type, 'pdf') !== false) return '📄';
    if (strpos($type, 'image') !== false) return '🖼️';
    if (strpos($type, 'text') !== false) return '📝';
    if (strpos($type, 'word') !== false || strpos($type, 'document') !== false) return '📝';
    return '📄';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes - Personal Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/my_notes.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <?php include 'navigation_bar.php'; ?>

    <div class="container-fluid main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-user-lock"></i> My Personal Notes</h1>
            <p>Your private collection of uploaded files, organized by category</p>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="sidebar">
                    <h5><i class="fas fa-folder-open"></i> Categories</h5>

                    <a href="?category=all" class="category-item <?php echo $selectedCategory === 'all' ? 'active' : ''; ?>">
                        <span class="category-icon">📁</span>
                        All Files
                        <span class="file-count"><?php echo array_sum(array_column($categories, 'file_count')); ?></span>
                    </a>

                    <?php foreach ($categories as $category): ?>
                        <a href="?category=<?php echo urlencode($category['category']); ?>"
                            class="category-item <?php echo $selectedCategory === $category['category'] ? 'active' : ''; ?>">
                            <span class="category-icon">
                                <?php echo $categoryIcons[$category['category']] ?? '📝'; ?>
                            </span>
                            <?php echo ucfirst($category['category']); ?>
                            <span class="file-count"><?php echo $category['file_count']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-lg-9 col-md-8">
                <div class="content-area">
                    <div class="content-header">
                        <h2 class="content-title">
                            <i class="fas fa-files-o"></i>
                            <?php
                            if ($selectedCategory === 'all') {
                                echo 'All Personal Files (' . count($files) . ')';
                            } else {
                                echo ucfirst($selectedCategory) . ' Files (' . count($files) . ')';
                            }
                            ?>
                        </h2>
                        <a href="upload.php" class="upload-btn">
                            <i class="fas fa-plus"></i> Upload New File
                        </a>
                    </div>

                    <?php if (empty($files)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📁</div>
                            <h4>No files found</h4>
                            <p>
                                <?php if ($selectedCategory === 'all'): ?>
                                    You haven't uploaded any personal files yet.
                                <?php else: ?>
                                    No files found in the <?php echo ucfirst($selectedCategory); ?> category.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="files-grid">
                            <?php foreach ($files as $file): ?>
                                <div class="file-card" onclick="viewFile(<?php echo $file['id']; ?>)">
                                    <div class="category-badge">
                                        <?php echo $categoryIcons[$file['category']] ?? '📝'; ?>
                                        <?php echo ucfirst($file['category']); ?>
                                    </div>

                                    <div class="file-icon">
                                        <?php echo getFileIcon($file['file_type']); ?>
                                    </div>

                                    <div class="file-name">
                                        <?php echo htmlspecialchars($file['original_name']); ?>
                                    </div>

                                    <div class="file-info">
                                        <i class="fas fa-hdd"></i> <?php echo formatFileSize($file['file_size']); ?>
                                        <br>
                                        <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($file['upload_date'])); ?>
                                    </div>

                                    <?php if ($file['description']): ?>
                                        <div class="file-description">
                                            <?php echo htmlspecialchars($file['description']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="file-actions" onclick="event.stopPropagation()">
                                        <button class="action-btn view-btn" onclick="viewFile(<?php echo $file['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="action-btn download-btn" onclick="downloadFile(<?php echo $file['id']; ?>, '<?php echo htmlspecialchars($file['original_name']); ?>')">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                        <button class="action-btn delete-btn" onclick="deleteFile(<?php echo $file['id']; ?>, '<?php echo htmlspecialchars($file['original_name']); ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- File Viewer Modal -->
    <div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileModalLabel">File Viewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="fileModalBody" style="min-height: 500px;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/notes.js"></script>
</body>
</html>