<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('chatroom.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all fields';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters long';
        } elseif (strlen($username) > 50) {
            $error = 'Username must be less than 50 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error = 'Username can only contain letters, numbers, and underscores';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (userExists($username, $email)) {
            $error = 'Username or email already exists';
        } else {
            // Create user
            if (createUser($username, $email, $password)) {
                $success = 'Account created successfully! You can now login.';
                // Clear form data
                $username = $email = '';
            } else {
                $error = 'Failed to create account. Please try again.';
            }
        }
    }
}

$page_title = 'Register';
include '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 30px; color: #667eea;">
            <i class="fas fa-user-plus"></i>
            Create Account
        </h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <br><br>
                <a href="login.php" class="btn">Login Now</a>
            </div>
        <?php else: ?>
        
        <form method="POST" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($username ?? ''); ?>"
                    placeholder="Choose a unique username"
                    minlength="3"
                    maxlength="50"
                    pattern="[a-zA-Z0-9_]+"
                    title="Username can only contain letters, numbers, and underscores"
                    required
                >
                <small style="color: #666;">3-50 characters, letters, numbers, and underscores only</small>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    placeholder="Enter your email address"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Create a strong password"
                    minlength="6"
                    required
                >
                <small style="color: #666;">Minimum 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="form-control" 
                    placeholder="Confirm your password"
                    required
                >
            </div>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 20px;">
                <i class="fas fa-user-plus"></i>
                Create Account
            </button>
        </form>
        
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="color: #666;">Already have an account?</p>
            <a href="login.php" class="btn btn-secondary">
                <i class="fas fa-sign-in-alt"></i>
                Login Here
            </a>
        </div>
    </div>
</div>

<?php
function userExists($username, $email) {
    global $db;
    
    $db->query("SELECT id FROM users WHERE username = :username OR email = :email");
    $db->bind(':username', $username);
    $db->bind(':email', $email);
    
    return $db->single() !== false;
}

function createUser($username, $email, $password) {
    global $db;
    
    try {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $db->query("INSERT INTO users (username, email, password_hash, role, is_active, created_at) VALUES (:username, :email, :password_hash, 'user', 1, NOW())");
        $db->bind(':username', $username);
        $db->bind(':email', $email);
        $db->bind(':password_hash', $password_hash);
        
        $result = $db->execute();
        
        if ($result) {
            logActivity("New user registered: $username ($email)");
        }
        
        return $result;
    } catch (Exception $e) {
        logActivity("Registration failed for $username: " . $e->getMessage());
        return false;
    }
}

include '../includes/footer.php';
?>
