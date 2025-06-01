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

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/tokenization.php';
require_once __DIR__ . '/../../utils/helpers.php';
require_once __DIR__ . '/../../controllers/StrategyController.php';
require_once __DIR__ . '/../../controllers/TaskController.php';

$token = getBearerToken();

if (!$token || !isAuthenticated($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

$strategy_type = $_GET['type'] ?? '';

if (!in_array($strategy_type, ['global', 'monthly'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid strategy type. Use "global" or "monthly"']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || 
    !isset($data['goal']) || 
    !isset($data['description'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$strategyController = new StrategyController($db);
$taskController = new TaskController($db);

try {
    $db->beginTransaction();
    
    $strategyData = [
        'strategy_type' => $strategy_type === 'global' ? 'general' : 'monthly',
        'goal' => $data['goal'],
        'description' => $data['description']
    ];
    
    if ($strategy_type === 'global') {
        $result = $strategyController->createStrategy($strategyData, $data['user_id']);
        
        if (!$result['status']) {
            throw new Exception($result['error'] ?? "Failed to create global strategy");
        }
        
        $strategy_id = $result['strategy_id'];
        $isUpdate = false;
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Global strategy created successfully',
            'data' => [
                'strategy_id' => $strategy_id,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        $existingMonthlyStrategy = null;
        $isUpdate = false;
        
        $query = "SELECT id FROM strategies WHERE user_id = :user_id AND strategy_type = 'monthly' ORDER BY created_at DESC LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $existingMonthlyStrategy = $row['id'];
            $isUpdate = true;
            
            $result = $strategyController->updateStrategy($existingMonthlyStrategy, $data['user_id'], $strategyData);
            
            if (!$result['status']) {
                throw new Exception($result['error'] ?? "Failed to update monthly strategy");
            }
            
            $strategy_id = $existingMonthlyStrategy;
            
            $deleteTasksQuery = "DELETE FROM tasks WHERE user_id = :user_id";
            $deleteStmt = $db->prepare($deleteTasksQuery);
            $deleteStmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
            $deleteStmt->execute();
        } else {
            $result = $strategyController->createStrategy($strategyData, $data['user_id']);
            
            if (!$result['status']) {
                throw new Exception($result['error'] ?? "Failed to create monthly strategy");
            }
            
            $strategy_id = $result['strategy_id'];
        }
        
        $tasks = [];
        if (isset($data['tasks']) && is_array($data['tasks'])) {
            foreach ($data['tasks'] as $taskData) {
                $taskData['user_id'] = $data['user_id'];
                
                $taskResult = $taskController->createTask($data['user_id'], $taskData);
                
                if (!$taskResult['status']) {
                    throw new Exception("Failed to create task: " . ($taskData['headline'] ?? 'Unknown'));
                }
                
                $tasks[] = [
                    'id' => $taskResult['task_id'] ?? null,
                    'headline' => $taskData['headline'],
                    'date' => $taskData['date'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => $isUpdate ? 'Monthly strategy updated successfully' : 'Monthly strategy created successfully',
            'data' => [
                'strategy_id' => $strategy_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated' => $isUpdate,
                'tasks' => $tasks
            ]
        ]);
    }
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Failed to " . (($isUpdate ?? false) ? "update" : "create") . " {$strategy_type} strategy",
        'message' => $e->getMessage()
    ]);
}

