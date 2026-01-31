<?php
/**
 * Application Configuration
 */

// Site settings
define('SITE_NAME', getenv('SITE_NAME') ?: 'Conference Registration System');
define('SITE_URL', getenv('RAILWAY_PUBLIC_DOMAIN') ? 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN') : (getenv('SITE_URL') ?: 'http://localhost/conference'));
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@conference.com');

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour

// SMTP Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@conference.com');
define('SMTP_FROM_NAME', 'Conference Registration');

// File upload settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['application/pdf']);
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');

// Token expiry
define('VERIFICATION_TOKEN_EXPIRY', 86400); // 24 hours
define('RESET_TOKEN_EXPIRY', 3600); // 1 hour

// Abstract settings
define('ABSTRACT_MAX_WORDS', 500);

// Timezone
date_default_timezone_set('UTC');
