@extends('layouts.master-layouts')

@section('title') Order Data Migrate @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Order Data Migrate @endslot
@endcomponent

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
    </div>
    <div class="card-body pt-0">

        {{-- Success / Error Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <form action="{{ url('admin/data-migrate/orders-upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="excel_file" class="form-label">Upload Excel / CSV File</label>
                    <input type="file" class="form-control" id="excel_file" name="excel_file"
                        accept=".xlsx, .xls, .csv" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>

    </div>
</div>

<div id="edit_modal_placeholder"></div>

@endsection