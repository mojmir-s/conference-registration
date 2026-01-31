<?php
/**
 * User Login Page
 */

require_once __DIR__ . '/../../config/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/pages/user/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid form submission.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($email) || empty($password)) {
            $errors[] = 'Email and password are required.';
        } else {
            $result = loginUser($email, $password);

            if ($result['success']) {
                if ($result['is_admin']) {
                    redirect(SITE_URL . '/pages/admin/dashboard.php');
                } else {
                    redirect(SITE_URL . '/pages/user/dashboard.php');
                }
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$pageTitle = 'Login - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="card auth-card shadow">
            <div class="card-header">
                <h4><i class="bi bi-box-arrow-in-right me-2"></i>Login</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label required">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please enter your email address.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>

                    <div class="mb-4 text-end">
                        <a href="<?= SITE_URL ?>/pages/auth/forgot-password.php">Forgot password?</a>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <p class="text-center mb-0">
                    Don't have an account?
                    <a href="<?= SITE_URL ?>/pages/auth/register.php">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
