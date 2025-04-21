document.addEventListener('DOMContentLoaded', function() {
    // Chatbot elements
    const chatbotContainer = document.getElementById('chatbot-container');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotSend = document.getElementById('chatbot-send');
    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatbotThemeToggle = document.getElementById('chatbot-theme-toggle');

    // Theme toggle functionality
    chatbotThemeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const isDarkMode = document.body.classList.contains('dark-mode');
        localStorage.setItem('chatbot-theme', isDarkMode ? 'dark' : 'light');
        updateThemeIcon(isDarkMode ? 'dark' : 'light');
    });

    function updateThemeIcon(theme) {
        const icon = chatbotThemeToggle.querySelector('i');
        if (theme === 'dark') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    }

    // Check for saved theme preference
    const savedTheme = localStorage.getItem('chatbot-theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        updateThemeIcon('dark');
    }

    // Toggle chatbot visibility
    chatbotToggle.addEventListener('click', function() {
        chatbotContainer.classList.toggle('hidden');
    });

    // Function to clean response text
    function cleanResponse(text) {
        // Remove markdown formatting and clean up the text
        return text.replace(/\*\*/g, '')
                  .replace(/\n/g, '<br>')
                  .replace(/\d+\.\s/g, '<br>$&');
    }

    // Send message function
    function sendMessage() {
        const message = chatbotInput.value.trim();
        if (!message) return;

        // Add user message to chat
        addMessage(message, 'user');
        chatbotInput.value = '';

        // Show loading state
        const loadingMessage = addMessage('Thinking...', 'nexus loading');
        
        // Send message to server
        fetch('api/chatbot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading message
            loadingMessage.remove();
            
            if (data.success) {
                // Clean the response text before displaying
                const cleanReply = cleanResponse(data.reply);
                addMessage(cleanReply, 'nexus');
            } else {
                addMessage('Sorry, I encountered an error. Please try again.', 'nexus error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingMessage.remove();
            addMessage('Sorry, I encountered an error. Please try again.', 'nexus error');
        });
    }

    // Add message to chat
    function addMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message ${type}`;
        
        const label = document.createElement('div');
        label.className = 'message-label';
        label.textContent = type === 'user' ? 'You' : 'Nexus';
        
        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.innerHTML = message;
        
        messageDiv.appendChild(label);
        messageDiv.appendChild(bubble);
        chatbotMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        
        return messageDiv;
    }

    // Event listeners for sending messages
    chatbotSend.addEventListener('click', sendMessage);
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Initially hide the chatbot
    chatbotContainer.classList.add('hidden');
});

<?php
// Add this at the top of chatbot.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Example of making a POST request to an external API
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta2/models/text-bison-001:generateText?key=AIzaSyDQyJ4G_3sOxGxd6pIuNS_69iLDYI-7Z4w",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode([
        "prompt" => [
            "text" => "Hello, how are you?"
        ]
    ])
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    echo "Error: " . curl_error($curl);
} else {
    echo $response;
}

curl_close($curl);