@extends('layouts.admin')

@section('admin-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">RCIC Deadlines</h5>
                    <div>
                        <a href="{{ route('admin.rcic-deadlines.index') }}?export=csv" class="btn btn-secondary me-2">Export to CSV</a>
                        <a href="{{ route('admin.rcic-deadlines.create') }}" class="btn btn-custom">Add New Deadline</a>
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
                        <form action="{{ route('admin.rcic-deadlines.index') }}" method="GET" class="row g-3" id="searchForm">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search by title or category" name="search" value="{{ request('search') }}" id="searchInput">
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
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Days Before</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deadlines as $deadline)
                                    <tr>
                                        <td>{{ $deadline->title }}</td>
                                        <td>{{ $deadline->category }}</td>
                                        <td>{{ $deadline->type }}</td>
                                        <td>{{ $deadline->description }}</td>
                                        <td>{{ $deadline->days_before }}</td>
                                        <td>
                                            <span class="badge bg-{{ $deadline->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($deadline->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.rcic-deadlines.edit', $deadline->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                            <form action="{{ route('admin.rcic-deadlines.destroy', $deadline->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger btn-delete" onclick="return confirm('Are you sure you want to delete this deadline?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No deadlines found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $deadlines->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
