<?php
/**
 * Admin Appointments Management
 * Appointment Booking System
 */

// Define the project root path
$project_root = dirname(__DIR__);

require_once $project_root . '/config/database.php';
require_once $project_root . '/includes/functions.php';

// Require admin login
requireAdminLogin();

$admin_id = $_SESSION['admin_id'];

// Handle appointment actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        redirect('appointments.php', 'Invalid security token.', 'error');
    }
    
    $appointment_id = (int)($_POST['appointment_id'] ?? 0);
    $action = $_POST['action'];
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    try {
        $db = getDB();
        
        switch ($action) {
            case 'confirm':
                $stmt = $db->prepare("UPDATE appointments SET status = 'confirmed', confirmed_by = ?, confirmed_at = NOW(), notes = ? WHERE id = ?");
                $stmt->execute([$admin_id, $notes, $appointment_id]);
                
                logActivity($admin_id, 'appointment_confirmed', "Appointment #$appointment_id confirmed", 'admin');
                redirect('appointments.php', 'Appointment confirmed successfully.', 'success');
                break;
                
            case 'cancel':
                $stmt = $db->prepare("UPDATE appointments SET status = 'cancelled', confirmed_by = ?, confirmed_at = NOW(), notes = ? WHERE id = ?");
                $stmt->execute([$admin_id, $notes, $appointment_id]);
                
                logActivity($admin_id, 'appointment_cancelled', "Appointment #$appointment_id cancelled", 'admin');
                redirect('appointments.php', 'Appointment cancelled successfully.', 'success');
                break;
                
            case 'complete':
                $stmt = $db->prepare("UPDATE appointments SET status = 'completed', notes = ? WHERE id = ?");
                $stmt->execute([$notes, $appointment_id]);
                
                logActivity($admin_id, 'appointment_completed', "Appointment #$appointment_id marked as completed", 'admin');
                redirect('appointments.php', 'Appointment marked as completed.', 'success');
                break;
        }
    } catch (Exception $e) {
        error_log("Admin appointment action error: " . $e->getMessage());
        redirect('appointments.php', 'An error occurred while processing the appointment.', 'error');
    }
}

// Get filter parameters
$status_filter = sanitizeInput($_GET['status'] ?? 'all');
$date_filter = sanitizeInput($_GET['date'] ?? 'all');
$search = sanitizeInput($_GET['search'] ?? '');

// Build query conditions
$where_conditions = ["1=1"];
$params = [];

if ($status_filter !== 'all') {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

if ($date_filter === 'today') {
    $where_conditions[] = "a.appointment_date = CURDATE()";
} elseif ($date_filter === 'upcoming') {
    $where_conditions[] = "a.appointment_date >= CURDATE()";
} elseif ($date_filter === 'past') {
    $where_conditions[] = "a.appointment_date < CURDATE()";
}

if (!empty($search)) {
    $where_conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR a.reason LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = implode(' AND ', $where_conditions);

try {
    $db = getDB();
    
    // Get appointments with pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 15;
    $offset = ($page - 1) * $per_page;
    
    // Get total count
    $count_query = "
        SELECT COUNT(*) as total 
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        JOIN time_slots ts ON a.time_slot_id = ts.id 
        WHERE $where_clause
    ";
    $stmt = $db->prepare($count_query);
    $stmt->execute($params);
    $total_appointments = $stmt->fetch()['total'];
    $total_pages = ceil($total_appointments / $per_page);
    
    // Get appointments
    $query = "
        SELECT a.*, u.first_name, u.last_name, u.email, u.phone,
               ts.start_time, ts.end_time,
               admin.full_name as confirmed_by_name
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        JOIN time_slots ts ON a.time_slot_id = ts.id 
        LEFT JOIN admin_users admin ON a.confirmed_by = admin.id
        WHERE $where_clause
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();
    
    // Get status counts for filter badges
    $status_counts = [];
    $status_query = "
        SELECT status, COUNT(*) as count 
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        WHERE 1=1 " . (!empty($search) ? "AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR a.reason LIKE ?)" : "") . "
        GROUP BY status
    ";
    $stmt = $db->prepare($status_query);
    if (!empty($search)) {
        $stmt->execute([$search_param, $search_param, $search_param, $search_param]);
    } else {
        $stmt->execute();
    }
    while ($row = $stmt->fetch()) {
        $status_counts[$row['status']] = $row['count'];
    }
    
} catch (Exception $e) {
    error_log("Admin appointments error: " . $e->getMessage());
    $error_message = "An error occurred while loading appointments.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Admin Panel</title>
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
        .appointment-card {
            transition: transform 0.2s ease;
        }
        .appointment-card:hover {
            transform: translateY(-2px);
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="appointments.php">
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
                            <i class="bi bi-calendar-event text-primary"></i> Manage Appointments
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
                    
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" action="" class="row g-3">
                                        <div class="col-md-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>
                                                    All Status (<?php echo array_sum($status_counts); ?>)
                                                </option>
                                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>
                                                    Pending (<?php echo $status_counts['pending'] ?? 0; ?>)
                                                </option>
                                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>
                                                    Confirmed (<?php echo $status_counts['confirmed'] ?? 0; ?>)
                                                </option>
                                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>
                                                    Cancelled (<?php echo $status_counts['cancelled'] ?? 0; ?>)
                                                </option>
                                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>
                                                    Completed (<?php echo $status_counts['completed'] ?? 0; ?>)
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="date" class="form-label">Date Range</label>
                                            <select class="form-select" id="date" name="date">
                                                <option value="all" <?php echo $date_filter === 'all' ? 'selected' : ''; ?>>All Dates</option>
                                                <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                                                <option value="upcoming" <?php echo $date_filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                                <option value="past" <?php echo $date_filter === 'past' ? 'selected' : ''; ?>>Past</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="search" class="form-label">Search</label>
                                            <input type="text" class="form-control" id="search" name="search" 
                                                   value="<?php echo htmlspecialchars($search); ?>" 
                                                   placeholder="Search by name, email, or reason...">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-search"></i> Search
                                            </button>
                                        </div>
                                    </form>
                                    <div class="mt-3">
                                        <a href="appointments.php" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-x-circle"></i> Clear Filters
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointments List -->
                    <div class="row">
                        <div class="col-12">
                            <?php if (empty($appointments)): ?>
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                                        <h4 class="text-muted mt-3">No Appointments Found</h4>
                                        <p class="text-muted">
                                            <?php if ($status_filter !== 'all' || $date_filter !== 'all' || !empty($search)): ?>
                                                No appointments match your current filters. Try adjusting your search criteria.
                                            <?php else: ?>
                                                No appointments have been booked yet.
                                            <?php endif; ?>
                                        </p>
                                        <?php if ($status_filter !== 'all' || $date_filter !== 'all' || !empty($search)): ?>
                                            <a href="appointments.php" class="btn btn-outline-primary">
                                                <i class="bi bi-x-circle"></i> Clear Filters
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            Appointments 
                                            <span class="badge bg-secondary"><?php echo $total_appointments; ?> total</span>
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Patient</th>
                                                        <th>Date & Time</th>
                                                        <th>Reason</th>
                                                        <th>Status</th>
                                                        <th>Booked On</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($appointments as $appointment): ?>
                                                        <tr>
                                                            <td>
                                                                <div>
                                                                    <strong><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></strong>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($appointment['email']); ?>
                                                                        <?php if ($appointment['phone']): ?>
                                                                            <br><i class="bi bi-phone"></i> <?php echo htmlspecialchars($appointment['phone']); ?>
                                                                        <?php endif; ?>
                                                                    </small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <strong><?php echo formatDate($appointment['appointment_date']); ?></strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <?php echo formatTime($appointment['start_time']); ?> - 
                                                                    <?php echo formatTime($appointment['end_time']); ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <div style="max-width: 200px;">
                                                                    <?php echo htmlspecialchars($appointment['reason']); ?>
                                                                    <?php if ($appointment['notes']): ?>
                                                                        <br><small class="text-muted">
                                                                            <i class="bi bi-sticky"></i> 
                                                                            <?php echo htmlspecialchars($appointment['notes']); ?>
                                                                        </small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                            <td><?php echo getStatusBadge($appointment['status']); ?></td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?php echo formatDate($appointment['created_at'], 'M j, Y'); ?>
                                                                    <br>
                                                                    <?php echo formatTime($appointment['created_at']); ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group-vertical btn-group-sm" role="group">
                                                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                                                            data-bs-toggle="modal" data-bs-target="#appointmentModal"
                                                                            data-appointment='<?php echo json_encode($appointment); ?>'>
                                                                        <i class="bi bi-eye"></i> View
                                                                    </button>
                                                                    
                                                                    <?php if ($appointment['status'] === 'pending'): ?>
                                                                        <button type="button" class="btn btn-outline-success btn-sm"
                                                                                data-bs-toggle="modal" data-bs-target="#actionModal"
                                                                                data-action="confirm" data-id="<?php echo $appointment['id']; ?>"
                                                                                data-patient="<?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>">
                                                                            <i class="bi bi-check-circle"></i> Confirm
                                                                        </button>
                                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                                                data-bs-toggle="modal" data-bs-target="#actionModal"
                                                                                data-action="cancel" data-id="<?php echo $appointment['id']; ?>"
                                                                                data-patient="<?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>">
                                                                            <i class="bi bi-x-circle"></i> Cancel
                                                                        </button>
                                                                    <?php elseif ($appointment['status'] === 'confirmed' && strtotime($appointment['appointment_date']) <= time()): ?>
                                                                        <button type="button" class="btn btn-outline-info btn-sm"
                                                                                data-bs-toggle="modal" data-bs-target="#actionModal"
                                                                                data-action="complete" data-id="<?php echo $appointment['id']; ?>"
                                                                                data-patient="<?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>">
                                                                            <i class="bi bi-check2-circle"></i> Complete
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- Pagination -->
                                    <?php if ($total_pages > 1): ?>
                                        <div class="card-footer">
                                            <nav aria-label="Appointments pagination">
                                                <ul class="pagination pagination-sm justify-content-center mb-0">
                                                    <?php if ($page > 1): ?>
                                                        <li class="page-item">
                                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>">
                                                                <i class="bi bi-chevron-left"></i>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    
                                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>">
                                                                <?php echo $i; ?>
                                                            </a>
                                                        </li>
                                                    <?php endfor; ?>
                                                    
                                                    <?php if ($page < $total_pages): ?>
                                                        <li class="page-item">
                                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>">
                                                                <i class="bi bi-chevron-right"></i>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </nav>
                                            <div class="text-center mt-2">
                                                <small class="text-muted">
                                                    Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $per_page, $total_appointments); ?> 
                                                    of <?php echo $total_appointments; ?> appointments
                                                </small>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Appointment Details Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="appointmentDetails">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="appointment_id" id="actionAppointmentId">
                    <input type="hidden" name="action" id="actionType">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="actionModalTitle">Confirm Action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="actionModalText"></p>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Add any notes about this action..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="actionSubmitBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle appointment details modal
        document.getElementById('appointmentModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const appointment = JSON.parse(button.getAttribute('data-appointment'));
            
            const details = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Patient Information</h6>
                        <p><strong>Name:</strong> ${appointment.first_name} ${appointment.last_name}</p>
                        <p><strong>Email:</strong> ${appointment.email}</p>
                        ${appointment.phone ? `<p><strong>Phone:</strong> ${appointment.phone}</p>` : ''}
                    </div>
                    <div class="col-md-6">
                        <h6>Appointment Details</h6>
                        <p><strong>Date:</strong> ${new Date(appointment.appointment_date).toLocaleDateString()}</p>
                        <p><strong>Time:</strong> ${appointment.start_time} - ${appointment.end_time}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${appointment.status === 'pending' ? 'warning' : appointment.status === 'confirmed' ? 'success' : appointment.status === 'cancelled' ? 'danger' : 'info'}">${appointment.status}</span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Reason for Appointment</h6>
                        <p>${appointment.reason}</p>
                        ${appointment.notes ? `<h6>Notes</h6><p>${appointment.notes}</p>` : ''}
                        ${appointment.confirmed_by_name ? `<p><small class="text-muted">Confirmed by: ${appointment.confirmed_by_name}</small></p>` : ''}
                    </div>
                </div>
            `;
            
            document.getElementById('appointmentDetails').innerHTML = details;
        });
        
        // Handle action modal
        document.getElementById('actionModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const appointmentId = button.getAttribute('data-id');
            const patientName = button.getAttribute('data-patient');
            
            document.getElementById('actionAppointmentId').value = appointmentId;
            document.getElementById('actionType').value = action;
            
            const modal = this;
            const title = modal.querySelector('#actionModalTitle');
            const text = modal.querySelector('#actionModalText');
            const submitBtn = modal.querySelector('#actionSubmitBtn');
            
            switch (action) {
                case 'confirm':
                    title.textContent = 'Confirm Appointment';
                    text.textContent = `Are you sure you want to confirm the appointment for ${patientName}?`;
                    submitBtn.textContent = 'Confirm Appointment';
                    submitBtn.className = 'btn btn-success';
                    break;
                case 'cancel':
                    title.textContent = 'Cancel Appointment';
                    text.textContent = `Are you sure you want to cancel the appointment for ${patientName}?`;
                    submitBtn.textContent = 'Cancel Appointment';
                    submitBtn.className = 'btn btn-danger';
                    break;
                case 'complete':
                    title.textContent = 'Mark as Completed';
                    text.textContent = `Mark the appointment for ${patientName} as completed?`;
                    submitBtn.textContent = 'Mark as Completed';
                    submitBtn.className = 'btn btn-info';
                    break;
            }
        });
    </script>
</body>
</html>

