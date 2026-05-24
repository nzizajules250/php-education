<?php
// setup_db.php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "education_cms";

// Connect to MySQL
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (mysqli_query($conn, $sql)) {
    echo "Database '$dbname' verified/created successfully.\n";
} else {
    die("Error creating database: " . mysqli_error($conn));
}

// Select database
mysqli_select_db($conn, $dbname);

// Create users table
$createUsers = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $createUsers)) {
    echo "Table 'users' verified/created successfully.\n";
} else {
    echo "Error creating table 'users': " . mysqli_error($conn) . "\n";
}

// Create subjects table
$createSubjects = "CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $createSubjects)) {
    echo "Table 'subjects' verified/created successfully.\n";
} else {
    echo "Error creating table 'subjects': " . mysqli_error($conn) . "\n";
}

// Create pages table
$createPages = "CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    page_number INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $createPages)) {
    echo "Table 'pages' verified/created successfully.\n";
} else {
    echo "Error creating table 'pages': " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
