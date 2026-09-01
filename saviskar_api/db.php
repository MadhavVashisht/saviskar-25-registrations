<?php
// saviskar_api/db.php

$db_host = '127.0.0.1'; 
$db_user = 'root'; 
$db_pass = ''; 
$db_name = 'saviskar_db';

// First, connect without a specific database to create it if it doesn't exist
$conn = new mysqli($db_host, $db_user, $db_pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql_create_db = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($sql_create_db)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($db_name);

// Now, create all required tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(255),
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(20),
        college VARCHAR(255),
        state VARCHAR(100),
        city VARCHAR(100),
        is_faculty TINYINT(1) DEFAULT 0,
        year VARCHAR(10),
        course VARCHAR(100),
        faculty_incharge_id VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(100),
        type VARCHAR(100),
        team_size INT DEFAULT 1,
        price DECIMAL(10,2) DEFAULT 0.00,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS teams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        team_code VARCHAR(50) UNIQUE NOT NULL,
        team_name VARCHAR(255),
        event_id INT NOT NULL,
        leader_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS team_members (
        team_id INT NOT NULL,
        user_id INT NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (team_id, user_id),
        FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS saved_carts (
        user_id INT PRIMARY KEY,
        cart_data LONGTEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) DEFAULT 0.00,
        payment_status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS registration_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        registration_id INT NOT NULL,
        item_type VARCHAR(50) NOT NULL,
        item_id VARCHAR(50) NOT NULL,
        item_name VARCHAR(255),
        price DECIMAL(10,2) DEFAULT 0.00,
        team_id VARCHAR(50),
        team_status VARCHAR(50),
        FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        die("Error creating table: " . $conn->error);
    }
}

// Optionally insert some dummy events if the events table is empty
$result = $conn->query("SELECT COUNT(*) AS count FROM events");
if ($result && $result->fetch_assoc()['count'] == 0) {
    $dummy_events = [
        "INSERT INTO events (title, description, category, type, team_size, price, image_path) VALUES 
        ('Hackathon 2025', '24-hour coding challenge', 'technical', 'team', 4, 500.00, 'assets/img/events/hackathon.jpg')",
        "INSERT INTO events (title, description, category, type, team_size, price, image_path) VALUES 
        ('Dance Competition', 'Show your moves', 'cultural', 'team', 10, 1000.00, 'assets/img/events/dance.jpg')",
        "INSERT INTO events (title, description, category, type, team_size, price, image_path) VALUES 
        ('Gaming Tournament', 'Valorant & BGMI', 'technical', 'team', 5, 250.00, 'assets/img/events/gaming.jpg')",
        "INSERT INTO events (title, description, category, type, team_size, price, image_path) VALUES 
        ('Treasure Hunt', 'Find the hidden clues across campus', 'non-technical', 'team', 3, 150.00, 'assets/img/events/treasure.jpg')"
    ];
    foreach ($dummy_events as $sql) {
        $conn->query($sql);
    }
}
?>
