@extends('layouts.master-layouts')

@section('title') Sales Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Sales Management @endslot
@endcomponent

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        {{--<h4 class="card-title mb-0">Sales </h4>--}}
        @can("$permission_prefix-create")
        <div class="ml_auto">

            <button class="sh_btn btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"><i
                    class="mdi mdi-plus"></i>
                Add New</button>
            <button class="sh_btn btn btn-primary" data-bs-toggle="modal" data-bs-target="#OldAddModal"><i
                    class="mdi mdi-plus"></i>
                Old Sales</button>
            <a class="sh_btn btn btn-info" href="{{url('admin/download-democsv')}}" target="_blank"><i
                    class="mdi mdi-file"></i>Demo CSV</a>
        </div>
        @endcan
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-toggle="table"
                data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest"
                data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count"
                data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false"
                data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75"
                            data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="file_name">File Name</th>
                        <th data-field="user_name">Uploaded By</th>
                        <th data-field="created_at">Uploaded Date</th>

                        <th data-field="status" data-filter-control="select" data-sortable="false">Status</th>

                        <th data-field="action">Action</th>

                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@can("$permission_prefix-create")
@include('admin.sales.add-edit-modal')
@include('admin.sales.old-add-edit-modal')
@endcan
<!-- end modal -->
@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    console.log('ModuleBaseUrl',ModuleBaseUrl);
    var DataTableUrl = ModuleBaseUrl+"datatable";
    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function (res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }
  $(document).ready(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content')
    $(document).on("submit", "#oldadd_frm", function (e) {
        e.preventDefault();
        var form_data = new FormData($(this)[0]);
        $.ajax({
            url: ModuleBaseUrl + 'old-sales',
            headers: {
                'X-CSRF-Token': csrf,
            },
            type: "POST",
            data: form_data,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 'success') {
                    show_message(response.status, response.message);
                    $("#OldAddModal").modal('hide');
                    $("#oldadd_frm").trigger("reset");
                    refresh_datatable("#bstable");
                    $("#oldadd_frm .select2").val('').trigger('change');
                } else {
                    show_message(response.status, response.message);
                }
                remove_errors();
            },
            error: function (response) {
                show_errors(response.responseJSON.errors);
            }
        });
    });
    });
</script>

<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection
