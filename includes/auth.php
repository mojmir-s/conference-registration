<?php
/**
 * Authentication Functions
 */

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Require user to be logged in
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in to access this page.');
        redirect(SITE_URL . '/pages/auth/login.php');
    }
}

/**
 * Require user to be admin
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        setFlash('error', 'Access denied. Admin privileges required.');
        redirect(SITE_URL . '/pages/user/dashboard.php');
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, email, is_verified, is_admin, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Register new user
 */
function registerUser(string $email, string $password): array {
    $pdo = getDBConnection();

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }

    // Create user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $verificationToken = generateToken();

    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, verification_token) VALUES (?, ?, ?)");

    try {
        $stmt->execute([$email, $passwordHash, $verificationToken]);
        $userId = $pdo->lastInsertId();

        // Send verification email
        $sent = sendVerificationEmail($email, $verificationToken);

        return [
            'success' => true,
            'user_id' => $userId,
            'email_sent' => $sent,
            'message' => $sent
                ? 'Registration successful! Please check your email to verify your account.'
                : 'Registration successful but verification email could not be sent. Please contact admin.'
        ];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Verify user email
 */
function verifyEmail(string $token): bool {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ? AND is_verified = 0");
    $stmt->execute([$token]);

    return $stmt->rowCount() > 0;
}

/**
 * Login user
 */
function loginUser(string $email, string $password): array {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT id, email, password_hash, is_verified, is_admin FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    if (!$user['is_verified']) {
        return ['success' => false, 'message' => 'Please verify your email before logging in.'];
    }

    // Regenerate session ID for security
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['is_admin'] = $user['is_admin'];

    return ['success' => true, 'is_admin' => $user['is_admin']];
}

/**
 * Logout user
 */
function logoutUser(): void {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Request password reset
 */
function requestPasswordReset(string $email): array {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Return success anyway to prevent email enumeration
        return ['success' => true, 'message' => 'If an account exists with that email, a reset link has been sent.'];
    }

    $resetToken = generateToken();
    $expires = date('Y-m-d H:i:s', time() + RESET_TOKEN_EXPIRY);

    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
    $stmt->execute([$resetToken, $expires, $user['id']]);

    $sent = sendPasswordResetEmail($user['email'], $resetToken);

    return [
        'success' => true,
        'message' => 'If an account exists with that email, a reset link has been sent.'
    ];
}

/**
 * Validate reset token
 */
function validateResetToken(string $token): ?array {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->execute([$token]);

    return $stmt->fetch() ?: null;
}

/**
 * Reset password
 */
function resetPassword(string $token, string $newPassword): bool {
    $pdo = getDBConnection();

    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->execute([$passwordHash, $token]);

    return $stmt->rowCount() > 0;
}

/**
 * Update user password
 */
function updatePassword(int $userId, string $currentPassword, string $newPassword): array {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Current password is incorrect.'];
    }

    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$passwordHash, $userId]);

    return ['success' => true, 'message' => 'Password updated successfully.'];
}
