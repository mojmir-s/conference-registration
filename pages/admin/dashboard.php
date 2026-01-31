<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../../config/init.php';

requireAdmin();

$pdo = getDBConnection();

// Get statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $stmt->fetch()['count'];

// Verified users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_verified = 1");
$stats['verified_users'] = $stmt->fetch()['count'];

// Total registrations
$stmt = $pdo->query("SELECT COUNT(*) as count FROM registrations");
$stats['total_registrations'] = $stmt->fetch()['count'];

// Pending registrations
$stmt = $pdo->query("SELECT COUNT(*) as count FROM registrations WHERE registration_status = 'pending'");
$stats['pending_registrations'] = $stmt->fetch()['count'];

// Confirmed registrations
$stmt = $pdo->query("SELECT COUNT(*) as count FROM registrations WHERE registration_status = 'confirmed'");
$stats['confirmed_registrations'] = $stmt->fetch()['count'];

// Total abstracts
$stmt = $pdo->query("SELECT COUNT(*) as count FROM abstracts");
$stats['total_abstracts'] = $stmt->fetch()['count'];

// Submitted abstracts (pending review)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM abstracts WHERE status = 'submitted'");
$stats['submitted_abstracts'] = $stmt->fetch()['count'];

// Accepted abstracts
$stmt = $pdo->query("SELECT COUNT(*) as count FROM abstracts WHERE status = 'accepted'");
$stats['accepted_abstracts'] = $stmt->fetch()['count'];

// Accommodation requests
$stmt = $pdo->query("SELECT COUNT(*) as count FROM registrations WHERE needs_accommodation = 1");
$stats['accommodation_requests'] = $stmt->fetch()['count'];

// Recent registrations
$stmt = $pdo->query("
    SELECT r.*, u.email
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT 5
");
$recentRegistrations = $stmt->fetchAll();

// Recent abstracts
$stmt = $pdo->query("
    SELECT a.*, u.email
    FROM abstracts a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.submitted_at DESC
    LIMIT 5
");
$recentAbstracts = $stmt->fetchAll();

$pageTitle = 'Admin Dashboard - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h2>
            <p class="text-muted">Overview of conference registrations and abstracts.</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card h-100 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Users</h6>
                            <h2 class="mb-0"><?= $stats['total_users'] ?></h2>
                            <small class="text-success"><?= $stats['verified_users'] ?> verified</small>
                        </div>
                        <i class="bi bi-people stats-icon text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card h-100 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Registrations</h6>
                            <h2 class="mb-0"><?= $stats['total_registrations'] ?></h2>
                            <small class="text-warning"><?= $stats['pending_registrations'] ?> pending</small>
                        </div>
                        <i class="bi bi-clipboard-check stats-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card h-100 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Abstracts</h6>
                            <h2 class="mb-0"><?= $stats['total_abstracts'] ?></h2>
                            <small class="text-info"><?= $stats['submitted_abstracts'] ?> pending review</small>
                        </div>
                        <i class="bi bi-file-earmark-text stats-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card h-100 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Accommodation</h6>
                            <h2 class="mb-0"><?= $stats['accommodation_requests'] ?></h2>
                            <small>room requests</small>
                        </div>
                        <i class="bi bi-building stats-icon text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="<?= SITE_URL ?>/pages/admin/users-list.php" class="btn btn-primary w-100 py-3">
                                <i class="bi bi-people me-2"></i>Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="<?= SITE_URL ?>/pages/admin/user-add.php" class="btn btn-success w-100 py-3">
                                <i class="bi bi-person-plus me-2"></i>Add User
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="<?= SITE_URL ?>/pages/admin/abstracts-list.php" class="btn btn-info w-100 py-3">
                                <i class="bi bi-file-earmark-text me-2"></i>Manage Abstracts
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= SITE_URL ?>/pages/admin/export.php" class="btn btn-outline-secondary w-100 py-3">
                                <i class="bi bi-file-earmark-excel me-2"></i>Export Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Registrations -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Registrations</h5>
                    <a href="<?= SITE_URL ?>/pages/admin/users-list.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentRegistrations)): ?>
                        <p class="text-muted text-center py-4">No registrations yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentRegistrations as $reg): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']) ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($reg['email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $reg['registration_status'] ?>">
                                                <?= ucfirst($reg['registration_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($reg['created_at'], 'M j') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Abstracts -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Recent Abstracts</h5>
                    <a href="<?= SITE_URL ?>/pages/admin/abstracts-list.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentAbstracts)): ?>
                        <p class="text-muted text-center py-4">No abstracts submitted yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentAbstracts as $abstract): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars(substr($abstract['title'], 0, 40)) ?>...
                                            <br><small class="text-muted"><?= htmlspecialchars($abstract['email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $abstract['status'] ?>">
                                                <?= ucwords(str_replace('_', ' ', $abstract['status'])) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($abstract['submitted_at'], 'M j') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
