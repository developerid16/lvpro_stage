@extends('layouts.master-layouts')

@section('title') CSO Physical Collection @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') CSO Physical Collection @endslot
@endcomponent

<div class="card">
    <div class="card-header bg-white border-bottom mb-3">
        <div class="row g-2 align-items-end">

            <div class="col-md-3">
                <label class="form-label fw-bold">Filter By</label>
                <select id="filter_by" class="form-select">
                    <option value="member_id">Member ID</option>
                    <option value="receipt_no">Receipt Number</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Search</label>
                <input type="text" id="filter_value" class="form-control"
                    placeholder="Enter Member ID or Receipt No">
                </div>
                
            <div class="col-md-1">
                <button id="searchBtn" class="btn btn-primary w-100">
                    Search
                </button>
            </div>
            <div class="col-md-1">
                <button id="resetBtn" class="btn btn-secondary w-100">
                    Reset
                </button>
            </div>

        </div>
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
                        <th data-field="sr_no" data-width="75">Sr. No.</th>
                        <th data-field="receipt_no" data-sortable="true">Receipt No</th>
                        <th data-field="reward_name" data-sortable="true">Reward Name</th>
                        <th data-field="member_id" data-sortable="true">Member ID</th>
                        <th data-field="qty">Qty</th>
                        <th data-field="payment_mode">Payment Mode</th>
                        <th data-field="reward_type">Reward Type</th>
                        <th data-field="status">Status</th>
                        <th data-field="receipt_datetime">Receipt Date Time</th>
                        <th data-field="redeemed_datetime">Redeemed Date Time</th>
                        <th class="text-center" data-field="action" data-searchable="false"> Action</th>
                        <th class="text-center" data-field="remark" data-searchable="false"> Remark</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@can("$permission_prefix-create")
@include('admin.cso-physical.view')
@include('admin.cso-physical.issue')
@endcan
<!-- end modal -->
@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {

        params.data.filter_by = $('#filter_by').val();
        params.data.filter_value = $('#filter_value').val();

        $.get(DataTableUrl + '?' + $.param(params.data))
            .then(function (res) {
                $('.fixed-table-body .fixed-table-loading').removeClass('open');
                params.success(res);
            });
    }

    // Trigger reload on search
    $('#searchBtn').on('click', function () {
        $('#bstable').bootstrapTable('refresh', {pageNumber: 1});
    });

    $('#resetBtn').on('click', function () {

        // reset filter inputs
        $('#filter_by').val('member_id');
        $('#filter_value').val('');

        // reload table without filters
        $('#bstable').bootstrapTable('refresh', { pageNumber: 1 });
    });




    $(document).on('click', '.view-btn', function () {

        let id = $(this).data('id');

        $.ajax({
            url: ModuleBaseUrl + 'view/' + id,
            type: 'GET',
            success: function (res) {

                // populate modal
                $('#v_receipt_no').text(res.receipt_no);
                $('#v_reward_name').text(res.reward_name);
                $('#v_member_id').text(res.member_id);
                $('#v_qty').text(res.qty);
                $('#v_payment_mode').text(res.payment_mode);
                $('#v_reward_type').text(res.reward_type);
                $('#v_status').html(res.status_badge);
                $('#v_receipt_datetime').text(res.receipt_datetime);
                $('#v_redeemed_datetime').text(res.redeemed_datetime ?? '-');
                $('#v_remark').text(res.remark ?? '-');

                $('#viewModal').modal('show');
            }
        });
    });

    $(document).on('click', '.issue-btn', function () {

        let id = $(this).data('id');
        let receipt = $(this).data('receipt');

        $('#issue_purchase_id').val(id);
        $('#issue_receipt_no').text(receipt);
        $('#issueForm')[0].reset();

        $('#issueModal').modal('show');
    });

    $('#issueForm').on('submit', function (e) {
        e.preventDefault();

        // clear old inline error
        $('.remark-error').remove();

        $.ajax({
            url: ModuleBaseUrl + 'issue',
            type: 'POST',
            data: $(this).serialize(),

            success: function (res) {

                $('#issueModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Issued',
                    text: res.message || 'Purchase issued successfully.'
                });

                $('#bstable').bootstrapTable('refresh');
            },

            error: function (xhr) {

                let message = 'Something went wrong. Try again.';

                // Validation / logical error
                if (xhr.status === 422 && xhr.responseJSON) {

                    // custom message
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    // Laravel validation errors
                    if (xhr.responseJSON.errors?.remark) {
                        showRemarkError(xhr.responseJSON.errors.remark[0]);
                        return;
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Issue Failed',
                    text: message
                });
            }
        });
    });

</script>

<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection
