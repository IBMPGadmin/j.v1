@extends('layouts.user-layout')

@section('content')
<div class="container">
    <div class="row gap_top">
        <form method="GET" action="" id="filterForm" class="gap_top btn-shadow bg_custom p-4 rounded shadow-sm">
            <div class="row g-3 align-items-center">
                <div class="col-lg-12">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by Legal Act, Regulation, or Keyword.." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-4">
                    <select name="law_id" class="form-select">
                        <option value="">Select Law Subject</option>
                        <option value="1" {{ request('law_id') == '1' ? 'selected' : '' }}>Immigration</option>
                        <option value="2" {{ request('law_id') == '2' ? 'selected' : '' }}>Citizenship</option>
                        <option value="3" {{ request('law_id') == '3' ? 'selected' : '' }}>Criminal</option>
                    </select>
                </div>
                <div class="col-lg-4">
                    <select name="jurisdiction_id" class="form-select">
                        <option value="">Select Jurisdiction</option>
                        <option value="1" {{ request('jurisdiction_id') == '1' ? 'selected' : '' }}>Federal</option>
                        <optgroup label="Provincial">
                            <option value="2" {{ request('jurisdiction_id') == '2' ? 'selected' : '' }}>Alberta</option>
                            <option value="3" {{ request('jurisdiction_id') == '3' ? 'selected' : '' }}>British Columbia</option>
                            <option value="4" {{ request('jurisdiction_id') == '4' ? 'selected' : '' }}>Manitoba</option>
                            <option value="5" {{ request('jurisdiction_id') == '5' ? 'selected' : '' }}>New Brunswick</option>
                            <option value="6" {{ request('jurisdiction_id') == '6' ? 'selected' : '' }}>Newfoundland & Labarador</option>
                            <option value="7" {{ request('jurisdiction_id') == '7' ? 'selected' : '' }}>Nova Scotia</option>
                            <option value="8" {{ request('jurisdiction_id') == '8' ? 'selected' : '' }}>Ontario</option>
                            <option value="9" {{ request('jurisdiction_id') == '9' ? 'selected' : '' }}>Price Edward Island</option>
                            <option value="10" {{ request('jurisdiction_id') == '10' ? 'selected' : '' }}>Quebec</option>
                            <option value="11" {{ request('jurisdiction_id') == '11' ? 'selected' : '' }}>Saskatchewan</option>
                        </optgroup>
                        <optgroup label="Territorial">
                            <option value="12" {{ request('jurisdiction_id') == '12' ? 'selected' : '' }}>Nortwest Territories</option>
                            <option value="13" {{ request('jurisdiction_id') == '13' ? 'selected' : '' }}>Nunavut</option>
                            <option value="14" {{ request('jurisdiction_id') == '14' ? 'selected' : '' }}>Yukon</option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-lg-4">
                    <select name="act_id" class="form-select">
                        <option value="">Select Docs Category</option>
                        <option value="1" {{ request('act_id') == '1' ? 'selected' : '' }}>Acts</option>
                        <option value="2" {{ request('act_id') == '2' ? 'selected' : '' }}>Appeal & Review Processes</option>
                        <option value="3" {{ request('act_id') == '3' ? 'selected' : '' }}>CaseLaw</option>
                        <option value="4" {{ request('act_id') == '4' ? 'selected' : '' }}>Codes</option>
                        <option value="5" {{ request('act_id') == '5' ? 'selected' : '' }}>Enforcement</option>
                        <option value="6" {{ request('act_id') == '6' ? 'selected' : '' }}>Forms</option>
                        <option value="7" {{ request('act_id') == '7' ? 'selected' : '' }}>Guidelines</option>
                        <option value="8" {{ request('act_id') == '8' ? 'selected' : '' }}>Agreements</option>
                        <option value="9" {{ request('act_id') == '9' ? 'selected' : '' }}>Ministerial Instructions</option>
                        <option value="10" {{ request('act_id') == '10' ? 'selected' : '' }}>Operational Bulletins</option>
                        <option value="11" {{ request('act_id') == '11' ? 'selected' : '' }}>Policies</option>
                        <option value="12" {{ request('act_id') == '12' ? 'selected' : '' }}>Procedures</option>
                        <option value="13" {{ request('act_id') == '13' ? 'selected' : '' }}>Regulations</option>
                    </select>
                </div>
                <div class="col-lg-12 d-flex submit_reset_format justify-content-end">
                    <div class="button-group">
                        <button type="submit" class="btn btn-custom me-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button type="button" class="btn btn-reset" onclick="window.location.href='{{ url()->current() }}'">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <h2 class="gap_top">Client: {{ $client->client_name ?? 'N/A' }}</h2>
    <h3>Legal Tables</h3>
    <div class="row">
        @if($legalTables->isEmpty())
            <div class="col-12">
                <div class="alert alert-warning text-center mt-4">
                    <i class="fas fa-search"></i> No search results found.
                </div>
            </div>
        @else
            @foreach($legalTables as $table)
                <div class="col-lg-4 col-md-6 act-card btn-shadow">
                    <div class="act-card-inner">
                        <i class="fas fa-book act-icon"></i>
                        <div class="act-home-title">{{ $table->act_name }}</div>
                        <div class="act-category">Category: {{ $table->act_id }}</div>
                        <div class="act-law">Law Subject: {{ $table->law_id }}</div>
                        <div class="act-jurisdiction">Jurisdiction: {{ $table->jurisdiction_id }}</div>
                        <div class="act-description">Created: {{ $table->created_at }}</div>
                        <div class="view-button">
                            <a href="{{ route('legalTable.view', ['id' => $table->id, 'client_id' => $client->id ?? request('client_id')]) }}" class="btn btn-primary w-100">
                                View Document <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
