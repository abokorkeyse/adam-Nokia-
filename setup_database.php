<?php
require_once "config/database.php";

// Function to execute SQL queries
function executeQuery($conn, $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Query executed successfully<br>";
    } else {
        echo "Error executing query: " . mysqli_error($conn) . "<br>";
    }
}

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'technician', 'staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
executeQuery($conn, $sql);

// Create customers table
$sql = "CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
executeQuery($conn, $sql);

// Create devices table
$sql = "CREATE TABLE IF NOT EXISTS devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    serial_number VARCHAR(100) UNIQUE,
    issue_description TEXT NOT NULL,
    status ENUM('Received', 'In Progress', 'Completed', 'Delivered') NOT NULL DEFAULT 'Received',
    technician_id INT,
    received_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_date TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL
)";
executeQuery($conn, $sql);

// Create repairs table
$sql = "CREATE TABLE IF NOT EXISTS repairs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    device_id INT NOT NULL,
    technician_id INT NOT NULL,
    repair_description TEXT NOT NULL,
    parts_used TEXT,
    cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('In Progress', 'Completed', 'Failed') NOT NULL DEFAULT 'In Progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE CASCADE
)";
executeQuery($conn, $sql);

// Create activity_log table
$sql = "CREATE TABLE IF NOT EXISTS activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
executeQuery($conn, $sql);

// Create default admin user if not exists
$admin_username = "admin";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$admin_email = "admin@example.com";

$sql = "INSERT IGNORE INTO users (username, password, email, role) 
        VALUES (?, ?, ?, 'admin')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $admin_username, $admin_password, $admin_email);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo "<br>Database setup completed!<br>";
echo "Default admin credentials:<br>";
echo "Username: admin<br>";
echo "Password: admin123<br>";
echo "<br>Please change these credentials after first login!";
?> 