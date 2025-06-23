@extends('layouts.user-layout')

@php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
@endphp

@section('content')
<div class="client-section">
    <div class="container">        <!-- Add Client Form -->
        <div class="btn-shadow bg_custom p-4 rounded shadow-sm">
            <form method="POST" action="{{ route('clients.store') }}">
                @csrf
                <input type="hidden" name="add_client" value="1">
                <div class="row">    
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Add New Client..65456456465</h5>
                        <!-- Move button inside form and make it a submit button -->
                        <button type="submit" class="btn add-client-btn btn-custom">Add New Client</button>
                    </div>
                </div>
                <div class="gap_top card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @elseif (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="client_name">Client Name</label>
                                <input type="text" class="form-control" id="client_name" name="client_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="client_email">Email</label>
                                <input type="email" class="form-control" id="client_email" name="client_email" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="client_status">Status</label>
                                <select class="form-control" id="client_status" name="client_status" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Client Cards Section -->
        <div class="gap_top btn-shadow bg_custom p-4 rounded shadow-sm">
            <div class="card-header">
                <div class="clients-header">
                    <h5>Your Clients</h5>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="clientSearch" placeholder="Search clients...">
                    </div>
                </div>
            </div>
            <div class="card-body">                <div class="client-grid" id="clientGrid">
                    @php
                    $user_id = Auth::id();
                    $clients = [];
                    $most_recent = null;
                    $total_pages = 1;
                    $page = request()->get('page', 1);
                    
                    // Get clients and most recent access time
                    try {
                        $clients_per_page = 8;
                        $offset = ($page - 1) * $clients_per_page;
                        
                        // Debug log to verify user_id
                        Log::info('Fetching clients for user_id: ' . $user_id);
                        
                        // Direct query to check if table exists
                        $tableExists = DB::select("SHOW TABLES LIKE 'client_table'");
                        if(empty($tableExists)) {
                            Log::error('client_table does not exist in the database');
                        } else {
                            // Get table structure
                            $columns = DB::select("SHOW COLUMNS FROM client_table");
                            $columnNames = array_map(function($col) { return $col->Field; }, $columns);
                            Log::info('client_table columns: ' . implode(', ', $columnNames));
                            
                            // Fetch clients for this page - using basic query to avoid issues
                            $clients = DB::table('client_table')
                                ->where('user_id', $user_id)
                                ->orderBy('id', 'desc')
                                ->get();
                            
                            $total_clients = count($clients);
                            $total_pages = ceil($total_clients / $clients_per_page);
                            
                            // Paginate manually
                            $clients = $clients->slice($offset, $clients_per_page);
                            
                            // Debug log to check results
                            Log::info('Found ' . count($clients) . ' clients for user_id ' . $user_id);
                        }
                    } catch (\Exception $e) {
                        // Log error
                        Log::error('Error fetching clients: ' . $e->getMessage());
                    }
                    @endphp
                      @if(!$clients || count($clients) == 0)
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h4>No Clients Found</h4>
                            <p>Add your first client to get started</p>
                        </div>
                    @else                        @foreach($clients as $client)                                @php
                                $initial = strtoupper(substr($client->client_name ?? 'U', 0, 1));
                                $status = isset($client->client_status) ? strtolower($client->client_status) : 'unknown';
                                $is_recent = false;
                                $time_ago = '';
                                
                                // Check if the last_accessed column exists before using it
                                if (isset($client->last_accessed) && !empty($client->last_accessed) && $client->last_accessed !== null) {
                                    // Check if this is the most recently accessed client
                                    if($most_recent && $client->last_accessed == $most_recent) {
                                        $is_recent = true;
                                    }
                                    
                                    try {
                                        $last_accessed = new DateTime($client->last_accessed);
                                        $now = new DateTime();
                                        $interval = $now->diff($last_accessed);
                                        
                                        if ($interval->y > 0) {
                                            $time_ago = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->m > 0) {
                                            $time_ago = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->d > 0) {
                                            $time_ago = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->h > 0) {
                                            $time_ago = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->i > 0) {
                                            $time_ago = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
                                        } else {
                                            $time_ago = 'just now';
                                        }
                                    } catch (\Exception $e) {
                                        Log::error('Error formatting date: ' . $e->getMessage());
                                    }
                                }
                            @endphp<div class="client-card">
                                <div class="client-avatar">{{ $initial }}</div>
                                <div class="client-info">
                                    <div class="client-name">{{ $client->client_name }}</div>
                                    @if(isset($client->client_email))
                                    <div class="client-email">{{ $client->client_email }}</div>
                                    @endif 
                                    <div class="client-status d-flex align-items-center justify-content-center gap-2 mt-2">
                                        @if(isset($client->client_status))
                                        <span class="badge bg-{{ $client->client_status == 'Active' ? 'success' : 'secondary' }}">
                                            {{ $client->client_status }}
                                        </span>
                                        @endif
                                        @if($is_recent)
                                            <div class="recent-badge">Recent <i class="fa-solid fa-clock-rotate-left"></i></div>
                                        @endif
                                    </div>
                                    @if(!empty($time_ago))
                                        <div class="last-accessed mt-2">
                                            <small class="text-muted">Last accessed: {{ $time_ago }}</small>
                                        </div>
                                    @endif                                     
                                </div>
                                <div class="client-actions">
                                    <form action="{{ route('user.client.legal-tables', $client->id) }}" method="GET" style="display:inline;">
                                        <button type="submit" class="btn-custom2 select-client-btn">
                                            Select Client
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                  @if(isset($total_pages) && $total_pages > 1)
                <div class="pagination" id="pagination">
                    <button onclick="changePage(1)" {{ $page == 1 ? 'disabled' : '' }}>&laquo;</button>
                    <button onclick="changePage({{ max(1, $page - 1) }})" {{ $page == 1 ? 'disabled' : '' }}>&lsaquo;</button>
                    
                    @for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++)
                        <button onclick="changePage({{ $i }})" {{ $page == $i ? 'class="active"' : '' }}>{{ $i }}</button>
                    @endfor
                    
                    <button onclick="changePage({{ min($total_pages, $page + 1) }})" {{ $page == $total_pages ? 'disabled' : '' }}>&rsaquo;</button>
                    <button onclick="changePage({{ $total_pages }})" {{ $page == $total_pages ? 'disabled' : '' }}>&raquo;</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    body {
        background-color: var(--bg-color);
        color: var(--text-color);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .client-section {
        padding: 2rem 0;
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        margin-bottom: 2rem;
        overflow: hidden;
        transition: var(--transition);
    }

    .card:hover {
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
    }

    .card-header {
        background-color: var(--primary-color);
        color: white;
        border-bottom: none;
        padding: 1.25rem 1.5rem;
        position: relative;
    }

    .card-header:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }

    .card-header h5 {
        font-weight: 600;
        margin: 0;
    }

    .card-body {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-control {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }

    label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
        color: var(--text-color);
    }

    .btn-primary {
        background-color: var(--primary-color);
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: var(--transition);
    }

    .btn-primary:hover {
        background-color: var(--primary-hover);
        transform: translateY(-2px);
    }

    .alert {
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Client Cards Section */
    .clients-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .search-box {
        position: relative;
        width: 300px;
    }

    .search-box input {
        width: 100%;
        padding: 0.75rem 1.25rem 0.75rem 3rem;
        border-radius: 50px;
        border: 1px solid #e0e0e0;
        transition: var(--transition);
    }

    .search-box input:focus {
        border-color: var(--primary-color);
    }

    .search-box i {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--light-text);
    }

    .client-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        max-height: 400px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .client-card {
        background: var(--color-card-bg);
        border-radius: 0.5rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(30, 28, 28, 0.64);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
    }

    .client-card .recent-badge {
        background-color: var(--color-danger);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        opacity: 0.9;
    }

    .client-card .last-accessed {
        color: var(--light-text);
        font-size: 0.8rem;
        margin-top: 0.5rem;
        text-align: center;
    }

    .client-avatar {
        width: 70px;
        height: 70px;
        background: var(--color-theme-3);
        border-radius: 50%;
        margin: 0 auto 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: #fff;
        font-weight: 600;
    }

    .client-info {
        text-align: center;
        margin-bottom: 1.5rem;
        flex-grow: 1;
    }

    .client-name {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }

    .client-email {
        color: var(--light-text);
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
        word-break: break-all;
    }

    .client-status {
        justify-content: center;
        display: flex;
    }

    .client-actions {
        margin-top: auto;
    }

    .select-client-btn {
        width: 100%;
        padding: 0.75rem;
        background-color: var(--color-theme-3);
        color: white;
        border-radius: 8px;
        font-weight: 500;
        transition: var(--transition);
        cursor: pointer;
    }

    .add-client-btn {
        width: 20%;
        padding: 0.75rem;
        background-color: var(--color-theme-3);
        color: white;
        border-radius: 8px;
        font-weight: 500;
        transition: var(--transition);
        cursor: pointer;
        max-width: 180px !important;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--light-text);
        grid-column: 1 / -1;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #e0e0e0;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .client-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }
        
        .clients-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .search-box {
            width: 100%;
            margin-top: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Pagination functionality
    function changePage(page) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('page', page);
        window.location.href = currentUrl.toString();
    }    // Client search functionality
    document.getElementById('clientSearch').addEventListener('input', function(e) {
        const searchText = e.target.value.toLowerCase();
        const clientCards = document.querySelectorAll('.client-card');

        let hasVisibleCards = false;
        
        clientCards.forEach(card => {
            const name = card.querySelector('.client-name') ? card.querySelector('.client-name').textContent.toLowerCase() : '';
            const email = card.querySelector('.client-email') ? card.querySelector('.client-email').textContent.toLowerCase() : '';
            const status = card.querySelector('.badge') ? card.querySelector('.badge').textContent.toLowerCase() : '';

            if (name.includes(searchText) || email.includes(searchText) || status.includes(searchText)) {
                card.style.display = '';
                hasVisibleCards = true;
            } else {
                card.style.display = 'none';
            }
        });

        // Show empty state if no cards match search
        const emptyState = document.querySelector('.empty-state');
        if (!hasVisibleCards && !emptyState) {
            document.querySelector('.client-grid').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h4>No Matching Clients</h4>
                    <p>Try a different search term</p>
                </div>
            `;
        } else if (hasVisibleCards && emptyState && emptyState.querySelector('h4').textContent === 'No Matching Clients') {
            location.reload(); // Reload to show original cards when search is cleared
        }
    });    // Client selection functionality
    /*
    document.querySelectorAll('.select-client-btn').forEach(button => {
        button.addEventListener('click', function() {
            const clientId = this.dataset.clientId;
            const clientName = this.dataset.clientName;
            
            // Add loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Selecting...';
            this.disabled = true;
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Check if CSRF token exists
            if (!csrfToken) {
                console.error('CSRF token not found');
                alert('CSRF token not found. Please refresh the page.');
                this.innerHTML = 'Select Client';
                this.disabled = false;
                return;
            }

            console.log('Sending select-client request with client ID:', clientId);

            fetch('/select-client', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: `client_id=${encodeURIComponent(clientId)}&client_name=${encodeURIComponent(clientName)}`
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // Update the client display in the navigation bar
                    const currentClientSpan = document.getElementById('current-client');
                    if (currentClientSpan) {
                        currentClientSpan.textContent = clientName;
                    }
                    
                    // Show success message
                    alert('Client selected successfully. Redirecting to documents page...');
                    
                    // Redirect after successful selection
                    if (data.redirect) {
                        console.log('Redirecting to:', data.redirect);
                        window.location.href = data.redirect;
                    } else {
                        // Default fallback redirect
                        console.log('Using default redirect to /documents');
                        window.location.href = '/documents';
                    }
                } else {
                    this.innerHTML = 'Select Client';
                    this.disabled = false;
                    alert('Failed to select client: ' + (data.message || 'Unknown error'));
                }
            })            .catch(error => {
                console.error('Error:', error);
                this.innerHTML = 'Select Client';
                this.disabled = false;
                alert('An error occurred while selecting the client. Check the console for details.');
            });
        });
    });
    */
</script>
@endpush
