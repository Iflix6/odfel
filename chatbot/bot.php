<?php
require_once 'includes/admin_functions.php';

// Require admin access
requireAdmin();

$success = '';
$error = '';

// Handle bot settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_bot_settings') {
        $bot_name = sanitize($_POST['bot_name'] ?? '');
        $gemini_api_key = $_POST['gemini_api_key'] ?? '';
        $bot_personality = sanitize($_POST['bot_personality'] ?? '');
        $response_length = (int)($_POST['response_length'] ?? 200);
        
        // Validate
        if (empty($bot_name)) {
            $error = 'Bot name cannot be empty';
        } elseif ($response_length < 50 || $response_length > 500) {
            $error = 'Response length must be between 50 and 500 characters';
        } else {
            // Update bot settings
            $updated = true;
            $updated = $updated && updateSetting('bot_name', $bot_name);
            $updated = $updated && updateSetting('bot_personality', $bot_personality);
            $updated = $updated && updateSetting('bot_response_length', $response_length);
            
            // Only update API key if provided
            if (!empty($gemini_api_key)) {
                $updated = $updated && updateSetting('gemini_api_key', $gemini_api_key);
            }
            
            if ($updated) {
                $success = 'Bot settings updated successfully';
            } else {
                $error = 'Failed to update bot settings';
            }
        }
    }
}

// Get current bot settings
$bot_name = getSetting('bot_name') ?: 'ODFEL Assistant';
$gemini_api_key = getSetting('gemini_api_key');
$bot_personality = getSetting('bot_personality') ?: 'You are a helpful and friendly AI assistant.';
$response_length = getSetting('bot_response_length') ?: 200;

$page_title = 'Bot Settings';
include 'includes/admin_header.php';
?>

<div class="bot-management">
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="bot-container">
        <div class="bot-header">
            <div class="bot-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="bot-info">
                <h3><?php echo htmlspecialchars($bot_name); ?></h3>
                <p>AI Assistant Configuration</p>
                <div class="bot-status">
                    <span class="status-dot <?php echo !empty($gemini_api_key) ? 'active' : 'inactive'; ?>"></span>
                    <span><?php echo !empty($gemini_api_key) ? 'Active' : 'Inactive'; ?></span>
                </div>
            </div>
        </div>
        
        <div class="bot-tabs">
            <button class="tab-btn active" onclick="showBotTab('settings')">Bot Settings</button>
            <button class="tab-btn" onclick="showBotTab('test')">Test Bot</button>
            <button class="tab-btn" onclick="showBotTab('analytics')">Analytics</button>
        </div>
        
        <div id="settings-tab" class="bot-tab-content active">
            <form method="POST" class="bot-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_bot_settings">
                
                <div class="form-section">
                    <h4>Basic Configuration</h4>
                    
                    <div class="form-group">
                        <label for="bot_name">Bot Name</label>
                        <input 
                            type="text" 
                            id="bot_name" 
                            name="bot_name" 
                            value="<?php echo htmlspecialchars($bot_name); ?>"
                            class="form-control"
                            required
                        >
                        <small>The display name for your AI assistant</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="gemini_api_key">Google Gemini API Key</label>
                        <input 
                            type="password" 
                            id="gemini_api_key" 
                            name="gemini_api_key" 
                            placeholder="<?php echo !empty($gemini_api_key) ? 'API Key is set (leave blank to keep current)' : 'Enter your Gemini API key'; ?>"
                            class="form-control"
                        >
                        <small>
                            Get your API key from 
                            <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                        </small>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4>Personality & Behavior</h4>
                    
                    <div class="form-group">
                        <label for="bot_personality">Bot Personality</label>
                        <textarea 
                            id="bot_personality" 
                            name="bot_personality" 
                            class="form-control"
                            rows="4"
                            placeholder="Describe how the bot should behave and respond..."
                        ><?php echo htmlspecialchars($bot_personality); ?></textarea>
                        <small>Define the bot's personality and response style</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="response_length">Max Response Length</label>
                        <input 
                            type="range" 
                            id="response_length" 
                            name="response_length" 
                            min="50" 
                            max="500" 
                            value="<?php echo $response_length; ?>"
                            class="form-range"
                            oninput="updateRangeValue(this.value)"
                        >
                        <div class="range-labels">
                            <span>50</span>
                            <span id="rangeValue"><?php echo $response_length; ?></span>
                            <span>500</span>
                        </div>
                        <small>Maximum characters for bot responses</small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Bot Settings
                    </button>
                </div>
            </form>
        </div>
        
        <div id="test-tab" class="bot-tab-content">
            <div class="bot-test-section">
                <h4>Test Your AI Assistant</h4>
                <p>Send test messages to see how your bot responds</p>
                
                <div class="test-chat">
                    <div class="test-messages" id="testMessages">
                        <div class="test-message bot">
                            <div class="message-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="message-content">
                                <div class="message-text">
                                    Hello! I'm <?php echo htmlspecialchars($bot_name); ?>. Send me a test message to see how I respond!
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="test-input">
                        <div class="input-group">
                            <input 
                                type="text" 
                                id="testMessage" 
                                placeholder="Type a test message..."
                                class="form-control"
                                onkeypress="if(event.key==='Enter') sendTestMessage()"
                            >
                            <button onclick="sendTestMessage()" class="btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="analytics-tab" class="bot-tab-content">
            <div class="analytics-section">
                <h4>Bot Analytics</h4>
                
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="analytics-value">
                            <?php 
                            global $db;
                            $db->query("SELECT COUNT(*) as count FROM messages WHERE is_bot = 1");
                            $bot_messages = $db->single()['count'];
                            echo $bot_messages;
                            ?>
                        </div>
                        <div class="analytics-label">Total Responses</div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="analytics-value">
                            <?php 
                            $db->query("SELECT COUNT(*) as count FROM messages WHERE is_bot = 1 AND DATE(created_at) = CURDATE()");
                            $today_responses = $db->single()['count'];
                            echo $today_responses;
                            ?>
                        </div>
                        <div class="analytics-label">Today's Responses</div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="analytics-value">
                            <?php 
                            $db->query("SELECT COUNT(*) as total FROM messages");
                            $total_messages = $db->single()['count'];
                            $percentage = $total_messages > 0 ? round(($bot_messages / $total_messages) * 100, 1) : 0;
                            echo $percentage . '%';
                            ?>
                        </div>
                        <div class="analytics-label">Bot Message Ratio</div>
                    </div>
                    
                    <div class="analytics-card">
                        <div class="analytics-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="analytics-value">95%</div>
                        <div class="analytics-label">Response Rate</div>
                    </div>
                </div>
                
                <div class="analytics-chart">
                    <h5>Bot Activity (Last 7 Days)</h5>
                    <canvas id="botActivityChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bot-management {
    max-width: 900px;
    margin: 0 auto;
}

.bot-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.bot-header {
    background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%);
    color: white;
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.bot-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.bot-info h3 {
    margin: 0 0 5px 0;
    font-size: 1.8rem;
}

.bot-info p {
    margin: 0 0 10px 0;
    opacity: 0.9;
}

.bot-status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #dc3545;
}

.status-dot.active {
    background: #28a745;
    animation: pulse 2s infinite;
}

.bot-tabs {
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
    color: #ffc107;
    border-bottom-color: #ffc107;
}

.bot-tab-content {
    display: none;
    padding: 30px;
}

.bot-tab-content.active {
    display: block;
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.form-section:last-child {
    border-bottom: none;
}

.form-section h4 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 1.2rem;
}

.form-range {
    width: 100%;
    margin: 10px 0;
}

.range-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: #666;
}

#rangeValue {
    font-weight: bold;
    color: #ffc107;
}

.test-chat {
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 20px;
}

.test-messages {
    height: 300px;
    overflow-y: auto;
    padding: 20px;
}

.test-message {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.test-message.user {
    justify-content: flex-end;
}

.test-message.user .message-content {
    background: #667eea;
    color: white;
}

.message-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #ffc107;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.test-message.user .message-avatar {
    background: #667eea;
    color: white;
    order: 2;
}

.message-content {
    background: white;
    border-radius: 15px;
    padding: 10px 15px;
    max-width: 70%;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.test-input {
    padding: 20px;
    background: white;
    border-top: 1px solid #eee;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.analytics-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: transform 0.3s ease;
}

.analytics-card:hover {
    transform: translateY(-5px);
}

.analytics-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #ffc107;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.2rem;
}

.analytics-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.analytics-label {
    color: #666;
    font-size: 0.9rem;
}

.analytics-chart {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
}

.analytics-chart h5 {
    margin: 0 0 20px 0;
    color: #333;
}

@media (max-width: 768px) {
    .bot-header {
        flex-direction: column;
        text-align: center;
    }
    
    .analytics-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
}
</style>

<script>
function showBotTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.bot-tab-content').forEach(tab => {
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
    
    // Initialize chart if analytics tab is shown
    if (tabName === 'analytics') {
        setTimeout(initBotChart, 100);
    }
}

function updateRangeValue(value) {
    document.getElementById('rangeValue').textContent = value;
}

function sendTestMessage() {
    const input = document.getElementById('testMessage');
    const message = input.value.trim();
    
    if (!message) return;
    
    const messagesContainer = document.getElementById('testMessages');
    
    // Add user message
    const userMessage = document.createElement('div');
    userMessage.className = 'test-message user';
    userMessage.innerHTML = `
        <div class="message-content">
            <div class="message-text">${message}</div>
        </div>
        <div class="message-avatar">
            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
        </div>
    `;
    messagesContainer.appendChild(userMessage);
    
    // Clear input
    input.value = '';
    
    // Add loading message
    const loadingMessage = document.createElement('div');
    loadingMessage.className = 'test-message bot loading';
    loadingMessage.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="message-content">
            <div class="message-text">
                <i class="fas fa-spinner fa-spin"></i> Thinking...
            </div>
        </div>
    `;
    messagesContainer.appendChild(loadingMessage);
    
    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // Send to bot
    fetch('../chatbot/bot.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `message=${encodeURIComponent(message)}&csrf_token=<?php echo generateCSRFToken(); ?>`
    })
    .then(response => response.json())
    .then(data => {
        // Remove loading message
        loadingMessage.remove();
        
        // Add bot response
        const botMessage = document.createElement('div');
        botMessage.className = 'test-message bot';
        botMessage.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-text">${data.success ? data.response : 'Sorry, I encountered an error.'}</div>
            </div>
        `;
        messagesContainer.appendChild(botMessage);
        
        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    })
    .catch(error => {
        // Remove loading message
        loadingMessage.remove();
        
        // Add error message
        const errorMessage = document.createElement('div');
        errorMessage.className = 'test-message bot';
        errorMessage.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-text">Sorry, I'm having trouble connecting right now.</div>
            </div>
        `;
        messagesContainer.appendChild(errorMessage);
        
        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    });
}

function initBotChart() {
    const canvas = document.getElementById('botActivityChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Sample data for bot activity
    const data = [12, 18, 15, 22, 28, 25, 30];
    const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    
    drawBotChart(ctx, data, labels, canvas.width, canvas.height);
}

function drawBotChart(ctx, data, labels, width, height) {
    const padding = 40;
    const chartWidth = width - 2 * padding;
    const chartHeight = height - 2 * padding;
    const barWidth = chartWidth / data.length;
    const maxValue = Math.max(...data);
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    
    // Draw bars
    ctx.fillStyle = '#ffc107';
    data.forEach((value, index) => {
        const barHeight = (value / maxValue) * chartHeight;
        const x = padding + index * barWidth + barWidth * 0.1;
        const y = height - padding - barHeight;
        
        ctx.fillRect(x, y, barWidth * 0.8, barHeight);
        
        // Draw labels
        ctx.fillStyle = '#666';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(labels[index], x + barWidth * 0.4, height - 10);
        ctx.fillText(value, x + barWidth * 0.4, y - 5);
        
        ctx.fillStyle = '#ffc107';
    });
}
</script>

<?php include 'includes/admin_footer.php'; ?>
