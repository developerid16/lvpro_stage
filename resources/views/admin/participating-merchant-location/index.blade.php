@extends('layouts.master-layouts')

@section('title') Participating Merchant Location @endsection

@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Participating Merchant Location @endslot
@endcomponent


<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">

        @can("$permission_prefix-create")
        <button class="sh_btn ml_auto btn btn-primary add_btn" 
            data-url="{{ url('admin/participating-merchant/'.$participating_merchant_id.'/location/create') }}">
            <i class="mdi mdi-plus"></i> Add New
        </button>
        @endcan

    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">

            {{-- Required for datatable --}}
            <input type="hidden" id="participating_merchant_id" value="{{ $participating_merchant_id }}">

            <table class="table table-bordered"
                id="bstable"
                data-toggle="table"
                data-pagination="true"
                data-side-pagination="server"
                data-ajax="ajaxRequest"
                data-page-size="100"
                data-total-field="count"
                data-data-field="items">

                <thead>
                    <tr>
                        <th data-field="sr_no" data-width="70px">Sr. No.</th>
                        <th data-field="name">Name</th>
                        <th data-field="code">Code</th>
                        <th data-field="start_date">Start Date</th>
                        <th data-field="end_date">End Date</th>
                        <th data-field="club_location">Club Location</th>
                        <th data-field="status">Status</th>
                        <th data-field="action" class="text-center">Action</th>
                    </tr>
                </thead>

            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
@include('admin.participating-merchant-location.add-edit-modal')

@endsection


@section('script')

<script>
    let participating_merchant_id = "{{ $participating_merchant_id }}";

    // Correct Resource Base URL (for store/update/delete)
    let ModuleBaseUrl = "{{ url('admin/participating-merchant-location') }}/";

    // Correct Datatable URL
    let DataTableUrl = "{{ route('admin.participating-merchant-location.datatable') }}";

    function ajaxRequest(params) {

        params.data.participating_merchant_id = participating_merchant_id;

        // FIXED: Call datatable route, not index()
        $.get(DataTableUrl + "?" + $.param(params.data))
            .then(function (res) {
                $('.fixed-table-body .fixed-table-loading').removeClass('open');
                params.success(res);
            });
    }

    // Add modal loader
    $(".add_btn").on("click", function () {

        let url = $(this).data("url");

        $.get(url, function (res) {
            if (res.status === "success") {
                $("#AddEditModalContainer").html(res.html);
                $("#AddModal").modal("show");
            }
        });

    });

</script>

<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection
