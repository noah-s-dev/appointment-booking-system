<?php
/**
 * AJAX Endpoint - Get Available Time Slots
 * Appointment Booking System
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

$date = sanitizeInput($_POST['date'] ?? '');

// Validate date
if (empty($date) || !isValidFutureDate($date)) {
    echo json_encode(['success' => false, 'error' => 'Invalid date']);
    exit;
}

try {
    $db = getDB();
    
    // Get available time slots for the selected date
    $stmt = $db->prepare("
        SELECT 
            ts.id,
            ts.start_time,
            ts.end_time,
            ts.max_appointments,
            COALESCE(booked.count, 0) as booked_count,
            (ts.max_appointments - COALESCE(booked.count, 0)) as available_count
        FROM time_slots ts
        LEFT JOIN (
            SELECT time_slot_id, COUNT(*) as count 
            FROM appointments 
            WHERE status IN ('pending', 'confirmed') 
            GROUP BY time_slot_id
        ) booked ON ts.id = booked.time_slot_id
        WHERE ts.date = ? 
        AND ts.is_available = TRUE 
        AND (ts.max_appointments - COALESCE(booked.count, 0)) > 0
        ORDER BY ts.start_time ASC
    ");
    
    $stmt->execute([$date]);
    $time_slots = $stmt->fetchAll();
    
    // Format time slots for display
    $formatted_slots = [];
    foreach ($time_slots as $slot) {
        $formatted_slots[] = [
            'id' => $slot['id'],
            'start_time' => formatTime($slot['start_time']),
            'end_time' => formatTime($slot['end_time']),
            'available_count' => $slot['available_count']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'slots' => $formatted_slots
    ]);
    
} catch (Exception $e) {
    error_log("Time slots AJAX error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>

