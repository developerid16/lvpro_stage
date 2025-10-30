@extends('layouts.master-layouts')

@section('title') POS API Log @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') POS API Log @endslot
@endcomponent
 
<div class="card">


    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75" data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="name">Name</th>
                        <th data-field="start_time">Request Time</th>
                        <th data-field="end_time">Response Time</th>
                        <th data-field="req_data" style="width: 200px; word-break: break-all;">Request Data</th>
                        <th data-field="response_data" style="width: 200px;">Response Data</th>


                        <th data-field="status" data-sortable="false">Status</th>


                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@can("$permission_prefix-create")
@include('admin.sales.add-edit-modal')
@endcan
<!-- end modal -->
@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }
</script>
@endsection