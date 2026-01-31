<?php
/**
 * Admin - Edit User
 */

require_once __DIR__ . '/../../config/init.php';

requireAdmin();

$userId = $_GET['id'] ?? null;

if (!$userId || !is_numeric($userId)) {
    setFlash('error', 'Invalid user ID.');
    redirect(SITE_URL . '/pages/admin/users-list.php');
}

$pdo = getDBConnection();

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'User not found.');
    redirect(SITE_URL . '/pages/admin/users-list.php');
}

// Get registration data
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ?");
$stmt->execute([$userId]);
$registration = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid form submission.';
    } else {
        // Update user data
        $isVerified = isset($_POST['is_verified']) ? 1 : 0;
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;

        // Don't allow removing own admin status
        if ($userId == getCurrentUserId() && !$isAdmin) {
            $errors[] = 'You cannot remove your own admin privileges.';
        }

        // Update password if provided
        $newPassword = $_POST['new_password'] ?? '';

        if (empty($errors)) {
            try {
                if (!empty($newPassword)) {
                    if (!isValidPassword($newPassword)) {
                        $errors[] = 'Password must be at least 8 characters.';
                    } else {
                        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET is_verified = ?, is_admin = ?, password_hash = ? WHERE id = ?");
                        $stmt->execute([$isVerified, $isAdmin, $passwordHash, $userId]);
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET is_verified = ?, is_admin = ? WHERE id = ?");
                    $stmt->execute([$isVerified, $isAdmin, $userId]);
                }

                // Update registration if exists
                if ($registration) {
                    $registrationStatus = $_POST['registration_status'] ?? $registration['registration_status'];

                    $stmt = $pdo->prepare("UPDATE registrations SET
                        first_name = ?, last_name = ?, organization = ?, job_title = ?,
                        phone = ?, country = ?, registration_status = ?
                        WHERE user_id = ?");

                    $stmt->execute([
                        $_POST['first_name'] ?? $registration['first_name'],
                        $_POST['last_name'] ?? $registration['last_name'],
                        $_POST['organization'] ?? $registration['organization'],
                        $_POST['job_title'] ?? $registration['job_title'],
                        $_POST['phone'] ?? $registration['phone'],
                        $_POST['country'] ?? $registration['country'],
                        $registrationStatus,
                        $userId
                    ]);
                }

                if (empty($errors)) {
                    setFlash('success', 'User updated successfully.');
                    redirect(SITE_URL . '/pages/admin/users-list.php');
                }

            } catch (PDOException $e) {
                error_log("User update error: " . $e->getMessage());
                $errors[] = 'An error occurred. Please try again.';
            }
        }
    }
}

$pageTitle = 'Edit User - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/admin/dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/admin/users-list.php">Users</a></li>
                    <li class="breadcrumb-item active">Edit User</li>
                </ol>
            </nav>
            <h2><i class="bi bi-person-gear me-2"></i>Edit User</h2>
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

    <form method="POST">
        <?= csrfField() ?>

        <div class="row">
            <div class="col-lg-8">
                <!-- Account Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person me-2"></i>Account Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            <div class="form-text">Email cannot be changed.</div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <div class="form-text">Leave blank to keep current password.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_verified"
                                           name="is_verified" <?= $user['is_verified'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_verified">Email Verified</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_admin"
                                           name="is_admin" <?= $user['is_admin'] ? 'checked' : '' ?>
                                           <?= $userId == getCurrentUserId() ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="is_admin">Admin Privileges</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registration Details -->
                <?php if ($registration): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Registration Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       value="<?= htmlspecialchars($registration['first_name']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       value="<?= htmlspecialchars($registration['last_name']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="organization" class="form-label">Organization</label>
                                <input type="text" class="form-control" id="organization" name="organization"
                                       value="<?= htmlspecialchars($registration['organization'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="job_title" class="form-label">Job Title</label>
                                <input type="text" class="form-control" id="job_title" name="job_title"
                                       value="<?= htmlspecialchars($registration['job_title'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                       value="<?= htmlspecialchars($registration['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <select class="form-select" id="country" name="country">
                                    <option value="">Select country...</option>
                                    <?php foreach (getCountryList() as $country): ?>
                                        <option value="<?= $country ?>"
                                            <?= ($registration['country'] ?? '') === $country ? 'selected' : '' ?>>
                                            <?= $country ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="registration_status" class="form-label">Registration Status</label>
                            <select class="form-select" id="registration_status" name="registration_status">
                                <option value="pending" <?= $registration['registration_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $registration['registration_status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="cancelled" <?= $registration['registration_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>This user has not submitted a registration form yet.
                </div>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Save Changes
                    </button>
                    <a href="<?= SITE_URL ?>/pages/admin/users-list.php" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- User Info Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>User Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>User ID:</strong> <?= $user['id'] ?></p>
                        <p><strong>Created:</strong> <?= formatDate($user['created_at'], 'M j, Y g:i A') ?></p>
                        <p>
                            <strong>Status:</strong>
                            <?php if ($user['is_verified']): ?>
                                <span class="badge bg-success">Verified</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Unverified</span>
                            <?php endif; ?>
                            <?php if ($user['is_admin']): ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php endif; ?>
                        </p>
                        <?php if ($registration): ?>
                        <p>
                            <strong>Accommodation:</strong>
                            <?= $registration['needs_accommodation'] ? 'Yes' : 'No' ?>
                        </p>
                        <p>
                            <strong>Dietary:</strong>
                            <?= ucfirst($registration['dietary_requirements'] ?? 'None') ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
