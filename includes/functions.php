<?php
/**
 * Helper Functions
 */

/**
 * Sanitize input string
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to URL
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Set flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message as Bootstrap alert
 */
function displayFlash(): string {
    $flash = getFlash();
    if ($flash) {
        $type = $flash['type'] === 'error' ? 'danger' : $flash['type'];
        return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
            . htmlspecialchars($flash['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    return '';
}

/**
 * Generate random token
 */
function generateToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

/**
 * Validate email format
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function isValidPassword(string $password): bool {
    return strlen($password) >= 8;
}

/**
 * Count words in text
 */
function wordCount(string $text): int {
    return str_word_count(strip_tags($text));
}

/**
 * Format date for display
 */
function formatDate(string $date, string $format = 'M j, Y'): string {
    return date($format, strtotime($date));
}

/**
 * Get country list
 */
function getCountryList(): array {
    return [
        'United States', 'United Kingdom', 'Canada', 'Australia', 'Germany',
        'France', 'Spain', 'Italy', 'Netherlands', 'Belgium', 'Switzerland',
        'Austria', 'Sweden', 'Norway', 'Denmark', 'Finland', 'Poland',
        'Czech Republic', 'Hungary', 'Romania', 'Bulgaria', 'Greece',
        'Portugal', 'Ireland', 'Japan', 'China', 'South Korea', 'India',
        'Singapore', 'Malaysia', 'Thailand', 'Indonesia', 'Philippines',
        'Vietnam', 'Brazil', 'Mexico', 'Argentina', 'Chile', 'Colombia',
        'South Africa', 'Egypt', 'Nigeria', 'Kenya', 'Morocco', 'Israel',
        'United Arab Emirates', 'Saudi Arabia', 'Turkey', 'Russia', 'Ukraine',
        'New Zealand', 'Other'
    ];
}

/**
 * Get dietary options
 */
function getDietaryOptions(): array {
    return [
        'none' => 'No special requirements',
        'vegetarian' => 'Vegetarian',
        'vegan' => 'Vegan',
        'halal' => 'Halal',
        'kosher' => 'Kosher',
        'gluten-free' => 'Gluten-free',
        'other' => 'Other'
    ];
}

/**
 * Get room type options
 */
function getRoomTypes(): array {
    return [
        'single' => 'Single Room',
        'double' => 'Double Room',
        'shared' => 'Shared Room'
    ];
}

/**
 * Validate file upload
 */
function validateFileUpload(array $file): array {
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed.';
        return $errors;
    }

    if ($file['size'] > UPLOAD_MAX_SIZE) {
        $errors[] = 'File size exceeds ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . 'MB limit.';
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, UPLOAD_ALLOWED_TYPES)) {
        $errors[] = 'Only PDF files are allowed.';
    }

    return $errors;
}

/**
 * Handle file upload
 */
function handleFileUpload(array $file, int $userId): ?string {
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'abstract_' . $userId . '_' . time() . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }

    return null;
}

/**
 * Check if user has registration
 */
function hasRegistration(int $userId): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch() !== false;
}

/**
 * Get user registration
 */
function getRegistration(int $userId): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Get user abstracts
 */
function getUserAbstracts(int $userId): array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM abstracts WHERE user_id = ? ORDER BY submitted_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
