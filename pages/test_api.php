<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Require admin access
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Verify CSRF token - FIXED: consistent token name
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$api_key = getSetting('gemini_api_key');

if (empty($api_key)) {
    echo json_encode(['success' => false, 'message' => 'API key not configured']);
    exit;
}

try {
    $test_message = "Hello, this is a test message. Please respond with 'API test successful'.";
    $response = testGeminiAPI($test_message, $api_key);
    
    if ($response) {
        echo json_encode(['success' => true, 'message' => 'API connection successful', 'response' => $response]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No response from API']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function testGeminiAPI($message, $api_key) {
    // FIXED: Properly construct the API URL
    $base_url = str_replace(['"', 'GEMINI_API_KEY'], ['', $api_key], GEMINI_API_ENDPOINT);
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    [
                        'text' => $message
                    ]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 100,
        ]
    ];
    
    $options = [
        'http' => [
            'header' => [
                'Content-Type: application/json',
                'User-Agent: ODFEL-ChatBot-Test/1.0'
            ],
            'method' => 'POST',
            'content' => json_encode($data),
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($base_url, false, $context);
    
    if ($response === false) {
        $error = error_get_last();
        throw new Exception('Failed to connect to Gemini API: ' . ($error['message'] ?? 'Unknown error'));
    }
    
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from API');
    }
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return trim($result['candidates'][0]['content']['parts'][0]['text']);
    }
    
    if (isset($result['error'])) {
        throw new Exception('Gemini API Error: ' . $result['error']['message']);
    }
    
    throw new Exception('Unexpected API response format: ' . json_encode($result));
}
?>