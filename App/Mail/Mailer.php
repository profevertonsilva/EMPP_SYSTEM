<?php

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Transactional email over the SMTP account configured in .env (MAIL_*)
class Mailer {

    private function newMailer(){
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'];
        $mail->Port = (int) $_ENV['MAIL_PORT'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USER'];
        $mail->Password = $_ENV['MAIL_PASS'];
        // 465 is implicit TLS; any other port negotiates STARTTLS
        $mail->SMTPSecure = $mail->Port === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME'] ?? 'EMPP');
        return $mail;
    }

    // Sends a 6-digit code. $purpose is 'verify' (sign-up) or 'reset' (forgot password).
    // Returns false (and logs) when the SMTP fails.
    public function sendCode($purpose, $email, $name, $code, $minutes){
        try{
            $mail = $this->newMailer();
            $mail->addAddress($email, (string) $name);
            $mail->Subject = $purpose === 'reset'
                ? "$code is your EMPP password reset code"
                : "$code is your EMPP verification code";

            // The logo travels inside the message: BASE_URL may be localhost
            $mail->addEmbeddedImage('resources/img/logo_empp_w.png', 'empp-logo', 'empp.png');

            $mail->isHTML(true);
            $mail->Body = $this->renderTemplate('verify_code', [
                'purpose' => $purpose,
                'name' => (string) $name,
                'code' => $code,
                'minutes' => $minutes,
            ]);
            $greeting = trim((string) $name) === '' ? "Hi," : "Hi $name,";
            $mail->AltBody = $purpose === 'reset'
                ? "$greeting\n\n"
                    . "Your EMPP password reset code is: $code\n\n"
                    . "Type it on the reset page together with your new password. "
                    . "It is valid for $minutes minutes.\n\n"
                    . "If you did not ask to reset your password, ignore this email; your password stays the same.\n\n"
                    . "EMPP - Electrospun Membrane Property Predictor"
                : "$greeting\n\n"
                    . "Your EMPP verification code is: $code\n\n"
                    . "Type it on the verification page to confirm your email. "
                    . "It is valid for $minutes minutes.\n\n"
                    . "If you did not create an EMPP account, ignore this email; the account stays inactive.\n\n"
                    . "EMPP - Electrospun Membrane Property Predictor";

            return $mail->send();
        }catch(Exception $e){
            error_log('EMPP mail: ' . $e->getMessage());
            return false;
        }
    }

    private function renderTemplate($template, array $data){
        extract($data);
        ob_start();
        include __DIR__ . "/../View/email/$template.php";
        return ob_get_clean();
    }
}
