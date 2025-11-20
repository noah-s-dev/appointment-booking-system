-- Appointment Booking System - Complete Database Setup
-- Combined setup file with security enhancements and proper password hashing

-- Create database
CREATE DATABASE IF NOT EXISTS appointment_booking;
USE appointment_booking;

-- Users table for customer registration and login
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    remember_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Available time slots table
CREATE TABLE time_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    max_appointments INT DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_slot (date, start_time)
);

-- Appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    time_slot_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    confirmed_by INT NULL,
    confirmed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (time_slot_id) REFERENCES time_slots(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- System settings table
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Activity log table for security monitoring
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('user', 'admin') DEFAULT 'user',
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_user_type (user_type),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Login attempts tracking table
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(100),
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,
    user_agent TEXT,
    INDEX idx_ip_address (ip_address),
    INDEX idx_email (email),
    INDEX idx_attempt_time (attempt_time)
);

-- Password reset tokens table
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);

-- Insert default admin user with secure password hash (password: admin123)
-- Using proper bcrypt hash with cost factor 12
INSERT INTO admin_users (username, email, password_hash, full_name) VALUES 
('admin', 'admin@appointmentbooking.com', '$2y$12$ew8IBLjSWO9ems.HMZgz7.STl.48/QjyJNjWqkKhhxuYbbar/DP4S', 'System Administrator');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES 
('booking_advance_days', '30', 'Number of days in advance users can book appointments'),
('booking_hours_start', '09:00', 'Start time for daily appointments'),
('booking_hours_end', '17:00', 'End time for daily appointments'),
('appointment_duration', '60', 'Default appointment duration in minutes'),
('max_appointments_per_user', '5', 'Maximum active appointments per user'),
('site_name', 'Appointment Booking System', 'Name of the booking system'),
('admin_email', 'admin@appointmentbooking.com', 'Administrator email address'),
('session_timeout', '3600', 'Session timeout in seconds'),
('max_login_attempts', '5', 'Maximum login attempts before lockout'),
('lockout_duration', '900', 'Account lockout duration in seconds'),
('password_min_length', '8', 'Minimum password length'),
('require_email_verification', '0', 'Require email verification for new accounts'),
('enable_two_factor', '0', 'Enable two-factor authentication'),
('maintenance_mode', '0', 'Enable maintenance mode'),
('allow_registration', '1', 'Allow new user registration');

-- Generate time slots for the next 30 days (9 AM to 5 PM, hourly slots)
-- Using direct INSERT statements instead of stored procedure to avoid syntax issues
INSERT INTO time_slots (date, start_time, end_time, is_available) VALUES
-- Week 1 (Monday to Friday)
(CURDATE(), '09:00:00', '10:00:00', TRUE),
(CURDATE(), '10:00:00', '11:00:00', TRUE),
(CURDATE(), '11:00:00', '12:00:00', TRUE),
(CURDATE(), '12:00:00', '13:00:00', TRUE),
(CURDATE(), '13:00:00', '14:00:00', TRUE),
(CURDATE(), '14:00:00', '15:00:00', TRUE),
(CURDATE(), '15:00:00', '16:00:00', TRUE),
(CURDATE(), '16:00:00', '17:00:00', TRUE),

(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', '17:00:00', TRUE),

(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:00:00', '17:00:00', TRUE),

(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '16:00:00', '17:00:00', TRUE),

(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '16:00:00', '17:00:00', TRUE);

-- Create indexes for better performance
CREATE INDEX idx_appointments_user_id ON appointments(user_id);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_appointments_user_date ON appointments(user_id, appointment_date);
CREATE INDEX idx_appointments_status_date ON appointments(status, appointment_date);
CREATE INDEX idx_time_slots_date ON time_slots(date);
CREATE INDEX idx_time_slots_available ON time_slots(is_available);
CREATE INDEX idx_time_slots_date_available ON time_slots(date, is_available);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_active ON users(is_active);

-- Create views for easier data access
CREATE VIEW appointment_details AS
SELECT 
    a.id,
    a.appointment_date,
    a.appointment_time,
    a.reason,
    a.status,
    a.notes,
    a.created_at,
    u.first_name,
    u.last_name,
    u.email,
    u.phone,
    ts.start_time,
    ts.end_time,
    admin.full_name as confirmed_by_name
FROM appointments a
JOIN users u ON a.user_id = u.id
JOIN time_slots ts ON a.time_slot_id = ts.id
LEFT JOIN admin_users admin ON a.confirmed_by = admin.id;

CREATE VIEW available_slots AS
SELECT 
    ts.id,
    ts.date,
    ts.start_time,
    ts.end_time,
    ts.max_appointments,
    COALESCE(booked.count, 0) as booked_count,
    (ts.max_appointments - COALESCE(booked.count, 0)) as available_count
FROM time_slots ts
LEFT JOIN (
    SELECT time_slot_id, COUNT(*) as count 
    FROM appointments 
    WHERE status IN ('pending', 'confirmed') 
    GROUP BY time_slot_id
) booked ON ts.id = booked.time_slot_id
WHERE ts.is_available = TRUE 
AND ts.date >= CURDATE()
AND (ts.max_appointments - COALESCE(booked.count, 0)) > 0;

-- Clean up old activity logs (keep only last 90 days)
DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Clean up old login attempts (keep only last 30 days)
DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Clean up expired password reset tokens
DELETE FROM password_reset_tokens WHERE expires_at < NOW() OR used = TRUE;

-- Final message
SELECT 'Appointment Booking System database setup completed successfully!' as status; 