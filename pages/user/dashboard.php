<?php
/**
 * User Dashboard
 */

require_once __DIR__ . '/../../config/init.php';

requireLogin();

$userId = getCurrentUserId();
$user = getCurrentUser();
$registration = getRegistration($userId);
$abstracts = getUserAbstracts($userId);

$pageTitle = 'Dashboard - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
            <p class="text-muted">Welcome back! Here's an overview of your conference registration.</p>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Registration Status</h6>
                            <?php if ($registration): ?>
                                <span class="badge badge-<?= $registration['registration_status'] ?> fs-6">
                                    <?= ucfirst($registration['registration_status']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary fs-6">Not Submitted</span>
                            <?php endif; ?>
                        </div>
                        <i class="bi bi-clipboard-check stats-icon text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Abstracts Submitted</h6>
                            <h3 class="mb-0"><?= count($abstracts) ?></h3>
                        </div>
                        <i class="bi bi-file-earmark-text stats-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Account Status</h6>
                            <span class="badge bg-success fs-6">Verified</span>
                        </div>
                        <i class="bi bi-person-check stats-icon text-info"></i>
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
                        <div class="col-md-4 mb-3 mb-md-0">
                            <a href="<?= SITE_URL ?>/pages/user/registration-form.php"
                               class="btn btn-primary w-100 py-3">
                                <i class="bi bi-pencil-square me-2"></i>
                                <?= $registration ? 'Edit Registration' : 'Complete Registration' ?>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <a href="<?= SITE_URL ?>/pages/user/abstract-submit.php"
                               class="btn btn-success w-100 py-3">
                                <i class="bi bi-file-earmark-plus me-2"></i>
                                Submit Abstract
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= SITE_URL ?>/pages/user/profile.php"
                               class="btn btn-outline-secondary w-100 py-3">
                                <i class="bi bi-person-gear me-2"></i>
                                Account Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Details -->
    <?php if ($registration): ?>
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-vcard me-2"></i>Registration Details</h5>
                    <a href="<?= SITE_URL ?>/pages/user/registration-form.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?></p>
                            <p><strong>Organization:</strong> <?= htmlspecialchars($registration['organization'] ?? 'N/A') ?></p>
                            <p><strong>Job Title:</strong> <?= htmlspecialchars($registration['job_title'] ?? 'N/A') ?></p>
                            <p><strong>Country:</strong> <?= htmlspecialchars($registration['country'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phone:</strong> <?= htmlspecialchars($registration['phone'] ?? 'N/A') ?></p>
                            <p><strong>Dietary Requirements:</strong> <?= ucfirst($registration['dietary_requirements'] ?? 'None') ?></p>
                            <p><strong>Accommodation:</strong> <?= $registration['needs_accommodation'] ? 'Yes' : 'No' ?></p>
                            <?php if ($registration['needs_accommodation']): ?>
                                <p><strong>Check-in:</strong> <?= formatDate($registration['check_in_date']) ?></p>
                                <p><strong>Check-out:</strong> <?= formatDate($registration['check_out_date']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Submitted Abstracts -->
    <?php if (!empty($abstracts)): ?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Your Abstracts</h5>
                    <a href="<?= SITE_URL ?>/pages/user/abstract-submit.php" class="btn btn-sm btn-success">
                        <i class="bi bi-plus me-1"></i>Submit New
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Presentation Type</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($abstracts as $abstract): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($abstract['title']) ?>
                                        <?php if ($abstract['file_path']): ?>
                                            <i class="bi bi-paperclip text-muted" title="Has attachment"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= ucfirst($abstract['presentation_type']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $abstract['status'] ?>">
                                            <?= ucwords(str_replace('_', ' ', $abstract['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($abstract['submitted_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
