<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

// Get the message
$message = $_POST['message'] ?? '';
if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message is required']);
    exit;
}

try {
    // Get API key and other settings
    $api_key = getSetting('gemini_api_key');
    $bot_name = getSetting('bot_name') ?: 'ODFEL Assistant';
    $bot_personality = getSetting('bot_personality') ?: 'You are a helpful and friendly AI assistant.';
    $max_tokens = (int)(getSetting('bot_response_length') ?: 200);

    if (empty($api_key)) {
        throw new Exception("Bot is not configured properly");
    }

    // Form the complete endpoint URL with the API key
    $endpoint = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=" . $api_key;
    
    // Prepare request data with system prompt
    $data = [
        'contents' => [
            [
                'parts' => [
                    [
                        'text' => "$bot_personality\nUser: $message\nAssistant:"
                    ]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => $max_tokens,
            'topP' => 0.8,
            'topK' => 40
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
        ]
    ]);

    // Execute request
    $response = curl_exec($ch);
    
    if ($response === false) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        $error = json_decode($response, true);
        logActivity('Bot API Error: ' . $response);
        throw new Exception($error['error']['message'] ?? 'API request failed with status ' . $httpCode);
    }

    $result = json_decode($response, true);

    // Check for API error response
    if (isset($result['error'])) {
        logActivity('Bot API Error: ' . print_r($result['error'], true));
        throw new Exception($result['error']['message'] ?? 'Unknown API error');
    }

    // Extract the response text
    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception("Unexpected API response format");
    }

    $botResponse = trim($result['candidates'][0]['content']['parts'][0]['text']);

    // Save the interaction to the database
    saveMessage(
        $_SESSION['user_id'] ?? null,
        $bot_name,
        $botResponse,
        true
    );

    // Return success response
    echo json_encode([
        'success' => true,
        'response' => $botResponse
    ]);

} catch (Exception $e) {
    // Log the error with more details
    logActivity("Bot error: " . $e->getMessage() . "\nRequest: " . ($message ?? '') . "\nResponse: " . ($response ?? 'No response'));
    
    echo json_encode([
        'success' => false,
        'message' => "I'm sorry, but I encountered an error: " . $e->getMessage()
    ]);
}
?>