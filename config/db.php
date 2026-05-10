<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mqtt_alert_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql_db = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql_db) === TRUE) {
    // Database created or already exists
}

// Select database
$conn->select_db(DB_NAME);

// Create users table if not exists
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_users) === TRUE) {
    // Table created or already exists
}

// Make sure users table has role column
$sql_user_role = "ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user'";
$conn->query($sql_user_role);

// Make sure users table has avatar column
$sql_user_avatar = "ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL";
$conn->query($sql_user_avatar);

// Create alerts table if not exists
$sql_alerts = "CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    topic VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    alert_type ENUM('announcement', 'warning', 'emergency') DEFAULT 'announcement',
    status ENUM('active', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_alerts) === TRUE) {
    // Table created or already exists
}

// Create notifications table if not exists
$sql_notifications = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    alert_id INT NULL,
    topic VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (alert_id) REFERENCES alerts(id) ON DELETE CASCADE
)";

if ($conn->query($sql_notifications) === TRUE) {
    // Table created or already exists
}

// Make sure notifications table has alert_id and read_at columns
$sql_notification_alert_id = "ALTER TABLE notifications ADD COLUMN IF NOT EXISTS alert_id INT NULL";
$conn->query($sql_notification_alert_id);

// Ensure alert_id can be NULL when notifications are not linked to a specific alert record.
$sql_notification_alert_id_nullable = "ALTER TABLE notifications MODIFY COLUMN alert_id INT NULL";
$conn->query($sql_notification_alert_id_nullable);

$sql_notification_read_at = "ALTER TABLE notifications ADD COLUMN IF NOT EXISTS read_at TIMESTAMP NULL DEFAULT NULL";
$conn->query($sql_notification_read_at);

// Create sessions table
$sql_sessions = "CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_sessions) === TRUE) {
    // Table created or already exists
}

?>
