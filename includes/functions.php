<?php
require_once __DIR__ . '/db_connect.php';

// Constants - Check if already defined to prevent redeclaration
if (!defined('CSRF_TOKEN_NAME')) {
    define('CSRF_TOKEN_NAME', 'csrf_token');
}

if (!defined('GEMINI_API_ENDPOINT')) {
    define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$gemini_api_key}');
}

// Initialize session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
$db = new Database();

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Additional sanitization for output
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Require admin access
function requireAdmin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    
    if (!isAdmin()) {
        redirect('index.php');
    }
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Get user by ID
function getUserById($id) {
    global $db;
    try {
        $db->query("SELECT * FROM users WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    } catch (Exception $e) {
        logActivity('Error getting user by ID: ' . $e->getMessage());
        return false;
    }
}

// Get user by username
function getUserByUsername($username) {
    global $db;
    try {
        $db->query("SELECT * FROM users WHERE username = :username");
        $db->bind(':username', $username);
        return $db->single();
    } catch (Exception $e) {
        logActivity('Error getting user by username: ' . $e->getMessage());
        return false;
    }
}

// Get setting value
function getSetting($key) {
    global $db;
    try {
        $db->query("SELECT setting_value FROM settings WHERE setting_key = :key");
        $db->bind(':key', $key);
        $result = $db->single();
        return $result ? $result['setting_value'] : null;
    } catch (Exception $e) {
        // If settings table doesn't exist, return defaults
        $defaults = [
            'site_name' => 'ODFEL ChatBot',
            'bot_name' => 'ODFEL Assistant',
            'max_message_length' => '500',
            'gemini_api_key' => 'AIzaSyBu8WkfAboPIDMkVMMwixahwaZzQxSEUBw',
        ];
        return $defaults[$key] ?? null;
    }
}

// Update setting
function updateSetting($key, $value) {
    global $db;
    try {
        // Try to update first
        $db->query("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
        $db->bind(':key', $key);
        $db->bind(':value', $value);
        
        if ($db->execute() && $db->rowCount() > 0) {
            return true;
        }
        
        // If no rows affected, insert new setting
        $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)");
        $db->bind(':key', $key);
        $db->bind(':value', $value);
        return $db->execute();
        
    } catch (Exception $e) {
        logActivity('Error updating setting ' . $key . ': ' . $e->getMessage());
        return false;
    }
}

// Get recent messages
function getRecentMessages($limit = 50) {
    global $db;
    try {
        $db->query("SELECT m.*, u.username as user_username FROM messages m 
                    LEFT JOIN users u ON m.user_id = u.id 
                    ORDER BY m.created_at DESC LIMIT :limit");
        $db->bind(':limit', $limit, PDO::PARAM_INT);
        $messages = $db->resultset();
        
        // Fix usernames for deleted users
        foreach ($messages as &$message) {
            if (!$message['user_username'] && !$message['is_bot']) {
                $message['username'] = $message['username'] ?: 'Deleted User';
            } else if (!$message['is_bot']) {
                $message['username'] = $message['user_username'];
            }
        }
        
        return array_reverse($messages); // Return in chronological order
    } catch (Exception $e) {
        logActivity('Error getting recent messages: ' . $e->getMessage());
        return [];
    }
}

// Save message
function saveMessage($user_id, $username, $message, $is_bot = false) {
    global $db;
    try {
        $db->query("INSERT INTO messages (user_id, username, message, is_bot, created_at) VALUES (:user_id, :username, :message, :is_bot, NOW())");
        $db->bind(':user_id', $user_id);
        $db->bind(':username', $username);
        $db->bind(':message', $message);
        $db->bind(':is_bot', $is_bot ? 1 : 0);
        
        if ($db->execute()) {
            logActivity("Message saved: " . ($is_bot ? 'Bot' : $username));
            return $db->lastInsertId();
        }
        return false;
    } catch (Exception $e) {
        logActivity('Error saving message: ' . $e->getMessage());
        return false;
    }
}

// Check if user exists
function userExists($username, $email) {
    global $db;
    try {
        $db->query("SELECT id FROM users WHERE username = :username OR email = :email");
        $db->bind(':username', $username);
        $db->bind(':email', $email);
        return $db->single() !== false;
    } catch (Exception $e) {
        logActivity('Error checking if user exists: ' . $e->getMessage());
        return false;
    }
}

// Create new user
function createUser($username, $email, $password, $role = 'user') {
    global $db;
    try {
        // Check if user already exists
        if (userExists($username, $email)) {
            return false;
        }
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $db->query("INSERT INTO users (username, email, password_hash, role, is_active, created_at) VALUES (:username, :email, :password_hash, :role, 1, NOW())");
        $db->bind(':username', $username);
        $db->bind(':email', $email);
        $db->bind(':password_hash', $password_hash);
        $db->bind(':role', $role);
        
        if ($db->execute()) {
            logActivity("New user created: $username ($email)");
            return true;
        }
        return false;
    } catch (Exception $e) {
        logActivity('Error creating user: ' . $e->getMessage());
        return false;
    }
}

// Authenticate user
function authenticateUser($identifier, $password) {
    global $db;
    try {
        // Check if identifier is email or username
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $db->query("SELECT * FROM users WHERE email = :identifier AND is_active = 1");
        } else {
            $db->query("SELECT * FROM users WHERE username = :identifier AND is_active = 1");
        }
        
        $db->bind(':identifier', $identifier);
        $user = $db->single();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return false;
    } catch (Exception $e) {
        logActivity('Authentication error: ' . $e->getMessage());
        return false;
    }
}

// Login user
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    
    // Update last login
    updateLastLogin($user['id']);
    
    logActivity("User logged in: " . $user['username']);
}

// Update last login
function updateLastLogin($user_id) {
    global $db;
    try {
        $db->query("UPDATE users SET last_login = NOW() WHERE id = :id");
        $db->bind(':id', $user_id);
        $db->execute();
    } catch (Exception $e) {
        logActivity("Failed to update last login for user ID: $user_id");
    }
}

// Logout user
function logoutUser() {
    $username = $_SESSION['username'] ?? 'Unknown';
    
    // Destroy session
    session_unset();
    session_destroy();
    
    logActivity("User logged out: $username");
}

// Check session timeout (30 minutes)
function checkSessionTimeout() {
    if (isLoggedIn()) {
        $timeout = 30 * 60; // 30 minutes
        
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            logoutUser();
            redirect('login.php?timeout=1');
        }
        
        $_SESSION['last_activity'] = time();
    }
}

// Get bot response from Gemini API
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
        
        if (isset($result['error'])) {
            logActivity('Gemini API Error: ' . $result['error']['message']);
            return "I'm experiencing technical difficulties. Please try again later.";
        }
        
        return "I'm having trouble understanding right now. Please try rephrasing your message.";
        
    } catch (Exception $e) {
        logActivity("Bot response error: " . $e->getMessage());
        return "I'm experiencing technical difficulties. Please try again later.";
    }
}

// Get comprehensive admin statistics
// function getAdminStats() {
//     global $db;
    
//     $stats = [];
    
//     try {
//         // Total users
//         $db->query("SELECT COUNT(*) as count FROM users");
//         $stats['total_users'] = $db->single()['count'];
        
//         // New users today
//         $db->query("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
//         $stats['new_users_today'] = $db->single()['count'];
        
//         // Total messages
//         $db->query("SELECT COUNT(*) as count FROM messages");
//         $stats['total_messages'] = $db->single()['count'];
        
//         // Messages today
//         $db->query("SELECT COUNT(*) as count FROM messages WHERE DATE(created_at) = CURDATE()");
//         $stats['messages_today'] = $db->single()['count'];
        
//         // Bot messages
//         $db->query("SELECT COUNT(*) as count FROM messages WHERE is_bot = 1");
//         $stats['bot_messages'] = $db->single()['count'];
        
//         // Active users (last 24 hours)
//         $db->query("SELECT COUNT(DISTINCT user_id) as count FROM messages WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND user_id IS NOT NULL");
//         $stats['active_users'] = $db->single()['count'];
        
//         // Admin users
//         $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
//         $stats['admin_users'] = $db->single()['count'];
        
//         // Calculate percentages and averages
//         $stats['bot_percentage'] = $stats['total_messages'] > 0 ? round(($stats['bot_messages'] / $stats['total_messages']) * 100, 1) : 0;
//         $stats['avg_messages_per_user'] = $stats['total_users'] > 0 ? round($stats['total_messages'] / $stats['total_users'], 1) : 0;
        
//         // Bot status
//         $api_key = getSetting('gemini_api_key');
//         $stats['bot_status'] = !empty($api_key) ? 'active' : 'inactive';
//         $stats['bot_accuracy'] = 95; // Placeholder
        
//         // System stats (placeholders)
//         $stats['system_uptime'] = '99.9%';
//         $stats['storage_used'] = 25;
        
//     } catch (Exception $e) {
//         logActivity("Error getting admin stats: " . $e->getMessage());
//         // Return default values on error
//         $stats = [
//             'total_users' => 0,
//             'new_users_today' => 0,
//             'total_messages' => 0,
//             'messages_today' => 0,
//             'bot_messages' => 0,
//             'bot_percentage' => 0,
//             'active_users' => 0,
//             'admin_users' => 0,
//             'avg_messages_per_user' => 0,
//             'bot_status' => 'inactive',
//             'bot_accuracy' => 0,
//             'system_uptime' => '0%',
//             'storage_used' => 0
//         ];
//     }
    
//     return $stats;
// }

// Get all users for admin management
// function getAllUsers() {
//     global $db;
//     try {
//         $db->query("
//             SELECT u.*, 
//                    COUNT(m.id) as message_count
//             FROM users u 
//             LEFT JOIN messages m ON u.id = m.user_id 
//             GROUP BY u.id 
//             ORDER BY u.created_at DESC
//         ");
        
//         return $db->resultset();
//     } catch (Exception $e) {
//         logActivity("Error getting all users: " . $e->getMessage());
//         return [];
//     }
// }

// Toggle user status
// function toggleUserStatus($user_id) {
//     global $db;
//     try {
//         $db->query("UPDATE users SET is_active = NOT is_active WHERE id = :id");
//         $db->bind(':id', $user_id);
//         return $db->execute();
//     } catch (Exception $e) {
//         logActivity("Error toggling user status: " . $e->getMessage());
//         return false;
//     }
// }

// Change user role
// function changeUserRole($user_id, $new_role) {
//     global $db;
    
//     if (!in_array($new_role, ['user', 'admin'])) {
//         return false;
//     }
    
//     try {
//         $db->query("UPDATE users SET role = :role WHERE id = :id");
//         $db->bind(':role', $new_role);
//         $db->bind(':id', $user_id);
//         return $db->execute();
//     } catch (Exception $e) {
//         logActivity("Error changing user role: " . $e->getMessage());
//         return false;
//     }
// }

// Delete user
// function deleteUser($user_id) {
//     global $db;
//     try {
//         // Delete user's messages first
//         $db->query("DELETE FROM messages WHERE user_id = :id");
//         $db->bind(':id', $user_id);
//         $db->execute();
        
//         // Delete user
//         $db->query("DELETE FROM users WHERE id = :id");
//         $db->bind(':id', $user_id);
//         return $db->execute();
//     } catch (Exception $e) {
//         logActivity("Error deleting user: " . $e->getMessage());
//         return false;
//     }
// }

// Get user statistics
function getUserStats($user_id) {
    global $db;
    
    try {
        // Total messages
        $db->query("SELECT COUNT(*) as count FROM messages WHERE user_id = :user_id");
        $db->bind(':user_id', $user_id);
        $total_messages = $db->single()['count'];
        
        // Days active
        $db->query("SELECT COUNT(DISTINCT DATE(created_at)) as count FROM messages WHERE user_id = :user_id");
        $db->bind(':user_id', $user_id);
        $days_active = $db->single()['count'];
        
        // Last active
        $db->query("SELECT MAX(created_at) as last_active FROM messages WHERE user_id = :user_id");
        $db->bind(':user_id', $user_id);
        $last_active_result = $db->single();
        $last_active = $last_active_result['last_active'] ? timeAgo($last_active_result['last_active']) : 'Never';
        
        return [
            'total_messages' => $total_messages,
            'days_active' => $days_active,
            'last_active' => $last_active
        ];
    } catch (Exception $e) {
        logActivity("Error getting user stats: " . $e->getMessage());
        return [
            'total_messages' => 0,
            'days_active' => 0,
            'last_active' => 'Never'
        ];
    }
}

// Format time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' min ago';
    if ($time < 86400) return floor($time/3600) . ' hr ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}

// Log activity
function logActivity($message) {
    $logDir = __DIR__ . '/../logs';
    $logFile = $logDir . '/chatbot.log';
    
    // Create logs directory if it doesn't exist
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0755, true)) {
            // If mkdir fails, try to log to error_log instead
            error_log("Failed to create logs directory: $logDir");
            error_log("ChatBot Log: $message");
            return;
        }
    }
    
    $log = date('Y-m-d H:i:s') . " - " . $message . PHP_EOL;
    if (!file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX)) {
        error_log("Failed to write to chatbot log file: $logFile");
        error_log("ChatBot Log: $message");
    }
}

// Check session timeout on every page load
checkSessionTimeout();
?>