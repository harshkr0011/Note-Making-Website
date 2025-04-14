class AIChat {
    constructor() {
        this.chatContainer = document.getElementById('aiChatContainer');
        this.chatMessages = document.getElementById('aiChatMessages');
        this.chatInput = document.getElementById('aiChatInput');
        this.sendButton = document.getElementById('aiChatSend');
        this.toggleButtons = document.querySelectorAll('#toggleAIChat');
        this.settingsButton = document.getElementById('aiSettingsButton');
        this.settingsModal = document.getElementById('aiSettingsModal');
        
        // Ensure chat container is properly initialized
        if (this.chatContainer) {
            this.chatContainer.style.display = 'flex';
            this.chatContainer.classList.remove('hidden');
            this.chatContainer.style.opacity = '1';
            this.chatContainer.style.visibility = 'visible';
            this.chatContainer.style.pointerEvents = 'auto';
        }
        
        this.initializeEventListeners();
        this.loadSettings();
    }

    initializeEventListeners() {
        if (!this.chatContainer) return;

        this.sendButton.addEventListener('click', () => this.sendMessage());
        this.chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
        
        // Handle all toggle buttons
        this.toggleButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleChat();
            });
        });

        this.settingsButton.addEventListener('click', () => {
            this.settingsModal.classList.toggle('show');
        });

        // Close settings modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === this.settingsModal) {
                this.settingsModal.classList.remove('show');
            }
        });
    }

    async sendMessage() {
        const message = this.chatInput.value.trim();
        if (!message) return;

        // Add user message to chat
        this.addMessage('user', message);
        this.chatInput.value = '';

        try {
            const response = await fetch('api/ai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'chat',
                    message: message
                })
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Failed to send message');
            }
            
            if (data.error) {
                this.addMessage('ai', 'Error: ' + data.error);
            } else if (data.response) {
                this.addMessage('ai', data.response);
            }
        } catch (error) {
            console.error('Error:', error);
            this.addMessage('ai', 'Hello! I received your message: ' + message);
        }
    }

    addMessage(sender, message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}-message`;
        messageDiv.textContent = message;
        this.chatMessages.appendChild(messageDiv);
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    }

    async loadSettings() {
        try {
            const response = await fetch('api/ai.php?action=settings');
            const data = await response.json();
            if (data.settings) {
                this.updateSettingsUI(data.settings);
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    updateSettingsUI(settings) {
        document.getElementById('autoSummarize').checked = settings.auto_summarize;
        document.getElementById('summaryLength').value = settings.summary_length;
        document.getElementById('aiModel').value = settings.ai_model;
    }

    async saveSettings() {
        const settings = {
            auto_summarize: document.getElementById('autoSummarize').checked,
            summary_length: document.getElementById('summaryLength').value,
            ai_model: document.getElementById('aiModel').value
        };

        try {
            const response = await fetch('api/ai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_settings',
                    settings: settings
                })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            if (data.success) {
                this.settingsModal.classList.remove('show');
            } else {
                alert('Failed to save settings');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            alert('Error saving settings');
        }
    }

    toggleChat() {
        if (!this.chatContainer) return;

        if (this.chatContainer.classList.contains('hidden')) {
            this.chatContainer.classList.remove('hidden');
            this.chatContainer.style.display = 'flex';
            this.chatContainer.style.opacity = '1';
            this.chatContainer.style.visibility = 'visible';
            this.chatContainer.style.pointerEvents = 'auto';
        } else {
            this.chatContainer.classList.add('hidden');
            this.chatContainer.style.opacity = '0';
            this.chatContainer.style.visibility = 'hidden';
            this.chatContainer.style.pointerEvents = 'none';
            setTimeout(() => {
                this.chatContainer.style.display = 'none';
            }, 300);
        }
    }
}

// Initialize AI Chat when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.aiChat = new AIChat();
}); 