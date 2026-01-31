<?php
/**
 * Database Installation Script
 * Run this once to create all required tables
 */

require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();

    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255),
            is_verified TINYINT(1) DEFAULT 0,
            is_admin TINYINT(1) DEFAULT 0,
            verification_token VARCHAR(64),
            reset_token VARCHAR(64),
            reset_token_expires DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Create registrations table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            organization VARCHAR(255),
            job_title VARCHAR(100),
            phone VARCHAR(50),
            country VARCHAR(100),
            needs_accommodation TINYINT(1) DEFAULT 0,
            check_in_date DATE,
            check_out_date DATE,
            room_type ENUM('single', 'double', 'shared'),
            special_accommodation_requests TEXT,
            dietary_requirements ENUM('none', 'vegetarian', 'vegan', 'halal', 'kosher', 'gluten-free', 'other') DEFAULT 'none',
            dietary_other TEXT,
            food_allergies TEXT,
            registration_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Create abstracts table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS abstracts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(500) NOT NULL,
            authors TEXT NOT NULL,
            abstract_text TEXT NOT NULL,
            keywords VARCHAR(500),
            presentation_type ENUM('oral', 'poster', 'either') DEFAULT 'either',
            file_path VARCHAR(500),
            status ENUM('submitted', 'under_review', 'accepted', 'rejected') DEFAULT 'submitted',
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Create default admin user if not exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@conference.com']);

    if (!$stmt->fetch()) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, is_verified, is_admin) VALUES (?, ?, 1, 1)");
        $stmt->execute(['admin@conference.com', $adminPassword]);
        echo "Default admin user created (admin@conference.com / admin123)<br>";
    }

    echo "<h2>Installation Complete!</h2>";
    echo "<p>All database tables have been created successfully.</p>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
    echo "<p><strong>Default Admin Login:</strong><br>Email: admin@conference.com<br>Password: admin123</p>";
    echo "<p style='color:red;'><strong>Important:</strong> Change the admin password after first login!</p>";

} catch (PDOException $e) {
    echo "<h2>Installation Failed</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
