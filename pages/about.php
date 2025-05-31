<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'About';
include '../includes/header.php';
?>

<div class="container">
    <div style="background: white; padding: 60px 40px; border-radius: 15px; margin: 40px 0; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <h1 style="color: #667eea; text-align: center; margin-bottom: 30px;">
            <i class="fas fa-info-circle"></i>
            About ODFEL ChatBot
        </h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; margin-bottom: 40px;">
            <div>
                <h2 style="color: #333; margin-bottom: 20px;">Our Mission</h2>
                <p style="font-size: 1.1rem; line-height: 1.8; color: #666;">
                    ODFEL ChatBot is designed to revolutionize online communication by combining 
                    human interaction with advanced AI technology. We believe in creating a platform 
                    where users can engage in meaningful conversations while having access to 
                    intelligent assistance whenever needed.
                </p>
            </div>
            <div style="text-align: center;">
                <i class="fas fa-rocket" style="font-size: 6rem; color: #667eea; opacity: 0.7;"></i>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin: 40px 0;">
            <div style="text-align: center; padding: 20px;">
                <i class="fas fa-brain" style="font-size: 3rem; color: #667eea; margin-bottom: 15px;"></i>
                <h3 style="color: #333; margin-bottom: 10px;">AI-Powered</h3>
                <p style="color: #666;">Powered by Google's Gemini AI for intelligent conversations and assistance.</p>
            </div>
            
            <div style="text-align: center; padding: 20px;">
                <i class="fas fa-users" style="font-size: 3rem; color: #667eea; margin-bottom: 15px;"></i>
                <h3 style="color: #333; margin-bottom: 10px;">Community Driven</h3>
                <p style="color: #666;">Connect with like-minded individuals in our vibrant chat community.</p>
            </div>
            
            <div style="text-align: center; padding: 20px;">
                <i class="fas fa-shield-alt" style="font-size: 3rem; color: #667eea; margin-bottom: 15px;"></i>
                <h3 style="color: #333; margin-bottom: 10px;">Secure & Private</h3>
                <p style="color: #666;">Your privacy and data security are our highest priorities.</p>
            </div>
        </div>
        
        <div style="background: #f8f9fa; padding: 40px; border-radius: 10px; margin: 40px 0;">
            <h2 style="color: #333; text-align: center; margin-bottom: 30px;">Key Features</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <h4 style="color: #667eea; margin-bottom: 10px;">
                        <i class="fas fa-comments"></i> Real-time Chat
                    </h4>
                    <p style="color: #666; font-size: 0.9rem;">Instant messaging with live updates and notifications.</p>
                </div>
                
                <div>
                    <h4 style="color: #667eea; margin-bottom: 10px;">
                        <i class="fas fa-robot"></i> AI Assistant
                    </h4>
                    <p style="color: #666; font-size: 0.9rem;">Get help and answers from our intelligent chatbot.</p>
                </div>
                
                <div>
                    <h4 style="color: #667eea; margin-bottom: 10px;">
                        <i class="fas fa-mobile-alt"></i> Mobile Friendly
                    </h4>
                    <p style="color: #666; font-size: 0.9rem;">Fully responsive design for all devices.</p>
                </div>
                
                <div>
                    <h4 style="color: #667eea; margin-bottom: 10px;">
                        <i class="fas fa-cogs"></i> Admin Panel
                    </h4>
                    <p style="color: #666; font-size: 0.9rem;">Comprehensive management tools for administrators.</p>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <h2 style="color: #333; margin-bottom: 20px;">Ready to Join?</h2>
            <p style="font-size: 1.1rem; color: #666; margin-bottom: 30px;">
                Experience the future of online communication today.
            </p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn" style="margin-right: 15px;">Get Started</a>
                <a href="contact.php" class="btn btn-secondary">Contact Us</a>
            <?php else: ?>
                <a href="chatroom.php" class="btn">Enter Chat Room</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>