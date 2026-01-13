@extends('layouts.master-layouts')

@section('title') Reward Update Request @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Reward Update Request @endslot
@endcomponent



<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
      
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-id-field="id" data-toggle="table" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="false" data-search="false" data-total-field="count" data-data-field="items" data-page-size="100" data-page-list="[100, 500, 1000, 2000, All]" data-filter-control="true">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-width="70">Sr. No.</th>

                        <th data-field="month" data-sortable="true">
                            Month
                        </th>

                        <th data-field="requester" data-filter-control="input">
                            Requester
                        </th>
                          <th data-field="created_at">
                            Requested Date
                        </th>

                        <th data-field="name" data-filter-control="input">
                            Reward Name
                        </th>                      

                        <th data-field="inventory_type">
                            Inventory Type
                        </th>

                        <th data-field="inventory_qty">
                            Inventory Qty
                        </th>

                        <th data-field="voucher_value">
                            Voucher Value
                        </th>

                        <th data-field="clearing_method">
                            Clearing Method
                        </th>

                        <th data-field="status">
                            Status
                        </th>

                      
                        <th data-field="action" class="text-center" data-searchable="false">
                            Action
                        </th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>


<!-- end modal -->
@endsection

@section('script')
<script>
    const csrf = $('meta[name="csrf-token"]').attr('content');
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }

    $(document).on("click", ".approve_btn", function () {

        let id = $(this).data("id");

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to approve this reward update request?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'No, cancel',
            reverseButtons: true
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ url('admin/reward-update-request/approve') }}",
                    type: "POST",
                    data: {
                        id: id,
                        _token: csrf
                    },
                    success: function (response) {
                        console.log(response,'res');
                        
                        Swal.fire('Approved!', response.message, 'success');
                        $('#bstable').bootstrapTable('refresh');
                    },
                    error: function (xhr) {
                        console.log(xhr.responseJSON);

                        Swal.fire(
                            'Error',
                            xhr.responseJSON?.message || xhr.responseText,
                            'error'
                        );
                    }
                });

            }
        });
    });

    $(document).on("click", ".reject_btn", function () {

        let id = $(this).data("id");

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to reject this reward update request?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reject it!',
            cancelButtonText: 'No, cancel',
            reverseButtons: true
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ url('admin/reward-update-request') }}/" + id + "/reject",
                    type: "POST",
                    data: {
                        _token: csrf
                    },
                    success: function (response) {
                        Swal.fire('Rejected!',response.message, 'success' );
                        $('#bstable').bootstrapTable('refresh');
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong.', 'error' );
                    }
                });
            }
        });
    });

</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection