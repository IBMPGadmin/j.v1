<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'JurisLocator' }}</title>
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('user_assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('user_assets/css/login_styles.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    
    @php
    // Add this for pages that use TinyMCE
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Set global JavaScript variable for client ID
    echo '<script>';
    echo 'window.selectedClientId = ' . (isset($_SESSION['selected_client_id']) ? $_SESSION['selected_client_id'] : 'null') . ';';
    echo '</script>';
    @endphp

    @stack('styles')
</head>
<body id="body-pd" class="gap_right_home">
    <header class="header gradient-background" id="header">
        <div class="logo">
            <img src="{{ asset('user_assets/img/jurislocator-logo.png') }}" alt="jurislocator" />
        </div>

        <div class="header_toggle"> 
            <i class="bi bi-list" id="header-toggle"></i> 
        </div>
        
        <div class="header-controls">
            <!-- Selected Client Display -->
            <div class="selected-client footer-header-text">
                <p id="client-focus-toggle" style="cursor: pointer;">
                    Client in Focus: <span id="current-client">
                        {{ session('selected_client_name') ?? 'None' }}
                    </span>
                </p>
            </div>

            <!-- Combined Settings Dropdown -->
            <div class="settings-dropdown">
                <button class="settings-toggle" id="settings-toggle">
                    <i class="bi bi-gear settings_button"></i>  Settings <span class="down-arrow">&#9660;</span>
                </button>
            </div>

            <!-- Moved OUTSIDE of dropdown to fix width issue -->
            <div id="settings-panel" class="hidden">
                <div class="settings-content">
                    <div class="settings-tabs">
                        <button class="tab-button active" data-tab="themes-tab">Themes</button>
                        <button class="tab-button" data-tab="screen-tab">Screen Options</button>
                    </div>

                    <!-- Themes Section -->
                    <div id="themes-tab" class="tab-content active">
                        <h3>Choose a Theme</h3>
                        <p>Select a theme to customize the appearance of the application.</p>
                        <div class="theme-options">
                            <ul class="theme-list">
                                <li>
                                    <button class="theme-btn theme-circle" data-theme="default">
                                        <span class="circle-indicator"></span>
                                    </button>
                                    <span class="theme-name">Default</span>
                                </li>
                                <li>
                                    <button class="theme-btn theme-circle" data-theme="dark">
                                        <span class="circle-indicator"></span>
                                    </button>
                                    <span class="theme-name">Dark Mode</span>
                                </li>
                                <li>
                                    <button class="theme-btn theme-circle" data-theme="blue">
                                        <span class="circle-indicator"></span>
                                    </button>
                                    <span class="theme-name">Blue Theme</span>
                                </li>
                                <li>
                                    <button class="theme-btn theme-circle" data-theme="green">
                                        <span class="circle-indicator"></span>
                                    </button>
                                    <span class="theme-name">Green Theme</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Screen Options -->
                    <div id="screen-tab" class="tab-content">
                        <h3>Screen elements</h3>
                        <p>Some screen elements can be shown or hidden by using the checkboxes. Expand or collapse...</p>
                        <div class="widget-options">
                            <div class="option-item">
                                <input type="checkbox" id="widget-keyword_search" {{ isset($screen_options['keyword_search']) && $screen_options['keyword_search'] ? 'checked' : '' }}>
                                <label for="widget-keyword_search">Keyword Search</label>
                            </div>
                            <div class="option-item">
                                <input type="checkbox" id="widget-content_display_area" {{ isset($screen_options['content_display_area']) && $screen_options['content_display_area'] ? 'checked' : '' }}>
                                <label for="widget-content_display_area">Content Display Area</label>
                            </div>
                            <div class="option-item">
                                <input type="checkbox" id="widget-droppable_area" {{ isset($screen_options['droppable_area']) && $screen_options['droppable_area'] ? 'checked' : '' }}>
                                <label for="widget-droppable_area">Droppable Area</label>
                            </div>
                            <div class="option-item">
                                <input type="checkbox" id="widget-editor_container" {{ isset($screen_options['editor_container']) && $screen_options['editor_container'] ? 'checked' : '' }}>
                                <label for="widget-editor_container">Editor Container</label>
                            </div>
                        </div>

                        <h3>Layout</h3>
                        <div class="layout-options">
                            <div class="option-item">
                                <input type="radio" id="layout-default" name="layout" {{ (!isset($screen_options['layout']) || $screen_options['layout'] === 'default') ? 'checked' : '' }}>
                                <label for="layout-default">Default</label>
                            </div>
                            <div class="option-item">
                                <input type="radio" id="layout-compact" name="layout" {{ isset($screen_options['layout']) && $screen_options['layout'] === 'compact' ? 'checked' : '' }}>
                                <label for="layout-compact">Compact</label>
                            </div>
                        </div>

                        <h3>Additional settings</h3>
                        <div class="additional-settings">
                            <div class="option-item">
                                <input type="checkbox" id="sticky-header" {{ isset($screen_options['sticky_header']) && $screen_options['sticky_header'] ? 'checked' : '' }}>
                                <label for="sticky-header">Sticky header</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="header_img"> 
                <a href="{{ route('profile.edit') }}"><img src="{{ asset('user_assets/img/profile.jpg') }}" alt=""></a> 
            </div>
        </div>
    </header>
    
    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div class="nav_list"> 
                <a href="{{ route('user.dashboard') }}" class="nav_link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}"> 
                    <i class='bx bx-grid-alt nav_icon'>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                        </svg>
                    </i> 
                    <span class="nav_name">Dashboard</span> 
                </a> 

                <a href="{{ route('templates') }}" class="nav_link {{ request()->routeIs('templates') ? 'active' : '' }}"> 
                    <i class='bx bx-message-square-detail nav_icon'>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark" viewBox="0 0 16 16">
                            <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
                        </svg>
                    </i> 
                    <span class="nav_name">Templates</span> 
                </a> 

                           <a href="{{ route('user.government-links') }}" class="nav_link {{ request()->routeIs('user.government-links*') ? 'active' : '' }}"> 
                    <i class='bx bx-bookmark nav_icon'>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bookmark-check-fill" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M2 15.5V2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.74.439L8 13.069l-5.26 2.87A.5.5 0 0 1 2 15.5m8.854-9.646a.5.5 0 0 0-.708-.708L7.5 7.793 6.354 6.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0z"/>
                        </svg>
                    </i> 
                    <span class="nav_name">Government Links</span>
                </a> 

                <a href="{{ route('user.rcic-deadlines.index') }}" class="nav_link"> 
                    <i class="fas fa-calendar nav_icon"></i>
                    <span class="nav_name">RCIC Deadlines</span>
                </a>                <a href="{{ route('user.legal-key-terms.index') }}" class="nav_link"> 
                    <i class="fas fa-calendar nav_icon"></i>
                    <span class="nav_name">Legal key Terms</span>
                </a> 

                <a href="{{ route('payment.details') }}" class="nav_link {{ request()->routeIs('payment.details') ? 'active' : '' }}"> 
                    <i class='bx bx-bar-chart-alt-2 nav_icon'>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                            <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1z"/>
                        </svg>
                    </i> 
                    <span class="nav_name">Payment Details</span> 
                </a>

          
                  <a href="{{ route('profile.edit') }}" class="nav_link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"> 
                    <i class='bx bx-user nav_icon'>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                        </svg>
                    </i> 
                    <span class="nav_name">Profile Details</span> 
                </a> 


            </div>

            <a href="{{ route('logout') }}" 
               class="nav_link" 
               id="logout-link" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> 
                <i class='bx bx-log-out nav_icon'>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 12.5a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v2a.5.5 0 0 1-1 0v-2A1.5 1.5 0 0 1 6.5 2h8A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-8A1.5 1.5 0 0 1 5 12.5v-2a.5.5 0 0 1 1 0z"/>
                        <path fill-rule="evenodd" d="M.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L1.707 7.5H10.5a.5.5 0 0 1 0 1H1.707l2.147 2.146a.5.5 0 0 1-.708.708z"/>
                    </svg>
                </i> 
                <span class="nav_name">SignOut</span> 
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
    </div>

    <div class="main-content">
        @yield('content')
    </div>

    <footer class="gap_footer footer">
        <div class="row container">
            <p>&copy;<a target="_blank" href="https://immifocus.ca">immigopro</a> {{ date("Y") }} - All Rights Reserved</p>
        </div>
    </footer>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.4.1/tinymce.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom Scripts -->
    <script src="{{ asset('user_assets/js/template-manager.js') }}"></script>
    <script src="{{ asset('user_assets/js/config.js') }}"></script>
    <script src="{{ asset('user_assets/js/script.js') }}"></script>
    <script src="{{ asset('user_assets/js/side_bar.js') }}"></script>
    <script src="{{ asset('user_assets/js/navigation.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
