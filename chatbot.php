<?php
// Check if this is an AJAX request for bot response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_request'])) {
    header('Content-Type: application/json');
    
    $apiKey = 'AIzaSyBu8WkfAboPIDMkVMMwixahwaZzQxSEUBw';
    $apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent';

    function sendToGemini($message, $apiKey, $apiUrl) {
        // Add school context to the message
        $schoolContext = "You are a helpful school assistant chatbot. You help students, teachers, and parents with school-related questions including academic support, school policies, extracurricular activities, schedules, admissions, and general school information. Always be professional, supportive, and educational in your responses. Here's the user's question: ";
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $schoolContext . $message]
                    ]
                ]
            ]
        ];

        $ch = curl_init($apiUrl . '?key=' . $apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            return "I apologize, but I'm having trouble connecting right now. Please try again or contact the school office for immediate assistance.";
        }

        $result = json_decode($response, true);
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? 'I apologize, but I cannot provide a response right now. Please try again.';
    }

    $response = [
        'success' => false,
        'response' => ''
    ];

    $userMessage = trim($_POST['message'] ?? '');
    if ($userMessage) {
        $botResponse = sendToGemini($userMessage, $apiKey, $apiUrl);
        $response['success'] = true;
        $response['response'] = $botResponse;
    } else {
        $response['response'] = 'Please enter your question or message.';
    }

    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Assistant Chatbot</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #4472c4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chatbot-container {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 700px;
            height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .chatbot-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 25px 20px;
            text-align: center;
            position: relative;
        }

        .chatbot-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>');
        }

        .chatbot-header h2 {
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 1.4rem;
            position: relative;
            z-index: 1;
        }

        .school-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 8px;
            display: inline-block;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
        }

        .welcome-message {
            text-align: center;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }

        .welcome-message h3 {
            margin: 0 0 10px 0;
            color: #1565c0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .welcome-message p {
            margin: 5px 0;
            color: #0d47a1;
            font-size: 0.9rem;
        }

        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
            justify-content: center;
        }

        .quick-action-btn {
            background: #2196f3;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            background: #1976d2;
            transform: translateY(-2px);
        }

        .message {
            display: flex;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease;
        }

        .message.user {
            justify-content: flex-end;
        }

        .message-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 12px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .message.user .message-avatar {
            background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
            margin-right: 0;
            margin-left: 12px;
            order: 2;
        }

        .message-content {
            max-width: 75%;
            background: white;
            border-radius: 18px;
            padding: 15px 18px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
        }

        .message.user .message-content {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
            border: none;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .username {
            font-weight: bold;
            font-size: 0.9rem;
        }

        .timestamp {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        .message-text {
            line-height: 1.5;
            word-wrap: break-word;
        }

        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .input-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        #message-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        #message-input:focus {
            border-color: #2196f3;
            background: white;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }

        .send-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }

        .send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(33, 150, 243, 0.4);
        }

        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid #2196f3;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-style: italic;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2196f3;
            animation: typing 1.4s infinite ease-in-out;
        }

        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }

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

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.4;
            }
            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .chatbot-container {
                width: 95%;
                height: 90vh;
            }
            
            .message-content {
                max-width: 85%;
            }

            .quick-actions {
                flex-direction: column;
                align-items: center;
            }

            .quick-action-btn {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="chatbot-container">
        <div class="chatbot-header">
            <h2>
                <i class="fas fa-graduation-cap"></i>
                School Assistant
            </h2>
            <div class="school-badge">
                <i class="fas fa-school"></i> Academic Support Center
            </div>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <div class="welcome-message">
                <h3>
                    <i class="fas fa-hand-paper"></i>
                    Welcome to School Assistant!
                </h3>
                <p>I'm here to help with your academic questions and school-related inquiries.</p>
                <p><strong>I can assist with:</strong></p>
                <p>📚 Academic subjects and homework help</p>
                <p>📅 School schedules and events</p>
                <p>🎓 Admissions and enrollment</p>
                <p>🏫 School policies and procedures</p>
                <p>🎯 Study tips and resources</p>
                
                <div class="quick-actions">
                    <button class="quick-action-btn" onclick="sendQuickMessage('What are the school hours?')">
                        <i class="fas fa-clock"></i> School Hours
                    </button>
                    <button class="quick-action-btn" onclick="sendQuickMessage('Help me with math homework')">
                        <i class="fas fa-calculator"></i> Math Help
                    </button>
                    <button class="quick-action-btn" onclick="sendQuickMessage('What extracurricular activities are available?')">
                        <i class="fas fa-users"></i> Activities
                    </button>
                    <button class="quick-action-btn" onclick="sendQuickMessage('How do I contact a teacher?')">
                        <i class="fas fa-envelope"></i> Contact Info
                    </button>
                </div>
            </div>
        </div>
        
        <div class="chat-input-container">
            <form id="message-form">
                <div class="input-group">
                    <input 
                        type="text" 
                        id="message-input" 
                        placeholder="Ask about academics, schedules, policies, or anything school-related..."
                        maxlength="500"
                        autocomplete="off"
                        required
                    >
                    <button type="submit" class="send-btn" id="send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setupMessageForm();
        });

        function setupMessageForm() {
            const form = document.getElementById('message-form');
            const input = document.getElementById('message-input');
            
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
        }

        function sendQuickMessage(message) {
            const input = document.getElementById('message-input');
            input.value = message;
            sendMessage();
        }

        function sendMessage() {
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            
            if (!message) return;
            
            const sendBtn = document.getElementById('send-btn');
            sendBtn.disabled = true;
            
            // Add user message to chat
            addMessage(message, false);
            input.value = '';
            
            // Add typing indicator
            const loadingDiv = addTypingIndicator();
            
            // Send to bot
            const formData = new FormData();
            formData.append('message', message);
            formData.append('ajax_request', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Remove typing indicator
                loadingDiv.remove();
                
                if (data.success) {
                    addMessage(data.response, true);
                } else {
                    addMessage('I apologize, but I encountered an error. Please try again or contact the school office for immediate assistance.', true);
                }
            })
            .catch(error => {
                // Remove typing indicator
                loadingDiv.remove();
                addMessage('I\'m having trouble connecting right now. Please try again or contact the school office for immediate assistance.', true);
            })
            .finally(() => {
                sendBtn.disabled = false;
                input.focus();
            });
        }

        function addMessage(message, isBot) {
            const messagesContainer = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message' + (isBot ? '' : ' user');
            
            const currentTime = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    ${isBot ? '<i class="fas fa-graduation-cap"></i>' : '<i class="fas fa-user-graduate"></i>'}
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <span class="username">${isBot ? 'School Assistant' : 'You'}</span>
                        <span class="timestamp">${currentTime}</span>
                    </div>
                    <div class="message-text">
                        ${message}
                    </div>
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            return messageDiv;
        }

        function addTypingIndicator() {
            const messagesContainer = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message';
            
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <span class="username">School Assistant</span>
                        <span class="timestamp">Now</span>
                    </div>
                    <div class="message-text">
                        <div class="typing-indicator">
                            <span>Thinking</span>
                            <div class="dot"></div>
                            <div class="dot"></div>
                            <div class="dot"></div>
                        </div>
                    </div>
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            return messageDiv;
        }
    </script>
</body>
</html>