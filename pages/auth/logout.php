<?php
/**
 * User Logout
 */

require_once __DIR__ . '/../../config/init.php';

logoutUser();

setFlash('success', 'You have been logged out successfully.');
redirect(SITE_URL . '/pages/auth/login.php');
