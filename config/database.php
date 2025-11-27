<?php
/**
 * Database Configuration
 * Appointment Booking System
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'appointment_booking');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'Appointment Booking System');
define('APP_URL', 'http://localhost/appointment-booking-system');
define('ADMIN_EMAIL', 'admin@appointmentbooking.com');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// Timezone setting
date_default_timezone_set('America/New_York');

/**
 * Database Connection Class
 */
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $pdo;
    
    /**
     * Get database connection
     */
    public function getConnection() {
        $this->pdo = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed");
        }
        
        return $this->pdo;
    }
    
    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->pdo = null;
    }
}

/**
 * Get database instance
 */
function getDB() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->getConnection();
}
?>

