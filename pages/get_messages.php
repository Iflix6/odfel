<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Add error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("=== GET MESSAGES DEBUG START ===");

header('Content-Type: application/json');

// Require login for most actions
$action = $_GET['action'] ?? 'messages';
error_log("Action: $action");

if ($action !== 'stats' && !isLoggedIn()) {
    error_log("User not authenticated for action: $action");
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    // Initialize database connection if not already done
    if (!isset($db) || !$db) {
        // Try to get database connection from functions.php
        $db = getDBConnection(); // You may need to adjust this based on your functions.php
        if (!$db) {
            throw new Exception("Could not establish database connection");
        }
    }
    
    switch ($action) {
        case 'messages':
            $last_id = intval($_GET['last_id'] ?? 0);
            error_log("Getting messages after ID: $last_id");
            
            // Simplified query first to test basic functionality
            $sql = "SELECT id, user_id, username, message, is_bot, created_at 
                    FROM chatroom_messages 
                    WHERE id > :last_id 
                    ORDER BY created_at ASC 
                    LIMIT 50";
            
            error_log("SQL Query: $sql");
            error_log("Parameter last_id: $last_id");
            
            $db->query($sql);
            $db->bind(':last_id', $last_id);
            $messages = $db->resultset();
            
            error_log("Raw messages result: " . print_r($messages, true));
            
            // Ensure messages is an array
            if (!is_array($messages)) {
                $messages = [];
            }
            
            error_log("Found " . count($messages) . " messages");
            
            echo json_encode([
                'success' => true,
                'messages' => $messages,
                'debug' => [
                    'last_id' => $last_id,
                    'query' => $sql,
                    'count' => count($messages)
                ]
            ]);
            break;
            
        case 'online_count':
            // Count users active in last 5 minutes
            $sql = "SELECT COUNT(DISTINCT user_id) as count 
                    FROM chatroom_messages 
                    WHERE created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
                    AND user_id IS NOT NULL";
            
            $db->query($sql);
            $result = $db->single();
            
            echo json_encode([
                'success' => true,
                'count' => $result['count'] ?? 0
            ]);
            break;
            
        case 'stats':
            // Get basic stats for landing page
            $db->query("SELECT COUNT(DISTINCT user_id) as users FROM chatroom_messages WHERE user_id IS NOT NULL");
            $users_result = $db->single();
            
            $db->query("SELECT COUNT(*) as messages FROM chatroom_messages");
            $messages_result = $db->single();
            
            echo json_encode([
                'success' => true,
                'users' => $users_result['users'] ?? 0,
                'messages' => $messages_result['messages'] ?? 0
            ]);
            break;
            
        case 'test_db':
            // Test database connection and table existence
            $db->query("SHOW TABLES LIKE 'chatroom_messages'");
            $table_exists = $db->single();
            
            $db->query("SELECT COUNT(*) as total FROM chatroom_messages");
            $total_messages = $db->single();
            
            echo json_encode([
                'success' => true,
                'table_exists' => !empty($table_exists),
                'total_messages' => $total_messages['total'] ?? 0,
                'db_connection' => 'OK'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Exception in get_messages: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'error' => $e->getMessage(),
        'debug' => [
            'action' => $action,
            'db_available' => isset($db) ? 'yes' : 'no'
        ]
    ]);
}

error_log("=== GET MESSAGES DEBUG END ===");
?>