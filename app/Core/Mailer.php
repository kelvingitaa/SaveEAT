<?php
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private $lastError;

    public function send($to, $subject, $body, $isHTML = true): bool
    {
        $mail = new PHPMailer(true);

        try {
            if (APP_DEBUG) {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = 'error_log';
            }

            $mail->isSMTP();
            $mail->Host       = getenv('SMTP_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USERNAME');
            $mail->Password   = getenv('SMTP_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = getenv('SMTP_PORT');

            $mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME'));
            $mail->addAddress($to);
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            $this->lastError = null;
            return true;
        } catch (Exception $e) {
            $this->lastError = "Mailer Error: " . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}