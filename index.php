<?php
/**
 * Main Landing Page
 * Appointment Booking System
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Get some basic stats for display
$stats = [
    'total_appointments' => 0,
    'active_users' => 0,
    'available_slots' => 0
];

try {
    $db = getDB();
    
    // Get total appointments
    $stmt = $db->query("SELECT COUNT(*) as count FROM appointments");
    $stats['total_appointments'] = $stmt->fetch()['count'];
    
    // Get active users
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = TRUE");
    $stats['active_users'] = $stmt->fetch()['count'];
    
    // Get available slots for next 7 days
    $stmt = $db->query("
        SELECT COUNT(*) as count 
        FROM time_slots ts
        LEFT JOIN (
            SELECT time_slot_id, COUNT(*) as booked_count 
            FROM appointments 
            WHERE status IN ('pending', 'confirmed') 
            GROUP BY time_slot_id
        ) booked ON ts.id = booked.time_slot_id
        WHERE ts.date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND ts.is_available = TRUE 
        AND (ts.max_appointments - COALESCE(booked.booked_count, 0)) > 0
    ");
    $stats['available_slots'] = $stmt->fetch()['count'];
    
} catch (Exception $e) {
    error_log("Index page stats error: " . $e->getMessage());
}
?>
<?php 
$page_title = 'Easy Online Appointment Booking';
include 'includes/header.php'; 
?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 fade-in">
                    <h1>Book Appointments with Ease</h1>
                    <p class="lead text-light">Our simple and secure appointment booking system makes it easy to schedule your appointments online. No more phone calls or waiting on hold.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="register.php" class="btn btn-light btn-lg">
                            <i class="bi bi-person-plus"></i> Create Account
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center slide-in-right">
                    <i class="bi bi-calendar-check" style="font-size: 15rem; opacity: 0.8;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="stats-card">
                        <i class="bi bi-calendar-event text-primary"></i>
                        <h5><?php echo number_format($stats['total_appointments']); ?></h5>
                        <p class="text-muted mb-0">Total Appointments</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stats-card">
                        <i class="bi bi-people text-success"></i>
                        <h5><?php echo number_format($stats['active_users']); ?></h5>
                        <p class="text-muted mb-0">Active Users</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stats-card">
                        <i class="bi bi-clock text-info"></i>
                        <h5><?php echo number_format($stats['available_slots']); ?></h5>
                        <p class="text-muted mb-0">Available Slots This Week</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="text-gradient">Why Choose Our Booking System?</h2>
                    <p class="lead text-muted">Experience the convenience of modern appointment booking</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card feature-card">
                        <div class="card-body">
                            <i class="bi bi-clock-history"></i>
                            <h5>24/7 Availability</h5>
                            <p class="text-muted">Book appointments anytime, anywhere. Our system is available round the clock for your convenience.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card feature-card">
                        <div class="card-body">
                            <i class="bi bi-shield-check"></i>
                            <h5>Secure & Private</h5>
                            <p class="text-muted">Your personal information is protected with industry-standard security measures and encryption.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card feature-card">
                        <div class="card-body">
                            <i class="bi bi-phone"></i>
                            <h5>Mobile Friendly</h5>
                            <p class="text-muted">Fully responsive design that works perfectly on all devices - desktop, tablet, and mobile.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card feature-card">
                        <div class="card-body">
                            <i class="bi bi-bell"></i>
                            <h5>Instant Notifications</h5>
                            <p class="text-muted">Get notified immediately when your appointment is confirmed, rescheduled, or cancelled.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card feature-card">
                        <div class="card-body">
                            <i class="bi bi-calendar-plus"></i>
                            <h5>Easy Scheduling</h5>
                            <p class="text-muted">Simple and intuitive interface makes booking appointments quick and hassle-free.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card feature-card">
                        <div class="card-body">
                            <i class="bi bi-headset"></i>
                            <h5>Support Available</h5>
                            <p class="text-muted">Our support team is ready to help you with any questions or issues you may have.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="text-gradient">How It Works</h2>
                    <p class="lead text-muted">Get started in just three simple steps</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-4 text-center">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <span class="h4 mb-0">1</span>
                            </div>
                            <h5>Create Account</h5>
                            <p class="text-muted">Sign up with your basic information. It only takes a minute to get started.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 text-center">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <span class="h4 mb-0">2</span>
                            </div>
                            <h5>Choose Date & Time</h5>
                            <p class="text-muted">Select your preferred date and time from available slots that work for you.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 text-center">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <span class="h4 mb-0">3</span>
                            </div>
                            <h5>Get Confirmation</h5>
                            <p class="text-muted">Receive instant confirmation and manage your appointments from your dashboard.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-gradient-primary text-white">
        <div class="container text-center">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2>Ready to Get Started?</h2>
                    <p class="lead mb-4">Join thousands of users who trust our appointment booking system for their scheduling needs.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="register.php" class="btn btn-light btn-lg">
                            <i class="bi bi-person-plus"></i> Create Free Account
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Login Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

