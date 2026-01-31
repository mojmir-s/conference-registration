<?php
/**
 * Admin - Users List
 */

require_once __DIR__ . '/../../config/init.php';

requireAdmin();

$pdo = getDBConnection();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid request.');
    } else {
        $deleteId = (int)$_GET['delete'];

        // Don't allow deleting yourself
        if ($deleteId === getCurrentUserId()) {
            setFlash('error', 'You cannot delete your own account.');
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$deleteId]);

            if ($stmt->rowCount() > 0) {
                setFlash('success', 'User deleted successfully.');
            } else {
                setFlash('error', 'User not found.');
            }
        }
    }
    redirect(SITE_URL . '/pages/admin/users-list.php');
}

// Handle status update
if (isset($_GET['confirm']) && is_numeric($_GET['confirm'])) {
    $userId = (int)$_GET['confirm'];
    $stmt = $pdo->prepare("UPDATE registrations SET registration_status = 'confirmed' WHERE user_id = ?");
    $stmt->execute([$userId]);
    setFlash('success', 'Registration confirmed.');
    redirect(SITE_URL . '/pages/admin/users-list.php');
}

// Search and filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$whereClause = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (u.email LIKE ? OR r.first_name LIKE ? OR r.last_name LIKE ? OR r.organization LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if ($filter === 'pending') {
    $whereClause .= " AND r.registration_status = 'pending'";
} elseif ($filter === 'confirmed') {
    $whereClause .= " AND r.registration_status = 'confirmed'";
} elseif ($filter === 'no_registration') {
    $whereClause .= " AND r.id IS NULL";
}

// Get users with registrations
$sql = "SELECT u.*, r.first_name, r.last_name, r.organization, r.registration_status, r.needs_accommodation
        FROM users u
        LEFT JOIN registrations r ON u.id = r.user_id
        $whereClause
        ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="bi bi-people me-2"></i>Manage Users</h2>
                    <p class="text-muted mb-0">View and manage all registered users.</p>
                </div>
                <a href="<?= SITE_URL ?>/pages/admin/user-add.php" class="btn btn-success">
                    <i class="bi bi-person-plus me-2"></i>Add User
                </a>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="search" id="search-input"
                               placeholder="Search by email, name, or organization..."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="filter">
                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Users</option>
                        <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending Registration</option>
                        <option value="confirmed" <?= $filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="no_registration" <?= $filter === 'no_registration' ? 'selected' : '' ?>>No Registration</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Organization</th>
                            <th>Status</th>
                            <th>Accommodation</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="data-table-body">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <?= htmlspecialchars($user['email']) ?>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge bg-primary ms-1">Admin</span>
                                    <?php endif; ?>
                                    <?php if (!$user['is_verified']): ?>
                                        <span class="badge bg-warning ms-1">Unverified</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['first_name']): ?>
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($user['organization'] ?? '-') ?></td>
                                <td>
                                    <?php if ($user['registration_status']): ?>
                                        <span class="badge badge-<?= $user['registration_status'] ?>">
                                            <?= ucfirst($user['registration_status']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Registration</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['needs_accommodation']): ?>
                                        <i class="bi bi-check-circle text-success"></i>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatDate($user['created_at'], 'M j, Y') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= SITE_URL ?>/pages/admin/user-edit.php?id=<?= $user['id'] ?>"
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($user['registration_status'] === 'pending'): ?>
                                        <a href="<?= SITE_URL ?>/pages/admin/users-list.php?confirm=<?= $user['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                                           class="btn btn-outline-success" title="Confirm Registration">
                                            <i class="bi bi-check-lg"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($user['id'] !== getCurrentUserId()): ?>
                                        <a href="<?= SITE_URL ?>/pages/admin/users-list.php?delete=<?= $user['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                                           class="btn btn-outline-danger" title="Delete"
                                           data-confirm="Are you sure you want to delete this user? This cannot be undone.">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3 text-muted">
                Showing <?= count($users) ?> user(s)
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
