<?php
/**
 * Book Appointment Page
 * Appointment Booking System
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Require user login
requireLogin();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Get system settings
$booking_advance_days = (int)getSystemSetting('booking_advance_days', 30);
$max_appointments_per_user = (int)getSystemSetting('max_appointments_per_user', 5);

// Check user's current active appointments
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM appointments WHERE user_id = ? AND status IN ('pending', 'confirmed') AND appointment_date >= CURDATE()");
    $stmt->execute([$user_id]);
    $current_appointments = $stmt->fetch()['count'];
    
    if ($current_appointments >= $max_appointments_per_user) {
        $errors[] = "You have reached the maximum number of active appointments ($max_appointments_per_user). Please cancel or wait for existing appointments to complete.";
    }
} catch (Exception $e) {
    error_log("Booking error: " . $e->getMessage());
    $errors[] = 'An error occurred. Please try again.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($errors)) {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $appointment_date = sanitizeInput($_POST['appointment_date'] ?? '');
        $time_slot_id = (int)($_POST['time_slot_id'] ?? 0);
        $reason = sanitizeInput($_POST['reason'] ?? '');
        
        // Validation
        if (empty($appointment_date)) {
            $errors[] = 'Please select an appointment date.';
        } elseif (!isValidFutureDate($appointment_date)) {
            $errors[] = 'Please select a valid future date.';
        }
        
        if (empty($time_slot_id)) {
            $errors[] = 'Please select a time slot.';
        }
        
        if (empty($reason)) {
            $errors[] = 'Please provide a reason for your appointment.';
        }
        
        // Check if selected date is within allowed range
        if (!empty($appointment_date)) {
            $selected_date = new DateTime($appointment_date);
            $max_date = new DateTime();
            $max_date->add(new DateInterval("P{$booking_advance_days}D"));
            
            if ($selected_date > $max_date) {
                $errors[] = "You can only book appointments up to $booking_advance_days days in advance.";
            }
        }
        
        // Verify time slot availability
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    SELECT ts.*, 
                           COALESCE(booked.count, 0) as booked_count,
                           (ts.max_appointments - COALESCE(booked.count, 0)) as available_count
                    FROM time_slots ts
                    LEFT JOIN (
                        SELECT time_slot_id, COUNT(*) as count 
                        FROM appointments 
                        WHERE status IN ('pending', 'confirmed') 
                        GROUP BY time_slot_id
                    ) booked ON ts.id = booked.time_slot_id
                    WHERE ts.id = ? AND ts.date = ? AND ts.is_available = TRUE
                ");
                $stmt->execute([$time_slot_id, $appointment_date]);
                $time_slot = $stmt->fetch();
                
                if (!$time_slot) {
                    $errors[] = 'Selected time slot is not available.';
                } elseif ($time_slot['available_count'] <= 0) {
                    $errors[] = 'Selected time slot is fully booked.';
                }
                
                // Check if user already has an appointment at this time
                $stmt = $db->prepare("SELECT id FROM appointments WHERE user_id = ? AND appointment_date = ? AND time_slot_id = ? AND status IN ('pending', 'confirmed')");
                $stmt->execute([$user_id, $appointment_date, $time_slot_id]);
                if ($stmt->fetch()) {
                    $errors[] = 'You already have an appointment at this time.';
                }
                
            } catch (Exception $e) {
                error_log("Booking validation error: " . $e->getMessage());
                $errors[] = 'An error occurred while validating your booking.';
            }
        }
        
        // Create appointment
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $time_slot_id, $appointment_date, $time_slot['start_time'], $reason]);
                
                $appointment_id = $db->lastInsertId();
                
                // Log activity
                logActivity($user_id, 'appointment_booked', "Appointment booked for $appointment_date at {$time_slot['start_time']}");
                
                $success = true;
                
            } catch (Exception $e) {
                error_log("Booking creation error: " . $e->getMessage());
                $errors[] = 'An error occurred while booking your appointment. Please try again.';
            }
        }
    }
}

// Get available dates (next 30 days, excluding weekends)
$available_dates = [];
$start_date = new DateTime();
$end_date = new DateTime();
$end_date->add(new DateInterval("P{$booking_advance_days}D"));

while ($start_date <= $end_date) {
    // Skip weekends
    if ($start_date->format('N') < 6) { // Monday = 1, Sunday = 7
        $available_dates[] = $start_date->format('Y-m-d');
    }
    $start_date->add(new DateInterval('P1D'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-calendar-check"></i> <?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="book-appointment.php">Book Appointment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my-appointments.php">My Appointments</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Book New Appointment</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <h5><i class="bi bi-check-circle"></i> Appointment Booked Successfully!</h5>
                                <p>Your appointment has been submitted and is pending approval. You will be notified once it's confirmed.</p>
                                <div class="mt-3">
                                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                                    <a href="my-appointments.php" class="btn btn-outline-primary">View My Appointments</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="bookingForm">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label">Select Date *</label>
                                    <select class="form-select" id="appointment_date" name="appointment_date" required>
                                        <option value="">Choose a date...</option>
                                        <?php foreach ($available_dates as $date): ?>
                                            <option value="<?php echo $date; ?>" 
                                                    <?php echo (($_POST['appointment_date'] ?? '') == $date) ? 'selected' : ''; ?>>
                                                <?php echo formatDate($date, 'l, F j, Y'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="time_slot_id" class="form-label">Select Time *</label>
                                    <select class="form-select" id="time_slot_id" name="time_slot_id" required disabled>
                                        <option value="">First select a date...</option>
                                    </select>
                                    <div class="form-text">Available time slots will appear after selecting a date.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason for Appointment *</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3" required 
                                              placeholder="Please describe the reason for your appointment..."><?php echo htmlspecialchars($_POST['reason'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Please note:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Appointments are subject to approval</li>
                                        <li>You can book up to <?php echo $booking_advance_days; ?> days in advance</li>
                                        <li>Maximum <?php echo $max_appointments_per_user; ?> active appointments per user</li>
                                        <li>You currently have <?php echo $current_appointments; ?> active appointment(s)</li>
                                    </ul>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-calendar-plus"></i> Book Appointment
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load time slots when date is selected
        document.getElementById('appointment_date').addEventListener('change', function() {
            const date = this.value;
            const timeSlotSelect = document.getElementById('time_slot_id');
            
            if (date) {
                // Enable time slot dropdown
                timeSlotSelect.disabled = false;
                timeSlotSelect.innerHTML = '<option value="">Loading...</option>';
                
                // Fetch available time slots
                fetch('ajax/get-time-slots.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'date=' + encodeURIComponent(date) + '&csrf_token=' + encodeURIComponent('<?php echo generateCSRFToken(); ?>')
                })
                .then(response => response.json())
                .then(data => {
                    timeSlotSelect.innerHTML = '<option value="">Select a time...</option>';
                    
                    if (data.success && data.slots.length > 0) {
                        data.slots.forEach(slot => {
                            const option = document.createElement('option');
                            option.value = slot.id;
                            option.textContent = slot.start_time + ' - ' + slot.end_time + ' (' + slot.available_count + ' available)';
                            timeSlotSelect.appendChild(option);
                        });
                    } else {
                        timeSlotSelect.innerHTML = '<option value="">No available time slots</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    timeSlotSelect.innerHTML = '<option value="">Error loading time slots</option>';
                });
            } else {
                timeSlotSelect.disabled = true;
                timeSlotSelect.innerHTML = '<option value="">First select a date...</option>';
            }
        });
    </script>
</body>
</html>

