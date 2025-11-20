<?php
/**
 * Cancel Appointment
 * Appointment Booking System
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Require user login
requireLogin();

$user_id = $_SESSION['user_id'];
$appointment_id = (int)($_GET['id'] ?? 0);

if (!$appointment_id) {
    $_SESSION['error'] = "Invalid appointment ID.";
    header('Location: my-appointments.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cancel_reason = sanitizeInput($_POST['cancel_reason'] ?? '');
    
    try {
        $db = getDB();
        
        // First, verify the appointment exists and belongs to the user
        $verify_query = "
            SELECT a.*, ts.start_time, ts.end_time, u.first_name, u.last_name, u.email
            FROM appointments a 
            JOIN time_slots ts ON a.time_slot_id = ts.id 
            JOIN users u ON a.user_id = u.id
            WHERE a.id = ? AND a.user_id = ? AND a.status IN ('pending', 'confirmed')
        ";
        $stmt = $db->prepare($verify_query);
        $stmt->execute([$appointment_id, $user_id]);
        $appointment = $stmt->fetch();
        
        if (!$appointment) {
            $_SESSION['error'] = "Appointment not found or cannot be cancelled.";
            header('Location: my-appointments.php');
            exit;
        }
        
        // Check if appointment is in the future
        if (strtotime($appointment['appointment_date']) <= time()) {
            $_SESSION['error'] = "Cannot cancel appointments that have already passed.";
            header('Location: my-appointments.php');
            exit;
        }
        
        // Update appointment status to cancelled
        $update_query = "
            UPDATE appointments 
            SET status = 'cancelled', 
                notes = CONCAT(COALESCE(notes, ''), '\n\nCancelled by patient on ', NOW(), '. Reason: ', ?),
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ";
        $stmt = $db->prepare($update_query);
        $stmt->execute([$cancel_reason, $appointment_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            // Log the cancellation activity
            $activity_query = "
                INSERT INTO activity_log (user_id, user_type, action, details, ip_address, user_agent) 
                VALUES (?, 'user', 'cancel_appointment', ?, ?, ?)
            ";
            $activity_details = "Appointment cancelled for " . $appointment['first_name'] . " " . $appointment['last_name'] . 
                               " on " . $appointment['appointment_date'] . " at " . $appointment['start_time'] . 
                               ". Reason: " . $cancel_reason;
            $stmt = $db->prepare($activity_query);
            $stmt->execute([
                $user_id, 
                $activity_details, 
                $_SERVER['REMOTE_ADDR'], 
                $_SERVER['HTTP_USER_AGENT']
            ]);
            
            $_SESSION['success'] = "Appointment cancelled successfully.";
        } else {
            $_SESSION['error'] = "Failed to cancel appointment. Please try again.";
        }
        
    } catch (Exception $e) {
        error_log("Cancel appointment error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while cancelling the appointment.";
    }
    
    header('Location: my-appointments.php');
    exit;
}

// Get appointment details for confirmation page
try {
    $db = getDB();
    
    $query = "
        SELECT a.*, ts.start_time, ts.end_time,
               u.first_name, u.last_name, u.email
        FROM appointments a 
        JOIN time_slots ts ON a.time_slot_id = ts.id 
        JOIN users u ON a.user_id = u.id
        WHERE a.id = ? AND a.user_id = ? AND a.status IN ('pending', 'confirmed')
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([$appointment_id, $user_id]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        $_SESSION['error'] = "Appointment not found or cannot be cancelled.";
        header('Location: my-appointments.php');
        exit;
    }
    
    // Check if appointment is in the future
    if (strtotime($appointment['appointment_date']) <= time()) {
        $_SESSION['error'] = "Cannot cancel appointments that have already passed.";
        header('Location: my-appointments.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Cancel appointment error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while loading the appointment.";
    header('Location: my-appointments.php');
    exit;
}
?>
<?php 
$page_title = 'Cancel Appointment';
include 'includes/header.php'; 
?>
    
    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="my-appointments.php">My Appointments</a></li>
                <li class="breadcrumb-item active">Cancel Appointment</li>
            </ol>
        </nav>
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-x-circle text-danger"></i>
                        Cancel Appointment
                    </h1>
                    <a href="my-appointments.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Appointments
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Confirmation Card -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            Confirm Cancellation
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle"></i>
                                Are you sure you want to cancel this appointment?
                            </h6>
                            <p class="mb-0">This action cannot be undone. Please review the appointment details below.</p>
                        </div>
                        
                        <!-- Appointment Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Patient</h6>
                                <p class="mb-3">
                                    <strong><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></strong><br>
                                    <span class="text-muted"><?php echo htmlspecialchars($appointment['email']); ?></span>
                                </p>
                                
                                <h6 class="text-muted">Date & Time</h6>
                                <p class="mb-3">
                                    <strong><?php echo formatDate($appointment['appointment_date']); ?></strong><br>
                                    <span class="text-muted">
                                        <?php echo formatDate($appointment['appointment_date'], 'l'); ?> at 
                                        <?php echo formatTime($appointment['start_time']); ?> - 
                                        <?php echo formatTime($appointment['end_time']); ?>
                                    </span>
                                </p>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-muted">Status</h6>
                                <p class="mb-3">
                                    <?php echo getStatusBadge($appointment['status']); ?>
                                </p>
                                
                                <h6 class="text-muted">Reason for Visit</h6>
                                <p class="mb-3">
                                    <?php echo htmlspecialchars($appointment['reason']); ?>
                                </p>
                                
                                <h6 class="text-muted">Booked On</h6>
                                <p class="mb-3">
                                    <?php echo formatDate($appointment['created_at'], 'M j, Y'); ?><br>
                                    <span class="text-muted"><?php echo formatTime($appointment['created_at']); ?></span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Cancellation Form -->
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="cancel_reason" class="form-label">
                                    <strong>Reason for Cancellation (Optional)</strong>
                                </label>
                                <textarea class="form-control" id="cancel_reason" name="cancel_reason" 
                                          rows="3" placeholder="Please provide a reason for cancelling this appointment..."></textarea>
                                <div class="form-text">
                                    Providing a reason helps us improve our services and may help with rescheduling.
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="my-appointments.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Keep Appointment
                                </a>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-check-circle"></i> Confirm Cancellation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-info-circle text-info"></i>
                            What happens when you cancel?
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li><i class="bi bi-check text-success"></i> The appointment slot becomes available for other patients</li>
                            <li><i class="bi bi-check text-success"></i> You can book a new appointment at any time</li>
                            <li><i class="bi bi-check text-success"></i> No cancellation fees apply</li>
                            <li><i class="bi bi-check text-success"></i> You'll receive a confirmation email</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?> 