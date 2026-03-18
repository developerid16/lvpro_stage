<script>
    // Image Preview (Scoped to Current Modal)   
    $(document).on("change", "#EditModal #desktop_logo_input", function () {

        let file = this.files[0];
        if (!file) return;

        let reader = new FileReader();

        reader.onload = function (e) {
            $("#EditModal #desktop_logo_preview")
                .attr("src", e.target.result)
                .show()
                .removeAttr('data-file'); // remove old

            $("#EditModal #clear_desktop_logo_preview").show();
        };

        reader.readAsDataURL(file);
    });
    $(document).on("change", "#EditModal #mobile_logo_input", function () {

        let file = this.files[0];
        if (!file) return;

        let reader = new FileReader();

        reader.onload = function (e) {
            $("#EditModal #mobile_logo_preview")
                .attr("src", e.target.result)
                .show()
                .removeAttr('data-file');

            $("#EditModal #clear_mobile_logo_preview").show();
        };

        reader.readAsDataURL(file);
    });
</script>
<div class="modal fade" id="{{ (isset($data->id)) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false">
    
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    {{ isset($data->id) ? 'Edit' : 'Add' }} {{ $title ?? '' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form method="POST" action="javascript:void(0)" enctype="multipart/form-data"
                    id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}"
                    data-id="{{ $data->id ?? '' }}">

                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif

                    <div class="row">

                        <!-- Header -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Header <span class="required-hash">*</span></label>
                                <input type="text"  name="header"  maxlength="100" class="form-control" value="{{ $data->header ?? '' }}"  placeholder="Enter header">
                            </div>
                        </div>

                        <!-- Button Text -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Button Text</label>
                                <input type="text" name="button_text" maxlength="50"  class="form-control" value="{{ $data->button_text ?? '' }}" placeholder="Enter button text">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label>Banner Link</label>
                                <input type="url" name="link" class="form-control" placeholder="Enter banner link" value="{{ $data->link ?? '' }}">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label>Description</label>
                                <textarea name="description"  class="form-control" rows="3"  placeholder="Enter description">{{ $data->description ?? '' }}</textarea>
                            </div>
                        </div>

                       <!-- Desktop Banner -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Upload Image (Desktop)</label>

                                <input type="file" name="desktop_image" id="desktop_logo_input"   class="form-control" accept=".jpg,.jpeg,.png">

                                <div class="d-flex justify-content-between mt-2">

                                    <span class="text-secondary">
                                        (Size: 1712 × 240 | Format: PNG, JPG, JPEG)
                                    </span>

                                    <div class="position-relative">

                                        <img id="desktop_logo_preview"
                                            data-file="{{ $data->desktop_image ?? '' }}" src="{{ !empty($data->desktop_image) ? imageExists('uploads/image/'.$data->desktop_image) : imageExists('uploads/image/no-image.png') }}" style="max-width:50px;">
                                       
                                        <a href="javascript:void(0);" id="clear_desktop_logo_preview" class="btn btn-sm btn-danger position-absolute top-0 end-0 translate-middle p-0 img-delete-btn" style="  display:none;"><span class="mdi mdi-close-thick"></span></a>


                                    </div>

                                </div>
                            </div>
                        </div>


                        <!-- Mobile Banner -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Upload Image (Mobile)</label>

                                <input type="file" name="mobile_image" id="mobile_logo_input" class="form-control" accept=".jpg,.jpeg,.png">                                  

                                <div class="d-flex justify-content-between mt-2">

                                    <span class="text-secondary">
                                        (Size: 768 × 100 | Format: JPG, PNG)
                                    </span>                                   

                                    <div class="position-relative">
                                        <img id="mobile_logo_preview" data-file="{{ $data->mobile_image ?? '' }}" src="{{ !empty($data->mobile_image) ? imageExists('uploads/image/'.$data->mobile_image) : imageExists('uploads/image/no-image.png') }}" style="max-width:50px;">

                                        <a href="javascript:void(0);" id="clear_mobile_logo_preview" class="btn btn-sm btn-danger position-absolute top-0 end-0 translate-middle p-0 img-delete-btn" style="  display:none;"><span class="mdi mdi-close-thick"></span></a>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="col-6 d-grid">
                            <button type="reset" class="btn btn-outline-danger">
                                Reset
                            </button>
                        </div>

                        <div class="col-6 d-grid">
                            <button type="submit" class="btn btn-primary">
                                Submit
                            </button>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

