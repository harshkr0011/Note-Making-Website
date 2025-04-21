document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Starting initialization');
    
    // Initialize Quill editors
    const editor = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    const editEditor = new Quill('#editEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    // Initialize view editor (read-only)
    const viewEditor = new Quill('#viewEditor', {
        theme: 'snow',
        readOnly: true,
        modules: {
            toolbar: false
        }
    });

    document.getElementById('theme-toggle').addEventListener('click', function () {
        const body = document.body;
        const themeIcon = document.getElementById('theme-icon');
      
        body.classList.toggle('dark-mode');
      
        if (body.classList.contains('dark-mode')) {
          themeIcon.src = 'dark-icon.png';
        } else {
          themeIcon.src = 'light-icon.png';
        }
      });
      
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        themeToggle.classList.add('dark');
        themeToggle.innerHTML = `
            <span class="theme-icon sun"><i class="fas fa-sun"></i></span>
            <span class="theme-icon moon"><i class="fas fa-moon"></i></span>
            <span class="theme-text">Light Mode</span>
        `;
    } else {
        themeToggle.innerHTML = `
            <span class="theme-icon sun"><i class="fas fa-sun"></i></span>
            <span class="theme-icon moon"><i class="fas fa-moon"></i></span>
            <span class="theme-text">Dark Mode</span>
        `;
    }

    themeToggle.addEventListener('click', function() {
        body.classList.toggle('dark-mode');
        themeToggle.classList.toggle('dark');
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
            themeToggle.querySelector('.theme-text').textContent = 'Light Mode';
        } else {
            localStorage.setItem('theme', 'light');
            themeToggle.querySelector('.theme-text').textContent = 'Dark Mode';
        }
    });

    let selectedFolderId = null;

    // Function to load notes for a specific folder
    function loadNotes(folderId) {
        selectedFolderId = folderId;
        fetch(`api/get_notes.php?folder_id=${folderId}`)
            .then(response => response.json())
            .then(notes => {
                const notesContainer = document.getElementById('notesContainer');
                notesContainer.innerHTML = '';
                
                notes.forEach(note => {
                    const noteCard = document.createElement('div');
                    noteCard.className = 'col-md-4 mb-4 note-card';
                    noteCard.setAttribute('data-folder-id', note.folder_id || 'all');
                    noteCard.innerHTML = `
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">${note.title}</h5>
                                <p class="card-text">${note.content.substring(0, 100)}...</p>
                                ${note.tags ? `
                                    <div class="mb-2">
                                        ${note.tags.map(tag => `<span class="badge bg-primary me-1">${tag}</span>`).join('')}
                                    </div>
                                ` : ''}
                                <small class="text-muted">
                                    Last updated: ${new Date(note.updated_at).toLocaleDateString()}<br>
                                    Folder: ${note.folder_name || 'No Folder'}
                                </small>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-sm btn-info view-note" data-id="${note.id}">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-sm btn-primary edit-note" data-id="${note.id}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-note" data-id="${note.id}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item export-pdf" href="#" data-id="${note.id}">Export as PDF</a></li>
                                        <li><a class="dropdown-item export-md" href="#" data-id="${note.id}">Export as Markdown</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    `;
                    notesContainer.appendChild(noteCard);
                });

                // Reinitialize event listeners for the new note cards
                initializeNoteEventListeners();
            })
            .catch(error => {
                console.error('Error loading notes:', error);
            });
    }

    // Function to initialize note event listeners
    function initializeNoteEventListeners() {
        console.log('Initializing note event listeners...');
        
        // View note buttons
        document.querySelectorAll('.view-note').forEach(button => {
            button.addEventListener('click', function() {
                const noteId = this.getAttribute('data-id');
                console.log('Viewing note:', noteId);
                viewNote(noteId);
            });
        });

        // Edit note buttons
        document.querySelectorAll('.edit-note').forEach(button => {
            button.addEventListener('click', function() {
                const noteId = this.getAttribute('data-id');
                console.log('Editing note:', noteId);
                editNote(noteId);
            });
        });

        // Delete note buttons
        document.querySelectorAll('.delete-note').forEach(button => {
            button.addEventListener('click', function() {
                const noteId = this.getAttribute('data-id');
                console.log('Deleting note:', noteId);
                if (confirm('Are you sure you want to delete this note?')) {
                    deleteNote(noteId);
                }
            });
        });

        // Export buttons
        document.querySelectorAll('.export-pdf').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const noteId = this.getAttribute('data-id');
                console.log('Exporting PDF for note:', noteId);
                window.location.href = `api/export_pdf.php?id=${noteId}`;
            });
        });

        document.querySelectorAll('.export-md').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const noteId = this.getAttribute('data-id');
                console.log('Exporting Markdown for note:', noteId);
                window.location.href = `api/export_md.php?id=${noteId}`;
            });
        });
    }

    // Handle new note creation
    document.getElementById('saveNote').addEventListener('click', function() {
        const title = document.getElementById('noteTitle').value;
        const content = editor.root.innerHTML;
        const folderId = document.getElementById('noteFolder').value;
        const tags = document.getElementById('noteTags').value.split(',').map(tag => tag.trim()).filter(tag => tag);

        const formData = new FormData();
        formData.append('title', title);
        formData.append('content', content);
        formData.append('folder_id', folderId);
        formData.append('tags', JSON.stringify(tags));

        fetch('api/save_note.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('newNoteModal'));
                modal.hide();
                
                // Clear the form
                document.getElementById('noteTitle').value = '';
                editor.root.innerHTML = '';
                document.getElementById('noteTags').value = '';
                
                // Reload notes for the current folder
                if (selectedFolderId) {
                    loadNotes(selectedFolderId);
                }
            } else {
                alert(data.message || 'Error saving note');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving note. Please try again.');
        });
    });

    // Initialize folder creation modal
    const newFolderModal = new bootstrap.Modal(document.getElementById('newFolderModal'));
    
    // Handle new folder button click (opens modal)
    document.getElementById('createFolder').addEventListener('click', function() {
        newFolderModal.show();
    });

    // Handle folder creation form submission
    document.getElementById('newFolderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const folderName = document.getElementById('folderName').value.trim();
        
        if (!folderName) {
            alert('Please enter a folder name');
            return;
        }

        fetch('api/create_folder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: folderName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal
                newFolderModal.hide();
                
                // Clear the input
                document.getElementById('folderName').value = '';
                
                // Reload the folders list
                loadFolders();
            } else {
                alert(data.message || 'Error creating folder');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating folder');
        });
    });

    // Handle create button click in the modal
    document.querySelector('#newFolderModal .btn-primary').addEventListener('click', function() {
        const folderName = document.getElementById('folderName').value.trim();
        
        if (!folderName) {
            alert('Please enter a folder name');
            return;
        }

        fetch('api/create_folder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: folderName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal
                newFolderModal.hide();
                
                // Clear the input
                document.getElementById('folderName').value = '';
                
                // Reload the folders list
                loadFolders();
            } else {
                alert(data.message || 'Error creating folder');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating folder');
        });
    });

    // Handle note editing
    document.querySelectorAll('.edit-note').forEach(button => {
        button.addEventListener('click', function() {
            const noteId = this.dataset.id;
            
            // Fetch the full note content
            fetch(`api/get_note.php?id=${noteId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const note = data.note;
                        
                        // Set the note ID
                        document.getElementById('editNoteId').value = noteId;
                        
                        // Set the title
                        document.getElementById('editNoteTitle').value = note.title;
                        
                        // Set the content in the editor
                        editEditor.root.innerHTML = note.content;
                        
                        // Set the folder
                        document.getElementById('editNoteFolder').value = note.folder_id || '';
                        
                        // Set the tags
                        document.getElementById('editNoteTags').value = note.tags.join(', ');
                        
                        // Show the edit modal
                        const editModal = new bootstrap.Modal(document.getElementById('editNoteModal'));
                        editModal.show();
                    } else {
                        alert('Error loading note: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading note. Please try again.');
                });
        });
    });

    // Handle note update
    document.getElementById('updateNote').addEventListener('click', function() {
        const noteId = document.getElementById('editNoteId').value;
        const title = document.getElementById('editNoteTitle').value;
        const content = editEditor.root.innerHTML;
        const folderId = document.getElementById('editNoteFolder').value;
        const tags = document.getElementById('editNoteTags').value.split(',').map(tag => tag.trim()).filter(tag => tag);

        const formData = new FormData();
        formData.append('note_id', noteId);
        formData.append('title', title);
        formData.append('content', content);
        formData.append('folder_id', folderId);
        formData.append('tags', JSON.stringify(tags));

        fetch('api/update_note.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editNoteModal'));
                modal.hide();
                
                // Reload the page to show updated content
                location.reload();
            } else {
                alert(data.message || 'Error updating note');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating note. Please try again.');
        });
    });

    // Handle note deletion
    document.querySelectorAll('.delete-note').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this note?')) {
                const noteId = this.dataset.id;
                const formData = new FormData();
                formData.append('note_id', noteId);

                fetch('api/delete_note.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the note');
                });
            }
        });
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const notes = document.querySelectorAll('.note-card');
        const results = [];

        notes.forEach(note => {
            const title = note.querySelector('.card-title').textContent.toLowerCase();
            const content = note.querySelector('.card-text').textContent.toLowerCase();
            const tags = Array.from(note.querySelectorAll('.badge')).map(badge => badge.textContent.toLowerCase());
            
            // Calculate relevance score
            let score = 0;
            
            // Exact title match gets highest score
            if (title === searchTerm) {
                score += 100;
            }
            // Title contains search term
            else if (title.includes(searchTerm)) {
                score += 50;
            }
            
            // Content contains search term
            if (content.includes(searchTerm)) {
                score += 20;
            }
            
            // Tag matches
            tags.forEach(tag => {
                if (tag === searchTerm) {
                    score += 30;
                } else if (tag.includes(searchTerm)) {
                    score += 15;
                }
            });

            // Store note and its score
            if (score > 0) {
                results.push({
                    element: note,
                    score: score
                });
            }
        });

        // Sort results by score (highest first)
        results.sort((a, b) => b.score - a.score);

        // Hide all notes first
        notes.forEach(note => {
            note.style.display = 'none';
        });

        // Show only matching notes in order of relevance
        results.forEach(result => {
            result.element.style.display = '';
        });

        // If no search term, show all notes
        if (!searchTerm) {
            notes.forEach(note => {
                note.style.display = '';
            });
        }
    });

    // Update the active state of folder buttons based on URL
    function updateActiveFolder() {
        const urlParams = new URLSearchParams(window.location.search);
        const currentFolder = urlParams.get('folder') || 'all';
        
        document.querySelectorAll('.folder-btn').forEach(btn => {
            const folderId = btn.getAttribute('href').split('=')[1];
            if (folderId === currentFolder) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    // Call on page load
    updateActiveFolder();

    // Tag filtering
    document.querySelectorAll('.tag-list li').forEach(item => {
        item.addEventListener('click', function() {
            const tagName = this.querySelector('.badge').textContent;
            const notes = document.querySelectorAll('.card');

            notes.forEach(note => {
                const noteTags = Array.from(note.querySelectorAll('.badge')).map(badge => badge.textContent);
                if (noteTags.includes(tagName)) {
                    note.closest('.col-md-4').style.display = '';
                } else {
                    note.closest('.col-md-4').style.display = 'none';
                }
            });
        });
    });

    // Function to load folders
    function loadFolders() {
        console.log('Loading folders...');
        fetch('api/get_folders.php')
            .then(response => {
                console.log('Response received:', response);
                return response.json();
            })
            .then(data => {
                console.log('Folders data:', data);
                const folderList = document.getElementById('folderList');
                if (!folderList) {
                    console.error('Folder list element not found!');
                    return;
                }
                folderList.innerHTML = '';
                
                if (data.folders && Array.isArray(data.folders)) {
                    data.folders.forEach(folder => {
                        const folderItem = document.createElement('div');
                        folderItem.className = 'folder-item' + (selectedFolderId === folder.id ? ' selected' : '');
                        folderItem.innerHTML = `
                            <div class="folder-content">
                                <i class="fas fa-folder"></i>
                                <span>${folder.name}</span>
                            </div>
                        `;
                        folderItem.onclick = () => {
                            selectedFolderId = folder.id;
                            document.querySelectorAll('.folder-item').forEach(item => item.classList.remove('selected'));
                            folderItem.classList.add('selected');
                            loadNotes(folder.id);
                        };
                        folderList.appendChild(folderItem);
                    });
                } else {
                    console.error('Invalid folders data:', data);
                }
            })
            .catch(error => {
                console.error('Error loading folders:', error);
            });
    }

    // Add delete folder button handler
    document.getElementById('deleteFolder').addEventListener('click', () => {
        if (!selectedFolderId) {
            alert('Please select a folder to delete');
            return;
        }
        
        if (confirm('Are you sure you want to delete this folder? All notes in this folder will also be deleted.')) {
            fetch('api/delete_folder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ folder_id: selectedFolderId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    selectedFolderId = null;
                    loadFolders();
                    loadNotes();
                } else {
                    alert(data.error || 'Failed to delete folder');
                }
            })
            .catch(error => {
                console.error('Error deleting folder:', error);
                alert('Failed to delete folder');
            });
        }
    });

    // Load folders when the page loads
    loadFolders();

    // Handle view note button click
    document.querySelectorAll('.view-note').forEach(button => {
        button.addEventListener('click', function() {
            const noteId = this.getAttribute('data-id');
            viewNote(noteId);
        });
    });

    // Function to view note
    function viewNote(noteId) {
        fetch(`api/get_note.php?id=${noteId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const note = data.note;
                    document.getElementById('viewNoteTitle').textContent = note.title;
                    
                    // Set the content in the Quill editor
                    viewEditor.root.innerHTML = note.content;
                    
                    document.getElementById('viewNoteUpdated').textContent = new Date(note.updated_at).toLocaleString();
                    
                    // Clear and populate tags
                    const tagsContainer = document.getElementById('viewNoteTags');
                    tagsContainer.innerHTML = '';
                    if (note.tags && note.tags.length > 0) {
                        note.tags.forEach(tag => {
                            const tagElement = document.createElement('span');
                            tagElement.className = 'badge bg-primary me-1';
                            tagElement.textContent = tag;
                            tagsContainer.appendChild(tagElement);
                        });
                    } else {
                        tagsContainer.innerHTML = '<span class="text-muted">No tags</span>';
                    }

                    // Show the modal
                    const viewModal = new bootstrap.Modal(document.getElementById('viewNoteModal'));
                    viewModal.show();
                } else {
                    alert(data.message || 'Error loading note');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading note. Please try again.');
            });
    }

    // Function to filter notes by folder
    function filterNotesByFolder(folderId) {
        console.log('Filtering notes for folder ID:', folderId);
        const noteCards = document.querySelectorAll('.note-card');
        console.log('Total note cards:', noteCards.length);

        noteCards.forEach(card => {
            const cardFolderId = card.getAttribute('data-folder-id');
            console.log('Note card folder ID:', cardFolderId);

            if (folderId === 'all' || cardFolderId === folderId || cardFolderId === 'all') {
                console.log('Showing note for folder:', folderId);
                card.style.display = 'block';
            } else {
                console.log('Hiding note not in folder:', folderId);
                card.style.display = 'none';
            }
        });

        // Reinitialize all button event listeners after filtering
        initializeNoteEventListeners();
    }

    // Initialize folder filtering
    function initializeFolderFiltering() {
        console.log('Initializing folder filtering...');
        
        // Get all folder items
        const folderItems = document.querySelectorAll('.folder-item');
        console.log('Found folder items:', folderItems.length);
        
        // Add click handler to each folder
        folderItems.forEach(folder => {
            folder.addEventListener('click', function() {
                const folderId = this.getAttribute('data-folder-id');
                console.log('Clicked folder ID:', folderId);
                
                // Remove active class from all folders
                folderItems.forEach(f => f.classList.remove('active'));
                
                // Add active class to clicked folder
                this.classList.add('active');
                
                // Filter notes based on the selected folder
                filterNotesByFolder(folderId);
            });
        });

        // Set the first folder as active by default and load its notes
        if (folderItems.length > 0) {
            folderItems[0].classList.add('active');
            filterNotesByFolder(folderItems[0].getAttribute('data-folder-id'));
        }
    }

    // Call the initialization function
    initializeFolderFiltering();
    console.log('Folder filtering initialized');

    // Add animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);

    // Handle image upload in Quill
    editor.getModule('toolbar').addHandler('image', function() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = function() {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const range = editor.getSelection();
                    editor.insertEmbed(range.index, 'image', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        };
    });

    // Update note function
    async function updateNote() {
        const noteId = document.getElementById('noteId').value;
        const title = document.getElementById('noteTitle').value;
        const content = editor.root.innerHTML;
        const folderId = document.getElementById('folderSelect').value;
        const tags = Array.from(document.querySelectorAll('.tag-badge')).map(tag => tag.textContent.trim());

        try {
            const formData = new FormData();
            formData.append('note_id', noteId);
            formData.append('title', title);
            formData.append('content', content);
            formData.append('folder_id', folderId);
            formData.append('tags', JSON.stringify(tags));

            const response = await fetch('api/update_note.php', {
                method: 'POST',
                body: formData
            });

            // Check if the response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Try to parse the response as JSON
            let data;
            try {
                data = await response.json();
            } catch (e) {
                console.error('Error parsing JSON:', e);
                throw new Error('Invalid response from server');
            }

            if (data.success) {
                $('#editNoteModal').modal('hide');
                loadNotes();
                showAlert('Note updated successfully', 'success');
            } else {
                showAlert(data.message || 'Error updating note', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error updating note: ' + error.message, 'danger');
        }
    }

    // Handle logout button click
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
        e.preventDefault(); // Prevent default link behavior
        
        // Show confirmation dialog
        if (confirm('Are you sure you want to logout?')) {
            // If user confirms, redirect to logout page
            window.location.href = 'logout.php';
        }
    });

    // Function to edit a note
    function editNote(noteId) {
        console.log('Loading note for editing:', noteId);
        fetch(`api/get_note.php?id=${noteId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const note = data.note;
                    // Set note ID
                    document.getElementById('editNoteId').value = noteId;
                    // Set title
                    document.getElementById('editNoteTitle').value = note.title;
                    // Set content in editor
                    editEditor.root.innerHTML = note.content;
                    // Set folder
                    document.getElementById('editNoteFolder').value = note.folder_id || '';
                    // Set tags
                    document.getElementById('editNoteTags').value = note.tags ? note.tags.join(', ') : '';
                    
                    // Show edit modal
                    const editModal = new bootstrap.Modal(document.getElementById('editNoteModal'));
                    editModal.show();
                } else {
                    alert('Error loading note: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading note. Please try again.');
            });
    }

    // Function to delete a note
    function deleteNote(noteId) {
        console.log('Deleting note:', noteId);
        const formData = new FormData();
        formData.append('note_id', noteId);

        fetch('api/delete_note.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the note card from the DOM
                const noteCard = document.querySelector(`.note-card[data-note-id="${noteId}"]`);
                if (noteCard) {
                    noteCard.remove();
                }
                // Show success message
                alert('Note deleted successfully');
                // Reload the page to refresh the notes list
                location.reload();
            } else {
                alert(data.message || 'Error deleting note');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting note. Please try again.');
        });
    }
}); 