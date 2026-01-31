<?php
/**
 * Abstract Submission Form
 */

require_once __DIR__ . '/../../config/init.php';

requireLogin();

$userId = getCurrentUserId();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid form submission.';
    } else {
        // Collect form data
        $title = trim($_POST['title'] ?? '');
        $authors = trim($_POST['authors'] ?? '');
        $abstractText = trim($_POST['abstract_text'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
        $presentationType = $_POST['presentation_type'] ?? 'either';

        // Validation
        if (empty($title)) {
            $errors[] = 'Title is required.';
        }
        if (empty($authors)) {
            $errors[] = 'Authors are required.';
        }
        if (empty($abstractText)) {
            $errors[] = 'Abstract text is required.';
        } elseif (wordCount($abstractText) > ABSTRACT_MAX_WORDS) {
            $errors[] = 'Abstract exceeds ' . ABSTRACT_MAX_WORDS . ' words limit.';
        }

        // Handle file upload
        $filePath = null;
        if (isset($_FILES['abstract_file']) && $_FILES['abstract_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $fileErrors = validateFileUpload($_FILES['abstract_file']);
            if (!empty($fileErrors)) {
                $errors = array_merge($errors, $fileErrors);
            } else {
                $filePath = handleFileUpload($_FILES['abstract_file'], $userId);
                if (!$filePath) {
                    $errors[] = 'Failed to upload file.';
                }
            }
        }

        if (empty($errors)) {
            $pdo = getDBConnection();

            try {
                $sql = "INSERT INTO abstracts (user_id, title, authors, abstract_text, keywords, presentation_type, file_path)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId, $title, $authors, $abstractText, $keywords, $presentationType, $filePath]);

                setFlash('success', 'Abstract submitted successfully!');
                redirect(SITE_URL . '/pages/user/dashboard.php');

            } catch (PDOException $e) {
                error_log("Abstract submission error: " . $e->getMessage());
                $errors[] = 'An error occurred. Please try again.';
            }
        }
    }
}

$pageTitle = 'Submit Abstract - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-file-earmark-text me-2"></i>Submit Abstract</h2>
            <p class="text-muted">Submit your research abstract for the conference.</p>
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

    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <?= csrfField() ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Abstract Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label required">Title</label>
                    <input type="text" class="form-control" id="title" name="title"
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required
                           placeholder="Enter the title of your paper/presentation">
                </div>

                <div class="mb-3">
                    <label for="authors" class="form-label required">Authors</label>
                    <textarea class="form-control" id="authors" name="authors" rows="2" required
                              placeholder="List all authors (e.g., John Smith, Jane Doe, Bob Wilson)"><?= htmlspecialchars($_POST['authors'] ?? '') ?></textarea>
                    <div class="form-text">Include all co-authors with their affiliations</div>
                </div>

                <div class="mb-3">
                    <label for="abstract_text" class="form-label required">Abstract</label>
                    <textarea class="form-control" id="abstract_text" name="abstract_text" rows="8" required
                              placeholder="Enter your abstract text (maximum <?= ABSTRACT_MAX_WORDS ?> words)"><?= htmlspecialchars($_POST['abstract_text'] ?? '') ?></textarea>
                    <div class="d-flex justify-content-between">
                        <div class="form-text">Maximum <?= ABSTRACT_MAX_WORDS ?> words</div>
                        <div id="word-count" class="word-count">0 / <?= ABSTRACT_MAX_WORDS ?> words</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keywords" class="form-label">Keywords</label>
                    <input type="text" class="form-control" id="keywords" name="keywords"
                           value="<?= htmlspecialchars($_POST['keywords'] ?? '') ?>"
                           placeholder="Enter keywords separated by commas">
                    <div class="form-text">3-5 keywords recommended</div>
                </div>

                <div class="mb-3">
                    <label for="presentation_type" class="form-label">Preferred Presentation Type</label>
                    <select class="form-select" id="presentation_type" name="presentation_type">
                        <option value="oral" <?= ($_POST['presentation_type'] ?? '') === 'oral' ? 'selected' : '' ?>>
                            Oral Presentation
                        </option>
                        <option value="poster" <?= ($_POST['presentation_type'] ?? '') === 'poster' ? 'selected' : '' ?>>
                            Poster Presentation
                        </option>
                        <option value="either" <?= ($_POST['presentation_type'] ?? 'either') === 'either' ? 'selected' : '' ?>>
                            Either (No preference)
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="abstract_file" class="form-label">Upload Full Paper (Optional)</label>
                    <input type="file" class="form-control" id="abstract_file" name="abstract_file"
                           accept=".pdf,application/pdf">
                    <div class="form-text">PDF only, maximum 5MB</div>
                </div>
            </div>
        </div>

        <!-- Guidelines -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Submission Guidelines</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Abstract should not exceed <?= ABSTRACT_MAX_WORDS ?> words</li>
                    <li>Include all co-authors with their affiliations</li>
                    <li>Provide 3-5 relevant keywords</li>
                    <li>Full paper upload is optional at this stage</li>
                    <li>You will be notified of acceptance via email</li>
                </ul>
            </div>
        </div>

        <!-- Submit -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-send me-2"></i>Submit Abstract
            </button>
            <a href="<?= SITE_URL ?>/pages/user/dashboard.php" class="btn btn-outline-secondary btn-lg">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
