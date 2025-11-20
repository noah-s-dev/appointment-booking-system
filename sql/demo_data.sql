-- Appointment Booking System - Demo Data
-- This file contains demo users and sample appointments for testing purposes

USE appointment_booking;

-- Clear existing demo data (if any)
DELETE FROM appointments WHERE user_id IN (SELECT id FROM users WHERE email LIKE '%demo%');
DELETE FROM users WHERE email LIKE '%demo%';

-- Insert demo users with secure password hashes (all passwords are 'demo123')
INSERT INTO users (first_name, last_name, email, phone, password_hash, is_active) VALUES
('John', 'Smith', 'john.smith@demo.com', '+1-555-0101', '$2y$12$xOz9Auz66OTlxe9JOn7A0.GApcIns3XnXBdkw5GW3zMhbEA0DZwBu', TRUE),
('Sarah', 'Johnson', 'sarah.johnson@demo.com', '+1-555-0102', '$2y$12$xOz9Auz66OTlxe9JOn7A0.GApcIns3XnXBdkw5GW3zMhbEA0DZwBu', TRUE),
('Michael', 'Brown', 'michael.brown@demo.com', '+1-555-0103', '$2y$12$xOz9Auz66OTlxe9JOn7A0.GApcIns3XnXBdkw5GW3zMhbEA0DZwBu', TRUE),
('Emily', 'Davis', 'emily.davis@demo.com', '+1-555-0104', '$2y$12$xOz9Auz66OTlxe9JOn7A0.GApcIns3XnXBdkw5GW3zMhbEA0DZwBu', TRUE),
('David', 'Wilson', 'david.wilson@demo.com', '+1-555-0105', '$2y$12$xOz9Auz66OTlxe9JOn7A0.GApcIns3XnXBdkw5GW3zMhbEA0DZwBu', TRUE),
('Lisa', 'Anderson', 'lisa.anderson@demo.com', '+1-555-0106', '$2y$12$xOz9Auz66OTlxe9JOn7A0.GApcIns3XnXBdkw5GW3zMhbEA0DZwBu', TRUE),
('Robert', 'Taylor', 'robert.taylor@demo.com', '+1-555-0107', '$2y$12$xOz9Auz66OTlxe9JOn7A0.GApcIns3XnXBdkw5GW3zMhbEA0DZwBu', TRUE),
('Jennifer', 'Martinez', 'jennifer.martinez@demo.com', '+1-555-0108', '$2y$12$xOz9Auz66OTlxe9JOn7A0.GApcIns3XnXBdkw5GW3zMhbEA0DZwBu', TRUE),
('Christopher', 'Garcia', 'christopher.garcia@demo.com', '+1-555-0109', '$2y$12$xOz9Auz66OTlxe9JOn7A0.GApcIns3XnXBdkw5GW3zMhbEA0DZwBu', TRUE),
('Amanda', 'Rodriguez', 'amanda.rodriguez@demo.com', '+1-555-0110', '$2y$12$xOz9Auz66OTlxe9JOn7A0.GApcIns3XnXBdkw5GW3zMhbEA0DZwBu', TRUE);

-- Insert additional time slots for demo appointments (next 7 days)
-- Using INSERT IGNORE to skip duplicates that already exist from setup.sql
INSERT IGNORE INTO time_slots (date, start_time, end_time, is_available) VALUES
-- Past days (for completed appointments)
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '16:00:00', '17:00:00', TRUE),

(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '16:00:00', '17:00:00', TRUE),

(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '16:00:00', '17:00:00', TRUE),

-- Today
(CURDATE(), '09:00:00', '10:00:00', TRUE),
(CURDATE(), '10:00:00', '11:00:00', TRUE),
(CURDATE(), '11:00:00', '12:00:00', TRUE),
(CURDATE(), '12:00:00', '13:00:00', TRUE),
(CURDATE(), '13:00:00', '14:00:00', TRUE),
(CURDATE(), '14:00:00', '15:00:00', TRUE),
(CURDATE(), '15:00:00', '16:00:00', TRUE),
(CURDATE(), '16:00:00', '17:00:00', TRUE),

-- Tomorrow
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', '17:00:00', TRUE),

-- Day 3
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:00:00', '17:00:00', TRUE),

-- Day 4
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '16:00:00', '17:00:00', TRUE),

-- Day 5
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '16:00:00', '17:00:00', TRUE),

-- Day 6
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '16:00:00', '17:00:00', TRUE),

-- Day 7
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '09:00:00', '10:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '10:00:00', '11:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '11:00:00', '12:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '12:00:00', '13:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '13:00:00', '14:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '14:00:00', '15:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '15:00:00', '16:00:00', TRUE),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '16:00:00', '17:00:00', TRUE);

-- Insert sample appointments with various statuses
-- Get user IDs for demo users
SET @john_id = (SELECT id FROM users WHERE email = 'john.smith@demo.com');
SET @sarah_id = (SELECT id FROM users WHERE email = 'sarah.johnson@demo.com');
SET @michael_id = (SELECT id FROM users WHERE email = 'michael.brown@demo.com');
SET @emily_id = (SELECT id FROM users WHERE email = 'emily.davis@demo.com');
SET @david_id = (SELECT id FROM users WHERE email = 'david.wilson@demo.com');
SET @lisa_id = (SELECT id FROM users WHERE email = 'lisa.anderson@demo.com');
SET @robert_id = (SELECT id FROM users WHERE email = 'robert.taylor@demo.com');
SET @jennifer_id = (SELECT id FROM users WHERE email = 'jennifer.martinez@demo.com');
SET @christopher_id = (SELECT id FROM users WHERE email = 'christopher.garcia@demo.com');
SET @amanda_id = (SELECT id FROM users WHERE email = 'amanda.rodriguez@demo.com');

-- Get admin ID
SET @admin_id = (SELECT id FROM admin_users WHERE username = 'admin');

-- Insert confirmed appointments (past and today)
-- Only insert if time slots exist
INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status, notes, confirmed_by, confirmed_at) 
SELECT @john_id, ts.id, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '10:00:00', 'Regular checkup', 'completed', 'Patient showed up on time. All vitals normal.', @admin_id, DATE_SUB(CURDATE(), INTERVAL 2 DAY)
FROM time_slots ts WHERE ts.date = DATE_SUB(CURDATE(), INTERVAL 2 DAY) AND ts.start_time = '10:00:00' LIMIT 1;

INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status, notes, confirmed_by, confirmed_at) 
SELECT @sarah_id, ts.id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '14:00:00', 'Follow-up consultation', 'completed', 'Follow-up scheduled for next month.', @admin_id, DATE_SUB(CURDATE(), INTERVAL 1 DAY)
FROM time_slots ts WHERE ts.date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND ts.start_time = '14:00:00' LIMIT 1;

INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status, notes, confirmed_by, confirmed_at) 
SELECT @michael_id, ts.id, CURDATE(), '09:00:00', 'Initial consultation', 'confirmed', 'Patient confirmed via phone call.', @admin_id, DATE_SUB(NOW(), INTERVAL 2 HOUR)
FROM time_slots ts WHERE ts.date = CURDATE() AND ts.start_time = '09:00:00' LIMIT 1;

INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status, notes, confirmed_by, confirmed_at) 
SELECT @emily_id, ts.id, CURDATE(), '11:00:00', 'Annual physical', 'confirmed', 'Pre-appointment instructions sent.', @admin_id, DATE_SUB(NOW(), INTERVAL 1 HOUR)
FROM time_slots ts WHERE ts.date = CURDATE() AND ts.start_time = '11:00:00' LIMIT 1;

-- Insert pending appointments
INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status) 
SELECT @david_id, ts.id, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', 'Consultation for new symptoms', 'pending'
FROM time_slots ts WHERE ts.date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND ts.start_time = '10:00:00' LIMIT 1;

INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status) 
SELECT @lisa_id, ts.id, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', 'Routine checkup', 'pending'
FROM time_slots ts WHERE ts.date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND ts.start_time = '15:00:00' LIMIT 1;

INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status) 
SELECT @robert_id, ts.id, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00', 'Follow-up appointment', 'pending'
FROM time_slots ts WHERE ts.date = DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND ts.start_time = '13:00:00' LIMIT 1;

INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status) 
SELECT @jennifer_id, ts.id, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '16:00:00', 'Specialist consultation', 'pending'
FROM time_slots ts WHERE ts.date = DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND ts.start_time = '16:00:00' LIMIT 1;

INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status) 
SELECT @christopher_id, ts.id, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00', 'Emergency follow-up', 'pending'
FROM time_slots ts WHERE ts.date = DATE_ADD(CURDATE(), INTERVAL 4 DAY) AND ts.start_time = '09:00:00' LIMIT 1;

INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status) 
SELECT @amanda_id, ts.id, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '14:00:00', 'Annual wellness check', 'pending'
FROM time_slots ts WHERE ts.date = DATE_ADD(CURDATE(), INTERVAL 5 DAY) AND ts.start_time = '14:00:00' LIMIT 1;

-- Insert cancelled appointments
INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status, notes) 
SELECT @john_id, ts.id, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '11:00:00', 'Regular checkup', 'cancelled', 'Patient called to cancel due to illness'
FROM time_slots ts WHERE ts.date = DATE_SUB(CURDATE(), INTERVAL 3 DAY) AND ts.start_time = '11:00:00' LIMIT 1;

INSERT INTO appointments (user_id, time_slot_id, appointment_date, appointment_time, reason, status, notes) 
SELECT @sarah_id, ts.id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '16:00:00', 'Follow-up', 'cancelled', 'Rescheduled for next week'
FROM time_slots ts WHERE ts.date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND ts.start_time = '16:00:00' LIMIT 1;

-- Insert sample activity log entries
INSERT INTO activity_log (user_id, user_type, action, details, ip_address, user_agent) VALUES
(@john_id, 'user', 'login', 'User logged in successfully', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(@sarah_id, 'user', 'book_appointment', 'Appointment booked for 2024-01-15 14:00:00', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(@michael_id, 'user', 'cancel_appointment', 'Appointment cancelled for 2024-01-10 11:00:00', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(@admin_id, 'admin', 'confirm_appointment', 'Appointment confirmed for John Smith', '192.168.1.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(@admin_id, 'admin', 'login', 'Admin logged in successfully', '192.168.1.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

-- Insert sample login attempts
INSERT INTO login_attempts (ip_address, email, success, user_agent) VALUES
('192.168.1.100', 'john.smith@demo.com', TRUE, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
('192.168.1.101', 'sarah.johnson@demo.com', TRUE, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
('192.168.1.102', 'invalid@email.com', FALSE, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
('192.168.1.103', 'michael.brown@demo.com', TRUE, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
('192.168.1.104', 'admin@appointmentbooking.com', TRUE, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

-- Display demo data summary
SELECT 'Demo data inserted successfully!' as status;

-- Show demo users
SELECT 'Demo Users:' as info;
SELECT 
    CONCAT(first_name, ' ', last_name) as full_name,
    email,
    phone,
    'demo123' as password
FROM users 
WHERE email LIKE '%demo%'
ORDER BY first_name;

-- Show appointment statistics
SELECT 'Appointment Statistics:' as info;
SELECT 
    status,
    COUNT(*) as count
FROM appointments 
GROUP BY status;

-- Show today's appointments
SELECT 'Today\'s Appointments:' as info;
SELECT 
    CONCAT(u.first_name, ' ', u.last_name) as patient_name,
    a.appointment_time,
    a.reason,
    a.status
FROM appointments a
JOIN users u ON a.user_id = u.id
WHERE a.appointment_date = CURDATE()
ORDER BY a.appointment_time; 