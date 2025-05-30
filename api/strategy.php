<?php
require_once __DIR__ . '/../utils/tokenization.php';  // Include this first
require_once __DIR__ . '/../utils/helpers.php';       // Then include helpers
require_once __DIR__ . '/../controllers/StrategyController.php';
require_once __DIR__ . '/../config/database.php';

// Get the endpoint from the request
$endpoint = $_GET['endpoint'] ?? '';

// Get the bearer token from the request headers
$token = getBearerToken();

// Check if the user is authenticated
if (!$token || !isAuthenticated($token)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Get the user ID from the token
$userId = getAuthUserIdFromToken($token);

// Initialize the controller
$database = new Database();
$controller = new StrategyController($database);

// Handle different endpoints
switch ($endpoint) {
    case 'create':
        // Get the POST data as JSON
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
            exit();
        }

        // Add the user ID to the data
        $data['user_id'] = $userId;

        // Create the strategy
        $result = $controller->createStrategy($data, $userId);
        echo json_encode($result);
        break;

    case 'get':
        // Get strategies for the user
        $result = $controller->getUserStrategies($userId);
        echo json_encode($result);
        break;

    case 'update':
        // Get the PUT data as JSON
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
            exit();
        }

        // Verify that the strategy belongs to the user
        if (!$controller->verifyStrategyOwnership($data['id'], $userId)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
            exit();
        }

        // Update the strategy
        $result = $controller->updateStrategy($data['id'], $userId, $data);
        echo json_encode($result);
        break;

    case 'delete':
        // Get the ID from the request
        $id = $_GET['id'] ?? $_GET['strategy_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
            exit();
        }

        // Verify that the strategy belongs to the user
        if (!$controller->verifyStrategyOwnership($id, $userId)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
            exit();
        }

        // Delete the strategy
        $result = $controller->deleteStrategy($id);
        echo json_encode($result);
        break;

    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
        break;
}


