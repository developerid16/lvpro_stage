<div class="modal fade" id="{{ (isset($data->id)) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ (isset($data->id)) ? 'Edit' : 'Add' }} {{ $title ?? ''}}</h5>
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
                                <label for="image">Image<span class="required-hash">*</span> </label>
                                <input id="image" type="file" accept="image/*" class="form-control" name="image"
                                    value="{{ $data->image ?? '' }}">
                                <span class="text-muted">237px x 178px</span>
                            </div>
                            @if (isset($data->image))

                            <a href='{{asset("images/$data->image")}}' data-lightbox='set-10'> <img
                                    src='{{asset("images/$data->image")}}' alt="" srcset="" height="50" width="50"> </a>
                            @endif
                        </div>


                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label for="status"> Status <span class="required-hash">*</span></label>
                                <select class="form-select select2" name="status" id="status">
                                    <option value="Active" {{ (isset($data->status) && $data->status == 'Active') ?
                                        'selected' : '' }} >Active</option>
                                    <option value="Disabled" {{ (isset($data->status) && $data->status ==
                                        'Disabled') ? 'selected' : '' }} >Disabled</option>
                                </select>
                                <div class="error" id="status_error"></div>
                            </div>
                        </div>

                        <div class="col-6 mt-3 d-grid">
                            <button class="btn btn-outline-danger waves-effect waves-light" type="reset"
                                onclick="remove_errors()">Reset</button>
                        </div>
                        <div class="col-6 mt-3 d-grid">
                            <button class="btn btn-primary waves-effect waves-light" type="submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>