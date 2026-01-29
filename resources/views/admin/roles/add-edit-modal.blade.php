<div class="modal fade" id="{{ (isset($data->id)) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
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
                         <div class="col-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="department">Select Department <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select" name="department" placeholder="Select Department">
                                    <option value="">Select Department</option>
                                    @foreach($department as $value)
                                    <option class="sh_dec" value="{{$value->id}}" {{ (isset($data->department) && $data->department == $value->id) ? 'selected' : '' }}>{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                <div class="error" id="department_error"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Name <span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name" autocomplete="name" placeholder="Enter Name" value="{{$data->name ?? ''}}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="status">Paid/Free</label>

                                <select class="sh_dec form-select" name="status">
                                    <option value="">Select</option>
                                    <option value="Paid" {{ (isset($data->status) && $data->status == 'Paid') ? 'selected' : '' }}>Paid</option>
                                    <option value="Free" {{ (isset($data->status) && $data->status == 'Free') ? 'selected' : '' }}>Free</option>
                                    <option value="Paid/Free" {{ (isset($data->status) && $data->status == 'Paid/Free') ? 'selected' : '' }}>Paid/Free</option>
                                </select>

                                <div class="sh_dec_s error" id="status_error"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="permission">Select Access <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select select-multiple " name="permission[]" multiple id="permission" placeholder="Select Access">
                                    @foreach($permission as $value)
                                    <option class="sh_dec" value="{{$value->id}}" {{(isset($rolePermissions) && in_array($value->
                                        id,$rolePermissions)) ? 'selected' : ''}}>{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                <div class="error" id="permission_error"></div>
                            </div>
                        </div>
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