// Legal document reference popup functionality
document.addEventListener('DOMContentLoaded', function() {
    // Global array to track popups
    window.floatingPopups = [];
    
    // Ensure all reference handlers are attached on page load
    attachReferenceHandlers();
    
    // Test button for modal
    const testModalButton = document.getElementById('test-modal-button');
    if (testModalButton) {
        testModalButton.addEventListener('click', function() {
            createFloatingPopup('Test Popup', 'This is a test popup content to verify the popup functionality is working correctly.', this);
        });
    }
    
    // Set up the section content box as drop target
    setupDropTarget();
});

// Create floating popup function
function createFloatingPopup(title, content, anchorElem) {
    // Create popup element
    const popup = document.createElement('div');
    popup.className = 'popup';
    popup.style.cssText = `
        display:block; position:absolute; min-width:340px; max-width:95vw; 
        background: linear-gradient(135deg, #f0f9ff 60%, #e0e7ff 100%);
        border:2.5px solid #3b82f6; 
        box-shadow:0 8px 32px rgba(0,0,0,0.18);
        border-radius:16px; 
        z-index:2000;
        font-family: 'Segoe UI', 'Arial', sans-serif;
        overflow: hidden;
    `;
    popup.id = 'popup-' + Math.random().toString(36).substr(2, 9);

    // Set popup HTML content
    popup.innerHTML = `
        <div class="popup-title" style="
            padding:12px 18px;
            background:linear-gradient(90deg,#3b82f6 60%,#6366f1 100%);
            border-bottom:2px solid #3b82f6;
            display:flex;align-items:center;justify-content:space-between;
            cursor:move;user-select:none; color:#fff; font-size:1.1rem;">
            <span class="title-text" style="font-weight:700;letter-spacing:0.5px;">${title}</span>
            <div class="popup-controls" style="display:flex;gap:10px;align-items:center;">
                <span class="copy" title="Copy content" style="cursor:pointer;display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#e0e7ff;color:#3b82f6;font-size:18px;transition:background 0.2s;"><i class="fas fa-copy"></i></span>
                <span class="arrow" style="font-size:20px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#e0e7ff;color:#3b82f6;transition:background 0.2s;">▼</span>
                <span class="close" style="font-size:22px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#fee2e2;color:#ef4444;transition:background 0.2s;">✖</span>
            </div>
        </div>
        <div class="content-body" style="width:100%; padding:18px; max-height:340px; overflow:auto; background:rgba(255,255,255,0.98);">
            <p class="popup-content" style="margin:0; font-size:1.05rem; color:#222;">${content}</p>
        </div>
    `;
    
    // Append to body
    document.body.appendChild(popup);
    
    // Position popup near clicked element
    if (anchorElem) {
        const rect = anchorElem.getBoundingClientRect();
        const popupRect = popup.getBoundingClientRect();
        
        let left = rect.left + (rect.width/2) - (popupRect.width/2) + window.scrollX;
        let top = rect.bottom + 10 + window.scrollY;
        
        // Keep popup within viewport
        left = Math.max(10, Math.min(left, window.innerWidth - popupRect.width - 10));
        top = Math.max(10, Math.min(top, window.innerHeight + window.scrollY - popupRect.height - 10));
        
        popup.style.left = left + 'px';
        popup.style.top = top + 'px';
    }
    
    // Add close handler
    popup.querySelector('.close').addEventListener('click', function() {
        popup.remove();
        window.floatingPopups = window.floatingPopups.filter(p => p !== popup);
    });
    
    // Add collapse/expand handler
    popup.querySelector('.arrow').addEventListener('click', function() {
        const body = popup.querySelector('.content-body');
        if (body.style.display === 'none') {
            body.style.display = 'block';
            this.textContent = '▼';
        } else {
            body.style.display = 'none';
            this.textContent = '▲';
        }
    });
    
    // Make popup draggable
    makeDraggable(popup);
    
    // Make popup droppable to section content
    popup.setAttribute('draggable', 'true');
    popup.addEventListener('dragstart', function(e) {
        e.dataTransfer.setData('text/popup-id', popup.id);
        e.dataTransfer.setData('text/plain', `Section ${title}`);
    });
    
    // Track popup
    window.floatingPopups.push(popup);
    
    return popup;
}

// Make an element draggable
function makeDraggable(element) {
    const titleBar = element.querySelector('.popup-title');
    if (!titleBar) return;
    
    let isDragging = false;
    let offsetX = 0;
    let offsetY = 0;
    
    titleBar.addEventListener('mousedown', function(e) {
        // Don't start drag if clicking buttons
        if (e.target.closest('.close') || e.target.closest('.copy') || e.target.closest('.arrow')) 
            return;
            
        isDragging = true;
        offsetX = e.clientX - element.getBoundingClientRect().left;
        offsetY = e.clientY - element.getBoundingClientRect().top;
        
        element.style.cursor = 'grabbing';
    });
    
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        const x = e.clientX - offsetX;
        const y = e.clientY - offsetY;
        
        element.style.left = x + 'px';
        element.style.top = y + 'px';
    });
    
    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            element.style.cursor = '';
        }
    });
}

// Handle reference clicks
function attachReferenceHandlers() {
    document.querySelectorAll('.ref').forEach(function(ref) {
        // Remove existing handler to avoid duplicates
        ref.removeEventListener('click', referenceClickHandler);
        
        // Add new click handler
        ref.addEventListener('click', referenceClickHandler);
    });
}

// The actual click handler function
function referenceClickHandler(e) {
    e.preventDefault();
    
    const sectionRef = this.getAttribute('data-section-id');
    const tableId = this.getAttribute('data-table-id') || this.getAttribute('data-category-id');
    
    if (!sectionRef || !tableId) {
        console.error("Missing data attributes on reference", this);
        return;
    }
    
    // Get current document context
    const currentDocumentTable = document.querySelector('meta[name="current-document-table"]')?.content;
    const currentDocumentCategoryId = document.querySelector('meta[name="current-document-category-id"]')?.content;
    
    if (currentDocumentTable && currentDocumentCategoryId) {
        console.log("Current document context:", currentDocumentTable, currentDocumentCategoryId);
    }
    
    // Get context data from closest section container
    let contextSection = '';
    let contextSubsection = '';
    
    // Try to find context (the current section/subsection) by looking at parent elements
    const sectionContainer = this.closest('.section-section');
    if (sectionContainer) {
        const sectionHeading = sectionContainer.querySelector('.clickable-heading');
        if (sectionHeading) {
            contextSection = sectionHeading.getAttribute('data-section-id');
            console.log("Found context section:", contextSection);
        }
        
        // Look for subsection within this section
        const subsectionContainer = this.closest('.subsection-section');
        if (subsectionContainer) {
            const subsectionHeading = subsectionContainer.querySelector('.clickable-heading');
            if (subsectionHeading) {
                contextSubsection = subsectionHeading.getAttribute('data-section-id');
                console.log("Found context subsection:", contextSubsection);
            }
        }
    }
    
    // Create popup with loading state
    const popup = createFloatingPopup(sectionRef, '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading content...</p></div>', this);
    
    // Build the URL with query parameters for context
    let url = `/api/section-content/${tableId}/${encodeURIComponent(sectionRef)}`;
    
    // Add context parameters if available
    const params = new URLSearchParams();
    if (contextSection) {
        params.append('context_section', contextSection);
    }
    if (contextSubsection) {
        params.append('context_subsection', contextSubsection);
    }
    
    // Add current document context if available
    if (currentDocumentTable) {
        params.append('current_document_table', currentDocumentTable);
    }
    if (currentDocumentCategoryId) {
        params.append('current_document_category_id', currentDocumentCategoryId);
    }
    
    // Add params to URL if we have any
    if (params.toString()) {
        url += '?' + params.toString();
    }
    // Fetch content with robust error handling
    console.log('Fetching section content for:', tableId, sectionRef, 'with context:', params.toString(), 'URL:', url);
    
    // Add visual logging for the user
    popup.querySelector('.popup-content').innerHTML = `
        <div class="alert alert-info">
            <p><strong>Fetching content...</strong></p>
            <p>Making API request to: ${url}</p>
        </div>
    `;
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Content-Type:', response.headers.get('content-type'));
            
            if (!response.ok) {
                // For API endpoints, even error responses should be JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(errorData => {
                        // If it's an authentication error, we may want to redirect to login
                        if (response.status === 401) {
                            console.log('Authentication required. Showing login message.');
                            throw new Error('Authentication required. Please log in to view this content.');
                        }
                        throw new Error(errorData.message || `Server returned error ${response.status}`);
                    });
                } else {
                    return response.text().then(text => {
                        throw new Error(`Server returned error ${response.status}: ${text.substring(0, 100)}...`);
                    });
                }
            }
            
            // Check content type to ensure it's JSON before parsing
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Not JSON, try to get the text and show error
                return response.text().then(text => {
                    throw new Error(`Expected JSON but got ${contentType || 'unknown content type'}: ${text.substring(0, 100)}...`);
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Section data received:', data);
            
            // Debug the data structure we received
            if (data.data) {
                console.log('Data array length:', Array.isArray(data.data) ? data.data.length : 'Not an array');
                if (Array.isArray(data.data) && data.data.length > 0) {
                    console.log('First item:', data.data[0]);
                }
            }
            
            if (data.error === false && data.data && Array.isArray(data.data) && data.data.length > 0) {
                let html = '';
                
                // Process each section returned
                data.data.forEach(function(section) {
                    html += `<div class="section-item mb-3 p-3 border-bottom">`;
                    
                    // Debug - log the section object
                    console.log('Processing section:', section);
                    
                    // Section title/heading (if available)
                    if (section.title) {
                        html += `<h5 class="section-title text-primary mb-2">${section.title}</h5>`;
                    }
                    
                    // Section text (prioritize specific content fields)
                    if (section.text_content) {
                        html += `<div class="section-text mb-2">${section.text_content}</div>`;
                    } else if (section.section_text) {
                        html += `<div class="section-text mb-2">${section.section_text}</div>`;
                    } else if (section.heading_text) {
                        html += `<div class="section-text mb-2">${section.heading_text}</div>`;
                    }
                    
                    // Metadata footer
                    html += `<div class="section-meta small text-muted">`;
                    
                    if (section.section_id) {
                        html += `<div><strong>Section:</strong> ${section.section_id}</div>`;
                    }
                    
                    // Show hierarchy info if available
                    const hierarchyInfo = [];
                    if (section.part) hierarchyInfo.push(`Part ${section.part}`);
                    if (section.division) hierarchyInfo.push(`Division ${section.division}`);
                    if (section.sub_division) hierarchyInfo.push(`Subdivision ${section.sub_division}`);
                    if (section.section && section.section !== section.section_id) hierarchyInfo.push(`Section ${section.section}`);
                    if (section.sub_section) hierarchyInfo.push(`Subsection ${section.sub_section}`);
                    if (section.paragraph) hierarchyInfo.push(`Paragraph ${section.paragraph}`);
                    if (section.sub_paragraph) hierarchyInfo.push(`Subparagraph ${section.sub_paragraph}`);
                    
                    if (hierarchyInfo.length > 0) {
                        html += `<div><strong>Location:</strong> ${hierarchyInfo.join(' > ')}</div>`;
                    }
                    
                    // If it's from another table/act, show that info
                    if (section.from_other_category) {
                        html += `<div class="mt-1 fw-bold text-info">From: ${section.act_name || 'Other document'}</div>`;
                    }
                    
                    html += `</div>`;
                    
                    // Add annotation button if user is logged in
                    if (section.section_id) {
                        html += `
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-primary add-note-btn" 
                                        data-section-id="${section.section_id}" 
                                        data-category-id="${section.category_id || tableId}">
                                    <i class="fas fa-sticky-note me-1"></i> Add Note
                                </button>
                            </div>
                        `;
                    }
                    
                    html += `</div>`;
                });
                
                popup.querySelector('.popup-content').innerHTML = html;
                
                // Attach handlers to any new references or buttons
                setTimeout(() => {
                    attachReferenceHandlers();
                    
                    // Add handlers for annotation buttons
                    popup.querySelectorAll('.add-note-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const sectionId = this.getAttribute('data-section-id');
                            const categoryId = this.getAttribute('data-category-id');
                            
                            // Show annotation modal or sidebar here
                            // This could be implemented based on your UI requirements
                            console.log(`Add note for section ${sectionId} in category ${categoryId}`);
                        });
                    });
                }, 100);
                  } else if (data.error === true) {
                // Server returned an error
                popup.querySelector('.popup-content').innerHTML = `
                    <div class="alert alert-danger">
                        <p><strong>Error:</strong> ${data.message || 'Failed to load content'}</p>
                    </div>
                `;
            } else {
                // No data found
                popup.querySelector('.popup-content').innerHTML = `
                    <div class="alert alert-info">
                        <p><strong>No content found</strong> for reference: ${sectionRef}</p>
                        <div class="mt-2">
                            <p class="small text-muted">Debug information:</p>
                            <ul class="small text-muted">
                                <li>Table ID: ${tableId}</li>
                                <li>Reference: ${sectionRef}</li>
                                <li>Context section: ${contextSection || 'None'}</li>
                                <li>Context subsection: ${contextSubsection || 'None'}</li>
                            </ul>
                            <button class="btn btn-sm btn-primary mt-2 retry-btn">Retry</button>
                        </div>
                    </div>
                `;
                
                // Add retry button handler
                popup.querySelector('.retry-btn')?.addEventListener('click', () => {
                    popup.querySelector('.popup-content').innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading content...</p></div>';
                    // Try with a slight delay
                    setTimeout(() => {
                        fetch(url)
                            .then(response => {
                                console.log('Retry response status:', response.status);
                                console.log('Content-Type:', response.headers.get('content-type'));
                                
                                if (!response.ok) {
                                    // For API endpoints, even error responses should be JSON
                                    const contentType = response.headers.get('content-type');
                                    if (contentType && contentType.includes('application/json')) {
                                        return response.json().then(errorData => {
                                            // If it's an authentication error, we may want to redirect to login
                                            if (response.status === 401) {
                                                console.log('Authentication required. Showing login message.');
                                                throw new Error('Authentication required. Please log in to view this content.');
                                            }
                                            throw new Error(errorData.message || `Server returned error ${response.status}`);
                                        });
                                    } else {
                                        return response.text().then(text => {
                                            throw new Error(`Server returned error ${response.status}: ${text.substring(0, 100)}...`);
                                        });
                                    }
                                }
                                
                                // Check content type to ensure it's JSON before parsing
                                const contentType = response.headers.get('content-type');
                                if (!contentType || !contentType.includes('application/json')) {
                                    // Not JSON, try to get the text and show error
                                    return response.text().then(text => {
                                        throw new Error(`Expected JSON but got ${contentType || 'unknown content type'}: ${text.substring(0, 100)}...`);
                                    });
                                }
                                
                                return response.json();
                            })
                            .then(data => {
                                console.log('Retry data:', data);
                                if (data.error === false && data.data && Array.isArray(data.data) && data.data.length > 0) {
                                    let html = '';
                                    data.data.forEach(function(section) {
                                        html += `<div class="section-item mb-3 p-3 border-bottom">`;
                                        if (section.title) {
                                            html += `<h5 class="section-title text-primary mb-2">${section.title}</h5>`;
                                        }
                                        if (section.text_content) {
                                            html += `<div class="section-text mb-2">${section.text_content}</div>`;
                                        } else if (section.section_text) {
                                            html += `<div class="section-text mb-2">${section.section_text}</div>`;
                                        } else if (section.heading_text) {
                                            html += `<div class="section-text mb-2">${section.heading_text}</div>`;
                                        }
                                        html += `</div>`;
                                    });
                                    popup.querySelector('.popup-content').innerHTML = html;
                                } else {
                                    popup.querySelector('.popup-content').innerHTML = `
                                        <div class="alert alert-warning">
                                            <p>Still no content found. Please check the reference and try again.</p>
                                        </div>
                                    `;
                                }
                            })
                            .catch(error => {
                                popup.querySelector('.popup-content').innerHTML = `
                                    <div class="alert alert-danger">
                                        <p><strong>Error on retry:</strong> ${error.message}</p>
                                    </div>
                                `;
                            });
                    }, 500);
                });
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            popup.querySelector('.popup-content').innerHTML = `
                <div class="alert alert-warning">
                    <h5>Error loading content</h5>
                    <p>${error.message}</p>
                    <div class="small text-muted mt-2">
                        <strong>Technical details:</strong><br>
                        ${error.stack ? error.stack.split('\n')[0] : 'Unknown error'}<br>
                        URL: ${url}<br>
                        Request Type: GET
                    </div>
                    <button class="btn btn-sm btn-primary mt-2 retry-btn">Retry</button>
                    <a href="/login" class="btn btn-sm btn-secondary mt-2">Login</a>
                </div>
            `;
            
            // Add retry button handler
            popup.querySelector('.retry-btn')?.addEventListener('click', () => {
                this.click();
                popup.remove();
            });
        });
}

// Setup drop target for the section content box
function setupDropTarget() {
    const sectionContentBox = document.getElementById('section-content-display');
    if (!sectionContentBox) return;
    
    sectionContentBox.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border', 'border-primary');
    });
    
    sectionContentBox.addEventListener('dragleave', function() {
        this.classList.remove('border', 'border-primary');
    });
    
    sectionContentBox.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border', 'border-primary');
        
        const popupId = e.dataTransfer.getData('text/popup-id');
        if (!popupId) return;
        
        const popup = document.getElementById(popupId);
        if (!popup) return;
        
        // Get content and title
        const content = popup.querySelector('.popup-content').innerHTML;
        const title = popup.querySelector('.title-text').textContent;
        
        // Create saved section element
        const sectionDiv = document.createElement('div');
        sectionDiv.className = 'saved-section-content mb-3 p-2 border rounded';
        sectionDiv.innerHTML = `
            <div class="d-flex justify-content-between border-bottom pb-1 mb-2">
                <strong class="text-primary">${title}</strong>
                <button type="button" class="btn-close btn-sm"></button>
            </div>
            ${content}
        `;
        
        // Add remove button handler
        sectionDiv.querySelector('.btn-close').addEventListener('click', function() {
            sectionDiv.remove();
        });
        
        // Add to section content box
        sectionContentBox.appendChild(sectionDiv);
        
        // Remove popup
        popup.remove();
        window.floatingPopups = window.floatingPopups.filter(p => p !== popup);
    });
}
