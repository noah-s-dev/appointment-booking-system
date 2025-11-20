<?php
/**
 * User Login Page
 * Appointment Booking System
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Rate limiting check
        $rate_limit_key = 'login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!checkRateLimit($rate_limit_key, MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME)) {
            $errors[] = 'Too many login attempts. Please try again in 15 minutes.';
        } else {
            // Validation
            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!validateEmail($email)) {
                $errors[] = 'Please enter a valid email address.';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required.';
            }
            
            // Authenticate user
            if (empty($errors)) {
                try {
                    $db = getDB();
                    $stmt = $db->prepare("SELECT id, first_name, last_name, email, password_hash, is_active FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    
                    if ($user && verifyPassword($password, $user['password_hash'])) {
                        if (!$user['is_active']) {
                            $errors[] = 'Your account has been deactivated. Please contact support.';
                        } else {
                            // Login successful
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['login_time'] = time();
                            
                            // Set remember me cookie if requested
                            if ($remember) {
                                $token = generateRandomString(32);
                                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true); // 30 days
                                
                                // Store token in database (you might want to create a remember_tokens table)
                                $stmt = $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                                $stmt->execute([$token, $user['id']]);
                            }
                            
                            // Log activity
                            logActivity($user['id'], 'user_login', "User logged in: $email");
                            
                            // Redirect to intended page or dashboard
                            $redirect_url = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                            unset($_SESSION['redirect_after_login']);
                            redirect($redirect_url, 'Welcome back!', 'success');
                        }
                    } else {
                        $errors[] = 'Invalid email or password.';
                        
                        // Log failed login attempt
                        if ($user) {
                            logActivity($user['id'], 'failed_login', "Failed login attempt: $email");
                        }
                    }
                } catch (Exception $e) {
                    error_log("Login error: " . $e->getMessage());
                    $errors[] = 'An error occurred. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm mt-5">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Login</h4>
                    </div>
                    <div class="card-body">
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
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me for 30 days
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p><a href="forgot-password.php">Forgot your password?</a></p>
                            <p>Don't have an account? <a href="register.php">Register here</a></p>
                            <p><a href="index.php">Back to Home</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

