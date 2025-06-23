@extends('layouts.admin')

@section('admin-content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">Admin Dashboard</div>
                <div class="card-body">
                    <h3>Welcome, Admin!</h3>
                    <p>This is the admin dashboard. Only users with the admin role can access this page.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
