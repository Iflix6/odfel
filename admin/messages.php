<?php
require_once 'includes/admin_functions.php';

// Require admin access
requireAdmin();

$success = '';
$error = '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $message_id = (int)($_POST['message_id'] ?? 0);
    
    if ($message_id > 0 && $action === 'delete') {
        if (deleteMessage($message_id)) {
            $success = 'Message deleted successfully';
        } else {
            $error = 'Failed to delete message';
        }
    }
}

// Get messages for current page
$messages = getAllMessages($limit, $offset);

// Get total count for pagination
$total_messages = getMessageCount();
$total_pages = ceil($total_messages / $limit);

$page_title = 'Manage Messages';
include 'includes/admin_header.php';
?>

<div class="messages-management">
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="messages-header">
        <h3>
            <i class="fas fa-comments"></i>
            Message Management
        </h3>
        <div class="messages-stats">
            <span class="stat-badge">
                <i class="fas fa-comment"></i>
                <?php echo $total_messages; ?> Total Messages
            </span>
            <span class="stat-badge">
                <i class="fas fa-robot"></i>
                <?php echo count(array_filter($messages, function($m) { return $m['is_bot']; })); ?> Bot Messages
            </span>
        </div>
    </div>
    
    <div class="messages-container">
        <?php foreach ($messages as $message): ?>
        <div class="message-item <?php echo $message['is_bot'] ? 'bot-message' : 'user-message'; ?>">
            <div class="message-header">
                <div class="message-user">
                    <div class="message-avatar">
                        <?php if ($message['is_bot']): ?>
                            <i class="fas fa-robot"></i>
                        <?php else: ?>
                            <?php echo strtoupper(substr($message['username'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="message-info">
                        <div class="username"><?php echo htmlspecialchars($message['username']); ?></div>
                        <div class="message-time"><?php echo timeAgo($message['created_at']); ?></div>
                    </div>
                </div>
                
                <div class="message-actions">
                    <button class="btn-action danger" onclick="deleteMessage(<?php echo $message['id']; ?>)" title="Delete Message">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="message-content">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </div>
            
            <div class="message-meta">
                <span class="message-id">ID: <?php echo $message['id']; ?></span>
                <span class="message-date"><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="page-btn">
                <i class="fas fa-chevron-left"></i>
                Previous
            </a>
        <?php endif; ?>
        
        <span class="page-info">
            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
        </span>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="page-btn">
                Next
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Hidden form for actions -->
<form id="messageActionForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" id="actionType">
    <input type="hidden" name="message_id" id="actionMessageId">
</form>

<style>
.messages-management {
    max-width: 1000px;
    margin: 0 auto;
}

.messages-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.messages-header h3 {
    margin: 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.messages-stats {
    display: flex;
    gap: 15px;
}

.stat-badge {
    background: #f8f9fa;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
}

.messages-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message-item {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.message-item:hover {
    transform: translateY(-2px);
}

.message-item.bot-message {
    border-left: 4px solid #ffc107;
}

.message-item.user-message {
    border-left: 4px solid #667eea;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}

.message-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.message-item.bot-message .message-avatar {
    background: #ffc107;
    color: #333;
}

.username {
    font-weight: 600;
    color: #333;
}

.message-time {
    font-size: 0.8rem;
    color: #666;
}

.message-content {
    padding: 20px;
    line-height: 1.6;
    color: #333;
}

.message-meta {
    padding: 10px 20px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    color: #666;
}

.btn-action {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 5px;
    background: #f8f9fa;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-action.danger:hover {
    background: #dc3545;
    color: white;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-top: 30px;
    padding: 20px;
}

.page-btn {
    padding: 10px 20px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s ease;
    display: flex;
    align-items: center;
    gap: 5px;
}

.page-btn:hover {
    background: #5a6fd8;
}

.page-info {
    font-weight: 600;
    color: #666;
}

@media (max-width: 768px) {
    .messages-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .messages-stats {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .message-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .message-meta {
        flex-direction: column;
        gap: 5px;
    }
}
</style>

<script>
function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        document.getElementById('actionType').value = 'delete';
        document.getElementById('actionMessageId').value = messageId;
        document.getElementById('messageActionForm').submit();
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>
