<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin access
requireAdmin();

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $settings = [
            'gemini_api_key' => sanitize($_POST['gemini_api_key'] ?? ''),
            'site_name' => sanitize($_POST['site_name'] ?? ''),
            'bot_name' => sanitize($_POST['bot_name'] ?? ''),
            'max_message_length' => intval($_POST['max_message_length'] ?? 500)
        ];
        
        $updated = 0;
        foreach ($settings as $key => $value) {
            if (updateSetting($key, $value)) {
                $updated++;
            }
        }
        
        if ($updated > 0) {
            $success = 'Settings updated successfully!';
        } else {
            $error = 'Failed to update settings';
        }
    }
}

// Get current settings
$current_settings = [
    'gemini_api_key' => getSetting('gemini_api_key') ?: '',
    'site_name' => getSetting('site_name') ?: 'ODFEL ChatBot',
    'bot_name' => getSetting('bot_name') ?: 'ODFEL Assistant',
    'max_message_length' => getSetting('max_message_length') ?: 500
];

$page_title = 'Site Settings';
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>
            <i class="fas fa-cogs"></i>
            Site Settings
        </h1>
        <p>Configure general site settings and preferences</p>
        <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="admin-card">
        <h3>
            <i class="fas fa-sliders-h"></i>
            General Settings
        </h3>
        
        <form method="POST" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="site_name">Site Name</label>
                <input 
                    type="text" 
                    id="site_name" 
                    name="site_name" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($current_settings['site_name']); ?>"
                    required
                >
                <small style="color: #666;">The name of your chat application</small>
            </div>
            
            <div class="form-group">
                <label for="bot_name">Bot Name</label>
                <input 
                    type="text" 
                    id="bot_name" 
                    name="bot_name" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($current_settings['bot_name']); ?>"
                    required
                >
                <small style="color: #666;">Display name for the AI assistant</small>
            </div>
            
            <div class="form-group">
                <label for="max_message_length">Maximum Message Length</label>
                <input 
                    type="number" 
                    id="max_message_length" 
                    name="max_message_length" 
                    class="form-control" 
                    value="<?php echo $current_settings['max_message_length']; ?>"
                    min="50"
                    max="2000"
                    required
                >
                <small style="color: #666;">Maximum number of characters allowed per message (50-2000)</small>
            </div>
            
            <div class="form-group">
                <label for="gemini_api_key">Gemini API Key</label>
                <input 
                    type="password" 
                    id="gemini_api_key" 
                    name="gemini_api_key" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($current_settings['gemini_api_key']); ?>"
                    placeholder="Enter your Google Gemini API key"
                >
                <small style="color: #666;">
                    Get your API key from 
                    <a href="https://makersuite.google.com/app/apikey" target="_blank" style="color: #667eea;">
                        Google AI Studio
                    </a>
                </small>
            </div>
            
            <button type="submit" class="btn" style="margin-top: 20px;">
                <i class="fas fa-save"></i>
                Save Settings
            </button>
        </form>
    </div>
    
    <!-- API Status Check -->
    <div class="admin-card">
        <h3>
            <i class="fas fa-robot"></i>
            AI Assistant Status
        </h3>
        
        <div id="api-status">
            <?php if (empty($current_settings['gemini_api_key'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    Gemini API key is not configured. The AI assistant will not work.
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    API key is configured. Click the button below to test the connection.
                </div>
                
                <button onclick="testApiConnection()" class="btn" id="test-api-btn">
                    <i class="fas fa-plug"></i>
                    Test API Connection
                </button>
                
                <div id="api-test-result" style="margin-top: 15px;"></div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- System Information -->
    <div class="admin-card">
        <h3>
            <i class="fas fa-info-circle"></i>
            System Information
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <strong>PHP Version:</strong><br>
                <span style="color: #667eea;"><?php echo PHP_VERSION; ?></span>
            </div>
            
            <div>
                <strong>Server Software:</strong><br>
                <span style="color: #667eea;"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
            </div>
            
            <div>
                <strong>Database:</strong><br>
                <span style="color: #667eea;">MySQL</span>
            </div>
            
            <div>
                <strong>Application Version:</strong><br>
                <span style="color: #667eea;">1.0.0</span>
            </div>
        </div>
    </div>
</div>

<script>
function testApiConnection() {
    const button = document.getElementById('test-api-btn');
    const result = document.getElementById('api-test-result');
    
    button.disabled = true;
    button.innerHTML = '<div class="loading"></div> Testing...';
    
    fetch('test_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'csrf_token=<?php echo generateCSRFToken(); ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            result.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> API connection successful!</div>';
        } else {
            result.innerHTML = '<div class="alert alert-error"><i class="fas fa-times-circle"></i> API connection failed: ' + data.message + '</div>';
        }
    })
    .catch(error => {
        result.innerHTML = '<div class="alert alert-error"><i class="fas fa-times-circle"></i> Network error occurred</div>';
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-plug"></i> Test API Connection';
    });
}
</script>

<?php include '../includes/footer.php'; ?>