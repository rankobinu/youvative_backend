<?php
// Prevent header issues
ob_start();

// Include CORS handling
require_once 'api/cors.php';

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');

error_log("Requested Path: " . $path);
require_once 'config/database.php';
require_once 'utils/helpers.php';

// Simple routing
if (strpos($path, 'api/auth') === 0) {
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
}?>
