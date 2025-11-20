<?php
/**
 * Admin Logout
 * Appointment Booking System
 */

// Define the project root path
$project_root = dirname(__DIR__);

require_once $project_root . '/config/database.php';
require_once $project_root . '/includes/functions.php';

// Log activity if admin is logged in
if (isAdminLoggedIn()) {
    logActivity($_SESSION['admin_id'], 'admin_logout', "Admin logged out: " . ($_SESSION['admin_username'] ?? ''), 'admin');
}

// Destroy session and redirect
logoutAdmin();
?>

