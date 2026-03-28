@extends('layouts.master-layouts')

@section('title') Dashboard Popup @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Dashboard Popup Management @endslot
@endcomponent



<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        {{--<h4 class="card-title mb-0">Slider </h4>--}}
        
        <div>
            
         </div>
        <div class="d-flex gap-2">
            @if(hasActivePermission("$permission_prefix-create"))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"><i class="mdi mdi-plus"></i>
                Add New</button>
            @endcan

            <!-- @can('super admin')
                <a class="btn btn-danger"
                    href="{{ url('admin/departments/trash') }}">
                    <i class="mdi mdi-trash"></i> View Trash
                </a>
            @endcan -->
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="false" data-search="false" data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75" data-width-unit="px" data-searchable="false">Sr. No.</th>
                         <th data-field="name" data-filter-control="input" data-sortable="true">Name</th>
                        <th data-field="date">Date</th>
                         <th data-field="desktop_image">Desktop Image</th>
                        <th data-field="mobile_image">Mobile Image</th>
                        <th data-field="frequency" data-filter-control="select" data-sortable="false" data-filter-data="var:frequencyOptions">Frequency</th>
                        <th class="text-center" data-field="action" data-searchable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@if(hasActivePermission("$permission_prefix-create"))
@include('admin.dashboardpopup.add-edit-modal')
@endcan
<!-- end modal -->
@endsection

@section('script')
<script src='{{URL::asset("build/js/Sortable.min.js.js")}}'></script>

<script src='{{URL::asset("build/libs/tinymce/tinymce.min.js")}}'></script>
<script>
    var frequencyOptions = {
        'once-a-day': 'once-a-day',
        'always': 'always'
    };


    function initFlatpickr() {
            
        bindStartEndFlatpickr(
            'input[name="start_date"]',
            'input[name="end_date"]'
        );
       
    }
    document.addEventListener('DOMContentLoaded', initFlatpickr);
    
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }

    $(function() {
        $(document).on("change", ".slider_type", function() {
            $('.slider-type-amount').toggle()
            $('.slider-type-product').toggle()

        });

        tinymce.init({
            selector: "textarea.elm1",
            height: 200,
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
            setup: function (editor) {
                editor.on('keydown', function (e) {
                    var content = editor.getContent({ format: 'text' }); // Get plain text content
                if (content.length >= 180 && e.keyCode !== 8 && e.keyCode !== 46) { // Allow backspace and delete
                    e.preventDefault();
                }
                });
            },
            images_upload_url: '{{url("admin/image-upload-editor")}}',
            images_upload_base_path: "{{asset('images')}}/",
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "save table contextmenu directionality emoticons template textcolor"
            ],
            toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons ",
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
    });

    function initRowDragDrop() {

        let tbody = document.querySelector('.fixed-table-body table tbody');
        if (!tbody) return;

        if (tbody.sortable) {
            tbody.sortable.destroy();
        }

        tbody.sortable = new Sortable(tbody, {
            animation: 150,
            ghostClass: 'dragging-row',
            draggable: 'tr',

            onEnd: function () {

                let orderData = [];

                $('.fixed-table-body table tbody tr').each(function () {
                    let id = $(this).attr('data-row-id'); // ✅ MATCHING KEY

                    if (id) {
                        orderData.push({ id: id });
                    }
                });

                console.log('orderData:', orderData); // 🔥 WILL NOT BE EMPTY

                if (!orderData.length) {
                    console.error('No row IDs found');
                    return;
                }

                $.ajax({
                    url: "{{ url('admin/dashboardpopup/reorder') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        order: orderData
                    },
                    success: function () {
                        $('#bstable').bootstrapTable('refresh');
                    }
                });
            }
        });
    }


    $('#bstable').on('load-success.bs.table', function (e, data) {

        $('.fixed-table-body table tbody tr').each(function (index) {
            let rowData = data.items[index];

            if (rowData && rowData.id) {
                $(this).attr('data-row-id', rowData.id); // ✅ SET VALUE
            }
        });

        initRowDragDrop();
    });

    $('#AddModal').on('shown.bs.modal', function () {
         let modal = $(this);
        let form  = modal.find('form')[0];

        if (form) {
            form.reset(); // reset full form
        }

        modal.find('#desktop_logo_preview').hide();
        modal.find('#mobile_logo_preview').hide();

        modal.find('#clear_desktop_logo_preview').hide();
        modal.find('#clear_mobile_logo_preview').hide();
        // clear text + number inputs
        modal.find('input[type="text"], input[type="number"], textarea').val('');

        // reset selects
        modal.find('select').prop('selectedIndex', 0).trigger('change');

        // clear file inputs
        modal.find('input[type="file"]').val('');

    });    


/* IMAGE PREVIEW */

function imagePreview(input, preview, clearBtn){

    $(document).on("change", input, function () {

        let file = this.files[0];

        if (!file) return;

        let reader = new FileReader();

        reader.onload = function(e){
            $(preview)
                .attr("src", e.target.result) // 👈 FORCE update
                .removeAttr('data-file')      // 👈 remove old flag
                .show();

            $(clearBtn).show();
        };

        reader.readAsDataURL(file);
    });
}

imagePreview(
    "#desktop_logo_input",
    "#desktop_logo_preview",
    "#clear_desktop_logo_preview"
);

imagePreview(
    "#mobile_logo_input",
    "#mobile_logo_preview",
    "#clear_mobile_logo_preview"
);


/* CLEAR IMAGE */

$(document).on("click","#clear_desktop_logo_preview",function(){

    let modal = $(this).closest('.modal');

    modal.find('#desktop_logo_input').val('');
    modal.find('#desktop_logo_preview').hide();

    $(this).hide();

});

$(document).on("click","#clear_mobile_logo_preview",function(){

    let modal = $(this).closest('.modal');

    modal.find('#mobile_logo_input').val('');
    modal.find('#mobile_logo_preview').hide();

    $(this).hide();

});


/* EDIT MODAL IMAGE SHOW */

$(document).on('shown.bs.modal','#EditModal',function(){

    let modal = $(this);

    let desktopSrc = modal.find('#desktop_logo_preview').attr('src');
    let mobileSrc  = modal.find('#mobile_logo_preview').attr('src');

    if(desktopSrc){
        modal.find('#desktop_logo_preview').show();
        modal.find('#clear_desktop_logo_preview').show();
    }

    if(mobileSrc){
        modal.find('#mobile_logo_preview').show();
        modal.find('#clear_mobile_logo_preview').show();
    }

});
</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection