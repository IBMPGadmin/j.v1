/**
 * Direct reference fetch functionality
 * Uses data-ref-id for more precise content fetching
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize reference elements with click handlers
    initializeReferences();
});

/**
 * Initialize all reference elements with click handlers
 */
function initializeReferences() {
    // Find all elements with data-ref-id attribute
    document.querySelectorAll('[data-ref-id]').forEach(function(elem) {
        // Remove existing handlers to prevent duplicates
        elem.removeEventListener('click', referenceByIdClickHandler);
        // Add new click handler
        elem.addEventListener('click', referenceByIdClickHandler);
    });
}

/**
 * Click handler for reference elements
 */
function referenceByIdClickHandler(e) {
    e.preventDefault();
    
    // Get reference ID from element
    const refId = this.getAttribute('data-ref-id');
    if (!refId) {
        console.error("Missing data-ref-id attribute on element", this);
        return;
    }
    
    // Create popup with loading state
    const popup = createFloatingPopup(
        'Reference ' + refId, 
        '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading content...</p></div>', 
        this
    );
    
    // Fetch content by reference ID
    fetchReferenceData(refId, popup);
}

/**
 * Fetch reference data from the server
 */
function fetchReferenceData(refId, popup) {
    console.log('Fetching reference data for ID:', refId);
    fetch(`/reference/${encodeURIComponent(refId)}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) throw new Error("Server returned error " + response.status);
            return response.json();
        })
        .then(data => {
            console.log('Reference data received:', data);
            if (data.error === false && data.data) {
                // Update popup with content
                updatePopupContent(popup, data.data);
            } else {
                // Show error
                popup.querySelector('.popup-content').innerHTML = `
                    <div class="alert alert-info">
                        <p>No content found for reference ID: ${refId}</p>
                        <p>${data.message || ''}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            // Handle fetch error
            popup.querySelector('.popup-content').innerHTML = `
                <div class="alert alert-warning">
                    <p><strong>Error loading content:</strong> ${error.message}</p>
                    <button class="btn btn-sm btn-primary mt-2 retry-btn">Retry</button>
                </div>
            `;
            
            // Add retry button handler
            popup.querySelector('.retry-btn')?.addEventListener('click', () => {
                fetchReferenceData(refId, popup);
            });
        });
}

/**
 * Update popup with reference content
 */
function updatePopupContent(popup, data) {
    // Change the popup title if we have a section ID
    if (data.section_id) {
        popup.querySelector('.title-text').textContent = data.section_id;
    } else if (data.title) {
        popup.querySelector('.title-text').textContent = data.title;
    }
    
    // Build the content HTML
    let html = '<div class="reference-content">';
    
    // Add title if available
    if (data.title) {
        html += `<h5 class="text-primary mb-2">${data.title}</h5>`;
    }
    
    // Add main content
    if (data.content) {
        html += `<div class="mb-3">${data.content}</div>`;
    } else {
        html += `<div class="alert alert-info">No content available for this reference.</div>`;
    }
    
    // Add metadata if available
    if (data.metadata) {
        const meta = data.metadata;
        const metaItems = [];
        
        if (meta.part) metaItems.push(`Part ${meta.part}`);
        if (meta.division) metaItems.push(`Division ${meta.division}`);
        if (meta.sub_division) metaItems.push(`Subdivision ${meta.sub_division}`);
        if (meta.section) metaItems.push(`Section ${meta.section}`);
        if (meta.sub_section) metaItems.push(`Subsection ${meta.sub_section}`);
        if (meta.paragraph) metaItems.push(`Paragraph ${meta.paragraph}`);
        if (meta.sub_paragraph) metaItems.push(`Subparagraph ${meta.sub_paragraph}`);
        
        if (metaItems.length > 0) {
            html += `<div class="metadata small text-muted mt-2">`;
            html += `<strong>Location:</strong> ${metaItems.join(' > ')}`;
            html += `</div>`;
        }
    }
    
    html += '</div>';
    
    // Update popup content
    popup.querySelector('.popup-content').innerHTML = html;
    
    // Re-initialize any references in the content
    setTimeout(initializeReferences, 100);
}

/**
 * Helper function to generate reference elements
 * This can be called from JavaScript to add data-ref-id to elements
 */
function createReferenceTag(refId, text, elementType = 'span', additionalClasses = '') {
    const elem = document.createElement(elementType);
    elem.setAttribute('data-ref-id', refId);
    elem.className = `reference-tag ${additionalClasses}`;
    elem.style.cssText = 'color:#3b82f6;cursor:pointer;text-decoration:underline;';
    elem.innerHTML = text;
    return elem;
}
