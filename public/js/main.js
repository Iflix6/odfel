// Main JavaScript file for ODFEL ChatBot Application

document.addEventListener('DOMContentLoaded', function() {
    // Initialize application
    initializeApp();
});

function initializeApp() {
    // Mobile navigation toggle
    initMobileNav();
    
    // Initialize chat if on chat page
    if (document.getElementById('chat-container')) {
        initializeChat();
    }
    
    // Initialize forms
    initializeForms();
    
    // Initialize admin features
    if (document.querySelector('.admin-container')) {
        initializeAdmin();
    }
}

// Mobile Navigation
function initMobileNav() {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }
}

// Chat Functionality
function initializeChat() {
    const chatMessages = document.getElementById('chat-messages');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    
    if (!messageForm) return;
    
    // Load initial messages
    loadMessages();
    
    // Auto-refresh messages every 3 seconds
    setInterval(loadMessages, 3000);
    
    // Handle form submission
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    // Handle Enter key
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
}

function sendMessage() {
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    // Disable input while sending
    messageInput.disabled = true;
    sendButton.disabled = true;
    sendButton.innerHTML = '<div class="loading"></div>';
    
    // Add user message to chat immediately
    addMessageToChat({
        username: currentUser,
        message: message,
        is_bot: false,
        created_at: new Date().toISOString()
    });
    
    // Send to server
    fetch('send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `message=${encodeURIComponent(message)}&csrf_token=${csrfToken}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            messageInput.style.height = 'auto';
            
            // If bot response, add it to chat
            if (data.bot_response) {
                setTimeout(() => {
                    addMessageToChat({
                        username: 'ODFEL Assistant',
                        message: data.bot_response,
                        is_bot: true,
                        created_at: new Date().toISOString()
                    });
                }, 1000);
            }
        } else {
            showAlert('Error sending message: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Network error occurred', 'error');
    })
    .finally(() => {
        // Re-enable input
        messageInput.disabled = false;
        sendButton.disabled = false;
        sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
        messageInput.focus();
    });
}

function loadMessages() {
    fetch('get_messages.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateChatMessages(data.messages);
        }
    })
    .catch(error => {
        console.error('Error loading messages:', error);
    });
}

function updateChatMessages(messages) {
    const chatMessages = document.getElementById('chat-messages');
    if (!chatMessages) return;
    
    const currentScroll = chatMessages.scrollTop;
    const isScrolledToBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= currentScroll + 1;
    
    chatMessages.innerHTML = '';
    
    messages.forEach(message => {
        addMessageToChat(message, false);
    });
    
    // Maintain scroll position or scroll to bottom if user was at bottom
    if (isScrolledToBottom) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

function addMessageToChat(message, scrollToBottom = true) {
    const chatMessages = document.getElementById('chat-messages');
    if (!chatMessages) return;
    
    const messageElement = document.createElement('div');
    messageElement.className = `message ${message.is_bot ? 'bot' : 'user'}`;
    
    const timeAgo = formatTimeAgo(message.created_at);
    
    if (message.is_bot) {
        messageElement.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-username">${escapeHtml(message.username || 'ODFEL Assistant')}</div>
                <div class="message-text">${escapeHtml(message.message)}</div>
                <div class="message-time">${timeAgo}</div>
            </div>
        `;
    } else {
        messageElement.innerHTML = `
            <div class="message-content">
                <div class="message-username">${escapeHtml(message.username)}</div>
                <div class="message-text">${escapeHtml(message.message)}</div>
                <div class="message-time">${timeAgo}</div>
            </div>
            <div class="message-avatar">
                ${message.username.charAt(0).toUpperCase()}
            </div>
        `;
    }
    
    chatMessages.appendChild(messageElement);
    
    if (scrollToBottom) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// Form Validation
function initializeForms() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let message = '';
    
    // Remove existing error
    removeFieldError(field);
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        message = 'This field is required';
    }
    
    // Email validation
    else if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Please enter a valid email address';
        }
    }
    
    // Password validation
    else if (field.name === 'password' && value) {
        if (value.length < 6) {
            isValid = false;
            message = 'Password must be at least 6 characters long';
        }
    }
    
    // Confirm password validation
    else if (field.name === 'confirm_password' && value) {
        const password = document.querySelector('input[name="password"]');
        if (password && value !== password.value) {
            isValid = false;
            message = 'Passwords do not match';
        }
    }
    
    if (!isValid) {
        showFieldError(field, message);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('error');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    
    field.parentNode.appendChild(errorElement);
}

function removeFieldError(field) {
    field.classList.remove('error');
    
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Admin Functions
function initializeAdmin() {
    // Initialize data tables
    initializeDataTables();
    
    // Initialize admin forms
    initializeAdminForms();
    
    // Load admin stats
    loadAdminStats();
}

function initializeDataTables() {
    const tables = document.querySelectorAll('.data-table');
    
    tables.forEach(table => {
        // Add sorting functionality
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, this.dataset.sort);
            });
        });
    });
}

function sortTable(table, column) {
    // Basic table sorting implementation
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aVal = a.querySelector(`td[data-${column}]`).textContent.trim();
        const bVal = b.querySelector(`td[data-${column}]`).textContent.trim();
        
        return aVal.localeCompare(bVal);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

function loadAdminStats() {
    fetch('admin_stats.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatsDisplay(data.stats);
        }
    })
    .catch(error => {
        console.error('Error loading admin stats:', error);
    });
}

function updateStatsDisplay(stats) {
    Object.keys(stats).forEach(key => {
        const element = document.getElementById(`stat-${key}`);
        if (element) {
            element.textContent = stats[key];
        }
    });
}

// Utility Functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' min ago';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hr ago';
    
    return date.toLocaleDateString();
}

function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alert-container';
    container.style.cssText = `
        position: fixed;
        top: 90px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    `;
    document.body.appendChild(container);
    return container;
}

// Global variables (set by PHP)
let currentUser = '';
let csrfToken = '';
let isAdmin = false;

// Set global variables from PHP
function setGlobalVars(user, token, admin) {
    currentUser = user;
    csrfToken = token;
    isAdmin = admin;
}