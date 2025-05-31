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
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include necessary files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/tokenization.php';
require_once __DIR__ . '/../../utils/helpers.php';
require_once __DIR__ . '/../../models/Strategy.php';
require_once __DIR__ . '/../../models/Task.php';

// Get the bearer token
$token = getBearerToken();

// Check if the user is authenticated
if (!$token || !isAuthenticated($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get strategy type from query parameter
$strategy_type = $_GET['type'] ?? '';

// Validate strategy type
if (!in_array($strategy_type, ['global', 'monthly'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid strategy type. Use "global" or "monthly"']);
    exit();
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['user_id']) || 
    !isset($data['goal']) || 
    !isset($data['description'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

// Database connection
$database = new Database();
$db = $database->getConnection();

try {
    // Handle based on strategy type
    if ($strategy_type === 'global') {
        // Create global strategy
        $strategy = new Strategy($db);
        $strategy->user_id = $data['user_id'];
        $strategy->strategy_type = 'general'; // Set type to general
        $strategy->goal = $data['goal'];
        $strategy->description = $data['description'];
        
        $strategy_id = $strategy->create();
        if (!$strategy_id) {
            throw new Exception("Failed to create global strategy");
        }
        
        // Update user status to 'active' if they were 'new subscriber'
        $query = "UPDATE users SET status = 'active' WHERE id = :user_id AND status = 'new subscriber'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Global strategy created successfully',
            'data' => [
                'strategy_id' => $strategy_id,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        // Start transaction for monthly strategy
        $db->beginTransaction();
        
        // Create monthly goal (strategy)
        $strategy = new Strategy($db);
        $strategy->user_id = $data['user_id'];
        $strategy->strategy_type = 'monthly'; // Set type to monthly
        $strategy->goal = $data['goal'];
        $strategy->description = $data['description'];
        
        $strategy_id = $strategy->create();
        if (!$strategy_id) {
            throw new Exception("Failed to create monthly strategy");
        }
        
        // Create tasks if provided
        $tasks = [];
        if (isset($data['tasks']) && is_array($data['tasks'])) {
            foreach ($data['tasks'] as $taskData) {
                // Verify user_id is set
                if (!isset($data['user_id'])) {
                    throw new Exception("User ID is required for tasks");
                }
                
                $task = new Task($db);
                $task->user_id = $data['user_id']; // Connect task to user
                $task->type = $taskData['type'];
                $task->headline = $taskData['headline'];
                $task->purpose = $taskData['purpose'];
                $task->date = $taskData['date'];
                $task->status = 'upcoming'; // Default status
                
                $task_id = $task->create();
                if (!$task_id) {
                    throw new Exception("Failed to create task: " . $taskData['headline']);
                }
                
                $tasks[] = [
                    'id' => $task_id,
                    'headline' => $task->headline,
                    'date' => $task->date,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }
        
        // Commit transaction
        $db->commit();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Monthly strategy created successfully',
            'data' => [
                'strategy_id' => $strategy_id,
                'created_at' => date('Y-m-d H:i:s'),
                'tasks' => $tasks
            ]
        ]);
    }
    
} catch (Exception $e) {
    // Rollback transaction if it was started
    if ($strategy_type === 'monthly' && $db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Failed to create {$strategy_type} strategy",
        'message' => $e->getMessage()
    ]);
}


