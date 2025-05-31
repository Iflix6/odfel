<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error = 'Please fill in all fields';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } else {
            // Save contact message to database or send email
            if (saveContactMessage($name, $email, $subject, $message)) {
                $success = 'Thank you for your message! We will get back to you soon.';
            } else {
                $error = 'Failed to send message. Please try again.';
            }
        }
    }
}

$page_title = 'Contact';
include '../includes/header.php';
?>

<div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 40px; margin: 40px 0;">
        
        <!-- Contact Form -->
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px; color: #667eea;">
                <i class="fas fa-envelope"></i>
                Contact Us
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
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
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
                    <label for="subject">Subject</label>
                    <input 
                        type="text" 
                        id="subject" 
                        name="subject" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea 
                        id="message" 
                        name="message" 
                        class="form-control" 
                        rows="5" 
                        required
                    ><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">
                    <i class="fas fa-paper-plane"></i>
                    Send Message
                </button>
            </form>
        </div>
        
        <!-- Contact Information -->
        <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <h2 style="color: #667eea; margin-bottom: 30px;">
                <i class="fas fa-info-circle"></i>
                Get in Touch
            </h2>
            
            <p style="font-size: 1.1rem; line-height: 1.8; color: #666; margin-bottom: 30px;">
                We'd love to hear from you! Whether you have questions, feedback, or need support, 
                our team is here to help.
            </p>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 20px;">Contact Information</h3>
                
                <div style="margin-bottom: 15px;">
                    <i class="fas fa-envelope" style="color: #667eea; margin-right: 10px; width: 20px;"></i>
                    <strong>Email:</strong> support@odfel.com
                </div>
                
                <div style="margin-bottom: 15px;">
                    <i class="fas fa-phone" style="color: #667eea; margin-right: 10px; width: 20px;"></i>
                    <strong>Phone:</strong> +1 (555) 123-4567
                </div>
                
                <div style="margin-bottom: 15px;">
                    <i class="fas fa-map-marker-alt" style="color: #667eea; margin-right: 10px; width: 20px;"></i>
                    <strong>Address:</strong> 123 Tech Street, Digital City, DC 12345
                </div>
                
                <div style="margin-bottom: 15px;">
                    <i class="fas fa-clock" style="color: #667eea; margin-right: 10px; width: 20px;"></i>
                    <strong>Hours:</strong> Mon-Fri 9AM-6PM EST
                </div>
            </div>
            
            <div>
                <h3 style="color: #333; margin-bottom: 20px;">Follow Us</h3>
                <div style="display: flex; gap: 15px;">
                    <a href="#" style="color: #667eea; font-size: 1.5rem;">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" style="color: #667eea; font-size: 1.5rem;">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" style="color: #667eea; font-size: 1.5rem;">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <a href="#" style="color: #667eea; font-size: 1.5rem;">
                        <i class="fab fa-github"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function saveContactMessage($name, $email, $subject, $message) {
    global $db;
    
    try {
        $db->query("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (:name, :email, :subject, :message, NOW())");
        $db->bind(':name', $name);
        $db->bind(':email', $email);
        $db->bind(':subject', $subject);
        $db->bind(':message', $message);
        return $db->execute();
    } catch (Exception $e) {
        // If contact_messages table doesn't exist, just log the message
        logActivity("Contact message from $name ($email): $subject - $message");
        return true;
    }
}

include '../includes/footer.php';
?>