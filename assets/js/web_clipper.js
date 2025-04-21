// Web Clipper for Nexus Notes
(function() {
    // Configuration
    const API_URL = 'http://localhost/Nexus%20Notes/api/clip_content.php';
    
    // Get selected text or entire page content
    function getSelectedContent() {
        let content = '';
        if (window.getSelection) {
            content = window.getSelection().toString();
        } else if (document.selection && document.selection.type != 'Control') {
            content = document.selection.createRange().text;
        }
        
        // If no text is selected, get the main content
        if (!content.trim()) {
            const mainContent = document.querySelector('main, article, .content, #content, .post, .article');
            if (mainContent) {
                content = mainContent.innerText;
            } else {
                content = document.body.innerText;
            }
        }
        
        return content.trim();
    }

    // Get page title
    function getPageTitle() {
        return document.title || window.location.href;
    }

    // Function to send clipped content to the server
    function sendToServer(data) {
        const baseUrl = window.location.protocol + '//' + window.location.host;
        const apiUrl = baseUrl + '/Nexus%20Notes/api/clip_content.php';

        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
            credentials: 'include' // This is important for session cookies
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Content clipped successfully!');
                window.close();
            } else {
                throw new Error(data.message || 'Failed to save content');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error clipping content: ' + error.message + '\n\nPlease make sure you are logged into Nexus Notes and try again.');
        });
    }

    // Function to create and show the clipping interface
    function showClippingInterface() {
        // Create modal container
        const modal = document.createElement('div');
        modal.className = 'nexus-notes-clipper-modal';
        modal.innerHTML = `
            <div class="nexus-notes-clipper-content">
                <h2>Clip to Nexus Notes</h2>
                <div class="form-group">
                    <label for="clipperTitle">Title:</label>
                    <input type="text" id="clipperTitle" value="${document.title}">
                </div>
                <div class="form-group">
                    <label for="clipperContent">Content:</label>
                    <textarea id="clipperContent" rows="10">${getSelectedContent()}</textarea>
                </div>
                <div class="form-group">
                    <label for="clipperTags">Tags (comma-separated):</label>
                    <input type="text" id="clipperTags" placeholder="tag1, tag2, tag3">
                </div>
                <div class="form-group">
                    <label for="clipperFolder">Folder:</label>
                    <select id="clipperFolder" required>
                    </select>
                </div>
                <div class="button-group">
                    <button id="clipperCancel">Cancel</button>
                    <button id="clipperSave">Save</button>
                </div>
            </div>
        `;

        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            .nexus-notes-clipper-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
            }
            .nexus-notes-clipper-content {
                background: white;
                padding: 20px;
                border-radius: 5px;
                width: 80%;
                max-width: 600px;
                max-height: 80vh;
                overflow-y: auto;
            }
            .form-group {
                margin-bottom: 15px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
            }
            .form-group input,
            .form-group textarea,
            .form-group select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .button-group {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 20px;
            }
            .button-group button {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            #clipperCancel {
                background: #f1f1f1;
            }
            #clipperSave {
                background: #4CAF50;
                color: white;
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(modal);

        // Load folders
        loadFolders();

        // Add event listeners
        document.getElementById('clipperCancel').addEventListener('click', () => {
            document.body.removeChild(modal);
            document.head.removeChild(style);
        });

        document.getElementById('clipperSave').addEventListener('click', () => {
            const data = {
                title: document.getElementById('clipperTitle').value,
                content: document.getElementById('clipperContent').value,
                url: window.location.href,
                tags: document.getElementById('clipperTags').value.split(',').map(tag => tag.trim()).filter(tag => tag),
                folder_id: document.getElementById('clipperFolder').value || null
            };
            sendToServer(data);
        });
    }

    // Function to load folders from the server
    function loadFolders() {
        const baseUrl = window.location.protocol + '//' + window.location.host;
        const apiUrl = baseUrl + '/Nexus%20Notes/api/get_folders.php';

        fetch(apiUrl, {
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const folderSelect = document.getElementById('clipperFolder');
                folderSelect.innerHTML = ''; // Clear any existing options
                
                data.folders.forEach(folder => {
                    const option = document.createElement('option');
                    option.value = folder.id;
                    option.textContent = folder.name;
                    folderSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading folders:', error);
            alert('Error loading folders. Please try again.');
        });
    }

    // Initialize the clipper
    showClippingInterface();
})(); 