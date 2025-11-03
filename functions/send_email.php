<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPModules/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPModules/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPModules/PHPMailer/src/SMTP.php';

function sendEmail($receiver, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'plpasig.sso@gmail.com';
        $mail->Password   = 'npla ugmc iafq mvaf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('plpasig.sso@gmail.com', 'Pamantasan ng Lungsod ng Pasig - Student Success Office');
        $mail->addAddress($receiver);

        $mail->isHTML(true);
        $mail->Subject = $subject;

        $mail->Body = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}