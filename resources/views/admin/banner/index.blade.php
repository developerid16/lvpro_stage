@extends('layouts.master-layouts')

@section('title') Banner @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Banner Management @endslot
@endcomponent



<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        {{--<h4 class="card-title mb-0">Slider </h4>--}}
        @can("$permission_prefix-create")
        <button class="ml_auto sh_btn btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"><i class="mdi mdi-plus"></i>
            Add New</button>
        @endcan
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="false" data-search="false" data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75" data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="header">Header</th>
                        <th data-field="button_text">Button Text</th>
                        <th data-field="link">Link</th>
                        <th data-field="desktop_image">Desktop Image</th>
                        <th data-field="mobile_image">Mobile Image</th>
                        <th class="text-center" data-field="action" data-searchable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@can("$permission_prefix-create")
@include('admin.banner.add-edit-modal')
@endcan
<!-- end modal -->
@endsection

@section('script')

<script>

var ModuleBaseUrl = "{{ $module_base_url }}/";
var DataTableUrl  = ModuleBaseUrl + "datatable";

function ajaxRequest(params){
    $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res){
        $('.fixed-table-body .fixed-table-loading').removeClass('open');
        params.success(res)
    })
}

/* ADD MODAL RESET */
$('#AddModal').on('shown.bs.modal', function () {

    let modal = $(this);

    modal.find('form')[0].reset();

    modal.find('#desktop_logo_preview').hide();
    modal.find('#mobile_logo_preview').hide();

    modal.find('#clear_desktop_logo_preview').hide();
    modal.find('#clear_mobile_logo_preview').hide();

});


/* IMAGE PREVIEW */

function imagePreview(input, preview, clearBtn){

    $(document).on("change", input, function(){

        let file = this.files[0];

        if(!file){
            $(preview).hide();
            $(clearBtn).hide();
            return;
        }

        let reader = new FileReader();

        reader.onload = function(e){

            $(preview).attr("src", e.target.result).show();
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

    if(modal.find('#desktop_logo_preview').data('file')){
        modal.find('#desktop_logo_preview').show();
        modal.find('#clear_desktop_logo_preview').show();
    }

    if(modal.find('#mobile_logo_preview').data('file')){
        modal.find('#mobile_logo_preview').show();
        modal.find('#clear_mobile_logo_preview').show();
    }

});
</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection