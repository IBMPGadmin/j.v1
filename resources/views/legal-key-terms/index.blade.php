@extends('layouts.admin')

@section('admin-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Legal Key Terms</h5>
                    <div>
                        <a href="{{ route('admin.legal-key-terms.index') }}?export=csv" class="btn btn-secondary me-2">Export to CSV</a>
                        <a href="{{ route('admin.legal-key-terms.create') }}" class="btn btn-custom">Add New Term</a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Search Form -->
                    <div class="mb-4">
                        <form action="{{ route('admin.legal-key-terms.index') }}" method="GET" class="row g-3" id="searchForm">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search terms..." name="search" value="{{ request('search') }}" id="searchInput">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti ti-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="language" class="form-select" id="languageFilter">
                                    <option value="">All Languages</option>
                                    @foreach($languages as $code => $name)
                                        <option value="{{ $code }}" {{ request('language') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
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
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-filter"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Terms Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Term</th>
                                    <th>Definition</th>
                                    <th>Language</th>
                                    <th>Category</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($terms as $term)
                                    <tr>
                                        <td>{{ $term->term }}</td>
                                        <td>{{ Str::limit($term->definition, 100) }}</td>
                                        <td>{{ $languages[$term->language] ?? $term->language }}</td>
                                        <td>{{ $term->category }}</td>
                                        <td>{{ $term->source }}</td>
                                        <td>
                                            <span class="badge bg-{{ $term->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($term->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.legal-key-terms.edit', $term->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                            <form action="{{ route('admin.legal-key-terms.destroy', $term->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this term?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No terms found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $terms->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-submit form when filters change
    document.querySelectorAll('#languageFilter, #categoryFilter, #statusFilter').forEach(function(element) {
        element.addEventListener('change', function() {
            document.getElementById('searchForm').submit();
        });
    });
</script>
@endpush
@endsection
