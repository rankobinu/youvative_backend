<?php
// Get the requesting origin
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// List of allowed origins
$allowed_origins = [
    'http://localhost:5173',  // Local development
    'http://localhost:3000',  // Alternative local port
    'https://youvative.com',  // Production domain
    'https://www.youvative.com',  // Production with www
    'https://youvative-frontend.onrender.com'  // Render deployment
];

// Check if the origin is allowed
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // For development, you might want to allow any origin
    // Comment this out in production for better security
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
