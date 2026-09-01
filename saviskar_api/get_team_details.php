<?php
// api/get_team_details.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';// ... (db connection error handling) ...

$teamCode = isset($_GET['teamCode']) ? $_GET['teamCode'] : '';
if (empty($teamCode)) {
    die(json_encode([]));
}

// FIXED: Changed `tm.status = 'approved'` to `tm.status != 'declined'` to show both approved and pending members
$stmt = $conn->prepare("
    SELECT u.name, tm.status 
    FROM team_members tm
    JOIN users u ON tm.user_id = u.id
    JOIN teams t ON tm.team_id = t.id
    WHERE t.team_code = ? AND tm.status != 'declined'
");
$stmt->bind_param("s", $teamCode);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

echo json_encode($members);
$stmt->close();
$conn->close();
?>