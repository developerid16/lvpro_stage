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
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec" for="tier_id">Tier Id<span class="required-hash">*</span></label>
                                <input id="tier_id" type="text" class="sh_dec form-control" name="tier_id" placeholder="Enter Tier Id"
                                    value="{{ $data->tier_id ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec" for="alias_name">Alias Name<span class="required-hash">*</span></label>
                                <input id="alias_name" type="text" class="sh_dec form-control" name="alias_name" placeholder="Enter Alias Name"
                                    value="{{ $data->alias_name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec" for="tier_name">Safra's Tier Name<span class="required-hash">*</span></label>
                                <input id="tier_name" type="text" class="sh_dec form-control" name="tier_name" placeholder="Enter Safra's Tier Name"
                                    value="{{ $data->tier_name ?? '' }}">
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