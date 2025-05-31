<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Require login for most actions
$action = $_GET['action'] ?? 'messages';

if ($action !== 'stats' && !isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    global $db;
    
    switch ($action) {
        case 'messages':
            $last_id = intval($_GET['last_id'] ?? 0);
            
            $db->query("SELECT m.*, u.username as user_username FROM messages m LEFT JOIN users u ON m.user_id = u.id WHERE m.id > :last_id ORDER BY m.created_at ASC LIMIT 50");
            $db->bind(':last_id', $last_id);
            $messages = $db->resultset();
            
            // Fix username for messages where user might be deleted
            foreach ($messages as &$message) {
                if (!$message['user_username'] && !$message['is_bot']) {
                    $message['username'] = $message['username'] ?: 'Deleted User';
                } else if (!$message['is_bot']) {
                    $message['username'] = $message['user_username'];
                }
            }
            
            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
            break;
            
        case 'online_count':
            // Count users active in last 5 minutes
            $db->query("SELECT COUNT(DISTINCT user_id) as count FROM messages WHERE created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND user_id IS NOT NULL");
            $result = $db->single();
            
            echo json_encode([
                'success' => true,
                'count' => $result['count'] ?? 0
            ]);
            break;
            
        case 'stats':
            // Get basic stats for landing page
            $db->query("SELECT COUNT(DISTINCT user_id) as users FROM messages WHERE user_id IS NOT NULL");
            $users_result = $db->single();
            
            $db->query("SELECT COUNT(*) as messages FROM messages");
            $messages_result = $db->single();
            
            echo json_encode([
                'success' => true,
                'users' => $users_result['users'] ?? 0,
                'messages' => $messages_result['messages'] ?? 0
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    logActivity("Error in get_messages.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
