@extends('layouts.admin')

@section('admin-content')
<style>
    .upload-container {
        max-width: 800px;
        margin: 30px auto;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .form-container {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .table-container {
        margin-top: 30px;
        background-color: white;
        padding: 20px;
        border-radius: 8px;
    }
    .form-label {
        font-weight: 500;
        color: #495057;
    }
    .input-group {
        margin-bottom: 15px;
    }
    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        padding: 8px 20px;
    }
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
</style>

<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Home</h5>
                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                            <li class="breadcrumb-item">Legal Documents</li>
                            <li class="breadcrumb-item active">Add Legal Documents</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="upload-container">
                <h2 class="mb-4 text-center">Upload Legal XML File</h2>
                @if(session('success'))
                    <div class="alert alert-success mt-3">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="form-container">
                    <form action="{{ route('admin.legal-documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label"><b>Select Processing Method:</b></label>
                            <p class="text-muted" style="margin-top: -8px; margin-bottom: 10px;">
                                <small>Please select the most suitable method for your file type.<br>
                                Read the guidelines given in the brackets before uploading.</small>
                            </p>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="process_type" id="standard_process" value="standard" checked>
                                <label class="form-check-label" for="standard_process">
                                    Standard XML Process (For All the Acts & Regulations )
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="process_type" id="alternative_process" value="alternative">
                                <label class="form-check-label" for="alternative_process">
                                    Alternative XML Process (Recommend Specially for Large Acts like <b>Criminal Code</b>)
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="xmlfile" class="form-label">Select XML File:</label>
                            <input type="file" class="form-control" id="xmlfile" name="xmlfile" accept=".xml" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="law_id" class="form-label">Law ID:</label>
                                    <input type="number" class="form-control" id="law_id" name="law_id" required min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="act_id" class="form-label">Act ID:</label>
                                    <input type="number" class="form-control" id="act_id" name="act_id" required min="1">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="act_name" class="form-label">Act Name:</label>
                                    <input type="text" class="form-control" id="act_name" name="act_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jurisdiction_id" class="form-label">Jurisdiction ID:</label>
                                    <input type="number" class="form-control" id="jurisdiction_id" name="jurisdiction_id" required min="1">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="act_name_1" class="form-label">Reference Name 01:</label>
                                    <input type="text" class="form-control" id="act_name_1" name="act_name_1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="act_name_2" class="form-label">Reference Name 02:</label>
                                    <input type="text" class="form-control" id="act_name_2" name="act_name_2">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="act_name_3" class="form-label">Reference Name 03:</label>
                                    <input type="text" class="form-control" id="act_name_3" name="act_name_3">
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Upload and Process</button>
                        </div>
                    </form>
                </div>
                <div class="table-container">
                    @php
                        $db = new mysqli(
                            config('database.connections.mysql.host', 'localhost'),
                            config('database.connections.mysql.username', 'root'),
                            config('database.connections.mysql.password', ''),
                            config('database.connections.mysql.database', 'testings')
                        );
                        $result = $db->query("SELECT * FROM legal_tables_master ORDER BY created_at DESC");
                    @endphp
                    @if($result && $result->num_rows > 0)
                        <h3>Existing Legal Hierarchy Tables:</h3>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Table Name</th>
                                        <th>Original Filename</th>
                                        <th>Act Name</th>
                                        <th>Reference Name 1</th>
                                        <th>Reference Name 2</th>
                                        <th>Reference Name 3</th>
                                        <th>Law ID</th>
                                        <th>Act ID</th>
                                        <th>Jurisdiction ID</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @while($row = $result->fetch_assoc())
                                        <tr>
                                            <td>{{ $row['table_name'] }}</td>
                                            <td>{{ $row['original_filename'] }}</td>
                                            <td>{{ $row['act_name'] }}</td>
                                            <td>{{ $row['act_name_1'] }}</td>
                                            <td>{{ $row['act_name_2'] }}</td>
                                            <td>{{ $row['act_name_3'] }}</td>
                                            <td>{{ $row['law_id'] }}</td>
                                            <td>{{ $row['act_id'] }}</td>
                                            <td>{{ $row['jurisdiction_id'] }}</td>
                                            <td>{{ $row['created_at'] }}</td>
                                            <td>
                                                <!-- You can add real actions here -->
                                                <a href="#" class="btn btn-sm btn-info me-2">View</a>
                                                <a href="#" class="btn btn-sm btn-success">Download</a>
                                            </td>
                                        </tr>
                                    @endwhile
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">No tables have been created yet.</div>
                    @endif
                </div>
                {{-- You can add a table here to show existing uploaded documents if needed --}}
            </div>
        </div>
    </div>
</div>
@endsection
