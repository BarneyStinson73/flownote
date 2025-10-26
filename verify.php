<?php
include 'config.php';

$msg = '';
$msgType = 'info'; // info, success, danger

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

// Validate input
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $msg = 'Invalid email address in verification link.';
    $msgType = 'danger';
} elseif (!preg_match('/^[a-f0-9]{32}$/i', $token)) {
    $msg = 'Invalid token in verification link.';
    $msgType = 'danger';
} else {
    // Check if user exists with this email and token
    $stmt = $conn->prepare("SELECT id, name, is_verified FROM users WHERE email=? AND token=? LIMIT 1");
    if (!$stmt) {
        $msg = 'Database error occurred. Please try again later.';
        $msgType = 'danger';
        error_log('Prepare failed: ' . $conn->error);
    } else {
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            if ((int)$row['is_verified'] === 1) {
                $msg = 'Your account is already verified. You can now log in.';
                $msgType = 'success';
            } else {
                // Verify the account
                $upd = $conn->prepare("UPDATE users SET is_verified=1, token=NULL WHERE id=?");
                if (!$upd) {
                    $msg = 'Database error occurred during verification.';
                    $msgType = 'danger';
                    error_log('Update prepare failed: ' . $conn->error);
                } else {
                    $upd->bind_param("i", $row['id']);
                    if ($upd->execute()) {
                        $msg = 'Congratulations ' . htmlspecialchars($row['name']) . '! Your account has been successfully verified. You can now log in.';
                        $msgType = 'success';
                        error_log('User verified successfully: ' . $email);
                    } else {
                        $msg = 'Verification failed due to a database error. Please try again later.';
                        $msgType = 'danger';
                        error_log('Update failed: ' . $upd->error);
                    }
                    $upd->close();
                }
            }
        } else {
            $msg = 'Invalid or expired verification link. The link may have already been used or has expired.';
            $msgType = 'warning';
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification - FlowNote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <?php if ($msgType === 'success'): ?>
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                            <?php elseif ($msgType === 'danger'): ?>
                                <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                            <?php else: ?>
                                <i class="bi bi-exclamation-circle-fill text-warning" style="font-size: 3rem;"></i>
                            <?php endif; ?>
                        </div>
                        
                        <h2 class="mb-4">Email Verification</h2>
                        
                        <div class="alert alert-<?php echo $msgType; ?> text-start">
                            <?php echo $msg; ?>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-house-fill me-2"></i>Return to Home
                            </a>
                            
                            <?php if ($msgType === 'success'): ?>
                                <a href="login.php" class="btn btn-outline-success">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login Now
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($msgType !== 'success' && !empty($email)): ?>
                        <div class="mt-4 pt-4 border-top">
                            <p class="text-muted small">
                                Having trouble? <a href="mailto:support@flownote.com">Contact Support</a>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>