@extends('layouts.master-layouts')

@section('title') Rewards Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Rewards Management CMS @endslot
@endcomponent



<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Rewards Redemption Management</h4>

    </div>--}}
    <div class="card-body">
        <div class="sh_table table-responsive">
            <table class="table table-bordered" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75" data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="unique_id" data-filter-control="input" data-sortable="true">Unique ID
                        </th>
                        <th data-field="name" data-filter-control="input" data-sortable="true" data-escape="true">Name</th>
                        <th data-field="reward" data-filter-control="input" data-sortable="true" data-escape="true">Reward</th>
                        <th data-field="key_use" data-filter-control="input" data-sortable="true">Amount</th>
                        <th data-field="created_at" data-sortable="true">Added Date</th>
                        <th data-field="expiry_date" data-sortable="true">Expiry Date</th>
                        <th data-field="status" data-filter-control="select" data-filter-data="var:statusDefault" data-sortable="false">Status</th>
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
    var statusDefault = {
        Purchased: 'Issued',
        Redeemed: 'Redeemed',
        Expired: 'Expired',
    };

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }
</script>

@endsection