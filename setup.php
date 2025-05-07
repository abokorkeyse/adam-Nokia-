<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "mobile_management";

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($database);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'technician') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created successfully<br>";
} else {
    echo "Error creating table 'users': " . $conn->error . "<br>";
}

// Create customers table
$sql = "CREATE TABLE IF NOT EXISTS customers (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'customers' created successfully<br>";
} else {
    echo "Error creating table 'customers': " . $conn->error . "<br>";
}

// Create devices table
$sql = "CREATE TABLE IF NOT EXISTS devices (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    technician_id INT,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    imei VARCHAR(50),
    password VARCHAR(100),
    problem TEXT NOT NULL,
    notes TEXT,
    status ENUM('Received', 'In Progress', 'Completed') NOT NULL DEFAULT 'Received',
    received_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_date TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'devices' created successfully<br>";
} else {
    echo "Error creating table 'devices': " . $conn->error . "<br>";
}

// Insert default admin user if not exists
$sql = "SELECT id FROM users WHERE username = 'admin'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $default_password = password_hash("admin123", PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, email, role) VALUES ('admin', '$default_password', 'admin@example.com', 'admin')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Default admin user created successfully<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<strong>Please change these credentials after first login!</strong><br>";
    } else {
        echo "Error creating default admin user: " . $conn->error . "<br>";
    }
}

// Create database configuration file
$config_content = "<?php
// Database configuration
define('DB_SERVER', '$host');
define('DB_USERNAME', '$username');
define('DB_PASSWORD', '$password');
define('DB_NAME', '$database');

// Attempt to connect to MySQL database
\$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if(\$conn === false){
    die(\"ERROR: Could not connect. \" . mysqli_connect_error());
}
?>";

// Write configuration to file
if (file_put_contents("config/database.php", $config_content) === FALSE) {
    echo "Error creating database configuration file<br>";
} else {
    echo "Database configuration file created successfully<br>";
}

// Create config directory if it doesn't exist
if (!file_exists("config")) {
    mkdir("config", 0777, true);
    echo "Config directory created successfully<br>";
}

$conn->close();

echo "<br>Setup completed! <a href='index.php'>Go to login page</a>";
?> 