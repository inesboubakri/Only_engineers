<?php
header('Content-Type: application/json');

// Read and parse the JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['text']) || !isset($input['target_lang'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing text or target_lang']);
    exit;
}

// Ensure text is not empty
if (trim($input['text']) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Text cannot be empty']);
    exit;
}

// Get DeepL API key from environment variable
$apiKey = getenv('DEEPL_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'DeepL API key not configured']);
    exit;
}

// Initialize cURL to call DeepL API
$ch = curl_init('https://api-free.deepl.com/v2/translate');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/x-www-form-urlencoded',
        "Authorization: DeepL-Auth-Key $apiKey"
    ],
    CURLOPT_POSTFIELDS     => http_build_query([
        'text'        => $input['text'],
        'target_lang' => strtoupper($input['target_lang'])
    ]),
    CURLOPT_TIMEOUT        => 30
]);

// Execute cURL request
$response = curl_exec($ch);
$err      = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Handle cURL or HTTP errors
if ($err || $httpCode !== 200) {
    http_response_code($httpCode ?: 500);
    echo json_encode(['error' => $err ?: "DeepL API error (HTTP $httpCode)"]);
    exit;
}

// Pass through the DeepL response
echo $response;
