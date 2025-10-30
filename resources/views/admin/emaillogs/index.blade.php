@extends('layouts.master-layouts')

@section('title') Email Log @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Email Log @endslot
@endcomponent





<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <div class="ml_auto">

            <button class="sh_btn btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"><i
                    class="mdi mdi-plus"></i>
                Add New</button>
           
        </div>

    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-toggle="table"
                data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest"
                data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count"
                data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false"
                data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75"
                            data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="email" data-filter-control="input" data-sortable="true">Email</th>
                        <th data-field="type" data-filter-control="input" data-sortable="true">Type</th>
                        <th data-field="status" data-filter-control="input" data-sortable="false">Status</th>
                        <th data-field="created_at">Time</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@include('admin.emaillogs.add-edit-modal')
<!-- end modal -->
@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    const csrf = $('meta[name="csrf-token"]').attr('content')
    var DataTableUrl = ModuleBaseUrl+"datatable";
    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function (res) {
         $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }
 
  
</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection