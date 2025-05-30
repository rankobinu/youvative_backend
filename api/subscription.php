<?php
// Include necessary files
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/tokenization.php';
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../controllers/SubscriptionController.php';

// Initialiser la base de données
$database = new Database();
$db = $database->getConnection();

// Initialiser le contrôleur
$controller = new SubscriptionController($db);

// Obtenir l'endpoint
$endpoint = $_GET['endpoint'] ?? '';

// Obtenir le token d'authentification
$token = getBearerToken();

switch ($endpoint) {
    case 'create':
        // Ce cas nécessite un token valide pour récupérer l'ID utilisateur
        if (!$token || !isAuthenticated($token)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        // Extraire l'ID de l'utilisateur à partir du token
        $userId = getAuthUserIdFromToken($token);

        // Obtenir les données de la requête
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['card_number'], $data['expiry_date'], $data['cvv'], $data['plan'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            exit();
        }

        // Passer l'ID utilisateur et les données à la fonction createSubscription
        $result = $controller->createSubscription($userId, $data);
        echo json_encode($result);
        break;

    case 'get':
        if (!$token || !isAuthenticated($token)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $userId = getAuthUserIdFromToken($token);
        $result = $controller->getSubscription($userId);
        echo json_encode($result);
        break;

    case 'update':
        if (!$token || !isAuthenticated($token)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $userId = getAuthUserIdFromToken($token);
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
            exit();
        }

        $data['user_id'] = $userId;
        $result = $controller->renewSubscription($userId, $data);
        echo json_encode($result);
        break;

    case 'cancel':
        // à implémenter si nécessaire
        break;

    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
        break;
}











