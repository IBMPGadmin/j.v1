@extends('layouts.admin')

@section('admin-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Government Links</h5>
                    <div>
                        <a href="{{ route('admin.government-links.index') }}?export=csv" class="btn btn-secondary me-2">Export to CSV</a>
                        <a href="{{ route('admin.government-links.create') }}" class="btn btn-custom">Add New Link</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif                    <!-- Search Form -->
                    <div class="mb-4">
                        <form action="{{ route('admin.government-links.index') }}" method="GET" class="row g-3" id="searchForm">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search by name or category" name="search" value="{{ request('search') }}" id="searchInput">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti ti-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-filter"></i> Filter
                                </button>
                            </div>                            <div class="col-md-1">
                                <a href="{{ route('admin.government-links.index') }}" class="btn btn-secondary w-100" id="resetButton">
                                    <i class="ti ti-refresh"></i>
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>URL</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Sort Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($links as $link)
                                    <tr>
                                        <td>{{ $link->name }}</td>
                                        <td>
                                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">
                                                {{ Str::limit($link->url, 40) }}
                                            </a>
                                        </td>
                                        <td>{{ $link->category }}</td>
                                        <td>
                                            <span class="badge {{ $link->active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $link->active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $link->sort_order }}</td>
                                        <td>
                                            <div class="btn-group" role="group">                                                <a href="{{ route('admin.government-links.edit', $link->id) }}" class="btn btn-sm btn-primary">
                                                    Edit
                                                </a>                                                <form action="{{ route('admin.government-links.destroy', $link->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger btn-delete">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No government links found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>                    <!-- Pagination and Results Count -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4">
                        <div class="mb-3 mb-md-0">
                            <span class="text-muted">
                                Showing {{ $links->firstItem() ?? 0 }} to {{ $links->lastItem() ?? 0 }} of {{ $links->total() }} entries
                                @if(request('search') || request('category') || request('status') !== null)
                                    <span class="ms-2">
                                        (filtered from {{ $links->total() }} total records)
                                    </span>
                                @endif
                            </span>
                        </div>
                        <div>
                            {{ $links->appends(request()->query())->onEachSide(1)->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when select fields change
        const categorySelect = document.getElementById('categoryFilter');
        const statusSelect = document.getElementById('statusFilter');
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        const resetButton = document.getElementById('resetButton');
        
        let searchTimeout = null;
        
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                searchForm.submit();
            });
        }
        
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                searchForm.submit();
            });
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 2 || this.value.length === 0) {
                        searchForm.submit();
                    }
                }, 500);
            });
        }
        
        if (resetButton) {
            resetButton.addEventListener('click', function(e) {
                e.preventDefault();
                // Clear all form inputs
                searchInput.value = '';
                categorySelect.value = '';
                statusSelect.value = '';
                // Submit the form
                searchForm.submit();
            });
        }
        
        // Delete confirmation
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this link? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush
