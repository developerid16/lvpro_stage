@extends('layouts.master-layouts')

@section('title') Contact Us Request @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Contact Us Request @endslot
@endcomponent





<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Contact Us Request</h4>
    </div>--}}
    <div class="card-body">
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
                        <th data-field="category" data-filter-control="input" data-sortable="true">Category</th>
                        <th data-field="subject" data-filter-control="input" data-sortable="true">Subject</th>
                        <th data-field="name" data-filter-control="input" data-sortable="true">Name</th>
 
                        <th data-field="created_at"  >Created At</th>

                        <th class="text-center" data-field="action" data-searchable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>


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

  
</script>
@endsection