<?php
header('Content-Type: application/json');
// ... rest of your code ...

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/tokenization.php';
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';

// Handle preflight OPTIONS request for CORS
if ($method === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:5173");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    switch ($endpoint) {
        case 'login':
            if (empty($data['email']) || empty($data['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Email and password are required']);
                exit;
            }
            $result = $auth->login($data['email'], $data['password']);
            http_response_code($result['status'] ? 200 : 401);
            echo json_encode($result);
            break;
        case 'register':
            if (empty($data['email']) || empty($data['username']) || empty($data['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Email, username, and password are required']);
                exit;
            }
            $result = $auth->register($data);
            http_response_code($result['status'] ? 201 : 400);
            echo json_encode($result);
            break;
        case 'forgot-password':
            if (empty($data['email'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Email is required']);
                exit;
            }
            $result = $auth->forgotPassword($data['email']);
            http_response_code($result['status'] ? 200 : 404);
            echo json_encode($result);
            break;
        case 'test-db':
            try {
                // Run a simple query to test DB connection
                $stmt = $db->query('SELECT 1');
                $result = $stmt->fetch();

                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => true, 'message' => 'Database connection is OK']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to fetch from database']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}


