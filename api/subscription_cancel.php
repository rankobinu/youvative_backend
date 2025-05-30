<?php
// Include database and controllers
require_once 'config/database.php';
require_once 'controllers/SubscriptionController.php';
require_once 'utils/helpers.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create subscription controller
$subscriptionController = new SubscriptionController($db);

// Handle request
$method = $_SERVER['REQUEST_METHOD'];

// Set response headers
header('Content-Type: application/json');

// Check authentication
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get authenticated user ID
$user_id = getAuthUserId();

// Only allow POST requests
if ($method === 'POST') {
    // Cancel subscription
    $result = $subscriptionController->cancelSubscription($user_id);

    if ($result['status']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
