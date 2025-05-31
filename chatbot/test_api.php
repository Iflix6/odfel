<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/functions.php';

// Get the API key
$api_key = getSetting('gemini_api_key');
echo "API Key from settings: " . $api_key . "\n";

// Test endpoint - Using gemini-2.0-flash model
$endpoint = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=" . $api_key;

// Basic test request
$data = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => "Say hello"
                ]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'topK' => 40,
        'topP' => 0.8,
        'maxOutputTokens' => 200,
    ]
];

// Set up curl request
$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_VERBOSE => true
]);

// Create a temporary file to store the verbose output
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "\nHTTP Status Code: " . $httpCode . "\n";

// If there was an error, get the curl error message
if($response === false) {
    echo "Curl Error: " . curl_error($ch) . "\n";
}

// Get verbose information
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
echo "\nVerbose log:\n", $verboseLog, "\n";

// Print the response
echo "\nAPI Response:\n";
print_r(json_decode($response, true));

curl_close($ch);
?>