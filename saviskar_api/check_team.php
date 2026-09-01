<?php
// api/check_team.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// This block handles the browser's preflight security check
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- Database Connection ---
require_once 'db.php';if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->teamId) || empty($data->eventId)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Team ID and Event ID are required."]);
    exit();
}

// Check if the team exists for the given event
$stmt = $conn->prepare("SELECT id FROM teams WHERE team_code = ? AND event_id = ?");
$stmt->bind_param("si", $data->teamId, $data->eventId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => true, "message" => "Team is valid."]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid Team ID for this event."]);
}

$stmt->close();
$conn->close();
?>