<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit();
}
// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/tokenization.php';
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../utils/task_status_updater.php';

header('Content-Type: application/json');

// Get the bearer token
$token = getBearerToken();

// Check if the user is authenticated
if (!$token || !isAuthenticated($token)) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get user ID from token
$userId = getAuthUserIdFromToken($token);

// Database connection
$database = new Database();
$db = $database->getConnection();

// First update any overdue tasks
updateMissedTasks($db, $userId);

// Get upcoming tasks using direct SQL query
$today = date('Y-m-d');
$query = "SELECT * FROM tasks 
          WHERE user_id = :user_id AND date >= :today AND status = 'upcoming'
          ORDER BY date ASC 
          LIMIT 10";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $userId);
$stmt->bindParam(':today', $today);
$stmt->execute();

// Fetch the results
$upcomingTasks = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $upcomingTasks[] = $row;
}

// Return upcoming tasks
echo json_encode([
    'status' => true,
    'upcoming_tasks' => $upcomingTasks
]);
