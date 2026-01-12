@extends('layouts.master-layouts')

@section('title') Notification @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{ url('/') }} @endslot
@slot('title') Notification Management @endslot
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
            <table class="table table-bordered" id="bstable"
                data-toggle="table"
                data-page-size="100"
                data-ajax="ajaxRequest"
                data-side-pagination="server"
                data-pagination="true"
                data-total-field="count"
                data-data-field="items">

                <thead>
                    <tr>
                        <th data-field="sr_no">Sr. No.</th>
                        <th data-field="title">Title</th>
                        <th data-field="type">Type</th>
                        <th data-field="date">Date</th>
                        <th data-field="created_at">Created At</th>
                        <th class="text-center" data-field="action">Action</th>
                    </tr>
                </thead>

            </table>
        </div>
    </div>
</div>

@can("$permission_prefix-create")
@include('admin.notification.add-edit-modal')
@endcan

@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function (res) {
            params.success(res)
        })
    }

    $(document).on('change', '#img', function () {
        const file = this.files[0];
        if (!file) return;
        $('#img_preview').attr('src', URL.createObjectURL(file)).show();
    });
    $(document).on('shown.bs.modal', '#AddModal', function () {
        $('#img_preview').attr('src', '').hide();
    });

    document.addEventListener('DOMContentLoaded', function () {
        initFlatpickrDate();
    });
</script>

<script src="{{ URL::asset('build/js/crud.js') }}"></script>
@endsection
