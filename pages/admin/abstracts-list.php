<?php
/**
 * Admin - Abstracts List
 */

require_once __DIR__ . '/../../config/init.php';

requireAdmin();

$pdo = getDBConnection();

// Handle status update
if (isset($_POST['update_status']) && is_numeric($_POST['abstract_id'])) {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid request.');
    } else {
        $abstractId = (int)$_POST['abstract_id'];
        $newStatus = $_POST['new_status'] ?? '';

        $validStatuses = ['submitted', 'under_review', 'accepted', 'rejected'];
        if (in_array($newStatus, $validStatuses)) {
            $stmt = $pdo->prepare("UPDATE abstracts SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $abstractId]);
            setFlash('success', 'Abstract status updated.');
        }
    }
    redirect(SITE_URL . '/pages/admin/abstracts-list.php');
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid request.');
    } else {
        $deleteId = (int)$_GET['delete'];

        // Get file path first
        $stmt = $pdo->prepare("SELECT file_path FROM abstracts WHERE id = ?");
        $stmt->execute([$deleteId]);
        $abstract = $stmt->fetch();

        if ($abstract) {
            // Delete file if exists
            if ($abstract['file_path'] && file_exists(UPLOAD_DIR . $abstract['file_path'])) {
                unlink(UPLOAD_DIR . $abstract['file_path']);
            }

            // Delete abstract
            $stmt = $pdo->prepare("DELETE FROM abstracts WHERE id = ?");
            $stmt->execute([$deleteId]);
            setFlash('success', 'Abstract deleted.');
        }
    }
    redirect(SITE_URL . '/pages/admin/abstracts-list.php');
}

// Search and filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$whereClause = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (a.title LIKE ? OR a.authors LIKE ? OR u.email LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($filter !== 'all') {
    $whereClause .= " AND a.status = ?";
    $params[] = $filter;
}

// Get abstracts
$sql = "SELECT a.*, u.email
        FROM abstracts a
        JOIN users u ON a.user_id = u.id
        $whereClause
        ORDER BY a.submitted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$abstracts = $stmt->fetchAll();

$pageTitle = 'Manage Abstracts - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-file-earmark-text me-2"></i>Manage Abstracts</h2>
            <p class="text-muted">Review and manage submitted abstracts.</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="search"
                               placeholder="Search by title, authors, or email..."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="filter">
                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Abstracts</option>
                        <option value="submitted" <?= $filter === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                        <option value="under_review" <?= $filter === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                        <option value="accepted" <?= $filter === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                        <option value="rejected" <?= $filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Abstracts List -->
    <?php if (empty($abstracts)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>No abstracts found.
        </div>
    <?php else: ?>
        <?php foreach ($abstracts as $abstract): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-<?= $abstract['status'] ?> me-2">
                        <?= ucwords(str_replace('_', ' ', $abstract['status'])) ?>
                    </span>
                    <span class="badge bg-secondary">
                        <?= ucfirst($abstract['presentation_type']) ?>
                    </span>
                </div>
                <small class="text-muted">
                    Submitted: <?= formatDate($abstract['submitted_at'], 'M j, Y g:i A') ?>
                </small>
            </div>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($abstract['title']) ?></h5>
                <p class="text-muted mb-2">
                    <strong>Authors:</strong> <?= htmlspecialchars($abstract['authors']) ?>
                </p>
                <p class="text-muted mb-2">
                    <strong>Submitted by:</strong> <?= htmlspecialchars($abstract['email']) ?>
                </p>

                <?php if ($abstract['keywords']): ?>
                <p class="mb-2">
                    <strong>Keywords:</strong>
                    <?php foreach (explode(',', $abstract['keywords']) as $keyword): ?>
                        <span class="badge bg-light text-dark"><?= htmlspecialchars(trim($keyword)) ?></span>
                    <?php endforeach; ?>
                </p>
                <?php endif; ?>

                <div class="mb-3">
                    <strong>Abstract:</strong>
                    <p class="mt-1 mb-0"><?= nl2br(htmlspecialchars($abstract['abstract_text'])) ?></p>
                </div>

                <?php if ($abstract['file_path']): ?>
                <p class="mb-3">
                    <i class="bi bi-paperclip me-1"></i>
                    <a href="<?= SITE_URL ?>/public/uploads/<?= htmlspecialchars($abstract['file_path']) ?>"
                       target="_blank">View Attached File</a>
                </p>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center">
                    <form method="POST" class="d-flex gap-2 align-items-center">
                        <?= csrfField() ?>
                        <input type="hidden" name="abstract_id" value="<?= $abstract['id'] ?>">
                        <input type="hidden" name="update_status" value="1">
                        <label class="form-label mb-0 me-2">Update Status:</label>
                        <select name="new_status" class="form-select form-select-sm" style="width: auto;">
                            <option value="submitted" <?= $abstract['status'] === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                            <option value="under_review" <?= $abstract['status'] === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                            <option value="accepted" <?= $abstract['status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                            <option value="rejected" <?= $abstract['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                    </form>

                    <a href="<?= SITE_URL ?>/pages/admin/abstracts-list.php?delete=<?= $abstract['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       data-confirm="Are you sure you want to delete this abstract?">
                        <i class="bi bi-trash me-1"></i>Delete
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="text-muted">
            Showing <?= count($abstracts) ?> abstract(s)
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
