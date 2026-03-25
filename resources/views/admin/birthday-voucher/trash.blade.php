@extends('layouts.master-layouts')

@section('title') Birthday Voucher Trash @endsection

@section('content')

@component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('li_1_link') {{ url('/') }} @endslot
    @slot('title') Birthday Voucher Trash @endslot
@endcomponent

<div class="card">

    <!-- Header -->
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">

        <!-- Back Button -->
        <a class="btn btn-success" href="{{ url('admin/birthday-voucher') }}">
            <i class="mdi mdi-arrow-left"></i> Back to List
        </a>

    </div>

    <!-- Table -->
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-bordered" id="bstable"
                data-toggle="table"
                data-page-list="[100, 500, 1000, 2000, All]"
                data-page-size="100"
                data-ajax="ajaxRequest"
                data-side-pagination="server"
                data-pagination="true"
                data-search="false"
                data-total-field="count"
                data-data-field="items"
                data-show-columns="false"
                data-show-toggle="false"
                data-show-export="false"
                data-filter-control="true">

                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75"
                            data-width-unit="px" data-searchable="false">Sr. No.</th>
                            <th data-field="month">Voucher Creation</th>
                        <th data-field="name" data-filter-control="input" data-sortable="true" data-escape="true">Name</th>
                            <th data-field="quantity" data-filter-control="input" data-sortable="true">Total</th>
                            <th data-field="issued">Issued</th>
                            <th data-field="claimed">Claimed</th>
                        <th data-field="redeemed">Redeemed</th>
                        <th data-field="image">Image</th>
                        <th data-field="status">Status</th>
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
@section('script')

<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "trash";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function (res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res);
        });
    }

    // ✅ RESTORE WITH SWAL
    $(document).on('click', '.restore_btn', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to restore this record!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {

                $.post(ModuleBaseUrl + id + '/restore', {
                    _token: '{{ csrf_token() }}'
                }, function (res) {

                    Swal.fire(
                        'Restored!',
                        res.message,
                        'success'
                    );

                    $('#bstable').bootstrapTable('refresh');
                });
            }
        });
    });


    // ✅ PERMANENT DELETE WITH SWAL
    $(document).on('click', '.force_delete_btn', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the record!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete permanently!'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: ModuleBaseUrl + id + '/force-delete',
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {

                        Swal.fire(
                            'Deleted!',
                            res.message,
                            'success'
                        );

                        $('#bstable').bootstrapTable('refresh');
                    }
                });
            }
        });
    });

</script>

<script src="{{ URL::asset('build/js/crud.js')}}"></script>

@endsection
