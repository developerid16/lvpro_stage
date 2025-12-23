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

                                <input id="" 
                                    type="file" 
                                    class="sh_dec form-control" 
                                    name="logo"
                                    accept="image/png, image/jpg, image/jpeg">

                                <img id="logo_preview"
                                    src="{{ isset($data) && $data->logo ? asset($data->logo) : '' }}"
                                    width="50"
                                    height="50"
                                    style="border:1px solid #ccc; margin-top:8px; {{ isset($data) && $data->logo ? '' : 'display:none;' }}">
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

