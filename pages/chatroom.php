<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require login
requireLogin();

$user = getUserById($_SESSION['user_id']);
$page_title = 'Chat Room';
include '../includes/header.php';
?>

<div class="chat-container">
    <!-- Chat Header -->
    <div class="chat-header">
        <div class="chat-info">
            <h2>
                <i class="fas fa-comments"></i>
                ODFEL Chat Room
            </h2>
            <span class="online-count">
                <i class="fas fa-circle" style="color: #28a745;"></i>
                <span id="online-users">0</span> online
            </span>
        </div>
        
        <div class="chat-controls">
            <!-- AI Bot Toggle -->
            <button id="bot-toggle" class="btn btn-bot" title="Chat with AI Assistant">
                <i class="fas fa-robot"></i>
                AI Assistant
            </button>
            
            <!-- User Menu -->
            <div class="user-menu">
                <button class="btn btn-secondary" onclick="toggleUserMenu()">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($user['username']); ?>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="user-dropdown" id="user-dropdown">
                    <a href="user_profile.php">
                        <i class="fas fa-user-edit"></i>
                        Profile
                    </a>
                    <?php if (isAdmin()): ?>
                    <a href="../admin/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Admin Panel
                    </a>
                    <?php endif; ?>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chat Messages Area -->
    <div class="chat-messages" id="chat-messages">
        <div class="loading-messages">
            <div class="loading"></div>
            <span>Loading messages...</span>
        </div>
    </div>
    
    <!-- Typing Indicator -->
    <div class="typing-indicator" id="typing-indicator" style="display: none;">
        <div class="typing-dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <span class="typing-text">Someone is typing...</span>
    </div>
    
    <!-- Message Input -->
    <div class="chat-input-container">
        <form id="message-form" class="message-form">
            <div class="input-group">
                <input 
                    type="text" 
                    id="message-input" 
                    placeholder="Type your message... (or mention @bot for AI assistance)"
                    maxlength="500"
                    autocomplete="off"
                    required
                >
                <button type="submit" class="send-btn" id="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div class="input-info">
                <span class="char-count">
                    <span id="char-count">0</span>/500
                </span>
                <span class="bot-hint">
                    💡 Type @bot or click AI Assistant to chat with our AI
                </span>
            </div>
        </form>
    </div>
</div>

<!-- AI Bot Modal -->
<div class="modal" id="bot-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-robot"></i>
                AI Assistant Chat
            </h3>
            <button class="close-btn" onclick="closeBotModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="bot-chat-messages" id="bot-chat-messages">
            <div class="bot-message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <span class="username">ODFEL Assistant</span>
                        <span class="timestamp">Now</span>
                    </div>
                    <div class="message-text">
                        Hello! I'm your AI assistant. How can I help you today?
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bot-input-container">
            <form id="bot-message-form">
                <div class="input-group">
                    <input 
                        type="text" 
                        id="bot-message-input" 
                        placeholder="Ask me anything..."
                        maxlength="500"
                        autocomplete="off"
                    >
                    <button type="submit" class="send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.loading {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 80px);
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.chat-info h2 {
    margin: 0;
    font-size: 1.5rem;
}

.online-count {
    font-size: 0.9rem;
    opacity: 0.9;
}

.chat-controls {
    display: flex;
    gap: 15px;
    align-items: center;
}

.btn-bot {
    background: #28a745;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-bot:hover {
    background: #218838;
    transform: translateY(-2px);
}

.user-menu {
    position: relative;
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    min-width: 150px;
    z-index: 1000;
    display: none;
}

.user-dropdown.show {
    display: block;
}

.user-dropdown a {
    display: block;
    padding: 12px 16px;
    color: #333;
    text-decoration: none;
    transition: background 0.3s ease;
}

.user-dropdown a:hover {
    background: #f8f9fa;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
}

.message {
    display: flex;
    margin-bottom: 20px;
    animation: slideIn 0.3s ease;
}

.message.own {
    justify-content: flex-end;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 12px;
    flex-shrink: 0;
}

.message.own .message-avatar {
    background: #28a745;
    margin-right: 0;
    margin-left: 12px;
    order: 2;
}

.message.bot .message-avatar {
    background: #ffc107;
    color: #333;
}

.message-content {
    max-width: 70%;
    background: white;
    border-radius: 18px;
    padding: 12px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.message.own .message-content {
    background: #667eea;
    color: white;
}

.message.bot .message-content {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.username {
    font-weight: bold;
    font-size: 0.9rem;
}

.timestamp {
    font-size: 0.8rem;
    opacity: 0.7;
}

.message-text {
    line-height: 1.4;
    word-wrap: break-word;
}

.typing-indicator {
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
}

.typing-dots {
    display: flex;
    gap: 4px;
}

.typing-dots span {
    width: 8px;
    height: 8px;
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

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-10px);
    }
}

.chat-input-container {
    padding: 20px;
    background: white;
    border-top: 1px solid #eee;
}

.input-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

#message-input, #bot-message-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #eee;
    border-radius: 25px;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.3s ease;
}

#message-input:focus, #bot-message-input:focus {
    border-color: #667eea;
}

.send-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.send-btn:hover {
    background: #5a6fd8;
    transform: scale(1.05);
}

.send-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.input-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    font-size: 0.8rem;
    color: #666;
}

.bot-hint {
    color: #667eea;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 15px;
    width: 90%;
    max-width: 600px;
    height: 80%;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #667eea;
    color: white;
    border-radius: 15px 15px 0 0;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    transition: background 0.3s ease;
}

.close-btn:hover {
    background: rgba(255,255,255,0.2);
}

.bot-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
}

.bot-input-container {
    padding: 20px;
    background: white;
    border-top: 1px solid #eee;
    border-radius: 0 0 15px 15px;
}

.loading-messages {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 40px;
    color: #666;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .chat-container {
        height: calc(100vh - 60px);
        border-radius: 0;
    }
    
    .chat-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .chat-controls {
        width: 100%;
        justify-content: center;
    }
    
    .message-content {
        max-width: 85%;
    }
    
    .modal-content {
        width: 95%;
        height: 90%;
    }
}
</style>

<script>
// Chat functionality
let lastMessageId = 0;
let isLoadingMessages = false;
let typingTimer;

// Initialize chat
document.addEventListener('DOMContentLoaded', function() {
    console.log('Chat initialized');
    loadMessages();
    setupMessageForm();
    setupBotModal();
    setupCharCounter();
    
    // Auto-refresh messages every 3 seconds
    setInterval(loadMessages, 3000);
    
    // Update online count every 30 seconds
    setInterval(updateOnlineCount, 30000);
    updateOnlineCount();
});

function loadMessages() {
    if (isLoadingMessages) return;
    
    isLoadingMessages = true;
    console.log('Loading messages, last ID:', lastMessageId);
    
    fetch('get_messages.php?last_id=' + lastMessageId)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Messages data:', data);
            if (data.success && data.messages && data.messages.length > 0) {
                const messagesContainer = document.getElementById('chat-messages');
                const loadingElement = messagesContainer.querySelector('.loading-messages');
                
                if (loadingElement) {
                    loadingElement.remove();
                }
                
                data.messages.forEach(message => {
                    appendMessage(message);
                    lastMessageId = Math.max(lastMessageId, parseInt(message.id));
                });
                
                scrollToBottom();
            } else if (data.success && lastMessageId === 0) {
                // No messages yet, remove loading indicator
                const messagesContainer = document.getElementById('chat-messages');
                const loadingElement = messagesContainer.querySelector('.loading-messages');
                if (loadingElement) {
                    loadingElement.innerHTML = '<p>No messages yet. Be the first to start the conversation!</p>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
            const messagesContainer = document.getElementById('chat-messages');
            const loadingElement = messagesContainer.querySelector('.loading-messages');
            if (loadingElement) {
                loadingElement.innerHTML = '<p style="color: red;">Error loading messages. Please refresh the page.</p>';
            }
        })
        .finally(() => {
            isLoadingMessages = false;
        });
}

function appendMessage(message) {
    const messagesContainer = document.getElementById('chat-messages');
    const messageElement = createMessageElement(message);
    messagesContainer.appendChild(messageElement);
}

function createMessageElement(message) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message';
    
    // Check if this is the current user's message
    if (message.user_id == <?php echo $_SESSION['user_id']; ?>) {
        messageDiv.classList.add('own');
    }
    
    if (message.is_bot == 1) {
        messageDiv.classList.add('bot');
    }
    
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    
    if (message.is_bot == 1) {
        avatar.innerHTML = '<i class="fas fa-robot"></i>';
    } else {
        const firstLetter = message.username ? message.username.charAt(0).toUpperCase() : 'U';
        avatar.textContent = firstLetter;
    }
    
    const content = document.createElement('div');
    content.className = 'message-content';
    
    const header = document.createElement('div');
    header.className = 'message-header';
    
    const username = document.createElement('span');
    username.className = 'username';
    username.textContent = message.is_bot == 1 ? 'ODFEL Assistant' : (message.username || 'Unknown User');
    
    const timestamp = document.createElement('span');
    timestamp.className = 'timestamp';
    timestamp.textContent = formatTime(message.created_at);
    
    header.appendChild(username);
    header.appendChild(timestamp);
    
    const text = document.createElement('div');
    text.className = 'message-text';
    text.textContent = message.message;
    
    content.appendChild(header);
    content.appendChild(text);
    
    if (!messageDiv.classList.contains('own')) {
        messageDiv.appendChild(avatar);
    }
    messageDiv.appendChild(content);
    if (messageDiv.classList.contains('own')) {
        messageDiv.appendChild(avatar);
    }
    
    return messageDiv;
}

function setupMessageForm() {
    const form = document.getElementById('message-form');
    const input = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    input.addEventListener('input', function() {
        clearTimeout(typingTimer);
        showTypingIndicator();
        
        typingTimer = setTimeout(() => {
            hideTypingIndicator();
        }, 1000);
    });
}

function sendMessage() {
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if (!message) {
        alert('Please enter a message');
        return;
    }
    
    const sendBtn = document.getElementById('send-btn');
    sendBtn.disabled = true;
    
    console.log('Sending message:', message);
    
    const formData = new FormData();
    formData.append('message', message);
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
    
    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Send response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Send response data:', data);
        if (data.success) {
            input.value = '';
            updateCharCount();
            // Load messages immediately after sending
            setTimeout(loadMessages, 500);
        } else {
            alert('Failed to send message: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Failed to send message. Please check your connection and try again.');
    })
    .finally(() => {
        sendBtn.disabled = false;
        input.focus();
    });
}

function setupBotModal() {
    const botToggle = document.getElementById('bot-toggle');
    const botModal = document.getElementById('bot-modal');
    const botForm = document.getElementById('bot-message-form');
    const botInput = document.getElementById('bot-message-input');
    
    botToggle.addEventListener('click', function() {
        botModal.classList.add('show');
        botInput.focus();
    });
    
    botForm.addEventListener('submit', function(e) {
        e.preventDefault();
        sendBotMessage();
    });
}

function sendBotMessage() {
    const input = document.getElementById('bot-message-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add user message to bot chat
    addBotMessage(message, false);
    input.value = '';
    
    // Add loading indicator
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'message bot loading-bot';
    loadingDiv.innerHTML = `
        <div class="message-avatar"><i class="fas fa-robot"></i></div>
        <div class="message-content">
            <div class="message-header">
                <span class="username">ODFEL Assistant</span>
                <span class="timestamp">Now</span>
            </div>
            <div class="message-text">
                <div class="loading"></div>
                Thinking...
            </div>
        </div>
    `;
    
    const botMessages = document.getElementById('bot-chat-messages');
    botMessages.appendChild(loadingDiv);
    botMessages.scrollTop = botMessages.scrollHeight;
    
    // Send to bot
    const formData = new FormData();
    formData.append('message', message);
    formData.append('ajax_request', '1');
    
    fetch('../chatbot.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        loadingDiv.remove();
        if (data.success) {
            addBotMessage(data.response, true);
        } else {
            addBotMessage('Sorry, I encountered an error. Please try again.', true);
        }
    })
    .catch(error => {
        console.error('Bot request error:', error);
        loadingDiv.remove();
        addBotMessage('Sorry, I\'m having trouble connecting. Please try again later.', true);
    });
}

function addBotMessage(message, isBot) {
    const botMessages = document.getElementById('bot-chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message' + (isBot ? ' bot' : ' own');
    
    messageDiv.innerHTML = `
        <div class="message-avatar">
            ${isBot ? '<i class="fas fa-robot"></i>' : '<?php echo htmlspecialchars(substr($user["username"], 0, 1)); ?>'}
        </div>
        <div class="message-content">
            <div class="message-header">
                <span class="username">${isBot ? 'ODFEL Assistant' : '<?php echo htmlspecialchars($user["username"]); ?>'}</span>
                <span class="timestamp">Now</span>
            </div>
            <div class="message-text">${message}</div>
        </div>
    `;
    
    botMessages.appendChild(messageDiv);
    botMessages.scrollTop = botMessages.scrollHeight;
}

function closeBotModal() {
    document.getElementById('bot-modal').classList.remove('show');
}

function setupCharCounter() {
    const input = document.getElementById('message-input');
    const counter = document.getElementById('char-count');
    
    input.addEventListener('input', updateCharCount);
}

function updateCharCount() {
    const input = document.getElementById('message-input');
    const counter = document.getElementById('char-count');
    const count = input.value.length;
    
    counter.textContent = count;
    
    if (count > 450) {
        counter.style.color = '#dc3545';
    } else if (count > 400) {
        counter.style.color = '#ffc107';
    } else {
        counter.style.color = '#666';
    }
}

function updateOnlineCount() {
    fetch('get_messages.php?action=online_count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('online-users').textContent = data.count;
            }
        })
        .catch(error => {
            console.error('Error updating online count:', error);
        });
}

function toggleUserMenu() {
    const dropdown = document.getElementById('user-dropdown');
    dropdown.classList.toggle('show');
}

function showTypingIndicator() {
    document.getElementById('typing-indicator').style.display = 'flex';
}

function hideTypingIndicator() {
    document.getElementById('typing-indicator').style.display = 'none';
}

function scrollToBottom() {
    const messagesContainer = document.getElementById('chat-messages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) { // Less than 1 minute
        return 'Just now';
    } else if (diff < 3600000) { // Less than 1 hour
        return Math.floor(diff / 60000) + 'm ago';
    } else if (diff < 86400000) { // Less than 1 day
        return Math.floor(diff / 3600000) + 'h ago';
    } else {
        return date.toLocaleDateString();
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('bot-modal');
    if (e.target === modal) {
        closeBotModal();
    }
    
    const dropdown = document.getElementById('user-dropdown');
    if (!e.target.closest('.user-menu')) {
        dropdown.classList.remove('show');
    }
});
</script>

<?php include '../includes/footer.php'; ?>