<?php
require_once 'includes/admin_functions.php';

// Require admin access
requireAdmin();

$success = '';
$error = '';
$debug_info = [];

// Enhanced updateSetting function with debugging
function updateSettingDebug($key, $value) {
    global $db, $debug_info;
    
    try {
        $debug_info[] = "Attempting to update setting: $key";
        
        // Check if setting exists
        $db->query("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        $existing = $db->single();
        
        if ($existing) {
            $debug_info[] = "Setting exists, updating...";
            $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
            $affected = $db->rowCount();
            $debug_info[] = "Update affected rows: $affected";
        } else {
            $debug_info[] = "Setting doesn't exist, inserting...";
            $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
            $affected = $db->rowCount();
            $debug_info[] = "Insert affected rows: $affected";
        }
        
        // Verify the save
        $db->query("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        $verification = $db->single();
        $debug_info[] = "Verification - Retrieved value: " . ($verification ? $verification['setting_value'] : 'NULL');
        
        return $affected > 0;
        
    } catch (Exception $e) {
        $debug_info[] = "Database error: " . $e->getMessage();
        error_log("updateSetting error: " . $e->getMessage());
        return false;
    }
}

// Handle bot settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $debug_info[] = "Action: " . $action;
    
    if ($action === 'update_bot_settings') {
        $bot_name = sanitize($_POST['bot_name'] ?? '');
        $gemini_api_key = $_POST['gemini_api_key'] ?? '';
        $bot_personality = sanitize($_POST['bot_personality'] ?? '');
        $response_length = (int)($_POST['response_length'] ?? 200);
        
        // Debug: Log received values
        $debug_info[] = "Received data:";
        $debug_info[] = "- Bot name: '$bot_name'";
        $debug_info[] = "- API key length: " . strlen($gemini_api_key);
        $debug_info[] = "- API key first 20 chars: " . substr($gemini_api_key, 0, 20);
        $debug_info[] = "- Personality length: " . strlen($bot_personality);
        $debug_info[] = "- Response length: $response_length";
        
        // Validate
        if (empty($bot_name)) {
            $error = 'Bot name cannot be empty';
            $debug_info[] = "Validation failed: Bot name empty";
        } elseif ($response_length < 50 || $response_length > 500) {
            $error = 'Response length must be between 50 and 500 characters';
            $debug_info[] = "Validation failed: Response length out of range";
        } else {
            $debug_info[] = "Validation passed, updating settings...";
            
            $all_success = true;
            
            // Update bot name
            $bot_name_result = updateSettingDebug('bot_name', $bot_name);
            $all_success = $all_success && $bot_name_result;
            
            // Update personality
            $personality_result = updateSettingDebug('bot_personality', $bot_personality);
            $all_success = $all_success && $personality_result;
            
            // Update response length
            $length_result = updateSettingDebug('bot_response_length', $response_length);
            $all_success = $all_success && $length_result;
            
            // Update API key if provided
            if (!empty($gemini_api_key)) {
                $debug_info[] = "API key provided, attempting to save...";
                $api_key_result = updateSettingDebug('gemini_api_key', $gemini_api_key);
                $all_success = $all_success && $api_key_result;
            } else {
                $debug_info[] = "No API key provided, skipping update";
            }
            
            if ($all_success) {
                $success = 'Bot settings updated successfully';
                $debug_info[] = "All updates completed successfully";
            } else {
                $error = 'Failed to update some bot settings';
                $debug_info[] = "Some updates failed";
            }
        }
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $debug_info[] = "POST request received but CSRF verification failed";
        $debug_info[] = "Expected CSRF token, got: " . ($_POST['csrf_token'] ?? 'NONE');
    }
}

// Get current bot settings with debugging
$debug_info[] = "Retrieving current settings...";

try {
    global $db;
    $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('bot_name', 'gemini_api_key', 'bot_personality', 'bot_response_length')");
    $current_settings = $db->resultset();
    
    $debug_info[] = "Found " . count($current_settings) . " settings in database";
    foreach ($current_settings as $setting) {
        $debug_info[] = "- {$setting['setting_key']}: " . (strlen($setting['setting_value']) > 50 ? substr($setting['setting_value'], 0, 50) . '...' : $setting['setting_value']);
    }
} catch (Exception $e) {
    $debug_info[] = "Error retrieving settings: " . $e->getMessage();
}

$bot_name = getSetting('bot_name') ?: 'ODFEL Assistant';
$gemini_api_key = getSetting('gemini_api_key');
$bot_personality = getSetting('bot_personality') ?: 'You are a helpful and friendly AI assistant.';
$response_length = getSetting('bot_response_length') ?: 200;

$page_title = 'Bot Settings';
include 'includes/admin_header.php';
?>

<div class="bot-management">
    <!-- Debug Information Panel -->
    <div class="debug-panel" style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; border-radius: 8px; max-height: 300px; overflow-y: auto;">
        <h5 style="margin: 0 0 10px 0; color: #495057;">Debug Information</h5>
        <div style="font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.4;">
            <?php foreach ($debug_info as $info): ?>
                <div style="margin-bottom: 2px; color: #6c757d;"><?php echo htmlspecialchars($info); ?></div>
            <?php endforeach; ?>
        </div>
        <button onclick="this.parentElement.style.display='none'" style="margin-top: 10px; padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Hide Debug</button>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
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
            <form method="POST" class="bot-form" onsubmit="return validateForm()">
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
                            type="text" 
                            id="gemini_api_key" 
                            name="gemini_api_key" 
                            placeholder="<?php echo !empty($gemini_api_key) ? 'API Key is set (leave blank to keep current) - Length: ' . strlen($gemini_api_key) : 'Enter your Gemini API key'; ?>"
                            class="form-control"
                            autocomplete="off"
                        >
                        <small>
                            Get your API key from 
                            <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                        </small>
                        <div id="api-key-info" style="margin-top: 5px; font-size: 11px; color: #666;"></div>
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
        
        <!-- Test and Analytics tabs remain the same -->
        <div id="test-tab" class="bot-tab-content">
            <div class="bot-test-section">
                <h4>Test Your AI Assistant</h4>
                <p>Send test messages to see how your bot responds</p>
                <!-- Test content... -->
            </div>
        </div>
        
        <div id="analytics-tab" class="bot-tab-content">
            <div class="analytics-section">
                <h4>Bot Analytics</h4>
                <!-- Analytics content... -->
            </div>
        </div>
    </div>
</div>

<!-- Your existing CSS styles -->
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

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #ffc107;
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
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

.form-actions {
    margin-top: 30px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #ffc107;
    color: #333;
}

.btn-primary:hover {
    background: #e0a800;
}

small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 0.9rem;
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
}

function updateRangeValue(value) {
    document.getElementById('rangeValue').textContent = value;
}

function validateForm() {
    const apiKey = document.getElementById('gemini_api_key').value;
    const botName = document.getElementById('bot_name').value;
    
    console.log('Form validation:');
    console.log('Bot name:', botName);
    console.log('API key length:', apiKey.length);
    console.log('API key preview:', apiKey.substring(0, 20) + '...');
    
    if (botName.trim() === '') {
        alert('Bot name is required');
        return false;
    }
    
    if (apiKey.length > 0 && apiKey.length < 30) {
        alert('API key seems too short. Google API keys are typically longer.');
        return false;
    }
    
    return true;
}

// Monitor API key input
document.getElementById('gemini_api_key').addEventListener('input', function() {
    const value = this.value;
    const info = document.getElementById('api-key-info');
    
    if (value.length > 0) {
        info.innerHTML = `Length: ${value.length} characters | Preview: ${value.substring(0, 20)}...`;
        info.style.color = value.length > 30 ? '#28a745' : '#ffc107';
    } else {
        info.innerHTML = '';
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>