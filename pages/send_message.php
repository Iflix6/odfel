<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Add error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("=== SEND MESSAGE DEBUG START ===");
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION data: " . print_r($_SESSION, true));

header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    error_log("User not logged in");
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get message content
$message = trim($_POST['message'] ?? '');
error_log("Message content: " . $message);

if (empty($message)) {
    error_log("Empty message");
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

if (strlen($message) > 500) {
    error_log("Message too long: " . strlen($message));
    echo json_encode(['success' => false, 'message' => 'Message too long (max 500 characters)']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    error_log("Attempting to insert message for user: $user_id ($username)");
    
    // Initialize database connection if not available
    if (!isset($db) || !$db) {
        $db = getDBConnection(); // Adjust this based on your functions.php
        if (!$db) {
            throw new Exception("Database connection not available");
        }
    }
    
    // Test database connection first
    $db->query("SELECT 1");
    $test = $db->single();
    error_log("Database connection test: " . ($test ? "OK" : "FAILED"));
    
    // Insert message into chatroom_messages table
    $sql = "INSERT INTO chatroom_messages (user_id, username, message, is_bot, created_at) 
            VALUES (:user_id, :username, :message, 0, NOW())";
    
    error_log("SQL: $sql");
    error_log("Parameters: user_id=$user_id, username=$username, message=$message");
    
    $db->query($sql);
    $db->bind(':user_id', $user_id);
    $db->bind(':username', $username);
    $db->bind(':message', $message);
    
    $execute_result = $db->execute();
    error_log("Execute result: " . ($execute_result ? "SUCCESS" : "FAILED"));
    
    if ($execute_result) {
        $message_id = $db->lastInsertId();
        error_log("Message inserted successfully with ID: $message_id");
        
        // Check if message mentions bot
        $bot_response = null;
        if (preg_match('/@bot\b/i', $message) || stripos($message, 'bot') !== false) {
            error_log("Bot mentioned, getting response");
            $bot_response = getBotResponse($message);
            if ($bot_response) {
                // Insert bot response
                $bot_sql = "INSERT INTO chatroom_messages (user_id, username, message, is_bot, created_at) 
                           VALUES (NULL, 'ODFEL Assistant', :message, 1, NOW())";
                $db->query($bot_sql);
                $db->bind(':message', $bot_response);
                $bot_result = $db->execute();
                error_log("Bot response inserted: " . ($bot_result ? "SUCCESS" : "FAILED"));
            }
        }
        
        // Log activity
        if (function_exists('logActivity')) {
            logActivity("Message sent by user: $username");
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Message sent successfully',
            'message_id' => $message_id,
            'bot_response' => $bot_response,
            'debug' => [
                'user_id' => $user_id,
                'username' => $username,
                'sql' => $sql
            ]
        ]);
    } else {
        // Get more detailed error information
        $error_info = $db->getError(); // You may need to implement this method
        error_log("Failed to execute query. Error: " . print_r($error_info, true));
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to send message',
            'debug' => [
                'sql' => $sql,
                'error' => $error_info
            ]
        ]);
    }
    
} catch (Exception $e) {
    error_log("Exception in send_message: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}

error_log("=== SEND MESSAGE DEBUG END ===");

function getBotResponse($user_message) {
    // Check if getSetting function exists
    if (!function_exists('getSetting')) {
        error_log("getSetting function not available");
        return "I'm sorry, but I'm not configured properly. Please contact an administrator.";
    }
    
    $api_key = getSetting('gemini_api_key');
    
    if (empty($api_key)) {
        error_log("Gemini API key not configured");
        return "I'm sorry, but I'm not configured properly. Please contact an administrator.";
    }
    
    try {
        // Check if GEMINI_API_ENDPOINT is defined
        if (!defined('GEMINI_API_ENDPOINT')) {
            define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');
        }
        
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
            error_log("Failed to get response from Gemini API");
            return "I'm having trouble connecting right now. Please try again later.";
        }
        
        $result = json_decode($response, true);
        error_log("Gemini API response: " . print_r($result, true));
        
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($result['candidates'][0]['content']['parts'][0]['text']);
        }
        
        error_log("Unexpected API response structure");
        return "I'm having trouble understanding right now. Please try rephrasing your message.";
        
    } catch (Exception $e) {
        error_log("Bot response error: " . $e->getMessage());
        if (function_exists('logActivity')) {
            logActivity("Bot response error: " . $e->getMessage());
        }
        return "I'm experiencing technical difficulties. Please try again later.";
    }
}
?>