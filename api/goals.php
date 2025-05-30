<?php
// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/MonthlyGoal.php';
require_once __DIR__ . '/../utils/tokenization.php';
require_once __DIR__ . '/../utils/helpers.php';

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

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Create a new monthly goal
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['month']) || !isset($data['year']) || !isset($data['description'])) {
            http_response_code(400);
            echo json_encode(['status' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        $goalModel->user_id = $userId;
        $goalModel->month = $data['month'];
        $goalModel->year = $data['year'];
        $goalModel->description = $data['description'];
        $goalModel->target_tasks = $data['target_tasks'] ?? 0;
        
        $goal_id = $goalModel->create();
        
        if ($goal_id) {
            echo json_encode([
                'status' => true, 
                'message' => 'Monthly goal created successfully',
                'goal_id' => $goal_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => false, 'message' => 'Failed to create monthly goal']);
        }
        break;
        
    case 'GET':
        // Get current month goal or specific month if provided
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        
        $goal = $goalModel->getCurrentMonthGoal($userId);
        
        if ($goal) {
            echo json_encode(['status' => true, 'goal' => $goal]);
        } else {
            echo json_encode(['status' => true, 'goal' => null, 'message' => 'No goal found for this month']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['status' => false, 'message' => 'Method not allowed']);
        break;
}