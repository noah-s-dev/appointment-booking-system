<?php
/**
 * Additional Security Functions
 * Appointment Booking System
 */

/**
 * Enhanced input validation and sanitization
 */

/**
 * Validate and sanitize appointment data
 */
function validateAppointmentData($data) {
    $errors = [];
    
    // Validate date
    if (empty($data['appointment_date'])) {
        $errors[] = 'Appointment date is required.';
    } elseif (!isValidFutureDate($data['appointment_date'])) {
        $errors[] = 'Please select a valid future date.';
    }
    
    // Validate time slot
    if (empty($data['time_slot_id']) || !is_numeric($data['time_slot_id'])) {
        $errors[] = 'Please select a valid time slot.';
    }
    
    // Validate reason
    if (empty($data['reason'])) {
        $errors[] = 'Reason for appointment is required.';
    } elseif (strlen($data['reason']) > 1000) {
        $errors[] = 'Reason must be less than 1000 characters.';
    }
    
    return $errors;
}

/**
 * Validate user registration data
 */
function validateUserRegistration($data) {
    $errors = [];
    
    // Validate first name
    if (empty($data['first_name'])) {
        $errors[] = 'First name is required.';
    } elseif (strlen($data['first_name']) > 50) {
        $errors[] = 'First name must be less than 50 characters.';
    } elseif (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $data['first_name'])) {
        $errors[] = 'First name contains invalid characters.';
    }
    
    // Validate last name
    if (empty($data['last_name'])) {
        $errors[] = 'Last name is required.';
    } elseif (strlen($data['last_name']) > 50) {
        $errors[] = 'Last name must be less than 50 characters.';
    } elseif (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $data['last_name'])) {
        $errors[] = 'Last name contains invalid characters.';
    }
    
    // Validate email
    if (empty($data['email'])) {
        $errors[] = 'Email is required.';
    } elseif (!validateEmail($data['email'])) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($data['email']) > 100) {
        $errors[] = 'Email must be less than 100 characters.';
    }
    
    // Validate phone (if provided)
    if (!empty($data['phone']) && !validatePhone($data['phone'])) {
        $errors[] = 'Please enter a valid phone number.';
    }
    
    // Validate password
    if (empty($data['password'])) {
        $errors[] = 'Password is required.';
    } elseif (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
    } elseif (strlen($data['password']) > 255) {
        $errors[] = 'Password is too long.';
    }
    
    // Check password strength
    if (!empty($data['password'])) {
        $strength_errors = validatePasswordStrength($data['password']);
        $errors = array_merge($errors, $strength_errors);
    }
    
    return $errors;
}

/**
 * Validate password strength
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    // Check for minimum requirements
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    
    // Check for common weak passwords
    $weak_passwords = ['password', '123456', 'admin', 'user', 'guest', 'test'];
    if (in_array(strtolower($password), $weak_passwords)) {
        $errors[] = 'Password is too common. Please choose a stronger password.';
    }
    
    return $errors;
}

/**
 * Security headers and protection
 */

/**
 * Set security headers
 */
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:;");
}

/**
 * Check for SQL injection patterns
 */
function detectSQLInjection($input) {
    $patterns = [
        '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b|\bCREATE\b|\bALTER\b)/i',
        '/(\bOR\b|\bAND\b)\s+\d+\s*=\s*\d+/i',
        '/[\'";]/',
        '/--/',
        '/\/\*.*\*\//',
        '/\bxp_\w+/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check for XSS patterns
 */
function detectXSS($input) {
    $patterns = [
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<iframe\b/i',
        '/<object\b/i',
        '/<embed\b/i',
        '/<form\b/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Enhanced input sanitization
 */
function sanitizeInputAdvanced($input, $type = 'string') {
    // First, basic sanitization
    $input = sanitizeInput($input);
    
    // Check for malicious patterns
    if (detectSQLInjection($input) || detectXSS($input)) {
        logSecurityIncident('malicious_input_detected', $input);
        return '';
    }
    
    // Type-specific sanitization
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'phone':
            return preg_replace('/[^0-9+\-\(\)\s]/', '', $input);
        case 'name':
            return preg_replace('/[^a-zA-Z\s\-\'\.]/u', '', $input);
        default:
            return $input;
    }
}

/**
 * Log security incidents
 */
function logSecurityIncident($type, $details = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO activity_log (user_id, user_type, action, details, ip_address, user_agent, created_at) VALUES (?, 'system', ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
            "security_incident_$type",
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log("Security incident logging error: " . $e->getMessage());
    }
}

/**
 * Check if user is in maintenance mode whitelist
 */
function isMaintenanceModeActive() {
    $maintenance_mode = getSystemSetting('maintenance_mode', '0');
    return $maintenance_mode === '1';
}

/**
 * Check if registration is allowed
 */
function isRegistrationAllowed() {
    $allow_registration = getSystemSetting('allow_registration', '1');
    return $allow_registration === '1';
}

/**
 * Enhanced session security
 */
function regenerateSessionId() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    $timeout = (int)getSystemSetting('session_timeout', SESSION_TIMEOUT);
    
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $timeout) {
            session_unset();
            session_destroy();
            return false;
        }
    }
    
    return true;
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 5242880) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed.';
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        $errors[] = 'File size exceeds maximum allowed size.';
    }
    
    // Check file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        $errors[] = 'File type not allowed.';
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    if (!in_array($mime_type, $allowed_mimes)) {
        $errors[] = 'Invalid file type.';
    }
    
    return $errors;
}

/**
 * Generate secure random token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Constant time string comparison
 */
function secureStringCompare($str1, $str2) {
    return hash_equals($str1, $str2);
}

/**
 * IP address validation and filtering
 */
function isValidIP($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

/**
 * Check if IP is in blacklist
 */
function isIPBlacklisted($ip) {
    // This could be expanded to check against a database of blacklisted IPs
    $blacklisted_ips = [
        '127.0.0.1', // Example - remove in production
    ];
    
    return in_array($ip, $blacklisted_ips);
}

/**
 * Get real IP address
 */
function getRealIPAddress() {
    $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            $ip = trim($ip);
            if (isValidIP($ip)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
?>

