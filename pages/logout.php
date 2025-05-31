<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('chatroom.php');
}

$error = '';
$success = '';

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
        } elseif (strlen($username) < 3 || strlen($username) > 20) {
            $error = 'Username must be between 3 and 20 characters';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            if (registerUser($username, $email, $password)) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = 'Username or email already exists';
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
            Join <?php echo SITE_NAME; ?>
        </h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    minlength="3"
                    maxlength="20"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
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
                    minlength="6"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="form-control" 
                    required
                >
            </div>
            
            <button type="submit" class="btn" style="width: 100%; margin-bottom: 20px;">
                <i class="fas fa-user-plus"></i>
                Create Account
            </button>
        </form>
        
        <div style="text-align: center;">
            <p>Already have an account? <a href="login.php" style="color: #667eea;">Login here</a></p>
            <p><a href="index.php" style="color: #666;">← Back to Home</a></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>