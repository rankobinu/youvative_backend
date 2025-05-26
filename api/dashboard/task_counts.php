<?php
// api/dashboard/task_counts.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/tokenization.php';
require_once __DIR__ . '/../../utils/helpers.php';

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

// Count tasks directly
$query = "SELECT COUNT(*) as total FROM tasks WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Return count
echo json_encode([
    'status' => true,
    'count' => (int)$row['total']
]);