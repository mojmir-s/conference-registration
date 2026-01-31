<?php
/**
 * Conference Registration Form
 */

require_once __DIR__ . '/../../config/init.php';

requireLogin();

$userId = getCurrentUserId();
$registration = getRegistration($userId);
$errors = [];
$isEdit = $registration !== null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid form submission.';
    } else {
        // Collect form data
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'organization' => trim($_POST['organization'] ?? ''),
            'job_title' => trim($_POST['job_title'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'needs_accommodation' => isset($_POST['needs_accommodation']) ? 1 : 0,
            'check_in_date' => $_POST['check_in_date'] ?? null,
            'check_out_date' => $_POST['check_out_date'] ?? null,
            'room_type' => $_POST['room_type'] ?? null,
            'special_accommodation_requests' => trim($_POST['special_accommodation_requests'] ?? ''),
            'dietary_requirements' => $_POST['dietary_requirements'] ?? 'none',
            'dietary_other' => trim($_POST['dietary_other'] ?? ''),
            'food_allergies' => trim($_POST['food_allergies'] ?? ''),
        ];

        // Validation
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required.';
        }
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required.';
        }

        if ($data['needs_accommodation']) {
            if (empty($data['check_in_date'])) {
                $errors[] = 'Check-in date is required for accommodation.';
            }
            if (empty($data['check_out_date'])) {
                $errors[] = 'Check-out date is required for accommodation.';
            }
            if (empty($data['room_type'])) {
                $errors[] = 'Room type is required for accommodation.';
            }
            if ($data['check_in_date'] && $data['check_out_date'] && $data['check_in_date'] >= $data['check_out_date']) {
                $errors[] = 'Check-out date must be after check-in date.';
            }
        } else {
            $data['check_in_date'] = null;
            $data['check_out_date'] = null;
            $data['room_type'] = null;
            $data['special_accommodation_requests'] = null;
        }

        if (empty($errors)) {
            $pdo = getDBConnection();

            try {
                if ($isEdit) {
                    // Update existing registration
                    $sql = "UPDATE registrations SET
                        first_name = ?, last_name = ?, organization = ?, job_title = ?,
                        phone = ?, country = ?, needs_accommodation = ?, check_in_date = ?,
                        check_out_date = ?, room_type = ?, special_accommodation_requests = ?,
                        dietary_requirements = ?, dietary_other = ?, food_allergies = ?
                        WHERE user_id = ?";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $data['first_name'], $data['last_name'], $data['organization'],
                        $data['job_title'], $data['phone'], $data['country'],
                        $data['needs_accommodation'], $data['check_in_date'],
                        $data['check_out_date'], $data['room_type'],
                        $data['special_accommodation_requests'], $data['dietary_requirements'],
                        $data['dietary_other'], $data['food_allergies'], $userId
                    ]);

                    setFlash('success', 'Registration updated successfully!');
                } else {
                    // Create new registration
                    $sql = "INSERT INTO registrations (
                        user_id, first_name, last_name, organization, job_title,
                        phone, country, needs_accommodation, check_in_date,
                        check_out_date, room_type, special_accommodation_requests,
                        dietary_requirements, dietary_other, food_allergies
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $userId, $data['first_name'], $data['last_name'], $data['organization'],
                        $data['job_title'], $data['phone'], $data['country'],
                        $data['needs_accommodation'], $data['check_in_date'],
                        $data['check_out_date'], $data['room_type'],
                        $data['special_accommodation_requests'], $data['dietary_requirements'],
                        $data['dietary_other'], $data['food_allergies']
                    ]);

                    setFlash('success', 'Registration submitted successfully!');
                }

                redirect(SITE_URL . '/pages/user/dashboard.php');

            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                $errors[] = 'An error occurred. Please try again.';
            }
        }
    }

    // Preserve form data on error
    $registration = $data ?? $registration;
}

$pageTitle = ($isEdit ? 'Edit' : 'Complete') . ' Registration - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-clipboard-check me-2"></i><?= $isEdit ? 'Edit' : 'Complete' ?> Registration</h2>
            <p class="text-muted">Please fill in your conference registration details.</p>
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

    <form method="POST" class="needs-validation" novalidate>
        <?= csrfField() ?>

        <!-- Personal Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label required">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="<?= htmlspecialchars($registration['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label required">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                               value="<?= htmlspecialchars($registration['last_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="organization" class="form-label">Organization / Institution</label>
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
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
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
            </div>
        </div>

        <!-- Accommodation -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Accommodation</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="needs_accommodation"
                           name="needs_accommodation" <?= ($registration['needs_accommodation'] ?? false) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="needs_accommodation">
                        I need accommodation during the conference
                    </label>
                </div>

                <div id="accommodation-fields" class="accommodation-fields">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="check_in_date" class="form-label">Check-in Date</label>
                            <input type="date" class="form-control" id="check_in_date" name="check_in_date"
                                   value="<?= htmlspecialchars($registration['check_in_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="check_out_date" class="form-label">Check-out Date</label>
                            <input type="date" class="form-control" id="check_out_date" name="check_out_date"
                                   value="<?= htmlspecialchars($registration['check_out_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="room_type" class="form-label">Room Type</label>
                            <select class="form-select" id="room_type" name="room_type">
                                <option value="">Select room type...</option>
                                <?php foreach (getRoomTypes() as $value => $label): ?>
                                    <option value="<?= $value ?>"
                                        <?= ($registration['room_type'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="special_accommodation_requests" class="form-label">Special Requests</label>
                        <textarea class="form-control" id="special_accommodation_requests"
                                  name="special_accommodation_requests" rows="2"
                                  placeholder="Any special requirements (accessibility, room location, etc.)"><?= htmlspecialchars($registration['special_accommodation_requests'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Food & Dietary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-egg-fried me-2"></i>Dietary Requirements</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dietary_requirements" class="form-label">Dietary Requirements</label>
                        <select class="form-select" id="dietary_requirements" name="dietary_requirements">
                            <?php foreach (getDietaryOptions() as $value => $label): ?>
                                <option value="<?= $value ?>"
                                    <?= ($registration['dietary_requirements'] ?? 'none') === $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" id="dietary_other_container"
                         style="<?= ($registration['dietary_requirements'] ?? '') === 'other' ? '' : 'display: none;' ?>">
                        <label for="dietary_other" class="form-label">Please Specify</label>
                        <textarea class="form-control" id="dietary_other" name="dietary_other"
                                  rows="1"><?= htmlspecialchars($registration['dietary_other'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="food_allergies" class="form-label">Food Allergies</label>
                    <textarea class="form-control" id="food_allergies" name="food_allergies" rows="2"
                              placeholder="List any food allergies or intolerances"><?= htmlspecialchars($registration['food_allergies'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-lg me-2"></i><?= $isEdit ? 'Update' : 'Submit' ?> Registration
            </button>
            <a href="<?= SITE_URL ?>/pages/user/dashboard.php" class="btn btn-outline-secondary btn-lg">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
