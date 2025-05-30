<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../utils/tokenization.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];


// 🔐 Support Apache pour récupérer Authorization si non présent
if (!isset($_SERVER['HTTP_AUTHORIZATION']) && function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            $_SERVER['HTTP_AUTHORIZATION'] = $value;
            break;
        }
    }
}
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = null;

if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
}

$userId = $token ? validateToken($token) : false;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$controller = new TaskController($db);

switch ($method) {
    case 'GET':
        $result = $controller->getUserTasks($userId);
        echo json_encode($result);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $controller->createTask($userId, $data);
        echo json_encode($result);
        break;

    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $query);
        $task_id = $query['task_id'] ?? null;
        $data = json_decode(file_get_contents("php://input"), true);
        if ($task_id && $data) {
            $result = $controller->updateTask($userId, $task_id, $data);
            echo json_encode($result);
        } else {
            echo json_encode(['status' => false, 'error' => 'Missing data']);
        }
        break;

    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'], $query);
        $task_id = $query['task_id'] ?? null;
        if ($task_id) {
            $result = $controller->deleteTask($userId, $task_id);
            echo json_encode($result);
        } else {
            echo json_encode(['status' => false, 'error' => 'Missing task ID']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}
