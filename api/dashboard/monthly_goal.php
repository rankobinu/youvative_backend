<?php
// api/dashboard/monthly_goal.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/MonthlyGoal.php';
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

// Create goal model
$goalModel = new MonthlyGoal($db);

// Get current month's goal
$currentGoal = $goalModel->getCurrentMonthGoal($userId);

if ($currentGoal) {
    echo json_encode([
        'status' => true, 
        'goal' => $currentGoal
    ]);
} else {
    echo json_encode([
        'status' => true, 
        'goal' => null,
        'message' => 'No goal set for current month'
    ]);
}