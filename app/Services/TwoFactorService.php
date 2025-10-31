<?php
namespace App\Services;

use App\Core\Mailer;
use App\Models\User;

class TwoFactorService
{
    private $mailer;
    private $userModel;
    private $lastError;

    public function __construct()
    {
        $this->mailer = new Mailer();
        $this->userModel = new User();
    }

    private function generateCode(): string
    {
        return sprintf("%06d", mt_rand(1, 999999));
    }

    public function initiateTwoFactor(int $userId, string $email): string
    {
        error_log("🎯 [2FA] initiateTwoFactor called");
        error_log("👤 [2FA] User ID: " . $userId);
        error_log("📧 [2FA] User Email: " . $email);
        
        $code = $this->generateCode();
        error_log("🔐 [2FA] Generated code: " . $code);
        
        $this->userModel->setTwoFactorCode($userId, $code);
        error_log("💾 [2FA] Code stored in database");

        $this->sendVerificationCode($email, $code);
        
        return $code;
    }

    private function sendVerificationCode(string $email, string $code): bool
    {
        error_log("🚀 [2FA] START - sendVerificationCode called");
        error_log("📧 [2FA] To: " . $email);
        error_log("🔢 [2FA] Code: " . $code);
        
        // Use getenv() instead of constants
        $smtpHost = getenv('SMTP_HOST');
        $smtpUser = getenv('SMTP_USERNAME');
        $smtpFrom = getenv('SMTP_FROM_EMAIL');
        
        error_log("⚙️ [2FA] SMTP_HOST: " . ($smtpHost ?: 'NOT FOUND'));
        error_log("⚙️ [2FA] SMTP_USERNAME: " . ($smtpUser ?: 'NOT FOUND'));
        error_log("⚙️ [2FA] SMTP_FROM_EMAIL: " . ($smtpFrom ?: 'NOT FOUND'));

        $subject = "Your SaveEAT Verification Code";
        $body = "
            <h2>SaveEAT Verification Code</h2>
            <p>Your verification code is: <strong>{$code}</strong></p>
            <p>This code expires in 10 minutes.</p>
        ";

        error_log("📨 [2FA] Calling mailer->send()...");
        
        $result = $this->mailer->send($email, $subject, $body);
        
        if ($result) {
            error_log("✅ [2FA] SUCCESS - mailer->send() returned true");
        } else {
            $error = $this->mailer->getLastError();
            error_log("❌ [2FA] FAILED - mailer->send() returned false");
            error_log("❌ [2FA] Error: " . ($error ?: 'No error message'));
        }
        
        error_log("🏁 [2FA] END - returning: " . ($result ? 'true' : 'false'));
        return $result;
    }

    public function verifyCode(int $userId, string $code): bool
    {
        return $this->userModel->verifyTwoFactorCode($userId, $code);
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}