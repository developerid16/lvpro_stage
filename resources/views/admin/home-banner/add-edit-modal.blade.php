<div class="modal fade" id="{{ isset($data->id) ? 'EditModal' : 'AddModal' }}"
     tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    {{ isset($data->id) ? 'Edit' : 'Add' }} Home Banner
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form method="POST" action="javascript:void(0)"
                      id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}"
                      data-id="{{ $data->id ?? '' }}" enctype="multipart/form-data">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif

                    <div class="row">

                        <!-- TITLE -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control"
                                   value="{{ $data->title ?? '' }}">
                        </div>

                        <!-- POSITION -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position</label>
                            <input type="number" name="position" class="form-control"
                                   value="{{ $data->position ?? 0 }}">
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ $data->description ?? '' }}</textarea>
                        </div>

                        <!-- IMAGE -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Banner Image <span class="text-danger">*</span></label>
                            <input type="file" name="image" class="form-control"
                                   accept="image/png,image/jpg,image/jpeg,image/webp">

                            @if(isset($data->image))
                                <img src="{{ asset('uploads/banner/'.$data->image) }}"
                                     width="120" class="mt-2 border">
                            @endif
                        </div>

                        <!-- ACTION TYPE -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Action Type</label>
                            <select name="action_type" class="form-select">
                                <option value="0" {{ ($data->action_type ?? 0) == 0 ? 'selected' : '' }}>None</option>
                                <option value="1" {{ ($data->action_type ?? 0) == 1 ? 'selected' : '' }}>External Link</option>
                                <option value="2" {{ ($data->action_type ?? 0) == 2 ? 'selected' : '' }}>Merchant</option>
                                <option value="3" {{ ($data->action_type ?? 0) == 3 ? 'selected' : '' }}>Reward</option>
                            </select>
                        </div>

                        <!-- ACTION VALUE -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Action Value</label>
                            <input type="text" name="action_value" class="form-control"
                                   value="{{ $data->action_value ?? '' }}"
                                   placeholder="URL / Merchant ID / Reward ID">
                        </div>

                        <!-- START DATE -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Start At</label>
                            <input type="datetime-local" name="start_at" class="form-control"
                                   value="{{ isset($data->start_at) ? \Carbon\Carbon::parse($data->start_at)->format('Y-m-d\TH:i') : '' }}">
                        </div>

                        <!-- END DATE -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">End At</label>
                            <input type="datetime-local" name="end_at" class="form-control"
                                   value="{{ isset($data->end_at) ? \Carbon\Carbon::parse($data->end_at)->format('Y-m-d\TH:i') : '' }}">
                        </div>

                        <!-- STATUS -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select">
                                <option value="Active" {{ ($data->status ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ ($data->status ?? '') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-6 d-grid">
                            <button type="reset" class="btn btn-outline-danger">Reset</button>
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
