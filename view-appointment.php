<?php
/**
 * View Appointment Details
 * Appointment Booking System
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Require user login
requireLogin();

$user_id = $_SESSION['user_id'];
$appointment_id = (int)($_GET['id'] ?? 0);

if (!$appointment_id) {
    header('Location: my-appointments.php');
    exit;
}

try {
    $db = getDB();
    
    // Get appointment details with security check (user can only view their own appointments)
    $query = "
        SELECT a.*, ts.start_time, ts.end_time, ts.date as slot_date,
               admin.full_name as confirmed_by_name,
               u.first_name, u.last_name, u.email, u.phone
        FROM appointments a 
        JOIN time_slots ts ON a.time_slot_id = ts.id 
        JOIN users u ON a.user_id = u.id
        LEFT JOIN admin_users admin ON a.confirmed_by = admin.id
        WHERE a.id = ? AND a.user_id = ?
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([$appointment_id, $user_id]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        $_SESSION['error'] = "Appointment not found or you don't have permission to view it.";
        header('Location: my-appointments.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("View appointment error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while loading the appointment.";
    header('Location: my-appointments.php');
    exit;
}
?>
<?php 
$page_title = 'View Appointment';
include 'includes/header.php'; 
?>
    
    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="my-appointments.php">My Appointments</a></li>
                <li class="breadcrumb-item active">View Appointment</li>
            </ol>
        </nav>
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-calendar-check text-primary"></i>
                        Appointment Details
                    </h1>
                    <div>
                        <a href="my-appointments.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Appointments
                        </a>
                        <?php if ($appointment['status'] === 'pending' || $appointment['status'] === 'confirmed'): ?>
                            <?php if (strtotime($appointment['appointment_date']) > time()): ?>
                                <a href="cancel-appointment.php?id=<?php echo $appointment['id']; ?>" 
                                   class="btn btn-outline-danger ms-2"
                                   onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                    <i class="bi bi-x-circle"></i> Cancel Appointment
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Appointment Details -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-event"></i>
                            Appointment Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Date & Time</h6>
                                <p class="mb-3">
                                    <strong><?php echo formatDate($appointment['appointment_date']); ?></strong><br>
                                    <span class="text-muted">
                                        <?php echo formatDate($appointment['appointment_date'], 'l'); ?> at 
                                        <?php echo formatTime($appointment['start_time']); ?> - 
                                        <?php echo formatTime($appointment['end_time']); ?>
                                    </span>
                                </p>
                                
                                <h6 class="text-muted">Status</h6>
                                <p class="mb-3">
                                    <?php echo getStatusBadge($appointment['status']); ?>
                                </p>
                                
                                <h6 class="text-muted">Reason for Visit</h6>
                                <p class="mb-3">
                                    <?php echo htmlspecialchars($appointment['reason']); ?>
                                </p>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-muted">Booked On</h6>
                                <p class="mb-3">
                                    <?php echo formatDate($appointment['created_at'], 'M j, Y'); ?><br>
                                    <span class="text-muted"><?php echo formatTime($appointment['created_at']); ?></span>
                                </p>
                                
                                <?php if ($appointment['confirmed_by_name']): ?>
                                    <h6 class="text-muted">Confirmed By</h6>
                                    <p class="mb-3">
                                        <?php echo htmlspecialchars($appointment['confirmed_by_name']); ?>
                                        <?php if ($appointment['confirmed_at']): ?>
                                            <br><small class="text-muted">
                                                on <?php echo formatDate($appointment['confirmed_at'], 'M j, Y'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($appointment['notes']): ?>
                                    <h6 class="text-muted">Notes</h6>
                                    <p class="mb-3">
                                        <?php echo nl2br(htmlspecialchars($appointment['notes'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Patient Information -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-person"></i>
                            Patient Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></strong>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-envelope text-muted"></i>
                            <?php echo htmlspecialchars($appointment['email']); ?>
                        </p>
                        <?php if ($appointment['phone']): ?>
                            <p class="mb-0">
                                <i class="bi bi-telephone text-muted"></i>
                                <?php echo htmlspecialchars($appointment['phone']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-lightning"></i>
                            Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="book-appointment.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Book New Appointment
                            </a>
                            <a href="my-appointments.php" class="btn btn-outline-secondary">
                                <i class="bi bi-list"></i> View All Appointments
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="bi bi-house"></i> Go to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?> 