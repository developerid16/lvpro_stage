<div class="modal fade" id="{{ isset($data->id) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="sh_sub_title modal-title">
                    {{ isset($data->id) ? 'Edit' : 'Add' }} {{ $title ?? '' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form method="POST" id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}" data-id="{{ $data->id ?? '' }}">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif

                    <div class="row">
                        <!-- NAME -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec">Name <span class="required-hash">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       name="name"
                                       value="{{ $data->name ?? '' }}"
                                       required>
                                <div class="error" id="name_error"></div>
                            </div>
                        </div>

                        <!-- STATUS -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec">Status <span class="required-hash">*</span></label>
                                <select class="form-select" name="status" required>
                                    <option value="Active" {{ isset($data) && $data->status == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ isset($data) && $data->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <div class="error" id="status_error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 d-grid">
                            <button type="reset" class="btn btn-outline-danger" onclick="remove_errors()">Reset</button>
                        </div>
                        <div class="col-6 d-grid">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
