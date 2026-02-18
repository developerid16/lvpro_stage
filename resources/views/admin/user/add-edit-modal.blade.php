<div class="modal fade" id="{{ (isset($data->id)) ? 'EditModal' : 'AddModal' }}" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg new-user-edit">
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
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Name<span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name" placeholder="Enter Name" value="{{ $data->name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Email<span class="required-hash">*</span></label>
                                <input id="email" type="email" class="sh_dec form-control" name="email" placeholder="Enter Email" value="{{ $data->email ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="password">Password<span class="required-hash">*</span></label>
                                <input id="password" type="password" class="sh_dec form-control" name="password" placeholder="Enter password" value="{{ $data->password ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Phone No<span class="required-hash">*</span></label>
                                <input id="phone" type="tel" class="sh_dec form-control" name="phone" placeholder="Enter Phone number" value="{{ $data->phone ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="status">Select Status<span class="required-hash">*</span></label>

                                <select class="sh_dec form-select status" name="status">
                                    <option value="Active" {{ (isset($data->status) && $data->status == 'Active') ?
                                        'selected' : '' }}>Active</option>
                                    <option value="Disabled" {{ (isset($data->status) && $data->status ==
                                        'Disabled') ? 'selected' : '' }}>Disabled</option>
                                    <option value="Lockout" {{ (isset($data->status) && $data->status ==
                                        'Lockout') ? 'selected' : '' }}>Lockout</option>
                                </select>
                                <div class="error" id="status_error"></div>
                            </div>
                        </div>

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec new-role-drop" for="role">Select Role<span class="required-hash">*</span></label>
                                <select class="form-select select-multiple sh_dec" multiple name="role[]">

                                    @foreach($role as $value)
                                    <option class="sh_dec" value="{{$value->name}}" {{ (isset($data->roles) && in_array($value->name,
                                        $data->roles->pluck('name')->toArray())) ? 'selected' : '' }}>{{ $value->name
                                        }}</option>
                                    @endforeach
                                </select>
                                <div class="error" id="role_error"></div>
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