<?php
// Set CORS headers
header('Content-Type: application/json');
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:3000',
    'https://youvative.com',
    'https://www.youvative.com',
    'https://youvative-frontend.onrender.com'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check for authorization
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch all counts in a single query for efficiency
    $stmt = $db->query("SELECT 
        COUNT(*) as total_users,
        SUM(status = 'new subscriber') as new_users,
        SUM(status = 'active') as active_users,
        SUM(status = 'inactive') as inactive_users,
        SUM(status = 'resubscribed') as resubscribed_users
        FROM users");
    
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Convert all values to integers
    $stats = array_map('intval', $stats);

    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'details' => $e->getMessage() // Only for development
    ]);
}
