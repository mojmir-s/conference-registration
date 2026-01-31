<?php
/**
 * Forgot Password Page
 */

require_once __DIR__ . '/../../config/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/pages/user/dashboard.php');
}

$submitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid form submission.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            $result = requestPasswordReset($email);
            $submitted = true;
        }
    }
}

$pageTitle = 'Forgot Password - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="card auth-card shadow">
            <div class="card-header">
                <h4><i class="bi bi-key me-2"></i>Forgot Password</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($submitted): ?>
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="bi bi-envelope text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h5>Check Your Email</h5>
                        <p class="text-muted">
                            If an account exists with that email address, we've sent a password reset link.
                            Please check your inbox and spam folder.
                        </p>
                        <a href="<?= SITE_URL ?>/pages/auth/login.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Login
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
                        Enter your email address and we'll send you a link to reset your password.
                    </p>

                    <form method="POST" class="needs-validation" novalidate>
                        <?= csrfField() ?>

                        <div class="mb-4">
                            <label for="email" class="form-label required">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            <div class="invalid-feedback">Please enter your email address.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-envelope me-2"></i>Send Reset Link
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <p class="text-center mb-0">
                        Remember your password?
                        <a href="<?= SITE_URL ?>/pages/auth/login.php">Login here</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
