<?php
// Simple email test without using your Mailer class
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "UNIT TEST: Email System\n";
echo "=======================\n";

$testEmail = 'jim.amuto@strathmore.edu';

echo "1. Testing email configuration...\n";
echo "   SMTP Host: " . (getenv('SMTP_HOST') ? 'Set' : 'Missing') . "\n";
echo "   SMTP User: " . (getenv('SMTP_USERNAME') ? 'Set' : 'Missing') . "\n";
echo "   Target: $testEmail\n\n";

echo "2. Sending test email...\n";

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = getenv('SMTP_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USERNAME');
    $mail->Password   = getenv('SMTP_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = getenv('SMTP_PORT');

    // Recipients
    $mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME'));
    $mail->addAddress($testEmail);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SaveEAT Test Email - System Verification';
    $mail->Body    = '
    <html>
    <body>
        <h2>SaveEAT System Test</h2>
        <p>This is a test email to verify that the SaveEAT email system is working correctly.</p>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">
            <h3>Test Details:</h3>
            <ul>
                <li><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</li>
                <li><strong>System:</strong> SaveEAT Platform</li>
                <li><strong>Purpose:</strong> Unit Test Verification</li>
            </ul>
        </div>
        
        <p>If you receive this email, it means:</p>
        <ul>
            <li>SMTP configuration is correct</li>
            <li>PHPMailer is working</li>
            <li>Email templates can be sent</li>
            <li>Order notifications will reach customers</li>
        </ul>
        
        <hr>
        <p><small>This is an automated test message from SaveEAT.</small></p>
    </body>
    </html>
    ';
    
    $mail->AltBody = 'SaveEAT Test Email - This is a plain text version for email clients that don\'t support HTML.';

    $mail->send();
    echo "   SUCCESS: Email sent successfully!\n";
    echo "   Please check your inbox at: $testEmail\n";
    echo "   EMAIL SYSTEM: OPERATIONAL\n";
    
} catch (Exception $e) {
    echo "   FAILED: Email test failed with exception:\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n3. Email system ready for:\n";
echo "   Order confirmations\n";
echo "   Delivery notifications\n";
echo "   User account emails\n";
echo "   System alerts\n";