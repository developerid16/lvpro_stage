@extends('layouts.master-layouts')
<style>
    .page-title-box {
        padding-bottom: 0px !important;
    }
</style>
@section('title') Location Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title')  Manage {{ $company_data->name }} Locations @endslot
{{-- @slot('title')  Manage Disney Locations @endslot --}}
@endcomponent



<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        {{--<h4 class="card-title mb-0">Passholder Company</h4>--}}
        @can("$permission_prefix-create")
        <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"><i class="mdi mdi-plus"></i>
            Add New</button>
        @endcan
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
                        <th data-field="name" data-filter-control="input" data-sortable="true">Name</th>
                        <th data-field="code" data-filter-control="input" data-sortable="true">Code</th>
                        <th data-field="address" data-filter-control="input" data-sortable="true">Address</th>
                        <th data-field="lease_duration" data-filter-control="input" data-sortable="true">Lease Durations</th>
                        <th data-field="status" data-filter-control="select" data-sortable="false">Status</th>
                        <th class="text-center" data-field="action" data-searchable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@can("$permission_prefix-create")
@include('admin.locations.add-edit-modal')
@endcan
<!-- end modal -->
@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl+"datatable";
    function ajaxRequest(params) {
        params.data.company_id = "{{$company_data->id}}";
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function (res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }

    $(function(){
        // target the first h4 inside .page-title-box
        var $h4 = $('.page-title-box').first();

        if ($h4.length) {
            // only insert if we don't already have the marker right after the h4
            if (!$h4.next().hasClass('page-merchant')) {
            // create the small element and insert it after the h4 (so it appears below the h4 and before the next element)
            $('<small class="page-merchant">(Participating Merchat)</small>')
                .css({
                'display': 'block',       // force it on its own line
                'font-size': '0.8em',     // small font
                'color': '#666',          // subtle color
                'margin-top': '4px'       // spacing
                })
                .insertAfter($h4);
            }
        }
    });

  
</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection