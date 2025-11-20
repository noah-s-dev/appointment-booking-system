<?php
/**
 * User Dashboard
 * Appointment Booking System
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Require user login
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user's appointments
try {
    $db = getDB();
    
    // Get upcoming appointments
    $stmt = $db->prepare("
        SELECT a.*, ts.start_time, ts.end_time 
        FROM appointments a 
        JOIN time_slots ts ON a.time_slot_id = ts.id 
        WHERE a.user_id = ? AND a.appointment_date >= CURDATE() 
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $stmt->execute([$user_id]);
    $upcoming_appointments = $stmt->fetchAll();
    
    // Get past appointments
    $stmt = $db->prepare("
        SELECT a.*, ts.start_time, ts.end_time 
        FROM appointments a 
        JOIN time_slots ts ON a.time_slot_id = ts.id 
        WHERE a.user_id = ? AND a.appointment_date < CURDATE() 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC 
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $past_appointments = $stmt->fetchAll();
    
    // Get user info
    $stmt = $db->prepare("SELECT first_name, last_name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error_message = "An error occurred while loading your dashboard.";
}
?>
<?php 
$page_title = 'Dashboard';
include 'includes/header.php'; 
?>

    <div class="container mt-4">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <!-- Welcome Section -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <h2 class="card-title text-white">Welcome back, <?php echo htmlspecialchars($user_info['first_name']); ?>!</h2>
                        <p class="card-text">Manage your appointments and book new ones from your dashboard.</p>
                        <a href="book-appointment.php" class="btn btn-light">
                            <i class="bi bi-plus-circle"></i> Book New Appointment
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-calendar-event text-primary" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2"><?php echo count($upcoming_appointments); ?></h5>
                        <p class="card-text">Upcoming Appointments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-clock-history text-info" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2"><?php echo count($past_appointments); ?></h5>
                        <p class="card-text">Past Appointments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-person-check text-success" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2">Active</h5>
                        <p class="card-text">Account Status</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Appointments -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Upcoming Appointments</h5>
                        <a href="my-appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcoming_appointments)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                <h6 class="text-muted mt-2">No upcoming appointments</h6>
                                <p class="text-muted">Book your first appointment to get started.</p>
                                <a href="book-appointment.php" class="btn btn-primary">Book Appointment</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($upcoming_appointments, 0, 5) as $appointment): ?>
                                            <tr>
                                                <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                                                <td><?php echo formatTime($appointment['appointment_time']); ?></td>
                                                <td><?php echo getStatusBadge($appointment['status']); ?></td>
                                                <td>
                                                    <a href="view-appointment.php?id=<?php echo $appointment['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">View</a>
                                                    <?php if ($appointment['status'] == 'pending'): ?>
                                                        <a href="cancel-appointment.php?id=<?php echo $appointment['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="book-appointment.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Book New Appointment
                            </a>
                            <a href="my-appointments.php" class="btn btn-outline-primary">
                                <i class="bi bi-calendar-event"></i> View All Appointments
                            </a>
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="bi bi-person"></i> Update Profile
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Account Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Account Info</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($user_info['first_name'] . ' ' . $user_info['last_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?></p>
                        <?php if ($user_info['phone']): ?>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_info['phone']); ?></p>
                        <?php endif; ?>
                        <a href="profile.php" class="btn btn-sm btn-outline-primary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>

