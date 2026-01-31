<?php
/**
 * Email Verification Page
 */

require_once __DIR__ . '/../../config/init.php';

$token = $_GET['token'] ?? '';
$success = false;

if (!empty($token)) {
    $success = verifyEmail($token);
}

$pageTitle = 'Verify Email - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="card auth-card shadow">
            <div class="card-header">
                <h4><i class="bi bi-envelope-check me-2"></i>Email Verification</h4>
            </div>
            <div class="card-body p-4 text-center">
                <?php if ($success): ?>
                    <div class="mb-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-success">Email Verified Successfully!</h5>
                    <p class="text-muted">Your email has been verified. You can now log in to your account.</p>
                    <a href="<?= SITE_URL ?>/pages/auth/login.php" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login Now
                    </a>
                <?php else: ?>
                    <div class="mb-4">
                        <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-danger">Verification Failed</h5>
                    <p class="text-muted">
                        The verification link is invalid or has expired.
                        Please try registering again or contact support.
                    </p>
                    <a href="<?= SITE_URL ?>/pages/auth/register.php" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Register Again
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
