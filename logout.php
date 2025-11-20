<?php
/**
 * User Logout
 * Appointment Booking System
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Log activity if user is logged in
if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'user_logout', "User logged out: " . ($_SESSION['user_email'] ?? ''));
}

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    
    // Clear token from database
    if (isLoggedIn()) {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
        }
    }
}

// Destroy session and redirect
logoutUser();
?>

