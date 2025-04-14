document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill editors
    const editor = new Quill('#editor', {
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

    // Theme toggle functionality
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    
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
                const notesList = document.getElementById('notesList');
                notesList.innerHTML = '';
                
                notes.forEach(note => {
                    const noteItem = document.createElement('div');
                    noteItem.className = 'note-item';
                    noteItem.setAttribute('data-note-id', note.id);
                    noteItem.innerHTML = `
                        <div class="note-content" onclick="loadNoteContent(${note.id})">
                            <h3>${note.title}</h3>
                            <p>${note.content.substring(0, 100)}...</p>
                            <div class="note-tags">
                                ${note.tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
                            </div>
                        </div>
                        <button class="delete-note-btn" onclick="event.stopPropagation(); deleteNote(${note.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    notesList.appendChild(noteItem);
                });
            })
            .catch(error => {
                console.error('Error loading notes:', error);
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
            const card = this.closest('.card');
            const title = card.querySelector('.card-title').textContent;
            const content = card.querySelector('.card-text').textContent;
            const tags = Array.from(card.querySelectorAll('.badge')).map(badge => badge.textContent);
            const folderId = card.closest('.note-card').dataset.folderId;

            document.getElementById('editNoteId').value = noteId;
            document.getElementById('editNoteTitle').value = title;
            editEditor.root.innerHTML = content;
            document.getElementById('editNoteTags').value = tags.join(', ');
            document.getElementById('editNoteFolder').value = folderId;

            const editModal = new bootstrap.Modal(document.getElementById('editNoteModal'));
            editModal.show();
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

    // Handle note export
    document.querySelectorAll('.export-pdf').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const noteId = this.dataset.id;
            window.location.href = `api/export_pdf.php?id=${noteId}`;
        });
    });

    document.querySelectorAll('.export-md').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const noteId = this.dataset.id;
            window.location.href = `api/export_md.php?id=${noteId}`;
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
                
                // Add "All Notes" option
                const allNotesItem = document.createElement('div');
                allNotesItem.className = 'folder-item' + (!selectedFolderId ? ' selected' : '');
                allNotesItem.innerHTML = `
                    <div class="folder-content">
                        <i class="fas fa-folder"></i>
                        <span>All Notes</span>
                    </div>
                `;
                allNotesItem.onclick = () => {
                    selectedFolderId = null;
                    document.querySelectorAll('.folder-item').forEach(item => item.classList.remove('selected'));
                    allNotesItem.classList.add('selected');
                    loadNotes();
                };
                folderList.appendChild(allNotesItem);
                
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
}); 