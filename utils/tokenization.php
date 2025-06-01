<?php
define('JWT_SECRET', '2005');

function generateToken($data) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload = json_encode([
        'exp' => time() + 2592000,
        'iat' => time(),
        'data' => $data
    ]);

    $encodedHeader = base64UrlEncode($header);
    $encodedPayload = base64UrlEncode($payload);

    $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", JWT_SECRET, true);
    $encodedSignature = base64UrlEncode($signature);

    return "$encodedHeader.$encodedPayload.$encodedSignature";
}

function validateToken($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }

    list($encodedHeader, $encodedPayload, $providedSignature) = $parts;

    $expectedSignature = base64UrlEncode(
        hash_hmac('sha256', "$encodedHeader.$encodedPayload", JWT_SECRET, true)
    );

    if (!hash_equals($expectedSignature, $providedSignature)) {
        return false;
    }

    $payload = json_decode(base64UrlDecode($encodedPayload), true);

    if (!$payload || !isset($payload['data']['id'])) {
        return false;
    }

    if (isset($payload['exp']) && time() > $payload['exp']) {
        return false;
    }

    return $payload['data']['id'];
}

function decodeToken($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    return json_decode(base64UrlDecode($parts[1]), true);
}

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

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







