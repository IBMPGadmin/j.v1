@extends('layouts.admin')

@section('admin-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Government Link Details</h5>
                    <a href="{{ route('admin.government-links.index') }}" class="btn btn-custom">Back to List</a>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Name</h6>
                        <p class="lead">{{ $link->name }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">URL</h6>
                        <p><a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">{{ $link->url }}</a></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Category</h6>
                        <p>{{ $link->category ?: 'Not specified' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Description</h6>
                        <p>{{ $link->description ?: 'No description available' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Status</h6>
                        <p>
                            <span class="badge {{ $link->active ? 'bg-success' : 'bg-danger' }}">
                                {{ $link->active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Sort Order</h6>
                        <p>{{ $link->sort_order }}</p>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">                        <a href="{{ route('admin.government-links.edit', $link->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('admin.government-links.destroy', $link->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this link?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
