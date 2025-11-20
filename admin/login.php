<?php
/**
 * Admin Login Page
 * Appointment Booking System
 */

// Define the project root path
$project_root = dirname(__DIR__);

require_once $project_root . '/config/database.php';
require_once $project_root . '/includes/functions.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token (temporarily disabled for development)
    // if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    //     $errors[] = 'Invalid security token. Please try again.';
    // } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Rate limiting check
        $rate_limit_key = 'admin_login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!checkRateLimit($rate_limit_key, MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME)) {
            $errors[] = 'Too many login attempts. Please try again in 15 minutes.';
        } else {
            // Validation
            if (empty($username)) {
                $errors[] = 'Username is required.';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required.';
            }
            
            // Authenticate admin
            if (empty($errors)) {
                try {
                    $db = getDB();
                    $stmt = $db->prepare("SELECT id, username, email, full_name, password_hash, is_active FROM admin_users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $username]);
                    $admin = $stmt->fetch();
                    
                    if ($admin && verifyPassword($password, $admin['password_hash'])) {
                        if (!$admin['is_active']) {
                            $errors[] = 'Your admin account has been deactivated. Please contact the system administrator.';
                        } else {
                            // Login successful
                            $_SESSION['admin_id'] = $admin['id'];
                            $_SESSION['admin_username'] = $admin['username'];
                            $_SESSION['admin_name'] = $admin['full_name'];
                            $_SESSION['admin_email'] = $admin['email'];
                            $_SESSION['admin_login_time'] = time();
                            
                            // Update last login
                            $stmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                            $stmt->execute([$admin['id']]);
                            
                            // Log activity
                            logActivity($admin['id'], 'admin_login', "Admin logged in: {$admin['username']}", 'admin');
                            
                            redirect('dashboard.php', 'Welcome back, ' . $admin['full_name'] . '!', 'success');
                        }
                    } else {
                        $errors[] = 'Invalid username or password.';
                        
                        // Log failed login attempt
                        if ($admin) {
                            logActivity($admin['id'], 'failed_admin_login', "Failed admin login attempt: {$admin['username']}", 'admin');
                        }
                    }
                } catch (Exception $e) {
                    error_log("Admin login error: " . $e->getMessage());
                    $errors[] = 'An error occurred. Please try again.';
                }
            }
        }
    // }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $project_root; ?>/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .admin-login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .admin-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .admin-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card admin-login-card">
                    <div class="admin-header">
                        <i class="bi bi-shield-lock"></i>
                        <h3 class="mb-0">Admin Portal</h3>
                        <p class="mb-0 opacity-75"><?php echo APP_NAME; ?></p>
                    </div>
                    <div class="card-body p-4">
                        <?php displayFlashMessage(); ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <!-- CSRF token temporarily disabled for development -->
                            <!-- <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>"> -->
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person"></i> Username or Email
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                       placeholder="Enter your username or email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i> Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter your password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Login to Admin Panel
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <hr>
                            <p class="text-muted mb-0">
                                <i class="bi bi-arrow-left"></i> 
                                <a href="<?php echo $project_root; ?>/index.php" class="text-decoration-none">Back to Main Site</a>
                            </p>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Demo Credentials:</strong><br>
                                Username: <code>admin</code><br>
                                Email: <code>admin@appointmentbooking.com</code><br>
                                Password: <code>admin123</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

