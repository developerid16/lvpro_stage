@extends('layouts.master-layouts')

@section('title') Role Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot

@slot('title') Role Management @endslot
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
            <table class="table sh_table" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75" data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="name" data-filter-control="input" data-sortable="true">Name</th>
                        <th data-field="permissions" data-filter-control="input" data-sortable="false">Permissions</th>
                        <th class="text-center" data-field="action" data-searchable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@if(Auth::user()->can("$permission_prefix-create"))
@include('admin.roles.add-edit-modal')
@endif
<!-- end modal -->
@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
            var elems = document.querySelectorAll(".js-switch");
            for (var i = 0; i < elems.length; i++) {
                var switchery = new Switchery(elems[i], {
                    size: 'small',
                    color: '#39DA8A',
                    secondaryColor: '#FF5B5C'
                });
            }
        })
    }

    $(document).ready(function() {
        $("#AddModal .select2").select2({
            dropdownParent: $("#AddModal")
        });

        $(document).on("submit", "#add_frm", function(e) {
            e.preventDefault();
            var form_data = new FormData($(this)[0]);
            $.ajax({
                url: ModuleBaseUrl.slice(0, -1),
                headers: {
                    'X-CSRF-Token': "{{ csrf_token() }}",
                },
                type: "POST",
                data: form_data,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        show_message(response.status, response.message);
                        $("#AddModal").modal('hide');
                        $("#add_frm").trigger("reset");
                        refresh_datatable("#bstable");
                        $("#add_frm .select2").val('').trigger('change');

                    } else {
                        show_message(response.status, response.message);
                    }
                    remove_errors()
                },
                error: function(response) {
                    show_errors(response.responseJSON.errors);
                }
            });
        });

        $(document).on("click", ".edit", function(e) {
            $("#EditModal").modal('hide').remove();
            var id = $(this).data('id');
            $.ajax({
                url: ModuleBaseUrl + id + "/edit",
                type: 'GET',
                data: '',
                headers: {
                    'X-CSRF-Token': "{{ csrf_token() }}"
                },
                success: function(response) {
                    $("body").append(response.html);
                    $("#EditModal .select2").select2({
                        dropdownParent: $("#EditModal")
                    });
                    $("#EditModal .select-multiple").chosen({
                        // dropdownParent: $("#EditModal .modal-content")
                    });
                    $("#EditModal").modal('show');
                }
            });
        });


        $(document).on("submit", "#edit_frm", function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var form_data = new FormData($(this)[0]);
            $.ajax({
                url: ModuleBaseUrl + id,
                headers: {
                    'X-CSRF-Token': "{{ csrf_token() }}",
                },
                type: "POST",
                data: form_data,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        show_message(response.status, response.message);
                        $("#EditModal").modal('hide');
                        refresh_datatable("#bstable");
                    } else {
                        show_message(response.status, response.message);
                    }
                },
                error: function(response) {
                    show_errors(response.responseJSON.errors, "#edit_frm");
                }
            });
        });

        $(document).on("click", ".delete_btn", function(e) {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "you want to delete this record.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: ModuleBaseUrl + id,
                        type: 'DELETE',
                        data: '',
                        headers: {
                            'X-CSRF-Token': "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            show_message(response.status, response.message);
                            refresh_datatable("#bstable");
                        }
                    });
                }
            });
        })
    });
</script>
@endsection