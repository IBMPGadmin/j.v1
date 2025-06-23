@extends('layouts.admin')

@section('admin-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add New Legal Key Term</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.legal-key-terms.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="term" class="form-label">Term <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('term') is-invalid @enderror" id="term" name="term" value="{{ old('term') }}" required>
                            @error('term')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="definition" class="form-label">Definition <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('definition') is-invalid @enderror" id="definition" name="definition" rows="4" required>{{ old('definition') }}</textarea>
                            @error('definition')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="language" class="form-label">Language <span class="text-danger">*</span></label>
                            <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
                                <option value="">Select Language</option>
                                @foreach($languages as $code => $name)
                                    <option value="{{ $code }}" {{ old('language') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('language')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="source" class="form-label">Source</label>
                            <input type="text" class="form-control @error('source') is-invalid @enderror" id="source" name="source" value="{{ old('source') }}">
                            @error('source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('admin.legal-key-terms.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
