<?php
// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/ProfileController.php';
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

// 🔐 Récupérer le token JWT depuis le header Authorization
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = null;

if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
}

// 🔒 Validation du token
$userId = $token ? validateToken($token) : false;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized - Invalid or missing token']);
    exit;
}

// 🔧 Connexion à la base de données + contrôleur
$database = new Database();
$db = $database->getConnection();
$profileController = new ProfileController($db);

// 📡 Traitement des requêtes
switch ($method) {
    case 'GET':
        $result = $profileController->getProfile($userId);
        http_response_code($result['status'] ? 200 : 404);
        echo json_encode($result);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $profileController->updateProfile($userId, $data);
        http_response_code($result['status'] ? 200 : 400);
        echo json_encode($result);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}






