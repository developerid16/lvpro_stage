<script>
    // Image Preview (Scoped to Current Modal)
    // Image Preview (Fully Scoped)
$(document).on("change", "#logo_input", function () {

    let file = this.files[0];
    let modal = $(this).closest('.modal');

    let preview = modal.find('#logo_preview');
    let clearBtn = modal.find('#clear_logo_preview');

    if (!file) {
        preview.attr("src", "{{ asset('uploads/image/no-image.png') }}");
        clearBtn.hide();
        return;
    }

    let reader = new FileReader();

    reader.onload = function (e) {
        preview.attr("src", e.target.result);
        preview.show();
        clearBtn.show();
    };

    reader.readAsDataURL(file);
});


// Clear Button
$(document).on("click", "#clear_logo_preview", function () {

    let modal = $(this).closest('.modal');

    modal.find('#logo_input').val('');
    modal.find('#logo_preview')
        .attr("src", "{{ asset('uploads/image/no-image.png') }}")
        .show();

    $(this).hide();
});
</script>
<div class="modal fade" id="{{ (isset($data->id)) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="sh_sub_title modal-title">{{ (isset($data->id)) ? 'Edit' : 'Add' }} {{ $title ?? ''}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1" method="POST" action="javascript:void(0)"
                    id="{{ (isset($data->id)) ? 'edit_frm' : 'add_frm' }}" data-id="{{ $data->id ?? ''}}">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif
                    <div class="row">
                        
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Name <span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name" placeholder="Enter Name"
                                    value="{{ $data->name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="logo">Logo <span class="required-hash">*</span></label>

                                <input id="logo_input" 
                                    type="file" 
                                    class="sh_dec form-control" 
                                    name="logo"
                                    accept="image/png, image/jpg, image/jpeg">
                                <div class="d-flex justify-content-between mt-1">
                                    <span class="text-secondary">(100 px X 100 px)</span>
                                    <div class="position-relative d-inline-block">
                                        <img id="logo_preview" src="{{ isset($data) && $data->logo ? imageExists('uploads/image/' . $data->logo) : '' }}" style="max-width:50px;"  alt="Logo Preview" />
                                        <a href="javascript:void(0);" id="clear_logo_preview" class="btn btn-sm btn-danger position-absolute top-0 end-0 translate-middle p-0 img-delete-btn" style="  display:none;"><span class="mdi mdi-close-thick"></span></a>
                                    </div>
                                </div>


                            </div>
                        </div>




                         <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="status">Status <span class="required-hash">*</span></label>
                                
                                <select class="sh_dec form-select" name="status"  style="width: 100%;">
                                    <option class="sh_dec" value="Active" {{ (isset($data->status) && $data->status == 'Active') ?
                                        'selected' : '' }} >Active</option>
                                    <option class="sh_dec" value="Inactive" {{ (isset($data->status) && $data->status ==
                                        'Inactive') ? 'selected' : '' }} >Inactive</option>
                                </select>
                                <div class="sh_dec_s error" id="status_error"></div>
                            </div>
                        </div>
                       
                    </div>
                    <div class="row">
                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" type="reset"
                                onclick="remove_errors()">Reset</button>
                        </div>
                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn btn btn-primary waves-effect waves-light" type="submit">Submit</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

