{{-- resources/views/admin/reward/index.blade.php --}}
@extends('layouts.master-layouts')

@section('title')
    Transaction History
@endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('li_1_link') {{ url('/') }} @endslot
    @slot('title') Transaction History @endslot
@endcomponent

<div class="card">
  
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered"
                id="bstable"
                data-toggle="table"
                data-side-pagination="server"
                data-pagination="true"
                data-search="false"
                data-page-size="100"
                data-ajax="ajaxRequest"
                data-total-field="count"
                data-data-field="items">

                <thead>
                <tr>
                    <th data-field="sr_no" data-width="70">#</th>
                    <th data-field="transaction_id">Transaction ID</th>
                    <th data-field="user">Member Id</th>
                    <th data-field="receipt_no">Receipt No</th>
                    <th data-field="payment_mode">Payment Mode</th>
                    <th data-field="request_amount">Request Amount</th>
                    <th data-field="authorized_amount">Authorized Amount</th>
                    <th data-field="status">Status</th>
                    <th data-field="created_at">Created On</th>
                    <th data-field="action">Action</th>
                </tr>
                </thead>
            </table>

        </div>
    </div>
</div>


{{-- ADD / EDIT --}}
@can("$permission_prefix-create")
@endcan
@endsection


@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data))
            .then(res => params.success(res));
    }


    $(document).on("click", ".view_vouchers", function() {

        var receipt_no = $(this).data('receipt');

        $.ajax({
            url: ModuleBaseUrl +  receipt_no + '/vouchers',
            type: 'GET',
            success: function(response) {
                if (response.status) {
                    $("body").append(response.html);
                    $("#VoucherDetailModal").modal('show');
                }
            }
        });
    });

</script>
@endsection
