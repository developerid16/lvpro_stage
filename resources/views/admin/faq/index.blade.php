@extends('layouts.master-layouts')

@section('title') {{$category->name}} - FAQ @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') {{$category->name}} - FAQ @endslot
@endcomponent





<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        {{--<h4 class="card-title mb-0">FAQ`s</h4>--}}
        @can("$permission_prefix-create")
        <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"><i class="mdi mdi-plus"></i>
            Add New</button>
        @endcan
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75" data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="question" data-filter-control="input" data-sortable="true">Question</th>
                        <th data-field="answer" data-filter-control="input" data-sortable="true">Answer</th>
                        <th data-field="faq_order">Order</th>

                        <th data-field="status" data-filter-control="select" data-sortable="false">Status</th>

                        <th class="text-center" data-field="action" data-searchable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@can("$permission_prefix-create")
@include('admin.faq.add-edit-modal')
@endcan
<!-- end modal -->
@endsection

@section('script')
<script src='{{URL::asset("build/libs/tinymce/tinymce.min.js")}}'></script>
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable" + '?category_id=' + '{{$category->id}}';
    const csrf = $('meta[name="csrf-token"]').attr('content')

    function ajaxRequest(params) {
        $.get(DataTableUrl + '&' + $.param(params.data)).then(function(res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }
    $(document).ready(function() {
        tinymce.init({
            selector: "textarea.elm1",
            height: 300,
            images_upload_url: '{{url("admin/image-upload-editor")}}',
            images_upload_base_path: "{{asset('images')}}/",
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "save table contextmenu directionality emoticons template paste textcolor"
            ],
            toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",
            style_formats: [{
                    title: 'Bold text',
                    inline: 'b'
                },
                {
                    title: 'Red text',
                    inline: 'span',
                    styles: {
                        color: '#ff0000'
                    }
                },
                {
                    title: 'Red header',
                    block: 'h1',
                    styles: {
                        color: '#ff0000'
                    }
                },
                {
                    title: 'Example 1',
                    inline: 'span',
                    classes: 'example1'
                },
                {
                    title: 'Example 2',
                    inline: 'span',
                    classes: 'example2'
                },
                {
                    title: 'Table styles'
                },
                {
                    title: 'Table row 1',
                    selector: 'tr',
                    classes: 'tablerow1'
                }
            ]
        });
        $(document).on("click", ".order_change", function(e) {
            const fd = new FormData();
            var id = $(this).data('id');
            var type = $(this).data('type');
            fd.append('id', id)
            fd.append('type', type)
            $.ajax({
                url: ModuleBaseUrl + 'up-down',
                headers: {
                    'X-CSRF-Token': csrf,
                },
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        show_message(response.status, response.message);
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
    });
</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection