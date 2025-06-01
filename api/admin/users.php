<?php
header('Content-Type: application/json');
$allowedOrigins = [
    'http://localhost:5173',
    'https://your-production-frontend-url.com'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/tokenization.php';
require_once __DIR__ . '/../../utils/helpers.php';
require_once __DIR__ . '/../../controllers/UserController.php';

$token = getBearerToken();

if (!$token || !isAuthenticated($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Create user controller
$userController = new UserController($db);

// Check if a specific user ID is requested
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($userId) {
    try {
        $result = $userController->getUserById($userId);
        
        if ($result['status']) {
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => $result['message']
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error',
            'details' => $e->getMessage() // Remove in production
        ]);
    }
    exit();
}

// If no specific user ID, proceed with listing users by status
// Get the endpoint parameter
$endpoint = $_GET['endpoint'] ?? '';

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

try {
    // Map endpoint to user status
    $statusMap = [
        'active' => 'active',
        'new' => 'new subscriber',
        'inactive' => 'inactive',
        'resubscribed' => 'resubscribed'
    ];
    
    if (!isset($statusMap[$endpoint])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
        exit();
    }
    
    $status = $statusMap[$endpoint];
    
    // Get users by status using the controller
    $result = $userController->getUsersByStatus($status, $page, $limit);
    
    // Return response
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'details' => $e->getMessage() // Remove in production
    ]);
}

