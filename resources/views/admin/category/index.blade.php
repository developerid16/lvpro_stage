@extends('layouts.master-layouts')

@section('title') Category @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Category Management @endslot
@endcomponent



<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        {{--<h4 class="card-title mb-0">Aircrew Company</h4>--}}
        @can("$permission_prefix-create")
        <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"><i class="mdi mdi-plus"></i>
            Add New</button>
        @endcan
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-bordered" id="bstable" data-toggle="table"
                data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest"
                data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count"
                data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false"
                data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75"  data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="name" data-filter-control="input" data-sortable="true">Name</th>
                        <th data-field="created_at" data-filter-control="input" data-sortable="false">Created Date & Time</th>
                        <th data-field="updated_at" data-filter-control="input" data-sortable="false">Last Updated Date & Time</th>
                        <th class="text-center" data-field="action" data-searchable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@can("$permission_prefix-create")
@include('admin.category.add-edit-modal')
@endcan
<!-- end modal -->
@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl+"datatable";
    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function (res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    } 

    $(document).on('change', '#logo', function (e) {
        let file = this.files[0];

        if (!file) return;

        let preview = document.getElementById('logo_preview');
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    });

   
</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection