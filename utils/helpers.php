<?php
// helpers.php

/**
 * Get the Authorization header from the request
 * @return string|null
 */
function getBearerToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $matches = [];
        if (preg_match('/Bearer (.+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

/**
 * Check if the token is valid
 * @param string $token
 * @return bool
 */
function isAuthenticated($token) {
    // Call the validateToken function from tokenization.php
    return validateToken($token);
}

/**
 * Decode the token and extract the user ID from it
 * @param string $token
 * @return mixed
 */
function getAuthUserIdFromToken($token) {
    // Use the decodeToken function from tokenization.php instead of getTokenPayload
    $decoded = decodeToken($token);
    return $decoded['data']['id'] ?? null;
}

/**
 * Extract payload data from JWT
 * @param string $token
 * @return array
 */
function getTokenPayload($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return [];
    }
    $payload = base64_decode(strtr($parts[1], '-_', '+/'));
    return json_decode($payload, true);
}

// The validateToken function is removed from here since it exists in tokenization.php








