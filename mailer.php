<?php
// mailer.php - PHPMailer with Gmail and Mailtrap support
require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail(string $toEmail, string $toName, string $token): bool {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // Set to 2 for debugging
        
        // Choose your email service
        $useGmail = true; // Set to false to use Mailtrap
        
        if ($useGmail) {
            // Gmail SMTP Configuration
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'app.flownote@gmail.com';          // Gmail
            $mail->Password   = 'qgxk wpzd pmhz wkcg';             // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Set sender
            $mail->setFrom('app.flownote@gmail.com', 'FlowNote');
        } else {
            // Mailtrap Configuration (for testing)
            $mail->Host = 'sandbox.smtp.mailtrap.io';  // Use sandbox for testing
            $mail->SMTPAuth = true;
            $mail->Username = 'YOUR_MAILTRAP_USERNAME';      // Get from Mailtrap dashboard
            $mail->Password = 'YOUR_MAILTRAP_PASSWORD';      // Get from Mailtrap dashboard
            $mail->Port = 2525;
            
            // Set sender
            $mail->setFrom('no-reply@flownote.com', 'FlowNote');
        }

        // Recipients
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo('no-reply@flownote.com', 'FlowNote Support');

        // Build verification link
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $verifyLink = $scheme . '://' . $host . $dir . '/verify.php?email=' . urlencode($toEmail) . '&token=' . urlencode($token);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify your FlowNote account';
        
        // HTML body
        $mail->Body = '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #2c5aa0;">Welcome to FlowNote!</h2>
                <p>Hi ' . htmlspecialchars($toName) . ',</p>
                <p>Thank you for registering with FlowNote. To complete your registration, please verify your email address by clicking the button below:</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $verifyLink . '" style="background-color: #2c5aa0; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Verify Email Address</a>
                </div>
                
                <p>If the button above doesn\'t work, you can also click on this link:</p>
                <p><a href="' . $verifyLink . '">' . $verifyLink . '</a></p>
                
                <p>If you didn\'t create an account with FlowNote, you can safely ignore this email.</p>
                
                <hr style="border: 1px solid #eee; margin: 30px 0;">
                <p style="color: #666; font-size: 12px;">This is an automated message, please do not reply to this email.</p>
            </div>
        </body>
        </html>';

        // Plain text version
        $mail->AltBody = "Hi {$toName},\n\n";
        $mail->AltBody .= "Thank you for registering with FlowNote. To verify your email address, please visit:\n";
        $mail->AltBody .= $verifyLink . "\n\n";
        $mail->AltBody .= "If you didn't create an account with FlowNote, you can safely ignore this email.";

        $result = $mail->send();
        
        if ($result) {
            error_log('Verification email sent successfully to: ' . $toEmail);
            return true;
        } else {
            error_log('Failed to send verification email to: ' . $toEmail);
            return false;
        }
        
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        error_log('Exception: ' . $e->getMessage());
        return false;
    }
}

/**
 * Test function to send a test email
 */
function sendTestEmail($toEmail = 'test@example.com', $toName = 'Test User'): bool {
    $testToken = bin2hex(random_bytes(16));
    return sendVerificationEmail($toEmail, $toName, $testToken);
}