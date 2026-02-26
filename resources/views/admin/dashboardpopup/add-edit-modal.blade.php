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
<div class="modal fade" id="{{ (isset($data->id)) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title sh_sub_title">{{ (isset($data->id)) ? 'Edit' : 'Add' }} {{ $title ?? ''}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1" method="POST" action="javascript:void(0)" enctype="multipart/form-data"
                    id="{{ (isset($data->id)) ? 'edit_frm' : 'add_frm' }}" data-id="{{ $data->id ?? ''}}">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label for="url">Header <span class="required-hash">*</span></label>
                                <input id="name" maxlength="25" type="text" class="sh_dec form-control" name="name" placeholder="Enter Header" value="{{ $data->name ?? '' }}">
                                <span class="sh_dec_s text-muted">Max 25 character allows</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label class="sh_dec" for="url">Button Text <span class="required-hash">*</span></label>
                                <input id="button" maxlength="10" type="text" class="sh_dec form-control" name="button"  placeholder="Enter button text" value="{{ $data->button ?? '' }}">
                                <span class="sh_dec_s text-muted">Max 10 character allows</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label class="sh_dec" for="url">Order <span class="required-hash">*</span></label>
                                <input id="order" maxlength="50" type="number" class="sh_dec form-control"  name="order" placeholder="Enter order" value="{{ $data->order ?? 0 }}" min="0" max="50">
                                <span class="sh_dec_s text-muted">Higher will be display first</span>
                            </div>
                        </div>                        
                        <hr class="dashed">
                       <div class="col-12 col-md-6">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec">Publish Start Date & Time <span class="required-hash">*</span></label>
                                <input
                                    type="text"
                                    class="form-control js-datetime-start"
                                    name="start_date"
                                    value="{{ isset($data->start_date) ? $data->start_date->format('Y-m-d H:i:s') : '' }}"
                                    placeholder="YYYY-MM-DD HH:mm:ss"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec">Publish End Date & Time <span class="required-hash">*</span></label>
                                <input
                                    type="text"
                                    class="form-control js-datetime-end"
                                    name="end_date"
                                    value="{{ isset($data->end_date) ? $data->end_date->format('Y-m-d H:i:s') : '' }}"
                                    placeholder="YYYY-MM-DD HH:mm:ss"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="frequency"> Popup Type <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select frequency " name="popup_type">
                                    <option value="">Select Popup Type</option>
                                    <option class="sh_dec" value="once-a-day" {{ (isset($data->frequency) &&  $data->frequency == 'once-a-day') ? 'selected' : '' }} >Once a day</option>
                                    <option class="sh_dec" value="always" {{ (isset($data->frequency) &&  $data->frequency == 'always') ? 'selected' : '' }} >Always when user opens the app </option>
                                </select>
                                <div class="error" id="frequency_error"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec">Upload Image <span class="required-hash">*</span></label>
                                <input 
                                    type="file" 
                                    name="image" 
                                    class="form-control"
                                    accept="image/png,image/jpeg,image/jpg"
                                >

                                @if(isset($data->image) && $data->image)
                                    <div class="mt-2">
                                        <img src="{{ imageExists($data->image)}}"  style="max-width:100px; border-radius:6px;">
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec">URL Link<span class="required-hash">*</span></label>
                                <input 
                                    type="url" 
                                    name="url" 
                                    class="form-control"
                                    placeholder=""
                                    value="{{ $data->url ?? '' }}"
                                >
                            </div>
                        </div>


                        {{-- <div class="col-12 col-md-12 slider-type-amount">
                            <div class="mb-3">
                                <label class="sh_dec" for="amount">Description <span class="required-hash">*</span></label>
                                <textarea class="elm1" name="description"> {{$data->description ?? ''}} </textarea>
                                <div class="error sh_dec" id="description_error"></div>
                            </div>
                        </div> --}}
                        <hr class="dashed">
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