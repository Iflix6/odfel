<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$message = trim($_POST['message'] ?? '');

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

if (strlen($message) > 500) {
    echo json_encode(['success' => false, 'message' => 'Message too long (max 500 characters)']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    // Insert message into database
    global $db;
    $db->query("INSERT INTO messages (user_id, username, message, is_bot, created_at) VALUES (:user_id, :username, :message, 0, NOW())");
    $db->bind(':user_id', $user_id);
    $db->bind(':username', $username);
    $db->bind(':message', $message);
    
    if ($db->execute()) {
        $message_id = $db->lastInsertId();
        
        // Check if message mentions bot
        if (preg_match('/@bot\b/i', $message) || stripos($message, 'bot') !== false) {
            // Send to bot for response
            $bot_response = getBotResponse($message);
            if ($bot_response) {
                // Insert bot response
                $db->query("INSERT INTO messages (user_id, username, message, is_bot, created_at) VALUES (NULL, 'ODFEL Assistant', :message, 1, NOW())");
                $db->bind(':message', $bot_response);
                $db->execute();
            }
        }
        
        // Log activity
        logActivity("Message sent by user: $username");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Message sent successfully',
            'message_id' => $message_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }
    
} catch (Exception $e) {
    logActivity("Error sending message: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}

function getBotResponse($user_message) {
    $api_key = getSetting('gemini_api_key');
    
    if (empty($api_key)) {
        return "I'm sorry, but I'm not configured properly. Please contact an administrator.";
    }
    
    try {
        $url = GEMINI_API_ENDPOINT . '?key=' . $api_key;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "You are ODFEL Assistant, a helpful AI chatbot. Respond to this message in a friendly and helpful way (keep it under 200 words): " . $user_message
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 200,
            ]
        ];
        
        $options = [
            'http' => [
                'header' => [
                    'Content-Type: application/json',
                    'User-Agent: ODFEL-ChatBot/1.0'
                ],
                'method' => 'POST',
                'content' => json_encode($data),
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            return "I'm having trouble connecting right now. Please try again later.";
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($result['candidates'][0]['content']['parts'][0]['text']);
        }
        
        return "I'm having trouble understanding right now. Please try rephrasing your message.";
        
    } catch (Exception $e) {
        logActivity("Bot response error: " . $e->getMessage());
        return "I'm experiencing technical difficulties. Please try again later.";
    }
}
?>
