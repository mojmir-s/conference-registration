<?php
/**
 * Reset Password Page
 */

require_once __DIR__ . '/../../config/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/pages/user/dashboard.php');
}

$token = $_GET['token'] ?? '';
$errors = [];
$success = false;

// Validate token
$user = null;
if (!empty($token)) {
    $user = validateResetToken($token);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    // Verify CSRF token
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid form submission.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (!isValidPassword($password)) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            if (resetPassword($token, $password)) {
                $success = true;
            } else {
                $errors[] = 'Password reset failed. The link may have expired.';
            }
        }
    }
}

$pageTitle = 'Reset Password - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="card auth-card shadow">
            <div class="card-header">
                <h4><i class="bi bi-key me-2"></i>Reset Password</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($success): ?>
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-success">Password Reset Successfully!</h5>
                        <p class="text-muted">
                            Your password has been changed. You can now log in with your new password.
                        </p>
                        <a href="<?= SITE_URL ?>/pages/auth/login.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login Now
                        </a>
                    </div>
                <?php elseif (!$user): ?>
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-danger">Invalid or Expired Link</h5>
                        <p class="text-muted">
                            This password reset link is invalid or has expired.
                            Please request a new one.
                        </p>
                        <a href="<?= SITE_URL ?>/pages/auth/forgot-password.php" class="btn btn-primary">
                            <i class="bi bi-key me-2"></i>Request New Link
                        </a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted mb-4">
                        Enter your new password for <strong><?= htmlspecialchars($user['email']) ?></strong>
                    </p>

                    <form method="POST" class="needs-validation" novalidate>
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label for="password" class="form-label required">New Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   minlength="8" required>
                            <div class="form-text">Minimum 8 characters</div>
                            <div id="password-strength" class="mt-1"></div>
                            <div class="invalid-feedback">Password must be at least 8 characters.</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label required">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password"
                                   name="confirm_password" required>
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-lg me-2"></i>Reset Password
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
