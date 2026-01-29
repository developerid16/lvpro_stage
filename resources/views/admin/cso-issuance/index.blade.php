@extends('layouts.master-layouts')

@section('title')
    CSO Issuance
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Admin
        @endslot
        @slot('li_1_link')
            {{ url('/') }}
        @endslot
        @slot('title')
            CSO Issuance
        @endslot
    @endcomponent
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
           
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="sh_table table table-bordered" id="bstable" data-toggle="table"
                    data-page-list="[100, 500, 1000, 2000, All]" data-search-time-out="1200" data-page-size="100"
                    data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="true" data-search="false"
                    data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false"
                    data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                    <thead>
                        <tr>
                            <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75"
                                data-width-unit="px" data-searchable="false">Sr. No.</th>
                          
                            <th data-field="name" data-filter-control="input" data-sortable="true" data-escape="true">Name</th>
                            <th data-field="quantity" data-filter-control="input" data-sortable="true">Total</th>
                            <th data-field="redeemed">Redeemed</th>
                            <th data-field="duration">Duration</th>
                            <th data-field="image">Image</th>
                            <th data-field="cso_method">CSO Method</th>
                            <th data-field="is_draft">Is Draft</th>
                            <th data-field="created_at">Created On</th>

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
        var DataTableUrl = ModuleBaseUrl + "datatable";
        var digitalMerChants = [];

        function ajaxRequest(params) {
            $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
                $('.fixed-table-body .fixed-table-loading').removeClass('open');
                params.success(res)
            })
        }  
       
       
    </script>
@endsection
