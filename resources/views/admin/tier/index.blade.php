@extends('layouts.master-layouts')

@section('title') Tier Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Tier Management @endslot
@endcomponent

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        @can("$permission_prefix-create")
        <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal">
            <i class="mdi mdi-plus"></i> Add New
        </button>
        @endcan
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-bordered" id="bstable" data-toggle="table"
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
                data-filter-control="true"
                data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no"           data-sortable="false" data-width="60" data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="tier_name"        data-filter-control="input" data-sortable="true">Name</th>
                        <th data-field="code"             data-filter-control="input" data-sortable="true">Code</th>
                        <th data-field="interest_groups"  data-sortable="false">Interest Groups</th>
                        <th data-field="member_types"     data-sortable="false">Member Types</th>
                        <th data-field="status"           data-filter-control="input" data-sortable="true">Status</th>
                        <th data-field="created_at"       data-sortable="false">Created Date & Time</th>
                        <th data-field="updated_at"       data-sortable="false">Last Updated</th>
                        <th class="text-center" data-field="action" data-searchable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@can("$permission_prefix-create")
@include('admin.tier.add-edit-modal')
@endcan

<div id="edit_modal_placeholder"></div>

@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl  = ModuleBaseUrl + "datatable";

    // ── Helper: show the IG/MT required error alert inside whatever modal is open ──
    function showIgMtError(msg) {
        $('#ig_or_mt_error_msg').text(msg);
        $('#ig_or_mt_error').removeClass('d-none');
        setTimeout(function () {
            var el = document.getElementById('ig_or_mt_error');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 150);
    }

    // ── Datatable ──────────────────────────────────────────────────────────────────
    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function (res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res);
        });
    }

    // ── Edit: load modal HTML via AJAX ─────────────────────────────────────────────
    $(document).on('click', '.edit', function () {
        var id = $(this).data('id');
        $.get(ModuleBaseUrl + id + '/edit', function (res) {
            if (res.status === 'success') {
                $('#edit_modal_placeholder').html(res.html);
                new bootstrap.Modal(document.getElementById('EditModal')).show();
            }
        });
    });

    // ── ADD form submit ────────────────────────────────────────────────────────────
    $(document).on('submit', '#add_frm', function (e) {
        e.preventDefault();
        var form = $(this);
        var btn  = form.find('[type="submit"]').prop('disabled', true);

        // ── Client-side: at least one IG or MT required ──
        var igCount = $('.ig-item').length;
        var mtCount = $('.mt-item').length;
        if (igCount === 0 && mtCount === 0) {
            btn.prop('disabled', false);
            showIgMtError('Please add at least one Interest Group or one Member Type.');
            return;
        }

        $.ajax({
            url: ModuleBaseUrl, type: 'POST',
            data: new FormData(form[0]),
            processData: false, contentType: false,
            headers: { 'X-CSRF-Token': "{{ csrf_token() }}" },
            success: function (res) {
                btn.prop('disabled', false);
                if (res.status === 'success') {
                    show_message(res.status, res.message);
                    bootstrap.Modal.getInstance(document.getElementById('AddModal')).hide();
                    $('#bstable').bootstrapTable('refresh');
                    form[0].reset();
                    remove_errors();
                } else {
                    show_message(res.status, res.message);
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false);
                var errors = (xhr.responseJSON && xhr.responseJSON.errors) ? xhr.responseJSON.errors : {};
                if (errors.ig_or_mt) {
                    showIgMtError(errors.ig_or_mt[0]);
                    delete errors.ig_or_mt;
                }
                if (Object.keys(errors).length) show_errors(errors);
            }
        });
    });

    // ── EDIT form submit ───────────────────────────────────────────────────────────
    $(document).on('submit', '#edit_frm', function (e) {
        e.preventDefault();
        var form = $(this);
        var id   = form.data('id');
        var btn  = form.find('[type="submit"]').prop('disabled', true);

        // ── Client-side: at least one IG or MT required ──
        var igCount = $('#EditModal .ig-item').length;
        var mtCount = $('#EditModal .mt-item').length;
        if (igCount === 0 && mtCount === 0) {
            btn.prop('disabled', false);
            showIgMtError('Please add at least one Interest Group or one Member Type.');
            return;
        }

        $.ajax({
            url: ModuleBaseUrl + id, type: 'POST',
            data: new FormData(form[0]),
            processData: false, contentType: false,
            headers: { 'X-CSRF-Token': "{{ csrf_token() }}" },
            success: function (res) {
                btn.prop('disabled', false);
                if (res.status === 'success') {
                    show_message(res.status, res.message);
                    bootstrap.Modal.getInstance(document.getElementById('EditModal')).hide();
                    $('#bstable').bootstrapTable('refresh');
                    remove_errors();
                } else {
                    show_message(res.status, res.message);
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false);
                var errors = (xhr.responseJSON && xhr.responseJSON.errors) ? xhr.responseJSON.errors : {};
                if (errors.ig_or_mt) {
                    showIgMtError(errors.ig_or_mt[0]);
                    delete errors.ig_or_mt;
                }
                if (Object.keys(errors).length) show_errors(errors);
            }
        });
    });

    // ── DELETE ─────────────────────────────────────────────────────────────────────
    $(document).on('click', '.delete_btn', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This tier and all its Interest Groups / Member Types will be deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: ModuleBaseUrl + id, type: 'DELETE',
                    headers: { 'X-CSRF-Token': "{{ csrf_token() }}" },
                    success: function (res) {
                        if (res.status === 'success') {
                            show_message(res.status, res.message);
                            $('#bstable').bootstrapTable('refresh');
                        }
                    }
                });
            }
        });
    });

</script>
<script src="{{ URL::asset('build/js/crud.js') }}"></script>
@endsection