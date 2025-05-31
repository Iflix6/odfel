<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin access
requireAdmin();

$success = '';
$error = '';

// Handle message actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $action = $_POST['action'] ?? '';
        $message_id = intval($_POST['message_id'] ?? 0);
        
        if ($action === 'delete_message') {
            if (deleteMessage($message_id)) {
                $success = 'Message deleted successfully';
            } else {
                $error = 'Failed to delete message';
            }
        }
    }
}

// Get pagination parameters
$page = intval($_GET['page'] ?? 1);
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Get messages with pagination
$messages = getMessagesWithPagination($offset, $per_page);
$total_messages = getTotalMessageCount();
$total_pages = ceil($total_messages / $per_page);

$page_title = 'Manage Chats';
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>
            <i class="fas fa-comments"></i>
            Manage Chat Messages
        </h1>
        <p>View and moderate chat messages and conversations</p>
        <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_messages; ?></div>
            <div class="stat-label">Total Messages</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getBotMessageCount(); ?></div>
            <div class="stat-label">Bot Messages</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getTodayMessageCount(); ?></div>
            <div class="stat-label">Today's Messages</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getActiveUsersToday(); ?></div>
            <div class="stat-label">Active Users Today</div>
        </div>
    </div>
    
    <div class="admin-card">
        <h3>
            <i class="fas fa-list"></i>
            Recent Messages
            <span style="font-size: 0.8rem; color: #666;">(Page <?php echo $page; ?> of <?php echo $total_pages; ?>)</span>
        </h3>
        
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?php echo $message['id']; ?></td>
                        <td>
                            <?php if ($message['is_bot']): ?>
                                <span style="color: #667eea;">
                                    <i class="fas fa-robot"></i>
                                    <?php echo htmlspecialchars($message['username']); ?>
                                </span>
                            <?php else: ?>
                                <?php echo htmlspecialchars($message['username']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="background: <?php echo $message['is_bot'] ? '#667eea' : '#28a745'; ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
                                <?php echo $message['is_bot'] ? 'Bot' : 'User'; ?>
                            </span>
                        </td>
                        <td style="max-width: 300px;">
                            <div style="max-height: 60px; overflow-y: auto; word-wrap: break-word;">
                                <?php echo nl2br(htmlspecialchars(substr($message['message'], 0, 200))); ?>
                                <?php if (strlen($message['message']) > 200): ?>
                                    <span style="color: #666;">...</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.9rem;">
                                <?php echo date('M j, Y', strtotime($message['created_at'])); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #666;">
                                <?php echo date('H:i:s', strtotime($message['created_at'])); ?>
                            </div>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="delete_message">
                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: #dc3545;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="margin-top: 20px; text-align: center;">
            <div style="display: inline-flex; gap: 10px; align-items: center;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="btn" style="padding: 8px 12px;">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <span style="padding: 8px 12px; background: #f8f9fa; border-radius: 5px;">
                    Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                </span>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="btn" style="padding: 8px 12px;">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
function getMessagesWithPagination($offset, $limit) {
    global $db;
    
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
    
    // Use stored username if user is deleted
    foreach ($messages as &$message) {
        if (!$message['user_username'] && !$message['is_bot']) {
            $message['username'] = $message['username'] ?: 'Deleted User';
        } else {
            $message['username'] = $message['user_username'] ?: $message['username'];
        }
    }
    
    return $messages;
}

function getTotalMessageCount() {
    global $db;
    
    $db->query("SELECT COUNT(*) as count FROM messages");
    return $db->single()['count'];
}

function getBotMessageCount() {
    global $db;
    
    $db->query("SELECT COUNT(*) as count FROM messages WHERE is_bot = 1");
    return $db->single()['count'];
}

function getTodayMessageCount() {
    global $db;
    
    $db->query("SELECT COUNT(*) as count FROM messages WHERE DATE(created_at) = CURDATE()");
    return $db->single()['count'];
}

function getActiveUsersToday() {
    global $db;
    
    $db->query("SELECT COUNT(DISTINCT user_id) as count FROM messages WHERE DATE(created_at) = CURDATE() AND user_id IS NOT NULL");
    return $db->single()['count'];
}

function deleteMessage($message_id) {
    global $db;
    
    try {
        $db->query("DELETE FROM messages WHERE id = :id");
        $db->bind(':id', $message_id);
        return $db->execute();
    } catch (Exception $e) {
        return false;
    }
}

include '../includes/footer.php';
?>