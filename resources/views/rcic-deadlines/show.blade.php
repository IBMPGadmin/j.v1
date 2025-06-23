@extends('layouts.admin')

@section('admin-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">RCIC Deadline Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Title</dt>
                        <dd class="col-sm-8">{{ $deadline->title }}</dd>
                        <dt class="col-sm-4">Category</dt>
                        <dd class="col-sm-8">{{ $deadline->category }}</dd>
                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $deadline->description }}</dd>
                        <dt class="col-sm-4">Deadline Date</dt>
                        <dd class="col-sm-8">{{ $deadline->deadline_date ? $deadline->deadline_date->format('Y-m-d') : '' }}</dd>
                        <dt class="col-sm-4">Days Before</dt>
                        <dd class="col-sm-8">{{ $deadline->days_before }}</dd>
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $deadline->status == 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($deadline->status) }}
                            </span>
                        </dd>
                    </dl>
                    <a href="{{ route('admin.rcic-deadlines.edit', $deadline->id) }}" class="btn btn-primary">Edit</a>
                    <a href="{{ route('admin.rcic-deadlines.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
