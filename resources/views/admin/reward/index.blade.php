@extends('layouts.master-layouts')

@section('title') Rewards @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') {{$type === 'campaign-voucher' ? 'Campaign Voucher Management' :'Rewards Management'}} @endslot
@endcomponent


<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        {{--<h4 class="card-title mb-0">Rewards</h4>--}}
        @can("$permission_prefix-create")
        <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"><i class="mdi mdi-plus"></i>
            Add New</button>
        @endcan
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-search-time-out="1200" data-page-size="100" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75" data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="code" data-filter-control="input" data-sortable="true" data-escape="true">Code</th>
                        <th data-field="name" data-filter-control="input" data-sortable="true" data-escape="true">Name</th>
                        <th data-field="no_of_keys" data-filter-control="input" data-sortable="true">Amount</th>
                        <th data-field="balance">Balance</th>
                        <th data-field="quantity" data-filter-control="input" data-sortable="true">Total</th>
                        <th data-field="total_redeemed" data-filter-control="input" data-sortable="true">Issuance</th>
                        <th data-field="redeemed">Redeemed</th>
                        <th data-field="duration">Duration</th>
                        <th data-field="image">Image</th>
                        <th data-field="created_at">Created On</th>

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
@include('admin.reward.add-edit-modal')
@endcan
<!-- end modal -->
@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var type = "{{$type}}";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        params.data.type = type
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }
    $(function() {
        $(document).on("change", ".reward_type", function() {
            $('.reward-type-amount').toggle()
            $('.reward-type-product').toggle()

        })
        $(document).on("change", "#is_featured", function() {
            $('.is-featured-div').toggle()
          

        })


        $(document).on("click", ".clear-time", function() {
            $("input[name='start_time']").val("");
            $("input[name='end_time']").val("");
        })
    });
</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>


@endsection