<?php
/**
 * User Registration Page
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
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (!isValidPassword($password)) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        // Register user if no errors
        if (empty($errors)) {
            $result = registerUser($email, $password);

            if ($result['success']) {
                setFlash('success', $result['message']);
                redirect(SITE_URL . '/pages/auth/login.php');
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$pageTitle = 'Register - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="card auth-card shadow">
            <div class="card-header">
                <h4><i class="bi bi-person-plus me-2"></i>Create Account</h4>
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
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label required">Password</label>
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
                            <i class="bi bi-person-plus me-2"></i>Register
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <p class="text-center mb-0">
                    Already have an account?
                    <a href="<?= SITE_URL ?>/pages/auth/login.php">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
