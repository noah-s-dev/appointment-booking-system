<?php
/**
 * Admin Dashboard
 * Appointment Booking System
 */

// Define the project root path
$project_root = dirname(__DIR__);

require_once $project_root . '/config/database.php';
require_once $project_root . '/includes/functions.php';

// Require admin login
requireAdminLogin();

$admin_id = $_SESSION['admin_id'];

// Get dashboard statistics
try {
    $db = getDB();
    
    // Get appointment statistics
    $stats = [];
    
    // Total appointments
    $stmt = $db->query("SELECT COUNT(*) as count FROM appointments");
    $stats['total_appointments'] = $stmt->fetch()['count'];
    
    // Pending appointments
    $stmt = $db->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'pending'");
    $stats['pending_appointments'] = $stmt->fetch()['count'];
    
    // Today's appointments
    $stmt = $db->query("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = CURDATE()");
    $stats['today_appointments'] = $stmt->fetch()['count'];
    
    // Total users
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = TRUE");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Get recent appointments (last 10)
    $stmt = $db->query("
        SELECT a.*, u.first_name, u.last_name, u.email, ts.start_time, ts.end_time
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        JOIN time_slots ts ON a.time_slot_id = ts.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $recent_appointments = $stmt->fetchAll();
    
    // Get pending appointments requiring attention
    $stmt = $db->query("
        SELECT a.*, u.first_name, u.last_name, u.email, ts.start_time, ts.end_time
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        JOIN time_slots ts ON a.time_slot_id = ts.id
        WHERE a.status = 'pending'
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 5
    ");
    $pending_appointments = $stmt->fetchAll();
    
    // Get appointment status distribution
    $stmt = $db->query("
        SELECT status, COUNT(*) as count 
        FROM appointments 
        GROUP BY status
    ");
    $status_distribution = [];
    while ($row = $stmt->fetch()) {
        $status_distribution[$row['status']] = $row['count'];
    }
    
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $error_message = "An error occurred while loading the dashboard.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $project_root; ?>/css/style.css" rel="stylesheet">
    <style>
        .admin-sidebar {
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            color: white;
        }
        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .admin-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar p-0">
                <div class="p-3">
                    <h4 class="text-center mb-4">
                        <i class="bi bi-shield-check"></i> Admin Panel
                    </h4>
                    <hr class="text-white-50">
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="appointments.php">
                            <i class="bi bi-calendar-event"></i> Appointments
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                        <a class="nav-link" href="time-slots.php">
                            <i class="bi bi-clock"></i> Time Slots
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                        <hr class="text-white-50">
                        <a class="nav-link" href="<?php echo $project_root; ?>/index.php" target="_blank">
                            <i class="bi bi-globe"></i> View Site
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 admin-content">
                <!-- Header -->
                <div class="bg-white shadow-sm p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">
                            <i class="bi bi-speedometer2 text-primary"></i> Dashboard
                        </h2>
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="p-4">
                    <?php displayFlashMessage(); ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-white bg-opacity-25 text-white me-3">
                                            <i class="bi bi-calendar-event"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo number_format($stats['total_appointments']); ?></h3>
                                            <p class="mb-0 opacity-75">Total Appointments</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-white bg-opacity-25 text-white me-3">
                                            <i class="bi bi-clock-history"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo number_format($stats['pending_appointments']); ?></h3>
                                            <p class="mb-0 opacity-75">Pending Approval</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-white bg-opacity-25 text-white me-3">
                                            <i class="bi bi-calendar-day"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo number_format($stats['today_appointments']); ?></h3>
                                            <p class="mb-0 opacity-75">Today's Appointments</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-white bg-opacity-25 text-white me-3">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo number_format($stats['total_users']); ?></h3>
                                            <p class="mb-0 opacity-75">Active Users</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <a href="appointments.php?status=pending" class="btn btn-warning w-100">
                                                <i class="bi bi-clock-history"></i> Review Pending
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="appointments.php" class="btn btn-primary w-100">
                                                <i class="bi bi-calendar-event"></i> All Appointments
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="time-slots.php" class="btn btn-info w-100">
                                                <i class="bi bi-clock"></i> Manage Slots
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="users.php" class="btn btn-success w-100">
                                                <i class="bi bi-people"></i> View Users
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Pending Appointments -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-clock-history text-warning"></i> Pending Appointments</h5>
                                    <a href="appointments.php?status=pending" class="btn btn-sm btn-outline-warning">View All</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($pending_appointments)): ?>
                                        <div class="text-center py-3">
                                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                            <p class="text-muted mt-2 mb-0">No pending appointments</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($pending_appointments as $appointment): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></h6>
                                                        <p class="mb-1 text-muted">
                                                            <i class="bi bi-calendar"></i> <?php echo formatDate($appointment['appointment_date']); ?>
                                                            <i class="bi bi-clock ms-2"></i> <?php echo formatTime($appointment['start_time']); ?>
                                                        </p>
                                                        <small class="text-muted"><?php echo htmlspecialchars(substr($appointment['reason'], 0, 50)) . (strlen($appointment['reason']) > 50 ? '...' : ''); ?></small>
                                                    </div>
                                                    <div class="btn-group-vertical btn-group-sm">
                                                        <a href="view-appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-outline-primary btn-sm">View</a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Appointments -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-clock text-info"></i> Recent Appointments</h5>
                                    <a href="appointments.php" class="btn btn-sm btn-outline-info">View All</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_appointments)): ?>
                                        <div class="text-center py-3">
                                            <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                                            <p class="text-muted mt-2 mb-0">No appointments yet</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach (array_slice($recent_appointments, 0, 5) as $appointment): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></h6>
                                                        <p class="mb-1 text-muted">
                                                            <i class="bi bi-calendar"></i> <?php echo formatDate($appointment['appointment_date']); ?>
                                                            <i class="bi bi-clock ms-2"></i> <?php echo formatTime($appointment['start_time']); ?>
                                                        </p>
                                                        <small><?php echo getStatusBadge($appointment['status']); ?></small>
                                                    </div>
                                                    <small class="text-muted"><?php echo formatDate($appointment['created_at'], 'M j'); ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

