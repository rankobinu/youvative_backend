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
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Temporarily comment out auth check for testing
// if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
//     http_response_code(401);
//     echo json_encode(['success' => false, 'error' => 'Unauthorized']);
//     exit();
// }

// Return simple test response
echo json_encode([
    'success' => true,
    'data' => [
        'test' => 'API is working',
        'timestamp' => time()
    ]
]);
exit();

require_once __DIR__ . '/../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Get basic user statistics
    $stats = [
        'total_users' => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'new_users' => (int)$db->query("SELECT COUNT(*) FROM users WHERE status = 'new subscriber'") ->fetchColumn(),
        'active_users' => (int)$db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn(),
        'inactive_users' => (int)$db->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")->fetchColumn(),
        'resubscribed_users' => (int)$db->query("SELECT COUNT(*) FROM users WHERE status = 'resubscribed'") ->fetchColumn()
    ];

    echo json_encode(['success' => true, 'data' => $stats]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}