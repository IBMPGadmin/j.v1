$(document).ready(function() {
    console.log("JavaScript Loaded - Debugging Start");

    // Toggle the client selection panel when "Client in Focus" is clicked
    $('#client-focus-toggle').click(function(e) {
        e.stopPropagation();
        $('#client-selection-panel').toggleClass('hidden');
    });

    // Hide the client panel if clicked outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#client-focus-toggle, #client-selection-panel').length) {
            $('#client-selection-panel').addClass('hidden');
        }
    });

    // Toggle settings panel
    $('#settings-toggle').click(function(e) {
        e.stopPropagation();
        $('#settings-panel').toggleClass('hidden');
    });

    // Close settings panel when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#settings-toggle, #settings-panel').length) {
            $('#settings-panel').addClass('hidden');
        }
    });

    // Toggle screen options panel
    $('#screen-options-toggle').click(function(e) {
        e.stopPropagation();
        $('#screen-options-panel').toggleClass('hidden');
    });

    // Close screen options panel when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#screen-options-toggle, #screen-options-panel').length) {
            $('#screen-options-panel').addClass('hidden');
        }
    });

    // Handle tab switching in settings panel
    $('.tab-button').click(function() {
        const tabId = $(this).data('tab');

        // Update active tab button
        $('.tab-button').removeClass('active');
        $(this).addClass('active');

        // Show selected tab content
        $('.tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
    });

    // Handle widget visibility changes
    $('.widget-options input[type="checkbox"]').change(function() {
        const widgetId = $(this).attr('id');
        const isChecked = $(this).is(':checked');

        // Apply visibility changes based on widget ID
        if (widgetId === 'widget-keyword_search') {
            $('#keyword-search, .keyword-search-container').toggle(isChecked);
        } else if (widgetId === 'widget-content_display_area') {
            $('#content, .content-display-area').toggle(isChecked);
        } else if (widgetId === 'widget-droppable_area') {
            $('#drag-area-right').toggle(isChecked);
        } else if (widgetId === 'widget-editor_container') {
            $('.editor-container').toggle(isChecked);
        }
    });

    // Handle layout changes
    $('.layout-options input[type="radio"]').change(function() {
        const layoutId = $(this).attr('id');

        // Remove existing layout classes
        $('#header').removeClass('layout-default layout-compact');

        // Add selected layout class
        $('#header').addClass(layoutId);
    });

    // Handle additional settings
    $('.additional-settings input[type="checkbox"]').change(function() {
        const settingId = $(this).attr('id');
        const isChecked = $(this).is(':checked');

        if (settingId === 'sticky-header') {
            if (isChecked) {
                $('#header').addClass('sticky');
            } else {
                $('#header').removeClass('sticky');
            }
        }
    });

    // Client selection handling
    $(document).on('click', '.select-client-btn', function() {
        const clientId = $(this).data('client-id');
        const clientName = $(this).data('client-name');

        console.log("Selected Client:", clientName, "ID:", clientId);

        // Send selected client to server
        fetch("../includes/set_selected_client.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `client_id=${clientId}&client_name=${encodeURIComponent(clientName)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Server Response:", data.message);

                // Update UI dynamically
                $('#current-client').text(data.client_name);

                // Close dropdown
                $('#settings-panel').addClass('hidden');

                // Force a page refresh to reload all client-specific data
                setTimeout(function () {
                    location.reload();
                }, 500); // Adding a small delay for smoother UX
            } else {
                console.error("Error:", data.message);
            }
        })
        .catch(error => console.error("Error selecting client:", error));
    });

    // Function to reset the editor
    function resetEditor() {
        if (tinymce.get('tiny-editor')) {
            tinymce.get('tiny-editor').setContent('');
        }
    }

    // Function to load client content
    function loadClientContent(clientId) {
        const editor = tinymce.get('tiny-editor');
        if (!editor) {
            console.error("TinyMCE editor not initialized");
            return;
        }

        // Set loading indicator in editor
        editor.setContent('<p>Loading content...</p>');

        // Fetch content for selected client
        $.get('../../../user/api/get_text.php', { 
            category_id: 1, // Update dynamically if needed
            client_id: clientId 
        })
        .done(function (response) {
            console.log("RAW Response from get_text.php:", response);

            if (typeof response === "string") {
                try {
                    response = JSON.parse(response);
                } catch (error) {
                    console.error('JSON Parsing Error:', error, response);
                    editor.setContent(''); // Clear editor on error
                    return;
                }
            }

            if (response.success && response.content) {
                editor.setContent(response.content);
                localStorage.setItem('tinymce-content', response.content);
            } else {
                editor.setContent('');
                localStorage.removeItem('tinymce-content');
            }
        })
        .fail(function (xhr, status, error) {
            console.error('AJAX error:', xhr.status, error);
            editor.setContent(''); // Clear editor on error
        });
    }

    // Ensure text area refreshes after login
    $(document).on('loginSuccess', function () {
        resetEditor();
    });

    // Ensure text area clears on logout
    $('#logout-button').on('click', function () {
        resetEditor();
    });

    // Initialize widgets on page load
    function initializeWidgets() {
        // Toggle individual elements based on saved options
        $('#keyword-search, .keyword-search-container').toggle(screenOptions.keyword_search);
        $('.content-display-area').toggle(screenOptions.content_display_area);
        $('.right-side-container').toggle(screenOptions.right_side_container);
        $('#drag-area-right').toggle(screenOptions.droppable_area);
        $('.editor-container').toggle(screenOptions.editor_container);
        
        // Set layout classes
        $('body').addClass(screenOptions.layout);
        
        // Apply sticky header if enabled
        if (screenOptions.sticky_header) {
            $('header').addClass('sticky');
        }
        
        // Adjust content width based on right side visibility
        adjustLayout();
    }

    // Theme Switcher Implementation
    document.addEventListener("DOMContentLoaded", function() {
        const themeButtons = document.querySelectorAll(".theme-btn");
        
        // Function to apply theme to the entire application
        function applyTheme(theme) {
            // Set theme attribute on document root
            document.documentElement.setAttribute("data-theme", theme);
            
            // Store the selected theme in localStorage for persistence
            localStorage.setItem("selectedTheme", theme);
            
            // Update UI elements to reflect current theme
            updateUIForTheme(theme);
        }
        
        // Function to update UI elements based on the selected theme
        function updateUIForTheme(theme) {
            // Update gradient backgrounds
            const gradientElements = document.querySelectorAll('.gradient-background');
            gradientElements.forEach(el => {
                el.classList.remove('theme-default', 'theme-dark', 'theme-blue', 'theme-green');
                el.classList.add('theme-' + theme);
            });
            
            // Update buttons with theme colors
            const themeButtons = document.querySelectorAll('.btn-custom');
            themeButtons.forEach(btn => {
                // The button styling is handled by CSS variables, but we can add theme-specific classes if needed
                btn.classList.remove('theme-default', 'theme-dark', 'theme-blue', 'theme-green');
                btn.classList.add('theme-' + theme);
            });
            
            // Update navigation icons
            const navIcons = document.querySelectorAll('.nav_icon');
            navIcons.forEach(icon => {
                // The icon styling is handled by CSS variables
            });
            
            // Update popup titles
            const popupTitles = document.querySelectorAll('.popup-title');
            popupTitles.forEach(title => {
                // The title styling is handled by CSS variables
            });
        }
        
        // Load the stored theme on page load
        const savedTheme = localStorage.getItem("selectedTheme") || "default";
        applyTheme(savedTheme);
        
        // Add active class to the current theme button
        document.querySelector(`.theme-btn[data-theme="${savedTheme}"]`)?.classList.add('active');
        
        // Event listeners for theme selection
        themeButtons.forEach(button => {
            button.addEventListener("click", function() {
                // Remove active class from all buttons
                themeButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to the clicked button
                this.classList.add('active');
                
                const selectedTheme = this.getAttribute("data-theme");
                applyTheme(selectedTheme);
                
                // Close settings panel after theme selection (optional)
                document.getElementById('settings-panel').classList.add('hidden');
            });
        });
    });

    initializeWidgets();

    function adjustLayout() {
        const isDroppableAreaVisible = $('#widget-droppable_area').is(':checked');
        const isEditorContainerVisible = $('#widget-editor_container').is(':checked');
        
        // Get the content area and right side container
        const $contentArea = $('.content-display-area');
        const $rightContainer = $('.right-side-container');
        
        // Check if either component in right side should be visible
        const shouldShowRightSide = isDroppableAreaVisible || isEditorContainerVisible;
        
        // First, toggle the right side container visibility
        $rightContainer.toggle(shouldShowRightSide);
        
        // Then adjust the content area's width using Bootstrap classes
        if (!shouldShowRightSide) {
            // Both are hidden, make content full width
            $contentArea.removeClass('col-lg-8').addClass('col-lg-12');
            console.log("Expanding content to full width");
        } else {
            // At least one is visible, use standard width
            $contentArea.removeClass('col-lg-12').addClass('col-lg-8');
            console.log("Setting content to standard width");
        }
        
        // Toggle individual components within right side container
        if (shouldShowRightSide) {
            $('#drag-area-right').toggle(isDroppableAreaVisible);
            $('.editor-container').toggle(isEditorContainerVisible);
        }
    }

    $('.widget-options input[type="checkbox"]').change(function() {
        const widgetId = $(this).attr('id');
        const isChecked = $(this).is(':checked');
        
        // Update screen options object
        if (widgetId === 'widget-droppable_area') {
            screenOptions.droppable_area = isChecked;
        } else if (widgetId === 'widget-editor_container') {
            screenOptions.editor_container = isChecked;
        } else if (widgetId === 'widget-keyword_search') {
            screenOptions.keyword_search = isChecked;
        } else if (widgetId === 'widget-content_display_area') {
            screenOptions.content_display_area = isChecked;
        }
        
        // If neither droppable nor editor is checked, hide right container
        if (widgetId === 'widget-droppable_area' || widgetId === 'widget-editor_container') {
            screenOptions.right_side_container = 
                $('#widget-droppable_area').is(':checked') || 
                $('#widget-editor_container').is(':checked');
        }
        
        // Apply UI changes
        $('#keyword-search, .keyword-search-container').toggle(screenOptions.keyword_search);
        $('.content-display-area').toggle(screenOptions.content_display_area);
        $('.right-side-container').toggle(screenOptions.right_side_container);
        $('#drag-area-right').toggle(screenOptions.droppable_area);
        $('.editor-container').toggle(screenOptions.editor_container);
        
        // Adjust layout
        adjustLayout();
    });

});

document.addEventListener("DOMContentLoaded", function () {
    const themeButtons = document.querySelectorAll(".theme-btn");
    const root = document.documentElement;

    // Load theme from localStorage if it exists
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme) {
        root.setAttribute("data-theme", savedTheme);
        highlightActiveTheme(savedTheme);
    }

    // Theme button click handler
    themeButtons.forEach((btn) => {
        btn.addEventListener("click", function () {
        const selectedTheme = this.getAttribute("data-theme");
        root.setAttribute("data-theme", selectedTheme);
        localStorage.setItem("theme", selectedTheme);
        showThemeNotification(`${selectedTheme} theme applied.`);
        highlightActiveTheme(selectedTheme);
        });
    });

    function showThemeNotification(message) {
        const notification = document.createElement("div");
        notification.className = "notification";
        notification.style.backgroundColor = "var(--color-theme-2)";
        notification.innerText = message;
        document.body.appendChild(notification);

        setTimeout(() => {
        notification.remove();
        }, 2000);
    }

    function highlightActiveTheme(theme) {
        // Remove active class from all theme buttons
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Add active class to the selected theme button
        const activeButton = document.querySelector(`.theme-btn[data-theme="${theme}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }
    }
});

// Add this function to your theme-related JavaScript
function highlightActiveTheme(theme) {
    // Remove active class from all theme buttons
    document.querySelectorAll('.theme-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to the selected theme button
    const activeButton = document.querySelector(`.theme-btn[data-theme="${theme}"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }
}

// Make sure this gets called when a theme is selected and on page load
document.addEventListener("DOMContentLoaded", function() {
    // Load the stored theme on page load
    const savedTheme = localStorage.getItem("selectedTheme") || "default";
    
    // Apply theme
    document.documentElement.setAttribute("data-theme", savedTheme);
    
    // Highlight the active theme button
    highlightActiveTheme(savedTheme);
    
    // Set up click handlers for theme buttons
    document.querySelectorAll(".theme-btn").forEach(button => {
        button.addEventListener("click", function() {
            const selectedTheme = this.getAttribute("data-theme");
            
            // Apply theme
            document.documentElement.setAttribute("data-theme", selectedTheme);
            localStorage.setItem("selectedTheme", selectedTheme);
            
            // Update UI
            highlightActiveTheme(selectedTheme);
        });
    });
});