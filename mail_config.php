<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function sendMail($to, $subject, $body, $altBody = '') {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Disable debug output
        $mail->isSMTP();                           // Send using SMTP
        $mail->Host       = '';      // SMTP server
        $mail->SMTPAuth   = true;                  // Enable SMTP authentication
        $mail->Username   = ''; // SMTP username (replace with your email)
        $mail->Password   = '';    // SMTP password (replace with your app password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587;                   // TCP port to connect to
        
        // Recipients
        $mail->setFrom('BrickbyBrick@gmail.com', 'BrickbyBrick'); // Sender email and name
        $mail->addAddress($to);                    // Add a recipient
        
        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Function to generate random OTP
function generateOTP($length = 6) {
    $characters = '0123456789';
    $otp = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[mt_rand(0, $max)];
    }
    
    return $otp;
}

// Function to generate password reset token
function generateResetToken() {
    return bin2hex(random_bytes(32));
}

// Function to send OTP email
function sendOTPEmail($email, $otp) {
    $subject = 'Email Verification OTP - Real Estate';
    $body = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
    $body .= '<h2 style="color: #2c3e50; text-align: center;">Email Verification</h2>';
    $body .= '<p>Thank you for registering with Real Estate. Please use the following OTP to verify your email address:</p>';
    $body .= '<div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;">' . $otp . '</div>';
    $body .= '<p>This OTP is valid for 10 minutes. If you did not request this verification, please ignore this email.</p>';
    $body .= '<p>Regards,<br>BrickbyBrick Team</p>';
    $body .= '</div>';
    
    $altBody = "Your OTP for email verification is: $otp";
    
    return sendMail($email, $subject, $body, $altBody);
}

// Function to send password reset email
function sendPasswordResetEmail($email, $token) {
    $reset_link = 'http://' . $_SERVER['HTTP_HOST'] . '/newreal/reset_password.php?token=' . $token;
    $website = 'http://' . $_SERVER['HTTP_HOST'] . '/newreal';
    $contact = $website . '/contact.php';
    
    $subject = 'Password Reset - BrickbyBrick';
    $body = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - BrickbyBrick</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; text-align: center;">
    <div style="max-width: 500px; margin: 30px auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); position: relative;">
        <img style="width: 100%; border-radius: 8px 8px 0 0;" src="https://www.whitecase.com/sites/default/files/images/hero/2024/03/2024-real-estate-market-sentiment-survey-hero.jpg" alt="Real Estate Header">
        <img style="width: 140px; height: 140px; margin-bottom: 15px; border-radius: 50%; object-fit: cover;" src="https://t3.ftcdn.net/jpg/03/33/09/90/360_F_333099003_k7dXXa71RslgmqM2yxTe0LvkA04wX9C8.jpg" alt="BrickbyBrick Logo">
        
        <h2 style="font-size: 22px; color: #333; margin-bottom: 10px;">Hello ' . $email . ',</h2>
        <p style="font-size: 16px; color: #555; line-height: 1.5; margin-bottom: 20px;">
            We received a request to reset your password. If you did not make this request, you can ignore this email.
        </p>
        <a href="' . $reset_link . '" style="display: inline-block; padding: 12px 22px; font-size: 16px; font-weight: bold; color: #fff !important; background: linear-gradient(135deg, #007bff, #0056b3); border-radius: 6px; text-decoration: none;">Reset Your Password</a>

        <div style="margin-top: 25px; background: #f8f8f8; padding: 15px; border-radius: 6px; text-align: left;">
            <img style="width: 100%; border-radius: 6px;" src="https://cdn.prod.website-files.com/6151887923ecfa4ac66a9e69/648ae1ccbe0209d4f34b5216_hero-after-decompressed.jpeg" alt="Featured Property">
            <p style="font-size: 18px; color: #333; font-weight: bold; margin: 10px 0 5px;">Luxury 3BHK Apartment</p>
            <p style="font-size: 14px; color: #666;">Nikol, Ahmedabad</p>
            <p><a href="' . $website . '" style="color:#007bff; text-decoration:none;">View Website</a></p>
        </div>

        <p style="margin-top: 25px; font-size: 14px; color: #777;">
            Need help? <a href="' . $contact . '" style="color:#007bff;">Contact Support</a>
        </p>

        <p style="margin-top: 20px; font-size: 12px; color: #777;">If the button above does not work, copy and paste this link into your browser:<br> 
        <a href="' . $reset_link . '">' . $reset_link . '</a></p>
        
        <p style="margin-top: 20px; font-size: 12px; color: #777;">© 2025 BrickbyBrick. All rights reserved.</p>
    </div>
</body>
</html>';
    
    $altBody = "Reset your password by clicking this link: $reset_link";
    
    $altBody = "Reset your password by clicking this link: $reset_link";
    
    return sendMail($email, $subject, $body, $altBody);
}
?>
