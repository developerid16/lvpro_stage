@extends('layouts.master-layouts')

@section('title') Merchant @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Merchant Management @endslot
@endcomponent



<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        {{--<h4 class="card-title mb-0">Aircrew Company</h4>--}}
        @can("$permission_prefix-create")
        <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"><i class="mdi mdi-plus"></i>
            Add New</button>
        @endcan
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-bordered" id="bstable" data-toggle="table"
                data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest"
                data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count"
                data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false"
                data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75"  data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="name" data-filter-control="input" data-sortable="true">Name</th>
                        <th data-field="logo" data-filter-control="input" data-sortable="true">Logo</th>
                        <th data-field="status" data-filter-control="input" data-sortable="true">Status</th>
                        <th data-field="created_at" data-filter-control="input" data-sortable="false">Created Date & Time</th>
                        <th data-field="updated_at" data-filter-control="input" data-sortable="false">Last Updated Date & Time</th>
                        <th class="text-center" data-field="action" data-searchable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@can("$permission_prefix-create")
@include('admin.merchant.add-edit-modal')
@endcan
<!-- end modal -->
@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl+"datatable";
    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function (res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    } 

    
    $('#AddModal').on('shown.bs.modal', function () {

        let preview = $(this).find('#logo_preview');
        let clearBtn = $(this).find('#clear_logo_preview');

        // Always show default no-image in Add
        preview
            .attr('src', "{{ asset('uploads/image/no-image.png') }}")
            .show();

        clearBtn.hide();
    });
    function imagePreview(inputSelector, previewSelector) {
        $(document).on("change", inputSelector, function () {
            let file = this.files[0];
            let preview = $(previewSelector);

            const clearBtn = $('#clear_logo_preview');
            if (!file) {
                preview.attr("src", "").hide();
                clearBtn.hide();
                return;
            }

            let reader = new FileReader();
            reader.onload = function (e) {
                preview.attr("src", e.target.result).show();
            };

            clearBtn.show();
            reader.readAsDataURL(file);
        });
    }
    $(document).on('click', '#clear_logo_preview', function () {

        const modal = $(this).closest('.modal'); // auto-detect modal
        const input = modal.find('#logo_input')[0];
        const preview = modal.find('#logo_preview');

        if (input) {
            input.value = '';   // reset file input
        }

        preview.attr('src', '').hide();
        $(this).hide();
    });
    imagePreview("#logo_input", "#logo_preview");
</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection