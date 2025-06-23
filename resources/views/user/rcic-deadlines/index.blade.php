@extends('layouts.user-layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>RCIC Deadlines</h5>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <div class="mb-4">
                        <form action="{{ route('user.rcic-deadlines.index') }}" method="GET" class="row g-3" id="searchForm">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search deadlines..." name="search" value="{{ request('search') }}" id="searchInput">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fa fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select" id="categoryFilter" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Deadlines Cards -->
                    <div class="row">
                        @forelse($deadlines as $deadline)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">{{ $deadline->title }}</h5>
                                    </div>
                                    <div class="card-body">
                                        @if($deadline->category)
                                            <div class="mb-2">
                                                <span class="badge bg-info">{{ $deadline->category }}</span>
                                            </div>
                                        @endif
                                        @if($deadline->type)
                                            <div class="mb-2">
                                                <span class="badge bg-secondary">{{ $deadline->type }}</span>
                                            </div>
                                        @endif
                                        <p class="card-text">{{ $deadline->description }}</p>
                                        @if($deadline->days_before)
                                            <div class="mt-3">
                                                <i class="fas fa-clock"></i>
                                                <strong>Days Before:</strong> {{ $deadline->days_before }} days
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    No deadlines found.
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $deadlines->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-submit form when category changes
    document.getElementById('categoryFilter').addEventListener('change', function() {
        document.getElementById('searchForm').submit();
    });
</script>
@endpush
@endsection
