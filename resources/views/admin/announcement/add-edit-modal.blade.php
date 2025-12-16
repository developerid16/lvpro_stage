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
                    <div class="row">

                        {{-- TITLE --}}
                        <div class="col-12 col-md-8">
                            <div class="mb-3">
                                <label class="sh_dec"> Title <span class="required-hash">*</span></label>
                                <input type="text"  class="form-control" name="title"  placeholder="Enter title" value="{{ $data->title ?? '' }}">
                            </div>
                        </div>

                        {{-- ORDER --}}
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec"> Order <span class="required-hash">*</span></label>
                                <input type="number" min="0" class="form-control" name="display_order" placeholder="Enter order" value="{{ $data->display_order ?? '' }}" >
                                <span class="text-muted">Higher value shows first</span>
                            </div>
                        </div>
                        <hr class="dashed">
                        {{-- START DATE --}}
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec">Publish Start Date & Time <span class="required-hash">*</span></label>
                                <input type="datetime-local" class="form-control" name="start_date" value="{{ isset($data->start_date) ? $data->start_date->format('Y-m-d\TH:i') : '' }}">
                            </div>
                        </div>

                        {{-- END DATE --}}
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec"> Publish End Date & Time <span class="required-hash">*</span></label>
                                <input type="datetime-local" class="form-control" name="end_date" value="{{ isset($data->end_date) ? $data->end_date->format('Y-m-d\TH:i') : '' }}">
                            </div>
                        </div>
                        <hr class="dashed">
                        {{-- MESSAGE --}}
                        
                        <div class="col-12 col-md-12 slider-type-amount"  >
                            <div class="mb-3">
                                <label class="sh_dec" for="amount">Message <span class="required-hash">*</span></label>
                                <textarea class="elm1" name="description"> {{$data->message ?? ''}} </textarea>
                                {{-- <span class="sh_dec_s text-muted">Max 180 character allows</span> --}}
                                <div class="error sh_dec" id="description_error"></div>
                            </div>
                        </div>

                        {{-- ACTIONS --}}
                        <div class="col-6 mt-3 d-grid">
                            <button type="reset" class="btn btn-outline-danger"onclick="remove_errors()"> Reset</button>
                        </div>

                        <div class="col-6 mt-3 d-grid">
                            <button type="submit" class="btn btn-primary"> Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>