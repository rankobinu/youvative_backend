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
// Include database and controllers
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once 'utils/helpers.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create user controller
$userController = new UserController($db);

// Handle request
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';

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

// Process request
if ($method === 'GET') {
    if ($endpoint === 'profile') {
        // Get user profile
        $result = $userController->getProfile($user_id);

        if ($result['status']) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode($result);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
} elseif ($method === 'POST') {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);

    if ($endpoint === 'profile') {
        // Update user profile
        $result = $userController->updateProfile($user_id, $data);

        if ($result['status']) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    } elseif ($endpoint === 'form') {
        // Submit form data
        if (!isset($data['instagram']) || !isset($data['location']) || !isset($data['goal']) ||
            !isset($data['occupation']) || !isset($data['comment'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All form fields are required']);
            exit;
        }

        $result = $userController->completeForm($user_id, $data);

        if ($result['status']) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
