<?php
// index.php
// Enable CORS for frontend access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
error_log("Requested Path: " . $path);
require_once 'config/database.php';
require_once 'utils/helpers.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');

// Simple routing
if (strpos($path, 'api/auth') === 0) {
    // Extract endpoint (after api/auth/)
    $endpoint = substr($path, strlen('api/auth/'));
    $_GET['endpoint'] = $endpoint;
    require_once 'api/auth.php';

} elseif (strpos($path, 'api/user') === 0) {
    $endpoint = substr($path, strlen('api/user/'));
    $_GET['endpoint'] = $endpoint;
    require_once 'api/user.php';

} elseif (strpos($path, 'api/subscription') === 0) {
    $endpoint = substr($path, strlen('api/subscription/'));
    $_GET['endpoint'] = $endpoint;
    require_once 'api/subscription.php';

} elseif (strpos($path, 'api/strategy') === 0) {
    $endpoint = substr($path, strlen('api/strategy/'));
    $_GET['endpoint'] = $endpoint;
    require_once 'api/strategy.php';

} else {
    // API documentation or unknown endpoint
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode([
        'error' => 'Endpoint not found',
        'path' => $path
    ]);
}

