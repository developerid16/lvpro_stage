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
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="url">Code <span class="required-hash">*</span></label>
                                <input id="code" maxlength="50" type="text" class="sh_dec form-control" name="code"
                                    placeholder="Enter code" value="{{ $data->code ?? '' }}" required>
                                <span class="sh_dec_s text-muted">Max 50 character allows</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="url">Slider Order <span
                                        class="required-hash">*</span></label>
                                <input id="slider_order" maxlength="50" type="text" class="sh_dec form-control"
                                    name="slider_order" placeholder="Enter slider order"
                                    value="{{ $data->slider_order ?? '' }}" required min="0">
                                <span class="sh_dec_s text-muted">Higher will be display first</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label for="url">Name <span class="required-hash">*</span></label>
                                <input id="name" maxlength="255" type="text" class="sh_dec form-control" name="name"
                                    placeholder="Enter name" value="{{ $data->name ?? '' }}" required>
                                <span class="sh_dec_s text-muted">Max 255 character allows</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="image">Image<span class="required-hash">*</span> </label>
                                <input id="image" type="file" accept="image/*" class="sh_dec form-control" name="image"
                                    value="{{ $data->image ?? '' }}">
                                <span class="text-muted sh_dec_s">237px x 178px</span>
                            </div>
                            @if (isset($data->image))
                            <a href='{{asset("images/$data->image")}}' data-lightbox='set-10'>
                            <img src='{{asset("images/$data->image")}}' alt="" srcset="" height="50" width="50">
                            </a>
                            @endif
                        </div>
                        <hr class="dashed">
                        <div class="col-12 col-md-6">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="start_date">Start Date <span
                                        class="required-hash">*</span></label>
                                <input id="start_date" type="date" @if (!isset($data->start_date))
                                min="{{date('Y-m-d')}}"
                                @endif class="form-control"
                                name="start_date"
                                value="{{ isset($data->start_date) ? $data->start_date->format('Y-m-d') : '' }}"
                                required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="end_date">End Date</label>
                                <input id="end_date" type="date" @if (!isset($data->start_date))
                                min="{{date('Y-m-d')}}"
                                @endif class="form-control"
                                name="end_date"
                                value="{{ isset($data->end_date) ? $data->end_date->format('Y-m-d') : '' }}" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="slider_type"> Slider Type <span
                                        class="required-hash">*</span></label>
                                <select class="sh_dec form-select slider_type " name="slider_type">
                                    <option class="sh_dec" value="in-app" {{ (isset($data->slider_type) &&
                                        $data->slider_type == 'in-app') ?
                                        'selected' : '' }} >In-App</option>
                                    <option class="sh_dec" value="external-url" {{ (isset($data->slider_type) &&
                                        $data->slider_type ==
                                        'external-url') ? 'selected' : '' }} >External URL</option>
                                </select>
                                <div class="error" id="slider_type_error"></div>
                            </div>
                        </div>
                        <hr class="dashed">
                        <div class="col-12 col-md-12 slider-type-amount" @if(isset($data->slider_type) &&
                            $data->slider_type == 'external-url')
                            style="display:none" @endif>
                            <div class="mb-3">
                                <label class="sh_dec" for="amount">Description <span
                                        class="required-hash">*</span></label>
                                <textarea class="elm1" name="description"> {{$data->description ?? ''}} </textarea>
                                <div class="error sh_dec" id="description_error"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 slider-type-product" @if(!isset($data->slider_type))
                            style="display:none" @endif
                            @if(isset($data->slider_type) && $data->slider_type == 'in-app') style="display:none"
                            @endif>
                            <div class="mb-3">
                                <label class="sh_dec" for="url">URL <span class="required-hash">*</span></label>
                                <input id="url" type="url" class="sh_dec form-control" name="url"
                                    placeholder="Enter url" value="{{ $data->url ?? '' }}">
                                <div class="error" id="url_error"></div>

                            </div>
                        </div>


                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="status"> Status <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select w-select-100" name="status" id="status"  >
                                    <option class="sh_dec" value="Active" {{ (isset($data->status) && $data->status ==
                                        'Active') ?
                                        'selected' : '' }} >Active</option>
                                    <option class="sh_dec" value="Disabled" {{ (isset($data->status) && $data->status ==
                                        'Disabled') ? 'selected' : '' }} >Disabled</option>
                                </select>
                                <div class="error" id="status_error"></div>
                            </div>
                        </div>




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