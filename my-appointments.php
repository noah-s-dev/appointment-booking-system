<?php
/**
 * My Appointments Page
 * Appointment Booking System
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Require user login
requireLogin();

$user_id = $_SESSION['user_id'];

// Get filter parameters
$status_filter = sanitizeInput($_GET['status'] ?? 'all');
$date_filter = sanitizeInput($_GET['date'] ?? 'all');

// Build query conditions
$where_conditions = ["a.user_id = ?"];
$params = [$user_id];

if ($status_filter !== 'all') {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

if ($date_filter === 'upcoming') {
    $where_conditions[] = "a.appointment_date >= CURDATE()";
} elseif ($date_filter === 'past') {
    $where_conditions[] = "a.appointment_date < CURDATE()";
}

$where_clause = implode(' AND ', $where_conditions);

try {
    $db = getDB();
    
    // Get appointments with pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 10;
    $offset = ($page - 1) * $per_page;
    
    // Get total count
    $count_query = "
        SELECT COUNT(*) as total 
        FROM appointments a 
        JOIN time_slots ts ON a.time_slot_id = ts.id 
        WHERE $where_clause
    ";
    $stmt = $db->prepare($count_query);
    $stmt->execute($params);
    $total_appointments = $stmt->fetch()['total'];
    $total_pages = ceil($total_appointments / $per_page);
    
    // Get appointments
    $query = "
        SELECT a.*, ts.start_time, ts.end_time,
               admin.full_name as confirmed_by_name
        FROM appointments a 
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
        WHERE a.user_id = ? 
        GROUP BY status
    ";
    $stmt = $db->prepare($status_query);
    $stmt->execute([$user_id]);
    while ($row = $stmt->fetch()) {
        $status_counts[$row['status']] = $row['count'];
    }
    
} catch (Exception $e) {
    error_log("My appointments error: " . $e->getMessage());
    $error_message = "An error occurred while loading your appointments.";
}
?>
<?php 
$page_title = 'My Appointments';
include 'includes/header.php'; 
?>
    <div class="container mt-4">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-calendar-event"></i> My Appointments</h2>
                    <a href="book-appointment.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Book New Appointment
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <label for="date" class="form-label">Date Range</label>
                                <select class="form-select" id="date" name="date">
                                    <option value="all" <?php echo $date_filter === 'all' ? 'selected' : ''; ?>>All Dates</option>
                                    <option value="upcoming" <?php echo $date_filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                    <option value="past" <?php echo $date_filter === 'past' ? 'selected' : ''; ?>>Past</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <a href="my-appointments.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            </div>
                        </form>
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
                                <?php if ($status_filter !== 'all' || $date_filter !== 'all'): ?>
                                    No appointments match your current filters. Try adjusting your search criteria.
                                <?php else: ?>
                                    You haven't booked any appointments yet. Get started by booking your first appointment.
                                <?php endif; ?>
                            </p>
                            <div class="mt-3">
                                <a href="book-appointment.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Book Appointment
                                </a>
                                <?php if ($status_filter !== 'all' || $date_filter !== 'all'): ?>
                                    <a href="my-appointments.php" class="btn btn-outline-secondary ms-2">
                                        <i class="bi bi-x-circle"></i> Clear Filters
                                    </a>
                                <?php endif; ?>
                            </div>
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
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
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
                                                    <strong><?php echo formatDate($appointment['appointment_date']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo formatDate($appointment['appointment_date'], 'l'); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo formatTime($appointment['start_time']); ?> - 
                                                    <?php echo formatTime($appointment['end_time']); ?>
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
                                                        <a href="view-appointment.php?id=<?php echo $appointment['id']; ?>" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                        <?php if ($appointment['status'] === 'pending' || $appointment['status'] === 'confirmed'): ?>
                                                            <?php if (strtotime($appointment['appointment_date']) > time()): ?>
                                                                <a href="cancel-appointment.php?id=<?php echo $appointment['id']; ?>" 
                                                                   class="btn btn-outline-danger btn-sm"
                                                                   onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                                    <i class="bi bi-x-circle"></i> Cancel
                                                                </a>
                                                            <?php endif; ?>
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
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
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
    
    <?php include 'includes/footer.php'; ?>

