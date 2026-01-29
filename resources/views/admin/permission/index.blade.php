@extends('layouts.master-layouts')

@section('title') Permission @endsection

@section('content')

@component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('li_1_link') {{ url('/') }} @endslot
    @slot('title') Permission Management @endslot
@endcomponent

<div class="card">
     <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        {{--<h4 class="card-title mb-0">Role Management</h4>--}}
        @if(Auth::user()->can("$permission_prefix-create"))
        <button class="btn btn-primary sh_btn ml_auto" data-bs-toggle="modal" data-bs-target="#AddModal"><i class="mdi mdi-plus"></i>
            Add New</button>
        @endif
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-bordered"
                   id="bstable"
                   data-toggle="table"
                   data-page-list="[100,500,1000,2000,All]"
                   data-page-size="100"
                   data-ajax="ajaxRequest"
                   data-side-pagination="server"
                   data-pagination="true"
                   data-search="false"
                   data-total-field="count"
                   data-data-field="items">

                <thead>
                    <tr>
                        <th data-field="sr_no" data-width="75">Sr. No.</th>
                        <th data-field="name" data-sortable="true">Name</th>
                        <th data-field="status" data-sortable="true">Status</th>
                        <th data-field="action" class="text-center">Action</th>
                    </tr>
                </thead>

            </table>
        </div>
    </div>
</div>

{{-- ADD MODAL --}}
@can("$permission_prefix-create")
    @include('admin.permission.add-edit-modal')
@endcan

@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data), function (res) {
            params.success(res);
        });
    }
</script>

<script src="{{ asset('build/js/crud.js') }}"></script>
@endsection
