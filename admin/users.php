<?php
require_once 'includes/admin_functions.php';

// Require admin access
requireAdmin();

$success = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    if ($user_id > 0) {
        switch ($action) {
            case 'toggle_status':
                if (toggleUserStatus($user_id)) {
                    $success = 'User status updated successfully';
                } else {
                    $error = 'Failed to update user status';
                }
                break;
                
            case 'change_role':
                $new_role = $_POST['role'] ?? '';
                if (changeUserRole($user_id, $new_role)) {
                    $success = 'User role updated successfully';
                } else {
                    $error = 'Failed to update user role';
                }
                break;
                
            case 'delete':
                if (deleteUser($user_id)) {
                    $success = 'User deleted successfully';
                } else {
                    $error = 'Failed to delete user';
                }
                break;
        }
    }
}

// Get all users
$users = getAllUsers();

$page_title = 'Manage Users';
include 'includes/admin_header.php';
?>

<div class="users-management">
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="users-header">
        <h3>
            <i class="fas fa-users"></i>
            User Management
        </h3>
        <div class="users-stats">
            <span class="stat-badge">
                <i class="fas fa-user"></i>
                <?php echo count($users); ?> Total Users
            </span>
            <span class="stat-badge">
                <i class="fas fa-user-shield"></i>
                <?php echo count(array_filter($users, function($u) { return $u['role'] === 'admin'; })); ?> Admins
            </span>
        </div>
    </div>
    
    <div class="users-table-container">
        <table class="users-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Messages</th>
                    <th>Last Active</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="user-row <?php echo $user['is_active'] ? '' : 'inactive'; ?>">
                    <td class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="username"><?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="user-id">ID: <?php echo $user['id']; ?></div>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span class="role-badge <?php echo $user['role']; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td><?php echo $user['message_count']; ?></td>
                    <td><?php echo $user['last_message'] ? timeAgo($user['last_message']) : 'Never'; ?></td>
                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                    <td class="actions">
                        <div class="action-buttons">
                            <button class="btn-action" onclick="toggleUserStatus(<?php echo $user['id']; ?>)" title="Toggle Status">
                                <i class="fas fa-power-off"></i>
                            </button>
                            
                            <button class="btn-action" onclick="changeUserRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')" title="Change Role">
                                <i class="fas fa-user-cog"></i>
                            </button>
                            
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <button class="btn-action danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" title="Delete User">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Hidden forms for actions -->
<form id="userActionForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" id="actionType">
    <input type="hidden" name="user_id" id="actionUserId">
    <input type="hidden" name="role" id="actionRole">
</form>

<style>
.users-management {
    max-width: 1400px;
    margin: 0 auto;
}

.users-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.users-header h3 {
    margin: 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.users-stats {
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

.users-table-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    overflow: hidden;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th {
    background: #f8f9fa;
    padding: 15px 10px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 1px solid #eee;
}

.users-table td {
    padding: 15px 10px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.user-row.inactive {
    opacity: 0.6;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
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

.username {
    font-weight: 600;
    color: #333;
}

.user-id {
    font-size: 0.8rem;
    color: #666;
}

.role-badge {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.role-badge.admin {
    background: #dc3545;
    color: white;
}

.role-badge.user {
    background: #28a745;
    color: white;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.action-buttons {
    display: flex;
    gap: 5px;
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

.btn-action:hover {
    background: #667eea;
    color: white;
}

.btn-action.danger:hover {
    background: #dc3545;
    color: white;
}

@media (max-width: 768px) {
    .users-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .users-stats {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .users-table-container {
        overflow-x: auto;
    }
    
    .users-table {
        min-width: 800px;
    }
}
</style>

<script>
function toggleUserStatus(userId) {
    if (confirm('Are you sure you want to toggle this user\'s status?')) {
        document.getElementById('actionType').value = 'toggle_status';
        document.getElementById('actionUserId').value = userId;
        document.getElementById('userActionForm').submit();
    }
}

function changeUserRole(userId, currentRole) {
    const newRole = currentRole === 'admin' ? 'user' : 'admin';
    const roleText = newRole === 'admin' ? 'administrator' : 'regular user';
    
    if (confirm(`Are you sure you want to make this user a ${roleText}?`)) {
        document.getElementById('actionType').value = 'change_role';
        document.getElementById('actionUserId').value = userId;
        document.getElementById('actionRole').value = newRole;
        document.getElementById('userActionForm').submit();
    }
}

function deleteUser(userId, username) {
    if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone and will delete all their messages.`)) {
        document.getElementById('actionType').value = 'delete';
        document.getElementById('actionUserId').value = userId;
        document.getElementById('userActionForm').submit();
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>
