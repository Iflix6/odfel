<?php
// Include functions first (contains all core functions)
require_once '../includes/functions.php';

// Include auth second (now functions are available)
require_once '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('chatroom.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
        } else {
            $user = authenticateUser($username, $password);
            
            if ($user) {
                // Login successful - use the loginUser function from functions.php
                loginUser($user);
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    redirect('chatroom.php');
                }
            } else {
                $error = 'Invalid username or password';
                logActivity("Failed login attempt for username: $username");
            }
        }
    }
}

$page_title = 'Login';
include '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 30px; color: #667eea;">
            <i class="fas fa-sign-in-alt"></i>
            Welcome Back
        </h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['timeout']) && $_GET['timeout'] == '1'): ?>
            <div class="alert alert-warning">Your session has expired. Please log in again.</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['logout']) && $_GET['logout'] == '1'): ?>
            <div class="alert alert-success">You have been logged out successfully.</div>
        <?php endif; ?>
        
        <form method="POST" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($username ?? ''); ?>"
                    placeholder="Enter your username or email"
                    required
                    autofocus
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter your password"
                        required
                    >
                    <button 
                        type="button" 
                        id="togglePassword" 
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;"
                        onclick="togglePasswordVisibility()"
                    >
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" id="remember_me" name="remember_me" style="margin: 0;">
                <label for="remember_me" style="margin: 0; font-weight: normal;">Remember me for 30 days</label>
            </div>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 20px;">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="color: #666;">Don't have an account?</p>
            <a href="register.php" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i>
                Create Account
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="forgot-password.php" style="color: #667eea; text-decoration: none; font-size: 14px;">
                <i class="fas fa-key"></i>
                Forgot your password?
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #667eea; text-decoration: none;">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const passwordField = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.className = 'fas fa-eye-slash';
    } else {
        passwordField.type = 'password';
        eyeIcon.className = 'fas fa-eye';
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('[data-validate]');
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(error => error.remove());
            document.querySelectorAll('.form-control').forEach(field => field.classList.remove('error'));
            
            // Validate username/email
            if (!usernameField.value.trim()) {
                showFieldError(usernameField, 'Username or email is required');
                isValid = false;
            } else if (usernameField.value.trim().length < 3) {
                showFieldError(usernameField, 'Username must be at least 3 characters');
                isValid = false;
            }
            
            // Validate password
            if (!passwordField.value) {
                showFieldError(passwordField, 'Password is required');
                isValid = false;
            } else if (passwordField.value.length < 6) {
                showFieldError(passwordField, 'Password must be at least 6 characters');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    function showFieldError(field, message) {
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.color = '#e74c3c';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '5px';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    // Auto-focus on page load
    if (usernameField && !usernameField.value) {
        usernameField.focus();
    }
    
    // Handle Enter key on form fields
    [usernameField, passwordField].forEach(field => {
        if (field) {
            field.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    form.submit();
                }
            });
        }
    });
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }, 5000);
    });
});
</script>

<style>
.form-control.error {
    border-color: #e74c3c !important;
    box-shadow: 0 0 5px rgba(231, 76, 60, 0.3) !important;
}

.alert {
    padding: 12px 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
    font-size: 14px;
}

.alert-error {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.form-container {
    background: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    max-width: 400px;
    margin: 50px auto;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
}

.btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
}

.btn-secondary:hover {
    box-shadow: 0 5px 15px rgba(116, 185, 255, 0.4);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

@media (max-width: 768px) {
    .form-container {
        margin: 20px auto;
        padding: 30px 20px;
    }
    
    .container {
        padding: 10px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>