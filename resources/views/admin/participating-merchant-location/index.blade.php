@extends('layouts.master-layouts')

@section('title') Participating Merchant outlet : {{ $participating_merchant ? $participating_merchant->name : ''}} @endsection

@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Participating Merchant outlet : {{ $participating_merchant ? $participating_merchant->name : '' }} @endslot
@endcomponent


<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">

        <button class="sh_btn btn btn-primary add_btn" data-bs-toggle="modal" data-bs-target="#UploadFileModal">
            <i class="mdi mdi-upload"></i> Upload File
        </button>
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
                        <th data-field="code">Redemption Code</th>
                        <th data-field="start_date">Lease Start Date</th>
                        <th data-field="end_date">Lease End Date</th>
                        <th data-field="club_location">Club Location</th>
                        <th data-field="status">Status</th>
                        <th data-field="action" class="text-center">Action</th>
                    </tr>
                </thead>

            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="UploadFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="uploadForm" enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Upload File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-1">
                        <label>Upload File</label>
                        <input type="file" name="file" class="form-control" required>
                        <span class="text-danger validation-error" data-field="file"></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">
                            Allowed: CSV, XLSX (Max: 2MB)
                        </small>

                        <a href="{{ url('admin/participating-merchant-location/download-sample') }}" 
                        class="btn btn-sm btn-outline-primary">
                            <i class="mdi mdi-download"></i> Download Sample
                        </a>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>

            </form>

        </div>
    </div>
</div>

{{-- Modal --}}
@include('admin.participating-merchant-location.add-edit-modal')

@endsection


@section('script')

<script>
     function initFlatpickr() {
            
            bindStartEndFlatpickr(
                'input[name="start_date"]',
                'input[name="end_date"]'
            );
        }
        document.addEventListener('DOMContentLoaded', initFlatpickr);

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

    $(document).on('show.bs.modal', '#UploadFileModal', function () {

        // ✅ Reset form
        $('#uploadForm')[0].reset();

        // ✅ Clear file input (important)
        $('#uploadForm input[type="file"]').val('');

        // ✅ Clear validation errors
        $('.validation-error').html('');

    });

    $(document).on('submit', '#uploadForm', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append('participating_merchant_id', participating_merchant_id);


        $.ajax({
            url: "{{ url('admin/participating-merchant-location/upload-file') }}",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status === 'success') {

                    // ✅ Reset form
                    $('#uploadForm')[0].reset();

                    // ✅ Clear validation messages
                    $('.validation-error').html('');

                    // ✅ Close modal
                    $('#UploadFileModal').modal('hide');

                    // ✅ Refresh table
                    refresh_datatable("#bstable");

                    // ✅ Show success message
                    show_message('success', res.message);
                }
            },
            error: function (xhr) {
                let errors = xhr.responseJSON.errors;

                $('.validation-error').text('');

                $.each(errors, function (key, value) {
                    $('[data-field="' + key + '"]').text(value[0]);
                });
            }
        });
    });

</script>

<script>
$(document).on("shown.bs.modal", "#AddModal, #EditModal", function () {
     $('.validation-error').hide();

    let modal = this;

    let start = modal.querySelector("#start_date");
    let end   = modal.querySelector("#end_date");

    if (!start || !end) return;

    // Remove old listeners
    if (start._handler) {
        start.removeEventListener("input", start._handler);
        start.removeEventListener("change", start._handler);
        delete start._handler;
    }

    // On modal open → end is readonly
    end.readOnly = true;

    let isEdit = start.value && start.value.trim() !== "";

    if (isEdit) {
        end.min = start.value;
        if (end.value && end.value < start.value) {
            end.value = start.value;
        }
    } else {
        end.value = "";
        end.removeAttribute("min");
    }

    // When start date changes
    function handle() {

        if (!start.value) {
            end.readOnly = true;
            end.value = "";
            end.removeAttribute("min");
            return;
        }

        // Allow user manual editing now
        end.readOnly = false;

        end.min = start.value;

        // Auto-fix invalid end date
        if (end.value && end.value < start.value) {
            end.value = start.value;
        }
    }

    // Attach handler
    start._handler = handle;
    start.addEventListener("change", handle);
    start.addEventListener("input", handle);
});
</script>


<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection
