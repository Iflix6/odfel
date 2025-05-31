<?php
require_once 'includes/admin_functions.php';

// Require admin access
requireAdmin();

// Get comprehensive statistics
$stats = getAdminStats();

$page_title = 'Admin Dashboard';
include 'includes/admin_header.php';
?>

<div class="admin-dashboard">
    <!-- Key Metrics -->
    <div class="metrics-grid">
        <div class="metric-card users">
            <div class="metric-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="metric-content">
                <div class="metric-number"><?php echo $stats['total_users']; ?></div>
                <div class="metric-label">Total Users</div>
                <div class="metric-change positive">
                    <i class="fas fa-arrow-up"></i>
                    +<?php echo $stats['new_users_today']; ?> today
                </div>
            </div>
        </div>

        <div class="metric-card messages">
            <div class="metric-icon">
                <i class="fas fa-comments"></i>
            </div>
            <div class="metric-content">
                <div class="metric-number"><?php echo $stats['total_messages']; ?></div>
                <div class="metric-label">Total Messages</div>
                <div class="metric-change positive">
                    <i class="fas fa-arrow-up"></i>
                    +<?php echo $stats['messages_today']; ?> today
                </div>
            </div>
        </div>

        <div class="metric-card bot">
            <div class="metric-icon">
                <i class="fas fa-robot"></i>
            </div>
            <div class="metric-content">
                <div class="metric-number"><?php echo $stats['bot_messages']; ?></div>
                <div class="metric-label">Bot Responses</div>
                <div class="metric-change">
                    <i class="fas fa-percentage"></i>
                    <?php echo $stats['bot_percentage']; ?>% of total
                </div>
            </div>
        </div>

        <div class="metric-card active">
            <div class="metric-icon">
                <i class="fas fa-circle"></i>
            </div>
            <div class="metric-content">
                <div class="metric-number"><?php echo $stats['active_users']; ?></div>
                <div class="metric-label">Active Users</div>
                <div class="metric-change">
                    <i class="fas fa-clock"></i>
                    Last 24 hours
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="admin-grid">
        <div class="admin-card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-users"></i>
                    User Management
                </h3>
                <span class="card-badge"><?php echo $stats['total_users']; ?></span>
            </div>
            <div class="card-content">
                <p>Manage user accounts, roles, and permissions. View user activity and moderate accounts.</p>
                <div class="card-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['admin_users']; ?></span>
                        <span class="stat-label">Admins</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['active_users']; ?></span>
                        <span class="stat-label">Active</span>
                    </div>
                </div>
            </div>
            <div class="card-actions">
                <a href="users.php" class="btn">Manage Users</a>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-comments"></i>
                    Chat Management
                </h3>
                <span class="card-badge"><?php echo $stats['total_messages']; ?></span>
            </div>
            <div class="card-content">
                <p>Monitor chat activity, moderate messages, and view conversation analytics.</p>
                <div class="card-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['messages_today']; ?></span>
                        <span class="stat-label">Today</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['avg_messages_per_user']; ?></span>
                        <span class="stat-label">Avg/User</span>
                    </div>
                </div>
            </div>
            <div class="card-actions">
                <a href="messages.php" class="btn">Manage Messages</a>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-robot"></i>
                    AI Assistant
                </h3>
                <span class="card-badge <?php echo $stats['bot_status']; ?>"><?php echo ucfirst($stats['bot_status']); ?></span>
            </div>
            <div class="card-content">
                <p>Configure AI assistant settings, API keys, and monitor bot performance.</p>
                <div class="card-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['bot_messages']; ?></span>
                        <span class="stat-label">Responses</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['bot_accuracy']; ?>%</span>
                        <span class="stat-label">Accuracy</span>
                    </div>
                </div>
            </div>
            <div class="card-actions">
                <a href="bot.php" class="btn">Bot Settings</a>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-cogs"></i>
                    System Settings
                </h3>
                <span class="card-badge">Active</span>
            </div>
            <div class="card-content">
                <p>Configure site settings, security options, and system preferences.</p>
                <div class="card-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['system_uptime']; ?></span>
                        <span class="stat-label">Uptime</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['storage_used']; ?>%</span>
                        <span class="stat-label">Storage</span>
                    </div>
                </div>
            </div>
            <div class="card-actions">
                <a href="settings.php" class="btn">Site Settings</a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="activity-section">
        <div class="admin-card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-chart-line"></i>
                    Recent Activity
                </h3>
                <div class="card-actions">
                    <button onclick="refreshActivity()" class="btn btn-sm">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                </div>
            </div>
            <div class="card-content">
                <div class="activity-tabs">
                    <button class="tab-btn active" onclick="showTab('messages')">Recent Messages</button>
                    <button class="tab-btn" onclick="showTab('users')">New Users</button>
                    <button class="tab-btn" onclick="showTab('system')">System Logs</button>
                </div>

                <div id="messages-tab" class="tab-content active">
                    <div class="activity-list">
                        <?php foreach ($stats['recent_messages'] as $message): ?>
                        <div class="activity-item">
                            <div class="activity-avatar">
                                <?php if ($message['is_bot']): ?>
                                    <i class="fas fa-robot"></i>
                                <?php else: ?>
                                    <?php echo strtoupper(substr($message['username'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <span class="activity-user"><?php echo htmlspecialchars($message['username']); ?></span>
                                    <span class="activity-time"><?php echo timeAgo($message['created_at']); ?></span>
                                </div>
                                <div class="activity-message">
                                    <?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?>
                                    <?php if (strlen($message['message']) > 100): ?>...<?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="users-tab" class="tab-content">
                    <div class="activity-list">
                        <?php foreach ($stats['recent_users'] as $user): ?>
                        <div class="activity-item">
                            <div class="activity-avatar">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <span class="activity-user"><?php echo htmlspecialchars($user['username']); ?></span>
                                    <span class="activity-time"><?php echo timeAgo($user['created_at']); ?></span>
                                </div>
                                <div class="activity-message">
                                    New user registered: <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="system-tab" class="tab-content">
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-avatar">
                                <i class="fas fa-server"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <span class="activity-user">System</span>
                                    <span class="activity-time">2 hours ago</span>
                                </div>
                                <div class="activity-message">Database backup completed successfully</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-avatar">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <span class="activity-user">Security</span>
                                    <span class="activity-time">5 hours ago</span>
                                </div>
                                <div class="activity-message">Security scan completed - no threats detected</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    max-width: 1400px;
    margin: 0 auto;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.metric-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-5px);
}

.metric-card.users { border-left: 5px solid #667eea; }
.metric-card.messages { border-left: 5px solid #28a745; }
.metric-card.bot { border-left: 5px solid #ffc107; }
.metric-card.active { border-left: 5px solid #dc3545; }

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.metric-card.users .metric-icon { background: #667eea; }
.metric-card.messages .metric-icon { background: #28a745; }
.metric-card.bot .metric-icon { background: #ffc107; }
.metric-card.active .metric-icon { background: #dc3545; }

.metric-content {
    flex: 1;
}

.metric-number {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    line-height: 1;
}

.metric-label {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.metric-change {
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.metric-change.positive { color: #28a745; }

.admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.admin-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.admin-card:hover {
    transform: translateY(-5px);
}

.card-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-badge {
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.card-badge.active { background: #28a745; }
.card-badge.inactive { background: #dc3545; }

.card-content {
    padding: 20px;
}

.card-content p {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.6;
}

.card-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
}

.stat-label {
    font-size: 0.8rem;
    color: #666;
}

.card-actions {
    padding: 20px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
}

.activity-section {
    margin-bottom: 30px;
}

.activity-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.tab-btn {
    background: none;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.activity-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.activity-user {
    font-weight: 600;
    color: #333;
}

.activity-time {
    font-size: 0.8rem;
    color: #666;
}

.activity-message {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
}
</style>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
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

function refreshActivity() {
    // Add loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<div class="loading"></div>';
    btn.disabled = true;
    
    // Simulate refresh (in real app, this would fetch new data)
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        location.reload();
    }, 1000);
}
</script>

<?php include 'includes/admin_footer.php'; ?>
