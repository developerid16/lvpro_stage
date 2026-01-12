<script>
     $(document).on('shown.bs.modal', '#EditModal', function () {
        initFlatpickr(this);        
    });

    function initFlatpickr(modal) {
        bindStartEndFlatpickrEdit(
            modal,
            'input[name="start_date"]',
            'input[name="end_date"]'
        );      
    }

   
</script>
<div class="modal fade" id="{{ isset($data->id) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="sh_sub_title modal-title">
                    {{ isset($data->id) ? 'Edit' : 'Add' }} Participating Merchant Outlet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form method="POST" action="javascript:void(0)" 
                      id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}"
                      data-id="{{ $data->id ?? '' }}">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif

                    <input type="hidden" name="participating_merchant_id" value="{{ $participating_merchant_id }}">

                    <div class="row">

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label>Name <span class="required-hash">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $data->name ?? '' }}" placeholder="Enter Name">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label>Code <span class="required-hash">*</span></label>
                                <input type="text" name="code" class="form-control" value="{{ $data->code ?? '' }}" placeholder="Enter Code">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label>Start Date <span class="required-hash">*</span></label>
                                <input type="text" name="start_date" id="start_date"
                                    class="form-control"
                                    value="{{ isset($data->start_date) ? $data->start_date : '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label>End Date <span class="required-hash">*</span></label>
                                <input type="text" name="end_date" id="end_date"
                                    class="form-control"
                                    value="{{ isset($data->end_date) ? $data->end_date : '' }}">
                            </div>
                        </div>


                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label>Participating Merchant <span class="required-hash">*</span></label>
                                <select name="participating_merchant_id" class="form-select">
                                    @foreach($merchants as $merchant)
                                        <option value="{{ $merchant->id }}"
                                            {{ isset($data) && $data->participating_merchant_id == $merchant->id ? 'selected' : '' }}>
                                            {{ $merchant->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label>Club Location <span class="required-hash">*</span></label>
                                <select name="club_location_id" class="form-select">
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}"
                                            {{ isset($data) && $data->club_location_id == $loc->id ? 'selected' : '' }}>
                                            {{ $loc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label>Status <span class="required-hash">*</span></label>
                                <select name="status" class="form-select">
                                    <option value="Active" {{ isset($data) && $data->status == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ isset($data) && $data->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-6 mt-3 d-grid">
                            <button class="btn btn-outline-danger" type="reset">Reset</button>
                        </div>
                        <div class="col-6 mt-3 d-grid">
                            <button class="btn btn-primary" type="submit">Submit</button>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>