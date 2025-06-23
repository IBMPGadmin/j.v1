@extends('layouts.user-layout')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4>Welcome to {{ session('selected_client_name') }}'s Workspace</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You are currently working with <strong>{{ session('selected_client_name') }}</strong>.
                    </div>
                    
                    <div class="row mt-4">                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-alt fa-3x mb-3 text-primary"></i>
                                    <h5>Templates</h5>
                                    <p class="text-muted">Access document templates for this client</p>
                                    <a href="{{ route('templates') }}" class="btn btn-outline-primary">View Templates</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-success"></i>
                                    <h5>Legal Documents</h5>
                                    <p class="text-muted">View all legal documents for this client</p>
                                    <a href="{{ route('documents.index') }}" class="btn btn-outline-success">View Documents</a>
                                </div>
                            </div>
                        </div>                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-history fa-3x mb-3 text-warning"></i>
                                    <h5>Recent Documents</h5>
                                    <p class="text-muted">View recently accessed documents</p>
                                    <a href="{{ route('documents.index') }}" class="btn btn-outline-warning">View History</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-cog fa-3x mb-3 text-secondary"></i>
                                    <h5>Client Settings</h5>
                                    <p class="text-muted">Manage client information and preferences</p>
                                    <a href="#" class="btn btn-outline-secondary">Client Settings</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
