-- ===========================================
-- SMART NOTIFICATION SYSTEM DATABASE SCHEMA
-- ===========================================
-- Database: mqtt_alert_db
-- Created: May 9, 2026
-- Description: Complete database schema for the MQTT Alert System
-- ===========================================

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS mqtt_alert_db;
USE mqtt_alert_db;

-- ===========================================
-- TABLE 1: users
-- ===========================================
-- Stores user account information
-- ===========================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    birth_date DATE,
    age INT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================
-- TABLE 2: alerts
-- ===========================================
-- Stores alerts created by admins
-- ===========================================
CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    topic VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    alert_type ENUM('announcement', 'warning', 'emergency') DEFAULT 'announcement',
    status ENUM('active', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===========================================
-- TABLE 3: notifications
-- ===========================================
-- Stores notification messages and their status
-- ===========================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    alert_id INT,
    topic VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (alert_id) REFERENCES alerts(id) ON DELETE CASCADE
);

-- ===========================================
-- TABLE 4: sessions
-- ===========================================
-- Stores user session information for authentication
-- ===========================================
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===========================================
-- SAMPLE DATA INSERTION
-- ===========================================
-- Uncomment the following lines to insert sample data
-- ===========================================

-- Sample users
-- INSERT INTO users (username, email, password) VALUES
-- ('admin', 'admin@example.com', '$2y$10$hashedpasswordhere'),
-- ('john_doe', 'john@example.com', '$2y$10$hashedpasswordhere'),
-- ('jane_smith', 'jane@example.com', '$2y$10$hashedpasswordhere');

-- Sample notifications
-- INSERT INTO notifications (user_id, topic, message, is_read) VALUES
-- (1, 'school/announcements', 'School will be closed tomorrow due to weather.', FALSE),
-- (1, 'system/alerts', 'System maintenance scheduled for tonight.', FALSE),
-- (2, 'weather/alerts', 'Heavy rain warning for the next 24 hours.', TRUE),
-- (2, 'school/announcements', 'Parent-teacher conference next week.', FALSE);

-- ===========================================
-- USEFUL QUERIES
-- ===========================================

-- Get all users
-- SELECT * FROM users;

-- Get all notifications for a specific user
-- SELECT * FROM notifications WHERE user_id = 1;

-- Get unread notifications for a user
-- SELECT * FROM notifications WHERE user_id = 1 AND is_read = FALSE;

-- Get notifications by topic
-- SELECT * FROM notifications WHERE topic = 'school/announcements';

-- Mark notification as read
-- UPDATE notifications SET is_read = TRUE WHERE id = 1;

-- Delete old notifications (older than 30 days)
-- DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Count total notifications per topic
-- SELECT topic, COUNT(*) as count FROM notifications GROUP BY topic ORDER BY count DESC;

-- Get user with their notification count
-- SELECT u.username, COUNT(n.id) as notification_count
-- FROM users u
-- LEFT JOIN notifications n ON u.id = n.user_id
-- GROUP BY u.id, u.username;

-- Get recent notifications (last 24 hours)
-- SELECT * FROM notifications WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Clean up expired sessions
-- DELETE FROM sessions WHERE expires_at < NOW();

-- ===========================================
-- INDEXES FOR PERFORMANCE
-- ===========================================
-- Add these indexes if you have many records
-- ===========================================

-- CREATE INDEX idx_user_id ON notifications(user_id);
-- CREATE INDEX idx_topic ON notifications(topic);
-- CREATE INDEX idx_created ON notifications(created_at);
-- CREATE INDEX idx_is_read ON notifications(is_read);
-- CREATE INDEX idx_session_token ON sessions(session_token);
-- CREATE INDEX idx_expires_at ON sessions(expires_at);

-- ===========================================
-- BACKUP QUERIES
-- ===========================================

-- Export users table
-- SELECT * FROM users INTO OUTFILE '/path/to/users_backup.csv'
-- FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n';

-- Export notifications table
-- SELECT * FROM notifications INTO OUTFILE '/path/to/notifications_backup.csv'
-- FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n';

-- Full database backup (run from command line)
-- mysqldump -u root -p mqtt_alert_db > mqtt_alert_db_backup.sql

-- ===========================================
-- DATABASE MAINTENANCE
-- ===========================================

-- Check table sizes
-- SELECT
--     table_name,
--     ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size in MB'
-- FROM information_schema.TABLES
-- WHERE table_schema = 'mqtt_alert_db'
-- ORDER BY (data_length + index_length) DESC;

-- Optimize tables (run periodically)
-- OPTIMIZE TABLE users;
-- OPTIMIZE TABLE notifications;
-- OPTIMIZE TABLE sessions;

-- Check for orphaned records
-- SELECT n.* FROM notifications n
-- LEFT JOIN users u ON n.user_id = u.id
-- WHERE u.id IS NULL;

-- ===========================================
-- SECURITY NOTES
-- ===========================================

-- Passwords are hashed using PHP's password_hash() with PASSWORD_DEFAULT
-- This uses bcrypt algorithm with automatic salt generation
-- Never store plain text passwords!

-- Session tokens should be cryptographically secure random strings
-- Expire sessions regularly to prevent session hijacking

-- Use prepared statements in PHP to prevent SQL injection
-- Always validate and sanitize user input

-- ===========================================
-- MQTT TOPIC MAPPING
-- ===========================================

-- The system uses these MQTT topics:
-- school/announcements - School-related notifications
-- system/alerts - System status and maintenance alerts
-- weather/alerts - Weather warnings and updates

-- You can add more topics by:
-- 1. Adding to admin.html dropdown
-- 2. Adding client.subscribe() in app.js
-- 3. Adding filter button in dashboard.html
-- 4. Adding emoji in getTopicEmoji() function

-- ===========================================
-- END OF SCHEMA
-- ===========================================