<div class="modal fade" id="{{ (isset($data->id)) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title sh_sub_title">{{ (isset($data->id)) ? 'Edit' : 'Add' }} {{ $title ?? ''}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1" method="POST" action="javascript:void(0)"
                    id="{{ (isset($data->id)) ? 'edit_frm' : 'add_frm' }}" data-id="{{ $data->id ?? ''}}">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif
                    <input type="hidden" name="company_id" value="{{ $company_data->id ?? '' }}">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Name<span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name"
                                    placeholder="Enter Name" value="{{ $data->name ?? '' }}">
                                <div class="sh_dec_s error" id="name_error"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="code">Code<span class="required-hash">*</span></label>
                                <input id="code" type="text" class="sh_dec form-control" name="code"
                                    placeholder="Enter code" value="{{ $data->code ?? '' }}">
                                <div class="sh_dec_s error" id="code_error"></div>
                                <span class="text-info">This code is use to redeem vouchers</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="address">Address<span class="required-hash">*</span></label>
                                <input id="address" type="text" class="sh_dec form-control" name="address"
                                    placeholder="Enter Address" value="{{ $data->address ?? '' }}">
                                <div class="sh_dec_s error" id="address_error"></div>
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
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="start_date">Start Date<span class="required-hash"></span></label>
                                <input id="start_date" type="date" class="sh_dec form-control" name="start_date"
                                     value="{{ $data->start_date ?? '' }}">
                                <div class="sh_dec_s error" id="start_date_error"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="end_date">End Date<span class="required-hash"></span></label>
                                <input id="end_date" type="date" class="sh_dec form-control" name="end_date"
                                     value="{{ $data->end_date ?? '' }}">
                                <div class="sh_dec_s error" id="end_date_error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" type="reset"
                                onclick="remove_errors()">Reset</button>
                        </div>
                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn btn btn-primary waves-effect waves-light"
                                type="submit">Submit</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>