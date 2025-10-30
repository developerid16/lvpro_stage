<div class="modal fade" id="{{ (isset($data->id)) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title sh_sub_title">{{ (isset($data->id)) ? 'Edit' : 'Add' }} {{ $title ?? ''}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1" method="POST" action="javascript:void(0)" id="{{ (isset($data->id)) ? 'edit_frm' : 'add_frm' }}" data-id="{{ $data->id ?? ''}}">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Name<span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name" placeholder="Enter Name" value="{{ $data->name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="code">Category<span class="required-hash">*</span></label>
                                <select name="code" id="code" class="sh_dec form-select">
                                    <option class="sh_dec" {{ (isset($data->code) && $data->code == 'All') ?
                                        'selected' : '' }} value="All">All</option>
                                    <option class="sh_dec" {{ (isset($data->code) && $data->code == 'Airport Operations') ?
                                        'selected' : '' }} value="Airport Operations">Airport Operations</option>
                                    <option class="sh_dec" {{ (isset($data->code) && $data->code == 'Airlines') ?
                                        'selected' : '' }} value="Airlines">Airlines</option>
                                    <option class="sh_dec" {{ (isset($data->code) && $data->code == 'Retail') ?
                                        'selected' : '' }} value="Retail">Retail</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="status">Status<span class="required-hash">*</span></label>
                                <select class="sh_dec form-select" name="status">
                                    <option class="sh_dec" value="Active" {{ (isset($data->status) && $data->status == 'Active') ?
                                        'selected' : '' }}>Active</option>
                                    <option class="sh_dec" value="Disabled" {{ (isset($data->status) && $data->status ==
                                        'Disabled') ? 'selected' : '' }}>Disabled</option>
                                </select>
                                <div class="sh_dec_s error" id="status_error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" type="reset" onclick="remove_errors()">Reset</button>
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