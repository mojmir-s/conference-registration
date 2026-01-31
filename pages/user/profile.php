<?php
/**
 * User Profile / Account Settings
 */

require_once __DIR__ . '/../../config/init.php';

requireLogin();

$userId = getCurrentUserId();
$user = getCurrentUser();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid form submission.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($currentPassword)) {
                $errors[] = 'Current password is required.';
            }
            if (empty($newPassword)) {
                $errors[] = 'New password is required.';
            } elseif (!isValidPassword($newPassword)) {
                $errors[] = 'New password must be at least 8 characters.';
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match.';
            }

            if (empty($errors)) {
                $result = updatePassword($userId, $currentPassword, $newPassword);
                if ($result['success']) {
                    setFlash('success', $result['message']);
                    redirect(SITE_URL . '/pages/user/profile.php');
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
    }
}

$pageTitle = 'Profile - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-person-gear me-2"></i>Account Settings</h2>
            <p class="text-muted">Manage your account and security settings.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Account Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Email Address</label>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Account Status</label>
                            <p class="mb-0">
                                <?php if ($user['is_verified']): ?>
                                    <span class="badge bg-success">Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Pending Verification</span>
                                <?php endif; ?>
                                <?php if ($user['is_admin']): ?>
                                    <span class="badge bg-primary ms-1">Admin</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Member Since</label>
                            <p class="mb-0"><?= formatDate($user['created_at'], 'F j, Y') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-3">
                            <label for="current_password" class="form-label required">Current Password</label>
                            <input type="password" class="form-control" id="current_password"
                                   name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label required">New Password</label>
                            <input type="password" class="form-control" id="password"
                                   name="new_password" minlength="8" required>
                            <div class="form-text">Minimum 8 characters</div>
                            <div id="password-strength" class="mt-1"></div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label required">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password"
                                   name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Links -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Quick Links</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= SITE_URL ?>/pages/user/dashboard.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                    <a href="<?= SITE_URL ?>/pages/user/registration-form.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-clipboard-check me-2"></i>Registration Form
                    </a>
                    <a href="<?= SITE_URL ?>/pages/user/abstract-submit.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark-text me-2"></i>Submit Abstract
                    </a>
                    <a href="<?= SITE_URL ?>/pages/auth/logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
