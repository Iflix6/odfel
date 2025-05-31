<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin_dashboard.php');
    } else {
        redirect('chatroom.php');
    }
}

$page_title = 'Welcome to ODFEL ChatBot';
include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">
                Welcome to <span class="brand-highlight">ODFEL</span>
                <br>ChatBot Experience
            </h1>
            <p class="hero-description">
                Connect with friends, engage in meaningful conversations, and get intelligent assistance 
                from our AI-powered chatbot. Experience the future of online communication today.
            </p>
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Get Started Free
                </a>
                <a href="login.php" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-number" id="total-users">0</span>
                    <span class="stat-label">Active Users</span>
                </div>
                <div class="stat">
                    <span class="stat-number" id="total-messages">0</span>
                    <span class="stat-label">Messages Sent</span>
                </div>
                <div class="stat">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">AI Support</span>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="chat-preview">
                <div class="chat-header-preview">
                    <div class="chat-title">
                        <i class="fas fa-comments"></i>
                        Live Chat
                    </div>
                    <div class="online-indicator">
                        <i class="fas fa-circle"></i>
                        Online
                    </div>
                </div>
                <div class="chat-messages-preview">
                    <div class="message-preview user">
                        <div class="message-avatar">J</div>
                        <div class="message-content">
                            <div class="message-text">Hello everyone! 👋</div>
                        </div>
                    </div>
                    <div class="message-preview bot">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-text">Welcome to ODFEL! How can I assist you today?</div>
                        </div>
                    </div>
                    <div class="message-preview user">
                        <div class="message-avatar">S</div>
                        <div class="message-content">
                            <div class="message-text">This AI is amazing! 🤖</div>
                        </div>
                    </div>
                </div>
                <div class="typing-preview">
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <span>AI is typing...</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <h2 class="section-title">Why Choose ODFEL ChatBot?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Real-time Chat</h3>
                <p>Instant messaging with live updates, emoji support, and seamless communication with users worldwide.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h3>AI Assistant</h3>
                <p>Powered by Google's Gemini AI for intelligent conversations, helpful answers, and 24/7 support.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Secure & Private</h3>
                <p>Your privacy matters. End-to-end security, data protection, and safe communication environment.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Friendly</h3>
                <p>Responsive design that works perfectly on all devices - desktop, tablet, and mobile.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Community Driven</h3>
                <p>Join a vibrant community of users sharing ideas, experiences, and building connections.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3>Easy to Use</h3>
                <p>Intuitive interface, simple navigation, and user-friendly design for the best experience.</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Sign Up</h3>
                    <p>Create your free account in seconds. Just username, email, and password - that's it!</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Join the Chat</h3>
                    <p>Enter our vibrant chat room and start connecting with users from around the world.</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Chat with AI</h3>
                    <p>Need help? Mention @bot or click the AI Assistant button for intelligent assistance.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Start Chatting?</h2>
            <p>Join thousands of users already enjoying the ODFEL ChatBot experience.</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-primary btn-large">
                    <i class="fas fa-rocket"></i>
                    Start Chatting Now
                </a>
                <a href="about.php" class="btn btn-outline">
                    <i class="fas fa-info-circle"></i>
                    Learn More
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* Hero Section */
.hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
    min-height: 80vh;
    display: flex;
    align-items: center;
}

.hero-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 20px;
}

.brand-highlight {
    color: #ffd700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.hero-description {
    font-size: 1.2rem;
    line-height: 1.6;
    margin-bottom: 40px;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
}

.btn-primary {
    background: #28a745;
    color: white;
    padding: 15px 30px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-primary:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.btn-secondary {
    background: transparent;
    color: white;
    border: 2px solid white;
    padding: 13px 28px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-secondary:hover {
    background: white;
    color: #667eea;
    transform: translateY(-2px);
}

.hero-stats {
    display: flex;
    gap: 40px;
}

.stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: #ffd700;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

/* Chat Preview */
.chat-preview {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    overflow: hidden;
    animation: float 6s ease-in-out infinite;
}

.chat-header-preview {
    background: #667eea;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-title {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.online-indicator {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9rem;
}

.online-indicator i {
    color: #28a745;
    animation: pulse 2s infinite;
}

.chat-messages-preview {
    padding: 20px;
    height: 200px;
    overflow: hidden;
}

.message-preview {
    display: flex;
    margin-bottom: 15px;
    align-items: flex-start;
    gap: 10px;
}

.message-preview.user {
    justify-content: flex-end;
}

.message-preview.user .message-avatar {
    order: 2;
    background: #28a745;
}

.message-preview.bot .message-avatar {
    background: #ffc107;
    color: #333;
}

.message-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.message-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 15px;
    max-width: 70%;
}

.message-preview.user .message-content {
    background: #667eea;
    color: white;
}

.message-preview.bot .message-content {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
}

.message-text {
    font-size: 0.9rem;
    line-height: 1.4;
}

.typing-preview {
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
    color: #666;
    font-size: 0.9rem;
}

.typing-dots {
    display: flex;
    gap: 3px;
}

.typing-dots span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #667eea;
    animation: typing 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

/* Features Section */
.features {
    padding: 80px 0;
    background: #f8f9fa;
}

.section-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 60px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-10px);
}

.feature-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 20px;
}

.feature-card h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.feature-card p {
    color: #666;
    line-height: 1.6;
}

/* How It Works */
.how-it-works {
    padding: 80px 0;
    background: white;
}

.steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    margin-top: 60px;
}

.step {
    text-align: center;
    position: relative;
}

.step-number {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 auto 20px;
}

.step h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.step p {
    color: #666;
    line-height: 1.6;
}

/* CTA Section */
.cta {
    padding: 80px 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
}

.cta-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.cta-content p {
    font-size: 1.2rem;
    margin-bottom: 40px;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.btn-large {
    padding: 18px 35px;
    font-size: 1.1rem;
}

.btn-outline {
    background: transparent;
    color: white;
    border: 2px solid white;
    padding: 16px 33px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-outline:hover {
    background: white;
    color: #667eea;
    transform: translateY(-2px);
}

/* Animations */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-8px);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .hero-stats {
        justify-content: center;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .steps {
        grid-template-columns: 1fr;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .section-title {
        font-size: 2rem;
    }
}
</style>

<script>
// Animate stats on page load
document.addEventListener('DOMContentLoaded', function() {
    animateStats();
    loadStats();
});

function animateStats() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const target = parseInt(stat.textContent) || 0;
        if (target === 0) return;
        
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            stat.textContent = Math.floor(current);
        }, 50);
    });
}

function loadStats() {
    fetch('get_messages.php?action=stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-users').textContent = data.users || '100+';
                document.getElementById('total-messages').textContent = data.messages || '1000+';
            }
        })
        .catch(error => {
            // Use default values if fetch fails
            document.getElementById('total-users').textContent = '100+';
            document.getElementById('total-messages').textContent = '1000+';
        });
}
</script>

<?php include '../includes/footer.php'; ?>
