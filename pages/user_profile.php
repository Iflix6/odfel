<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require login
requireLogin();

$success = '';
$error = '';
$user = getUserById($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $email = sanitize($_POST['email'] ?? '');
            
            if (empty($email)) {
                $error = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address';
            } else {
                if (updateUserProfile($_SESSION['user_id'], $email)) {
                    $success = 'Profile updated successfully!';
                    $user['email'] = $email;
                } else {
                    $error = 'Failed to update profile';
                }
            }
        } elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error = 'Please fill in all password fields';
            } elseif (strlen($new_password) < 6) {
                $error = 'New password must be at least 6 characters long';
            } elseif ($new_password !== $confirm_password) {
                $error = 'New passwords do not match';
            } elseif (!password_verify($current_password, $user['password_hash'])) {
                $error = 'Current password is incorrect';
            } else {
                if (updateUserPassword($_SESSION['user_id'], $new_password)) {
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Failed to change password';
                }
            }
        }
    }
}

$page_title = 'User Profile';
include '../includes/header.php';
?>

<div class="container">
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 30px;">
            <h1 style="color: #667eea; text-align: center; margin-bottom: 30px;">
                <i class="fas fa-user"></i>
                User Profile
            </h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Profile Information -->
            <div style="background: #f8f9fa; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
                <h2 style="color: #333; margin-bottom: 20px;">Profile Information</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div>
                        <strong>Username:</strong><br>
                        <span style="color: #667eea; font-size: 1.1rem;"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div>
                        <strong>Email:</strong><br>
                        <span style="color: #667eea; font-size: 1.1rem;"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div>
                        <strong>Role:</strong><br>
                        <span style="color: #667eea; font-size: 1.1rem; text-transform: capitalize;"><?php echo htmlspecialchars($user['role']); ?></span>
                    </div>
                    <div>
                        <strong>Member Since:</strong><br>
                        <span style="color: #667eea; font-size: 1.1rem;"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Update Profile Form -->
            <div style="margin-bottom: 30px;">
                <h2 style="color: #333; margin-bottom: 20px;">Update Profile</h2>
                
                <form method="POST" data-validate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($user['email']); ?>"
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i>
                        Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Change Password Form -->
            <div>
                <h2 style="color: #333; margin-bottom: 20px;">Change Password</h2>
                
                <form method="POST" data-validate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            class="form-control" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="form-control" 
                            minlength="6"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-control" 
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-key"></i>
                        Change Password
                    </button>
                </form>
            </div>
        </div>
        
        <!-- User Statistics -->
        <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <h2 style="color: #333; margin-bottom: 20px;">Your Statistics</h2>
            
            <?php $user_stats = getUserStats($_SESSION['user_id']); ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 2rem; font-weight: bold; color: #667eea;"><?php echo $user_stats['total_messages']; ?></div>
                    <div style="color: #666;">Messages Sent</div>
                </div>
                
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 2rem; font-weight: bold; color: #667eea;"><?php echo $user_stats['days_active']; ?></div>
                    <div style="color: #666;">Days Active</div>
                </div>
                
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 2rem; font-weight: bold; color: #667eea;"><?php echo $user_stats['last_active']; ?></div>
                    <div style="color: #666;">Last Active</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function updateUserProfile($user_id, $email) {
    global $db;
    
    try {
        $db->query("UPDATE users SET email = :email WHERE id = :id");
        $db->bind(':email', $email);
        $db->bind(':id', $user_id);
        return $db->execute();
    } catch (Exception $e) {
        return false;
    }
}

function updateUserPassword($user_id, $new_password) {
    global $db;
    
    try {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $db->query("UPDATE users SET password_hash = :password_hash WHERE id = :id");
        $db->bind(':password_hash', $password_hash);
        $db->bind(':id', $user_id);
        return $db->execute();
    } catch (Exception $e) {
        return false;
    }
}

function getUserStats($user_id) {
    global $db;
    
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
}

include '../includes/footer.php';
?>