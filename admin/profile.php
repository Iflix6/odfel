<?php
require_once 'includes/admin_functions.php';

// Require admin access
requireAdmin();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

if (!$user) {
    redirect('../pages/logout.php');
}

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $email = sanitize($_POST['email'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate
        if (empty($email)) {
            $error = 'Email cannot be empty';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } elseif ($email !== $user['email'] && userExists('', $email)) {
            $error = 'Email already in use';
        } elseif (!empty($new_password) && strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif (!empty($new_password) && $new_password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (!empty($new_password) && empty($current_password)) {
            $error = 'Current password is required to set a new password';
        } elseif (!empty($new_password) && !password_verify($current_password, $user['password_hash'])) {
            $error = 'Current password is incorrect';
        } else {
            // Update profile
            if (updateUserProfile($user_id, $email, $new_password)) {
                $success = 'Profile updated successfully';
                // Refresh user data
                $user = getUserById($user_id);
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
}

// Get user stats
$stats = getUserStats($user_id);

$page_title = 'Admin Profile';
include 'includes/admin_header.php';
?>

<div class="admin-profile">
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p class="profile-role">Administrator</p>
                <p class="profile-joined">
                    <i class="fas fa-calendar-alt"></i>
                    Joined <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                </p>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="profile-tabs">
            <button class="tab-btn active" onclick="showProfileTab('account')">Account Settings</button>
            <button class="tab-btn" onclick="showProfileTab('stats')">Activity Stats</button>
        </div>
        
        <div id="account-tab" class="profile-tab-content active">
            <form method="POST" class="profile-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        value="<?php echo htmlspecialchars($user['username']); ?>"
                        class="form-control"
                        disabled
                    >
                    <small>Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($user['email']); ?>"
                        class="form-control"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <input 
                        type="text" 
                        id="role" 
                        value="Administrator"
                        class="form-control"
                        disabled
                    >
                </div>
                
                <div class="form-divider">Change Password</div>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        class="form-control"
                    >
                    <small>Required only if changing password</small>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="form-control"
                        minlength="6"
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control"
                    >
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
        
        <div id="stats-tab" class="profile-tab-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_messages']; ?></div>
                    <div class="stat-label">Total Messages</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['days_active']; ?></div>
                    <div class="stat-label">Days Active</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['last_active']; ?></div>
                    <div class="stat-label">Last Active</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-value">Admin</div>
                    <div class="stat-label">Account Type</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-profile {
    max-width: 800px;
    margin: 0 auto;
}

.profile-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
}

.profile-info h2 {
    margin: 0 0 5px 0;
    font-size: 1.8rem;
}

.profile-role {
    margin: 0 0 10px 0;
    opacity: 0.9;
}

.profile-joined {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.8;
    display: flex;
    align-items: center;
    gap: 5px;
}

.profile-tabs {
    display: flex;
    border-bottom: 1px solid #eee;
}

.tab-btn {
    padding: 15px 20px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.profile-tab-content {
    display: none;
    padding: 30px;
}

.profile-tab-content.active {
    display: block;
}

.profile-form {
    max-width: 500px;
}

.form-divider {
    margin: 30px 0 20px 0;
    padding: 10px 0;
    border-top: 1px solid #eee;
    font-weight: 600;
    color: #333;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.2rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
}
</style>

<script>
function showProfileTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.profile-tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}
</script>

<?php include 'includes/admin_footer.php'; ?>
