@extends('layouts.master-layouts')

@section('title') Club Location @endsection

@section('content')

@component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('li_1_link') {{url('/')}} @endslot
    @slot('title') Club Location Management @endslot
@endcomponent


<div class="card">

    <!-- ✅ HEADER FIXED -->
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">

        <!-- Left side (empty or title if needed) -->
        <div>
            {{-- Optional Title --}}
            {{-- <h4 class="mb-0">Club Location</h4> --}}
        </div>

        <!-- Right side buttons -->
        <div class="d-flex gap-2">

            @if(hasActivePermission("$permission_prefix-create"))
                <button class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#AddModal">
                    <i class="mdi mdi-plus"></i> Add New
                </button>
            @endcan

            @can('super admin')
                <a class="btn btn-danger"
                    href="{{ url('admin/club-location/trash') }}">
                    <i class="mdi mdi-trash"></i> View Trash
                </a>
            @endcan

        </div>

    </div>

    <!-- TABLE -->
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-bordered" id="bstable"
                data-toggle="table"
                data-page-list="[100, 500, 1000, 2000, All]"
                data-page-size="100"
                data-ajax="ajaxRequest"
                data-side-pagination="server"
                data-pagination="true"
                data-search="false"
                data-total-field="count"
                data-data-field="items"
                data-show-columns="false"
                data-show-toggle="false"
                data-show-export="false"
                data-filter-control="true">

                <thead>
                    <tr>
                        <th data-field="sr_no" data-width="75">Sr. No.</th>
                        <th data-field="name" data-filter-control="input">Name</th>
                        <th data-field="code" data-filter-control="input">Code</th>
                        <th data-field="status">Status</th>
                        <th data-field="created_at">Created Date & Time</th>
                        <th class="text-center" data-field="action">Action</th>
                    </tr>
                </thead>

            </table>
        </div>
    </div>

</div>

<!-- CREATE MODAL -->
@if(hasActivePermission("$permission_prefix-create"))
    @include('admin.club-location.add-edit-modal')
@endcan

@endsection


@section('script')

<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    var merchant_id = "{{ $merchant_id ?? 0 }}";

    function ajaxRequest(params) {

        params.data.merchant_id = merchant_id;

        $.get(DataTableUrl + '?' + $.param(params.data)).then(function (res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res);
        });
    }

    $('#AddModal').on('shown.bs.modal', function () {
        $('.validation-error').hide();
    });
</script>

<script src="{{ URL::asset('build/js/crud.js')}}"></script>

@endsection
