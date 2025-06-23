@extends('layouts.user-layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Legal Key Terms</h5>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <div class="mb-4">
                        <form action="{{ route('user.legal-key-terms.index') }}" method="GET" class="row g-3" id="searchForm">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search terms..." name="search" value="{{ request('search') }}" id="searchInput">
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
                            <div class="col-md-3">
                                <select name="language" class="form-select" id="languageFilter" onchange="this.form.submit()">
                                    <option value="">All Languages</option>
                                    @foreach($languages as $code => $name)
                                        <option value="{{ $code }}" {{ request('language') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Terms Cards -->
                    <div class="row">
                        @forelse($terms as $term)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">{{ $term->term }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">{{ $term->definition }}</p>
                                        
                                        <div class="mt-3">
                                            @if($term->category)
                                                <div class="mb-2">
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-folder me-1"></i> {{ $term->category }}
                                                    </span>
                                                </div>
                                            @endif
                                            
                                            @if($term->source)
                                                <div class="text-muted">
                                                    <small><i class="fas fa-book me-1"></i> Source: {{ $term->source }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    No terms found.
                                </div>
                            </div>
                        @endforelse
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
@endsection
