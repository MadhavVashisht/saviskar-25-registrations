<?php
// api/get_events.php

// Allow requests from your frontend development server
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// --- Database Connection ---
require_once 'db.php';if ($conn->connect_error) {
    // Send a proper server error response
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit();
}

// --- Fetch all events ---
$sql = "SELECT id, title, description, category, type, team_size, price, image_path FROM events";
$result = $conn->query($sql);

$events = [];
if ($result->num_rows > 0) {
    // Fetch all rows into an associative array
    while($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

// --- Send the response ---
echo json_encode($events);

$conn->close();
?>