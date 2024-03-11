<?php
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Function to send email using PHPMailer
function sendEmail($to, $subject, $html)
{
    $mail = new PHPMailer(true);

    try {
        // Set mailer to use SMTP
        $mail->isSMTP();

        // Specify the SMTP server
        $mail->Host       = 'smtp.gmail.com';

        // Enable SMTP authentication
        $mail->SMTPAuth   = true;

        // SMTP username (My Gmail address)
        $mail->Username   = 'mehmnyan07@gmail.com';

        // SMTP password (My Gmail app password)
        $mail->Password   = 'ecidmexwseccybxr';

        // Enable TLS encryption
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // TCP port to connect to (587 is the default)
        $mail->Port       = 587;

        // Set email content
        $mail->setFrom('mehmnyan07@gmail.com');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = strip_tags($html);

        // Send the email
        $mail->send();


        return true;
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Function to send OTP to the user's email
function sendOTP($email, $otp) {
    try {
        $subject = 'OTP Verification';
        $html = "Your OTP is: $otp";

        // Call the sendEmail function to send OTP
        sendEmail($email, $subject, $html);

        // Store the timestamp when the OTP is generated in the session
        $_SESSION['otp_timestamp'] = time();

        return true;
    } catch (Exception $e) {
        return "Error sending OTP: {$e->getMessage()}";
    }
}

?>
