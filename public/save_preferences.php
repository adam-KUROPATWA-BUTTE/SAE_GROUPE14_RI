<?php

/**
 * save_preferences.php
 *
 * Endpoint responsible for saving user interface preferences via AJAX.
 * Primarily used to toggle accessibility modes (e.g., Tritanopia).
 *
 * Usage: Send a POST request with a JSON body containing the preference.
 * Example: {"tritanopia": true}
 */

// Start the session to access $_SESSION variables
session_start();

// 1. Securely retrieve raw POST data
// file_get_contents can return false if the stream cannot be read.
$rawInput = file_get_contents('php://input');

// If reading the input fails, stop execution immediately.
if ($rawInput === false) {
    http_response_code(400); // Bad Request
    exit('Unable to read input stream.');
}

// 2. Decode the JSON payload
// The second parameter 'true' forces the return of an associative array.
/** @var mixed $data */
$data = json_decode($rawInput, true);

// 3. Validate and Update Session
// We explicitly check if $data is an array to satisfy PHPStan strict typing.
if (is_array($data) && isset($data['tritanopia'])) {
    // Cast to bool ensures strict type safety
    $_SESSION['tritanopia'] = (bool) $data['tritanopia'];
}
