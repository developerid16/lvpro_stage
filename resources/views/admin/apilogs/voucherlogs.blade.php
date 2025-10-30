@extends('layouts.master-layouts')

@section('title') Voucher API Log @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Voucher API Log @endslot
@endcomponent

<div class="card">


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
                        <th data-field="voucher_no">Voucher No</th>
                        <th data-field="from_status">From Status</th>
                        <th data-field="to_status">To Status</th>
                        <th data-field="from_where" style="width: 200px; word-break: break-all;">From Who</th>
                        <th data-field="created_at" style="width: 200px;">Date Time</th>


                       


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