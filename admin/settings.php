<?php
require_once 'includes/admin_functions.php';

// Require admin access
requireAdmin();

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $site_name = sanitize($_POST['site_name'] ?? '');
    $bot_name = sanitize($_POST['bot_name'] ?? '');
    $max_message_length = (int)($_POST['max_message_length'] ?? 500);
    $gemini_api_key = trim($_POST['gemini_api_key'] ?? ''); // Added trim to remove whitespace
    
    // Validate
    if (empty($site_name)) {
        $error = 'Site name cannot be empty';
    } elseif (empty($bot_name)) {
        $error = 'Bot name cannot be empty';
    } elseif ($max_message_length < 100 || $max_message_length > 1000) {
        $error = 'Max message length must be between 100 and 1000';
    } else {
        // Update settings
        $updated = true;
        $updated = $updated && updateSetting('site_name', $site_name);
        $updated = $updated && updateSetting('bot_name', $bot_name);
        $updated = $updated && updateSetting('max_message_length', $max_message_length);
        
        // Always update API key (this allows clearing the key if needed)
        $api_updated = updateSetting('gemini_api_key', $gemini_api_key);
        
        // Log for debugging
        error_log("API Key update - Value: " . ($gemini_api_key ? '[SET]' : '[EMPTY]') . ", Result: " . ($api_updated ? 'SUCCESS' : 'FAILED'));
        
        $updated = $updated && $api_updated;
        
        if ($updated) {
            $success = 'Settings updated successfully';
            // If API key was provided, add confirmation
            if (!empty($gemini_api_key)) {
                $success .= ' - API key has been updated';
            } elseif (isset($_POST['gemini_api_key'])) {
                $success .= ' - API key has been cleared';
            }
        } else {
            $error = 'Failed to update settings. Please check the error logs.';
        }
    }
}

// Get current settings
$site_name = getSetting('site_name');
$bot_name = getSetting('bot_name');
$max_message_length = getSetting('max_message_length');
$gemini_api_key = getSetting('gemini_api_key');

$page_title = 'Site Settings';
include 'includes/admin_header.php';
?>

<div class="settings-management">
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="settings-container">
        <div class="settings-header">
            <h3>
                <i class="fas fa-cogs"></i>
                Site Settings
            </h3>
            <p>Configure your ODFEL ChatBot application settings</p>
        </div>
        
        <form method="POST" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="settings-section">
                <h4>General Settings</h4>
                
                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input 
                        type="text" 
                        id="site_name" 
                        name="site_name" 
                        value="<?php echo htmlspecialchars($site_name); ?>"
                        class="form-control"
                        required
                    >
                    <small>The name of your chatbot application</small>
                </div>
                
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
                    <label for="max_message_length">Max Message Length</label>
                    <input 
                        type="number" 
                        id="max_message_length" 
                        name="max_message_length" 
                        value="<?php echo $max_message_length; ?>"
                        class="form-control"
                        min="100"
                        max="1000"
                        required
                    >
                    <small>Maximum characters allowed per message (100-1000)</small>
                </div>
            </div>
            
            <div class="settings-section">
                <h4>AI Assistant Configuration</h4>
                
                <div class="form-group">
                    <label for="gemini_api_key">Google Gemini API Key</label>
                    <div class="api-key-container">
                        <input 
                            type="password" 
                            id="gemini_api_key" 
                            name="gemini_api_key" 
                            placeholder="<?php echo !empty($gemini_api_key) ? 'API Key is set (enter new key to update)' : 'Enter your Gemini API key'; ?>"
                            class="form-control"
                        >
                        <button type="button" class="btn btn-icon" onclick="toggleApiKeyVisibility()" title="Show/Hide API Key">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    <small>
                        Get your API key from 
                        <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                        <br>
                        <strong>Note:</strong> Enter a new key to update, or leave blank to clear the current key
                    </small>
                </div>
                
                <div class="api-status">
                    <div class="status-indicator">
                        <span class="status-dot <?php echo !empty($gemini_api_key) ? 'active' : 'inactive'; ?>"></span>
                        <span class="status-text">
                            AI Assistant: <?php echo !empty($gemini_api_key) ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($gemini_api_key)): ?>
                    <button type="button" class="btn btn-secondary" onclick="testApiKey()">
                        <i class="fas fa-vial"></i>
                        Test API Connection
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Settings
                </button>
                
                <button type="button" class="btn btn-secondary" onclick="resetToDefaults()">
                    <i class="fas fa-undo"></i>
                    Reset to Defaults
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.settings-management {
    max-width: 800px;
    margin: 0 auto;
}

.settings-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.settings-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    text-align: center;
}

.settings-header h3 {
    margin: 0 0 10px 0;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.settings-header p {
    margin: 0;
    opacity: 0.9;
}

.settings-form {
    padding: 30px;
}

.settings-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #eee;
}

.settings-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.settings-section h4 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.api-key-container {
    position: relative;
    display: flex;
    align-items: center;
}

.api-key-container .form-control {
    padding-right: 50px;
}

.btn-icon {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 5px;
    border-radius: 3px;
}

.btn-icon:hover {
    background: #f0f0f0;
    color: #333;
}

.api-status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 15px;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #dc3545;
}

.status-dot.active {
    background: #28a745;
    animation: pulse 2s infinite;
}

.status-text {
    font-weight: 600;
    color: #333;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

@media (max-width: 768px) {
    .settings-form {
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .api-status {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>

<script>
function toggleApiKeyVisibility() {
    const input = document.getElementById('gemini_api_key');
    const icon = document.getElementById('toggleIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function testApiKey() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    btn.disabled = true;
    
    // Test API connection
    fetch('../pages/test_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            test_message: 'Hello, this is a test message.'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Connection Successful!';
            btn.style.background = '#28a745';
            btn.style.color = 'white';
        } else {
            btn.innerHTML = '<i class="fas fa-times"></i> Connection Failed';
            btn.style.background = '#dc3545';
            btn.style.color = 'white';
            console.error('API Test Error:', data.error || 'Unknown error');
        }
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
            btn.style.color = '';
            btn.disabled = false;
        }, 3000);
    })
    .catch(error => {
        console.error('API Test Error:', error);
        btn.innerHTML = '<i class="fas fa-times"></i> Test Failed';
        btn.style.background = '#dc3545';
        btn.style.color = 'white';
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
            btn.style.color = '';
            btn.disabled = false;
        }, 3000);
    });
}

function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to their default values? This will clear your API key.')) {
        document.getElementById('site_name').value = 'ODFEL ChatBot';
        document.getElementById('bot_name').value = 'ODFEL Assistant';
        document.getElementById('max_message_length').value = '500';
        document.getElementById('gemini_api_key').value = '';
    }
}

// Show confirmation when form is submitted
document.querySelector('.settings-form').addEventListener('submit', function(e) {
    const apiKey = document.getElementById('gemini_api_key').value.trim();
    if (apiKey === '') {
        const currentlyHasKey = <?php echo !empty($gemini_api_key) ? 'true' : 'false'; ?>;
        if (currentlyHasKey) {
            if (!confirm('You have left the API key field blank. This will clear your current API key. Continue?')) {
                e.preventDefault();
                return;
            }
        }
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>