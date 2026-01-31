-- Conference Registration System Database Schema
-- Run this script to create all necessary tables

CREATE DATABASE IF NOT EXISTS conference_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE conference_db;

-- Users table (authentication)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrations table (conference details)
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    organization VARCHAR(255),
    job_title VARCHAR(100),
    phone VARCHAR(50),
    country VARCHAR(100),

    -- Accommodation
    needs_accommodation TINYINT(1) DEFAULT 0,
    check_in_date DATE,
    check_out_date DATE,
    room_type ENUM('single', 'double', 'shared'),
    special_accommodation_requests TEXT,

    -- Food
    dietary_requirements ENUM('none', 'vegetarian', 'vegan', 'halal', 'kosher', 'gluten-free', 'other') DEFAULT 'none',
    dietary_other TEXT,
    food_allergies TEXT,

    -- Status
    registration_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Abstracts table (paper submissions)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create default admin user (password: admin123 - CHANGE THIS!)
INSERT INTO users (email, password_hash, is_verified, is_admin)
VALUES ('admin@conference.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1)
ON DUPLICATE KEY UPDATE email = email;
