class TemplateManager {
    constructor(currentTemplateId = null) {
        this.apiUrl = 'template_api.php';
        this.currentTemplateId = currentTemplateId;
        this.currentUserId = document.body.dataset.userId || null;
        this.currentVersionId = null; // Track selected version
        this.isEditing = false; // Track if user is making changes
        this.lastSavedContent = ''; // Store the last saved content
        
        this.initializeSidebar();
        this.initializeEditor();
        this.loadTemplates();
        this.loadVersions();
        this.attachSaveHandler();
        this.handleClientSelection();
        
        if (!this.currentUserId) {
            console.error("User ID is missing. Ensure `data-user-id` is set on the <body> tag.");
        }
        
        this.setupAutoSave();
    }

    setupAutoSave() {
        // Save when window loses focus
        window.addEventListener('blur', () => {
            this.autoSave();
        });

        // Save when tab becomes hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.autoSave();
            }
        });

        // Save before unload (closing window or navigating away)
        window.addEventListener('beforeunload', (event) => {
            this.autoSave();
            
            // Only show a confirmation dialog if there are unsaved changes
            if (this.isEditing) {
                // Standard way to show confirmation dialog
                event.preventDefault();
                event.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return event.returnValue;
            }
        });
        
        // Set up periodic auto-save (every 60 seconds)
        setInterval(() => {
            this.autoSave();
        }, 60000);
    }

    initializeEditor() {
        tinymce.init({
            selector: '#template-editor',
            setup: (editor) => {
                editor.on('input', () => {
                    this.isEditing = true; // Mark editing state
                });
                editor.on('change', () => {
                    this.isEditing = true;
                });
                
                // Store initial content to determine if changes were made
                editor.on('init', () => {
                    this.lastSavedContent = editor.getContent();
                });
            },
            plugins: [
                'autoresize', 'preview', 'searchreplace', 'autolink',
                'directionality', 'visualblocks', 'visualchars', 'fullscreen',
                'image', 'link', 'media', 'codesample', 'table', 'charmap',
                'pagebreak', 'nonbreaking', 'anchor', 'insertdatetime',
                'advlist', 'lists', 'wordcount'
            ],
            toolbar: 'formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullscreen',
            menubar: 'file edit view insert format tools table help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
            branding: false,
            skin: false,
            autoresize_min_height: 200,
            autoresize_max_height: window.innerHeight - 200,
        });
    }

    async autoSave() {
        // Skip if no changes have been made
        if (!this.isEditing) return;

        const editor = tinymce.get('template-editor');
        if (!editor) return;

        const content = editor.getContent();
        
        // Don't save if content is empty or hasn't changed
        if (!content.trim() || content === this.lastSavedContent) return;

        try {
            let result;
            
            // If we have a version ID, update that version
            if (this.currentVersionId) {
                result = await this.updateExistingVersion(content);
            } 
            // Otherwise create a new untitled version
            else {
                result = await this.createNewAutoSaveVersion(content);
            }
            
            if (result.success) {
                console.log("Auto-saved successfully at " + new Date().toLocaleTimeString());
                this.lastSavedContent = content;
                this.isEditing = false;
                
                // If this was a new version, update the currentVersionId
                if (!this.currentVersionId && result.version_id) {
                    this.currentVersionId = result.version_id;
                    
                    // Refresh versions list to show the new auto-saved version
                    this.loadVersions();
                }
            }
        } catch (error) {
            console.error('Auto-save failed:', error);
        }
    }
    
    async updateExistingVersion(content) {
        // Update existing version
        const requestData = {
            user_id: this.currentUserId,
            template_id: this.currentTemplateId,
            version_id: this.currentVersionId,
            content: content
        };

        const response = await fetch(`${this.apiUrl}/version`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        });

        return await response.json();
    }
    
    async createNewAutoSaveVersion(content) {
        // Create a new auto-save version
        const timestamp = new Date().toLocaleTimeString();
        const versionName = `Auto-saved ${timestamp}`;
        
        const requestData = {
            user_id: this.currentUserId,
            template_id: this.currentTemplateId,
            version_name: versionName,
            content: content
        };

        const response = await fetch(`${this.apiUrl}/version`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        });

        return await response.json();
    }

    handleClientSelection() {
        document.querySelectorAll('.select-client-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const clientId = button.getAttribute('data-client-id');
                const clientName = button.getAttribute('data-client-name');

                console.log("Selected Client:", clientName, "ID:", clientId);

                try {
                    const response = await fetch("../includes/set_selected_client.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `client_id=${clientId}&client_name=${encodeURIComponent(clientName)}`
                    });

                    const result = await response.json();
                    if (result.success) {
                        console.log("Server Response:", result.message);

                        // Update UI dynamically
                        document.getElementById('current-client').textContent = result.client_name;

                        // Refresh only the versions list
                        this.loadVersions();
                    } else {
                        console.error("Error:", result.message);
                    }
                } catch (error) {
                    console.error("Error selecting client:", error);
                }
            });
        });
    }

    initializeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const mainContent = document.querySelector('.main-content');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                mainContent?.classList.toggle('sidebar-open');
            });

            // Close sidebar when clicking outside
            document.addEventListener('click', (event) => {
                if (!sidebar.contains(event.target) && 
                    !sidebarToggle.contains(event.target) && 
                    sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    mainContent?.classList.remove('sidebar-open');
                }
            });
        }
    }

    async loadTemplates() {
        try {
            const response = await fetch(`${this.apiUrl}/categories`);
    
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
    
            // Check if the response body is empty
            const text = await response.text();
            if (!text.trim()) {
                throw new Error('Empty response from server');
            }
    
            let templates;
            try {
                templates = JSON.parse(text);
            } catch (parseError) {
                throw new Error('Invalid JSON response from server');
            }
    
            const sidebar = document.getElementById('sidebar');
            const templatesContainer = document.createElement('div');
            templatesContainer.className = 'templates-sidebar';
    
            templates.forEach(template => {
                const templateItem = document.createElement('div');
                templateItem.className = 'template-item';
                templateItem.setAttribute('data-template-id', template.id);
                templateItem.textContent = template.title;
                templateItem.addEventListener('click', () => this.loadTemplate(template.id));
                templatesContainer.appendChild(templateItem);
            });
    
            // Attach the templates container
            const existingSidebar = sidebar.querySelector('.templates-sidebar');
            if (existingSidebar) {
                sidebar.removeChild(existingSidebar);
            }
            sidebar.appendChild(templatesContainer);
        } catch (error) {
            console.error('Error loading templates:', error.message);
            this.showNotification(error.message, 'error');
        }
    }

    async loadTemplate(templateId) {
        try {
            window.location.href = `template.php?template_id=${templateId}`;
        } catch (error) {
            console.error('Error loading template:', error);
            this.showNotification('Error loading template', 'error');
        }
    }

    async resetToDefault() {
        if (!confirm('Are you sure you want to reset to the default template? Any unsaved changes will be lost.')) {
            return;
        }
    
        try {
            const response = await fetch(`${this.apiUrl}/default?template_id=${this.currentTemplateId}`);
            const result = await response.json();
            
            if (result.success) {
                tinymce.get('template-editor').setContent(result.content);
                this.lastSavedContent = result.content;
                this.isEditing = false;
                this.currentVersionId = null; // Reset to no version selected
                this.showNotification('Template reset to default version');
            } else {
                throw new Error(result.error || 'Failed to reset template');
            }
        } catch (error) {
            console.error('Error resetting template:', error);
            this.showNotification(error.message, 'error');
        }
    }

    attachSaveHandler() {
        const saveButton = document.getElementById('saveTemplateBtn');
        const resetButton = document.getElementById('resetTemplateBtn');

        if (saveButton) {
            saveButton.addEventListener('click', async () => {
                await this.saveCurrentTemplate();
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', () => this.resetToDefault());
        }
    }

    async deleteVersion(versionId, element) {
        if (!confirm('Are you sure you want to delete this version?')) {
            return;
        }
    
        try {
            const response = await fetch(`${this.apiUrl}/version?version_id=${versionId}`, {
                method: 'DELETE',
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Remove the version from the UI
                element.remove();
                
                // If we deleted the current version, reset currentVersionId
                if (versionId == this.currentVersionId) {
                    this.currentVersionId = null;
                }
                
                this.showNotification('Version deleted successfully');
            } else {
                throw new Error(result.error || 'Failed to delete version');
            }
        } catch (error) {
            console.error('Error deleting version:', error);
            this.showNotification(error.message, 'error');
        }
    }

    async loadVersions() {
        try {
            const response = await fetch(`${this.apiUrl}/versions?template_id=${this.currentTemplateId}&user_id=${this.currentUserId}`);
            const versions = await response.json();
            
            const versionsList = document.getElementById('versions-list');
            versionsList.innerHTML = '';
            
            versions.forEach(version => {
                const versionItem = document.createElement('div');
                versionItem.className = 'list-group-item list-group-item-action d-flex align-items-center';
                
                // Highlight the currently selected version
                if (version.id == this.currentVersionId) {
                    versionItem.classList.add('active');
                }
                
                versionItem.innerHTML = `
                    <div class="flex-grow-1" style="cursor: pointer;">
                        <h5 class="mb-1">${version.version_name}</h5>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <small class="text-muted">${new Date(version.created_at).toLocaleDateString()} ${new Date(version.created_at).toLocaleTimeString()}</small>
                        <button class="btn btn-link delete-version p-0" title="Delete version">
                            <i class="fas fa-trash-alt text-danger"></i>
                        </button>
                    </div>
                `;
                
                versionItem.querySelector('.flex-grow-1').addEventListener('click', () => this.loadVersion(version.id));
                
                const deleteBtn = versionItem.querySelector('.delete-version');
                deleteBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.deleteVersion(version.id, versionItem);
                });
                
                versionsList.appendChild(versionItem);
            });
        } catch (error) {
            console.error('Error loading versions:', error);
            this.showNotification('Error loading versions', 'error');
        }
    }

    async loadVersion(versionId) {
        try {
            const response = await fetch(`${this.apiUrl}/version?version_id=${versionId}`);
            const version = await response.json();
            
            const editor = tinymce.get('template-editor');
            editor.setContent(version.content);
            
            this.currentVersionId = versionId;
            this.isEditing = false;
            this.lastSavedContent = version.content;
            
            // Update UI to highlight current version
            this.loadVersions();
        } catch (error) {
            console.error('Error loading version:', error);
            this.showNotification('Error loading version', 'error');
        }
    }

    async saveCurrentTemplate() {
        const editor = tinymce.get('template-editor');
        if (!editor) {
            console.error('Editor instance is not found.');
            return;
        }
        if (!this.currentTemplateId) {
            console.error('Missing template ID.');
            return;
        }
        if (!this.currentUserId) {
            console.error('Missing user ID.');
            return;
        }

        const versionName = prompt('Enter a name for this version:', 'Version ' + new Date().toLocaleString());
        if (!versionName) return;

        const content = editor.getContent();

        const requestData = {
            user_id: this.currentUserId,
            template_id: this.currentTemplateId,
            version_name: versionName,
            content: content
        };

        console.log("Sending request data:", requestData);

        try {
            const response = await fetch(`${this.apiUrl}/version`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });

            const result = await response.json();
            console.log("Server response:", result);

            if (result.success) {
                this.currentVersionId = result.version_id;
                this.showNotification('Template version saved successfully!');
                this.isEditing = false; // Reset editing state after manual save
                this.lastSavedContent = content;
                this.loadVersions();
            } else {
                throw new Error(`Failed to save template version: ${result.error || 'Unknown error'}`);
            }
        } catch (error) {
            console.error('Error saving template version:', error);
            this.showNotification(error.message, 'error');
        }
    }

    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}