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

// Include necessary files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/tokenization.php';
require_once __DIR__ . '/../../utils/helpers.php';

// Get the bearer token
$token = getBearerToken();

// Check if the user is authenticated and is an admin
if (!$token || !isAuthenticated($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Verify admin privileges
$payload = getTokenPayload($token);
if (!isset($payload['type']) || $payload['type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden: Admin access required']);
    exit();
}

// Get the endpoint parameter
$endpoint = $_GET['endpoint'] ?? '';

// Database connection
$database = new Database();
$db = $database->getConnection();

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    switch ($endpoint) {
        case 'active':
            // List active users
            $query = "SELECT id, username, email, created_at, status 
                     FROM users 
                     WHERE status = 'active' 
                     ORDER BY created_at DESC 
                     LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            // Count total for pagination
            $countStmt = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
            $total = $countStmt->fetchColumn();
            
            break;
            
        case 'new':
            // List new subscribers
            $query = "SELECT id, username, email, created_at, status 
                     FROM users 
                     WHERE status = 'new subscriber' 
                     ORDER BY created_at DESC 
                     LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            // Count total for pagination
            $countStmt = $db->query("SELECT COUNT(*) FROM users WHERE status = 'new subscriber'");
            $total = $countStmt->fetchColumn();
            
            break;
            
        case 'inactive':
            // List inactive users
            $query = "SELECT id, username, email, created_at, status 
                     FROM users 
                     WHERE status = 'inactive' 
                     ORDER BY created_at DESC 
                     LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            // Count total for pagination
            $countStmt = $db->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'");
            $total = $countStmt->fetchColumn();
            
            break;
            
        case 'resubscribed':
            // List resubscribed users
            $query = "SELECT id, username, email, created_at, status 
                     FROM users 
                     WHERE status = 'resubscribed' 
                     ORDER BY created_at DESC 
                     LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            // Count total for pagination
            $countStmt = $db->query("SELECT COUNT(*) FROM users WHERE status = 'resubscribed'");
            $total = $countStmt->fetchColumn();
            
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
            exit();
    }
    
    // Fetch all users
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return response with pagination info
    echo json_encode([
        'success' => true,
        'data' => [
            'users' => $users,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'details' => $e->getMessage() // Remove in production
    ]);
}
