<?php
define('JWT_SECRET', '2005'); // En production, choisis un secret fort et sécurisé

// Fonction pour générer un token JWT
function generateToken($data) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload = json_encode([
        'exp' => time() + 3600, // Expiration dans 1h
        'iat' => time(), // Création
        'data' => $data // Données utilisateur (ex: id)
    ]);

    // Encodage base64 URL-safe
    $encodedHeader = base64UrlEncode($header);
    $encodedPayload = base64UrlEncode($payload);

    // Signature
    $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", JWT_SECRET, true);
    $encodedSignature = base64UrlEncode($signature);

    return "$encodedHeader.$encodedPayload.$encodedSignature";
}

// Fonction pour valider un token JWT
function validateToken($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        error_log("❌ Format de token invalide");
        return false;
    }

    list($encodedHeader, $encodedPayload, $providedSignature) = $parts;

    // Recalcul de la signature attendue
    $expectedSignature = base64UrlEncode(
        hash_hmac('sha256', "$encodedHeader.$encodedPayload", JWT_SECRET, true)
    );

    if (!hash_equals($expectedSignature, $providedSignature)) {
        error_log("❌ Signature du token invalide");
        return false;
    }

    // Décodage du payload
    $payload = json_decode(base64UrlDecode($encodedPayload), true);

    if (!$payload || !isset($payload['data']['id'])) {
        error_log("❌ Payload invalide ou manquant");
        return false;
    }

    // Vérification expiration
    if (isset($payload['exp']) && time() > $payload['exp']) {
        error_log("⏰ Token expiré");
        return false;
    }

    return $payload['data']['id'];
}

// Fonction pour décoder un token sans le valider
function decodeToken($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    return json_decode(base64UrlDecode($parts[1]), true);
}

// Encodage Base64 URL-safe
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Décodage Base64 URL-safe
function base64UrlDecode($data) {
    $padding = 4 - (strlen($data) % 4);
    if ($padding < 4) {
        $data .= str_repeat('=', $padding);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function tokenizeCardInfo($cardNumber) {
    $cardNumber = preg_replace('/\D/', '', $cardNumber);
    $lastFour = substr($cardNumber, -4);
    $token = 'tok_' . uniqid() . '_' . $lastFour;
    return $token;
}







