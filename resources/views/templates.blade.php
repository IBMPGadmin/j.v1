@extends('layouts.user-layout')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Document Templates</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTemplateModal">
                        <i class="fas fa-plus me-2"></i> New Template
                    </button>
                </div>
                <div class="card-body">
                    @if(!session('selected_client_id'))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Please select a client to view their templates.
                        </div>
                    @else
                        <div class="row" id="templatesContainer">
                            <!-- Templates will be loaded here -->
                            @for($i = 1; $i <= 6; $i++)
                                <div class="col-md-4 mb-4">
                                    <div class="card template-card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-3">
                                                <h5 class="card-title">Sample Template {{ $i }}</h5>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                                        <li><a class="dropdown-item" href="#"><i class="fas fa-copy me-2"></i> Duplicate</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i> Delete</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <p class="card-text text-muted">This is a sample template description. It contains various sections that can be customized.</p>
                                            <div class="d-flex justify-content-between mt-3">
                                                <span class="badge bg-secondary">Document</span>
                                                <small class="text-muted">Updated: {{ date('M d, Y') }}</small>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent border-0">
                                            <button class="btn btn-outline-primary btn-sm w-100">
                                                <i class="fas fa-edit me-2"></i> Use Template
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Template Modal -->
<div class="modal fade" id="newTemplateModal" tabindex="-1" aria-labelledby="newTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newTemplateModalLabel">Create New Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newTemplateForm">
                    <div class="mb-3">
                        <label for="templateName" class="form-label">Template Name</label>
                        <input type="text" class="form-control" id="templateName" required>
                    </div>
                    <div class="mb-3">
                        <label for="templateDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="templateDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="templateType" class="form-label">Template Type</label>
                        <select class="form-control" id="templateType">
                            <option value="document">Document</option>
                            <option value="letter">Letter</option>
                            <option value="form">Form</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTemplateBtn">Create Template</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .template-card {
        transition: transform 0.2s;
    }
    
    .template-card:hover {
        transform: translateY(-5px);
    }
    
    .template-card .card-footer {
        padding-top: 0;
    }
    
    .badge {
        padding: 0.5em 0.8em;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Save template button action
        $('#saveTemplateBtn').click(function() {
            // Validation
            if(!$('#templateName').val()) {
                alert('Please enter a template name');
                return;
            }
            
            // Here you would normally save the template via AJAX
            // For this example, we'll just close the modal
            $('#newTemplateModal').modal('hide');
            
            // Show success message
            Swal.fire({
                title: 'Success!',
                text: 'Template created successfully',
                icon: 'success',
                confirmButtonText: 'Ok'
            });
        });
    });
</script>
@endpush
