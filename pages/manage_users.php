<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin access
requireAdmin();

$success = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $action = $_POST['action'] ?? '';
        $user_id = intval($_POST['user_id'] ?? 0);
        
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
                
            case 'delete_user':
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
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>
            <i class="fas fa-users"></i>
            Manage Users
        </h1>
        <p>Manage user accounts, roles, and permissions</p>
        <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="admin-card">
        <h3>
            <i class="fas fa-list"></i>
            All Users (<?php echo count($users); ?>)
        </h3>
        
        <div style="overflow-x: auto;">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th data-sort="id">ID</th>
                        <th data-sort="username">Username</th>
                        <th data-sort="email">Email</th>
                        <th data-sort="role">Role</th>
                        <th data-sort="status">Status</th>
                        <th data-sort="created_at">Joined</th>
                        <th>Messages</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td data-id="<?php echo $user['id']; ?>"><?php echo $user['id']; ?></td>
                        <td data-username="<?php echo htmlspecialchars($user['username']); ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </td>
                        <td data-email="<?php echo htmlspecialchars($user['email']); ?>">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </td>
                        <td data-role="<?php echo $user['role']; ?>">
                            <span style="background: <?php echo $user['role'] === 'admin' ? '#667eea' : '#28a745'; ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td data-status="<?php echo $user['is_active']; ?>">
                            <span style="background: <?php echo $user['is_active'] ? '#28a745' : '#dc3545'; ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td data-created_at="<?php echo $user['created_at']; ?>">
                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                        </td>
                        <td><?php echo $user['message_count']; ?></td>
                        <td>
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <!-- Toggle Status -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: <?php echo $user['is_active'] ? '#dc3545' : '#28a745'; ?>;">
                                        <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                                
                                <!-- Change Role -->
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="role" value="<?php echo $user['role'] === 'admin' ? 'user' : 'admin'; ?>">
                                    <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: #667eea;">
                                        Make <?php echo $user['role'] === 'admin' ? 'User' : 'Admin'; ?>
                                    </button>
                                </form>
                                
                                <!-- Delete User -->
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: #dc3545;">
                                        Delete
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
function getAllUsers() {
    global $db;
    
    $db->query("
        SELECT u.*, 
               COUNT(m.id) as message_count
        FROM users u 
        LEFT JOIN messages m ON u.id = m.user_id 
        GROUP BY u.id 
        ORDER BY u.created_at DESC
    ");
    
    return $db->resultset();
}

function toggleUserStatus($user_id) {
    global $db;
    
    try {
        $db->query("UPDATE users SET is_active = NOT is_active WHERE id = :id");
        $db->bind(':id', $user_id);
        return $db->execute();
    } catch (Exception $e) {
        return false;
    }
}

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
        return false;
    }
}

function deleteUser($user_id) {
    global $db;
    
    try {
        // Delete user's messages first
        $db->query("DELETE FROM messages WHERE user_id = :id");
        $db->bind(':id', $user_id);
        $db->execute();
        
        // Delete user
        $db->query("DELETE FROM users WHERE id = :id");
        $db->bind(':id', $user_id);
        return $db->execute();
    } catch (Exception $e) {
        return false;
    }
}

include '../includes/footer.php';
?>