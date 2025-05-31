<?php
// Get site name from settings
$site_name = getSetting('site_name') ?: 'ODFEL ChatBot';
$page_title = $page_title ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | <?php echo htmlspecialchars($site_name); ?> Admin</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../../public/img/favicon.ico" type="image/x-icon">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    
    <!-- Admin-specific styles -->
    <style>
        .admin-sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .admin-sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .admin-sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .admin-menu {
            padding: 20px 0;
        }
        
        .admin-menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .admin-menu-item:hover, .admin-menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .admin-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .admin-user-menu {
            position: relative;
        }
        
        .admin-user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 5px;
            min-width: 200px;
            display: none;
            z-index: 1000;
        }
        
        .admin-user-dropdown.show {
            display: block;
        }
        
        .admin-user-dropdown a {
            display: block;
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        
        .admin-user-dropdown a:hover {
            background: #f8f9fa;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .admin-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3><?php echo htmlspecialchars($site_name); ?></h3>
            <p>Admin Panel</p>
        </div>
        
        <div class="admin-menu">
            <a href="./dashboard.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            
            <a href="./users.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                Manage Users
            </a>
            
            <a href="./messages.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i>
                Manage Messages
            </a>
            
            <a href="./bot.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'bot.php' ? 'active' : ''; ?>">
                <i class="fas fa-robot"></i>
                Bot Settings
            </a>
            
            <a href="./settings.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cogs"></i>
                Site Settings
            </a>
            
            <a href="./profile.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i>
                Admin Profile
            </a>
            
            <a href="../pages/chatroom.php" class="admin-menu-item">
                <i class="fas fa-comments"></i>
                View Chat Room
            </a>
            
            <a href="../../pages/logout.php" class="admin-menu-item">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>
    
    <!-- Admin Content -->
    <div class="admin-content">
        <div class="admin-header">
            <h2><?php echo htmlspecialchars($page_title); ?></h2>
            
            <div class="admin-user-menu">
                <button class="btn" onclick="toggleAdminUserMenu()">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                    <i class="fas fa-chevron-down"></i>
                </button>
                
                <div class="admin-user-dropdown" id="adminUserDropdown">
                    <a href="../profile.php">
                        <i class="fas fa-user-circle"></i>
                        My Profile
                    </a>
                    <a href="../../pages/chatroom.php">
                        <i class="fas fa-comments"></i>
                        Chat Room
                    </a>
                    <a href="../../pages/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Page content starts here -->
