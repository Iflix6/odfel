<?php
// Include base files
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Admin-specific functions

// Get comprehensive admin statistics
function getAdminStats() {
    global $db;
    
    $stats = [];
    
    try {
        // Total users
        $db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $db->single()['count'];
        
        // New users today
        $db->query("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
        $stats['new_users_today'] = $db->single()['count'];
        
        // Total messages
        $db->query("SELECT COUNT(*) as count FROM messages");
        $stats['total_messages'] = $db->single()['count'];
        
        // Messages today
        $db->query("SELECT COUNT(*) as count FROM messages WHERE DATE(created_at) = CURDATE()");
        $stats['messages_today'] = $db->single()['count'];
        
        // Bot messages
        $db->query("SELECT COUNT(*) as count FROM messages WHERE is_bot = 1");
        $stats['bot_messages'] = $db->single()['count'];
        
        // Active users (last 24 hours)
        $db->query("SELECT COUNT(DISTINCT user_id) as count FROM messages WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND user_id IS NOT NULL");
        $stats['active_users'] = $db->single()['count'];
        
        // Admin users
        $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $stats['admin_users'] = $db->single()['count'];
        
        // Calculate percentages and averages
        $stats['bot_percentage'] = $stats['total_messages'] > 0 ? round(($stats['bot_messages'] / $stats['total_messages']) * 100, 1) : 0;
        $stats['avg_messages_per_user'] = $stats['total_users'] > 0 ? round($stats['total_messages'] / $stats['total_users'], 1) : 0;
        
        // Bot status
        $api_key = getSetting('gemini_api_key');
        $stats['bot_status'] = !empty($api_key) ? 'active' : 'inactive';
        $stats['bot_accuracy'] = 95; // Placeholder
        
        // System stats (placeholders)
        $stats['system_uptime'] = '99.9%';
        $stats['storage_used'] = 25;
        
        // Recent messages
        $db->query("SELECT m.*, u.username as user_username FROM messages m LEFT JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC LIMIT 10");
        $messages = $db->resultset();
        foreach ($messages as &$message) {
            if (!$message['user_username'] && !$message['is_bot']) {
                $message['username'] = $message['username'] ?: 'Deleted User';
            } else if (!$message['is_bot']) {
                $message['username'] = $message['user_username'];
            }
        }
        $stats['recent_messages'] = $messages;
        
        // Recent users
        $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
        $stats['recent_users'] = $db->resultset();
        
    } catch (Exception $e) {
        logActivity("Error getting admin stats: " . $e->getMessage());
        // Return default values on error
        $stats = [
            'total_users' => 0,
            'new_users_today' => 0,
            'total_messages' => 0,
            'messages_today' => 0,
            'bot_messages' => 0,
            'bot_percentage' => 0,
            'active_users' => 0,
            'admin_users' => 0,
            'avg_messages_per_user' => 0,
            'bot_status' => 'inactive',
            'bot_accuracy' => 0,
            'system_uptime' => '0%',
            'storage_used' => 0,
            'recent_messages' => [],
            'recent_users' => []
        ];
    }
    
    return $stats;
}

// Get all users for admin management
function getAllUsers() {
    global $db;
    try {
        $db->query("
            SELECT u.*, 
                   COUNT(m.id) as message_count,
                   MAX(m.created_at) as last_message
            FROM users u 
            LEFT JOIN messages m ON u.id = m.user_id 
            GROUP BY u.id 
            ORDER BY u.created_at DESC
        ");
        
        return $db->resultset();
    } catch (Exception $e) {
        logActivity("Error getting all users: " . $e->getMessage());
        return [];
    }
}

// Toggle user status
function toggleUserStatus($user_id) {
    global $db;
    try {
        $db->query("UPDATE users SET is_active = NOT is_active WHERE id = :id");
        $db->bind(':id', $user_id);
        return $db->execute();
    } catch (Exception $e) {
        logActivity("Error toggling user status: " . $e->getMessage());
        return false;
    }
}

// Change user role
function changeUserRole($user_id, $new_role) {
    global $db;
    
    if (!in_array($new_role, ['user', 'admin'])) {
        return false;
    }
    
    try {
        $db->query("UPDATE users SET role = :role WHERE id = :id");
        $db->bind(':role', $new_role);
        $db->bind(':id', $user_id);
        return $db->execute();
    } catch (Exception $e) {
        logActivity("Error changing user role: " . $e->getMessage());
        return false;
    }
}

// Delete user
function deleteUser($user_id) {
    global $db;
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Delete user's messages first
        $db->query("DELETE FROM messages WHERE user_id = :id");
        $db->bind(':id', $user_id);
        $db->execute();
        
        // Delete user
        $db->query("DELETE FROM users WHERE id = :id");
        $db->bind(':id', $user_id);
        $result = $db->execute();
        
        // Commit transaction
        $db->commit();
        
        return $result;
    } catch (Exception $e) {
        // Rollback on error
        $db->rollback();
        logActivity("Error deleting user: " . $e->getMessage());
        return false;
    }
}

// Delete message
function deleteMessage($message_id) {
    global $db;
    try {
        $db->query("DELETE FROM messages WHERE id = :id");
        $db->bind(':id', $message_id);
        return $db->execute();
    } catch (Exception $e) {
        logActivity("Error deleting message: " . $e->getMessage());
        return false;
    }
}

// Get all messages for admin
function getAllMessages($limit = 100, $offset = 0) {
    global $db;
    try {
        $db->query("
            SELECT m.*, u.username as user_username 
            FROM messages m 
            LEFT JOIN users u ON m.user_id = u.id 
            ORDER BY m.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $db->bind(':limit', $limit, PDO::PARAM_INT);
        $db->bind(':offset', $offset, PDO::PARAM_INT);
        
        $messages = $db->resultset();
        
        // Fix usernames for deleted users
        foreach ($messages as &$message) {
            if (!$message['user_username'] && !$message['is_bot']) {
                $message['username'] = $message['username'] ?: 'Deleted User';
            } else if (!$message['is_bot']) {
                $message['username'] = $message['user_username'];
            }
        }
        
        return $messages;
    } catch (Exception $e) {
        logActivity("Error getting all messages: " . $e->getMessage());
        return [];
    }
}

// Get message count for pagination
function getMessageCount() {
    global $db;
    try {
        $db->query("SELECT COUNT(*) as count FROM messages");
        $result = $db->single();
        return $result['count'];
    } catch (Exception $e) {
        logActivity("Error getting message count: " . $e->getMessage());
        return 0;
    }
}
?>
