<?php
/**
 * Email Functions using PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Autoload PHPMailer (via Composer)
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * Create configured PHPMailer instance
 */
function createMailer(): ?PHPMailer {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not installed. Run 'composer install'");
        return null;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;

        // Sender
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

        return $mail;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Send verification email
 */
function sendVerificationEmail(string $toEmail, string $token): bool {
    $mail = createMailer();
    if (!$mail) {
        return false;
    }

    $verifyUrl = SITE_URL . '/pages/auth/verify.php?token=' . urlencode($token);

    try {
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - ' . SITE_NAME;

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .button { display: inline-block; padding: 12px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . SITE_NAME . '</h1>
                </div>
                <div class="content">
                    <h2>Verify Your Email Address</h2>
                    <p>Thank you for registering! Please click the button below to verify your email address:</p>
                    <p style="text-align: center;">
                        <a href="' . $verifyUrl . '" class="button">Verify Email</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style="word-break: break-all;">' . $verifyUrl . '</p>
                    <p>This link will expire in 24 hours.</p>
                </div>
                <div class="footer">
                    <p>If you did not create an account, please ignore this email.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = "Verify Your Email\n\n"
            . "Thank you for registering! Please click the link below to verify your email:\n\n"
            . $verifyUrl . "\n\n"
            . "This link will expire in 24 hours.\n\n"
            . "If you did not create an account, please ignore this email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail(string $toEmail, string $token): bool {
    $mail = createMailer();
    if (!$mail) {
        return false;
    }

    $resetUrl = SITE_URL . '/pages/auth/reset-password.php?token=' . urlencode($token);

    try {
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - ' . SITE_NAME;

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .button { display: inline-block; padding: 12px 30px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . SITE_NAME . '</h1>
                </div>
                <div class="content">
                    <h2>Password Reset Request</h2>
                    <p>We received a request to reset your password. Click the button below to create a new password:</p>
                    <p style="text-align: center;">
                        <a href="' . $resetUrl . '" class="button">Reset Password</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style="word-break: break-all;">' . $resetUrl . '</p>
                    <p>This link will expire in 1 hour.</p>
                </div>
                <div class="footer">
                    <p>If you did not request a password reset, please ignore this email.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = "Password Reset Request\n\n"
            . "We received a request to reset your password. Click the link below:\n\n"
            . $resetUrl . "\n\n"
            . "This link will expire in 1 hour.\n\n"
            . "If you did not request a password reset, please ignore this email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send registration confirmation email
 */
function sendRegistrationConfirmation(string $toEmail, array $registration): bool {
    $mail = createMailer();
    if (!$mail) {
        return false;
    }

    try {
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Registration Confirmed - ' . SITE_NAME;

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . SITE_NAME . '</h1>
                </div>
                <div class="content">
                    <h2>Registration Confirmed!</h2>
                    <p>Dear ' . htmlspecialchars($registration['first_name']) . ',</p>
                    <p>Your conference registration has been confirmed. Here are your details:</p>
                    <div class="details">
                        <p><strong>Name:</strong> ' . htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) . '</p>
                        <p><strong>Organization:</strong> ' . htmlspecialchars($registration['organization'] ?? 'N/A') . '</p>
                        <p><strong>Status:</strong> Confirmed</p>
                    </div>
                    <p>You can view and update your registration by logging into your account.</p>
                </div>
                <div class="footer">
                    <p>Thank you for registering!</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}
