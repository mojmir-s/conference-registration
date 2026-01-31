<?php
/**
 * Admin - Add User
 * Manually add a user (bypasses email verification)
 */

require_once __DIR__ . '/../../config/init.php';

requireAdmin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid form submission.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $organization = trim($_POST['organization'] ?? '');
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;

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

        if (empty($firstName)) {
            $errors[] = 'First name is required.';
        }

        if (empty($lastName)) {
            $errors[] = 'Last name is required.';
        }

        if (empty($errors)) {
            $pdo = getDBConnection();

            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered.';
            } else {
                try {
                    $pdo->beginTransaction();

                    // Create user (already verified)
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, is_verified, is_admin) VALUES (?, ?, 1, ?)");
                    $stmt->execute([$email, $passwordHash, $isAdmin]);
                    $userId = $pdo->lastInsertId();

                    // Create registration
                    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, first_name, last_name, organization, registration_status) VALUES (?, ?, ?, ?, 'confirmed')");
                    $stmt->execute([$userId, $firstName, $lastName, $organization]);

                    $pdo->commit();

                    setFlash('success', 'User created successfully.');
                    redirect(SITE_URL . '/pages/admin/users-list.php');

                } catch (PDOException $e) {
                    $pdo->rollBack();
                    error_log("User creation error: " . $e->getMessage());
                    $errors[] = 'An error occurred. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Add User - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/admin/dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/admin/users-list.php">Users</a></li>
                    <li class="breadcrumb-item active">Add User</li>
                </ol>
            </nav>
            <h2><i class="bi bi-person-plus me-2"></i>Add User</h2>
            <p class="text-muted">Manually add a new user. Email verification will be bypassed.</p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" class="needs-validation" novalidate>
                <?= csrfField() ?>

                <!-- Account Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person me-2"></i>Account Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="email" class="form-label required">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label required">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   minlength="8" required>
                            <div class="form-text">Minimum 8 characters</div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin">
                            <label class="form-check-label" for="is_admin">Grant Admin Privileges</label>
                        </div>
                    </div>
                </div>

                <!-- Registration Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Registration Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label required">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label required">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="organization" class="form-label">Organization</label>
                            <input type="text" class="form-control" id="organization" name="organization"
                                   value="<?= htmlspecialchars($_POST['organization'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-person-plus me-2"></i>Create User
                    </button>
                    <a href="<?= SITE_URL ?>/pages/admin/users-list.php" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Note</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        Users created through this form will:
                    </p>
                    <ul class="mt-2 mb-0">
                        <li>Be automatically verified</li>
                        <li>Have their registration confirmed</li>
                        <li>Be able to login immediately</li>
                        <li>No verification email will be sent</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
