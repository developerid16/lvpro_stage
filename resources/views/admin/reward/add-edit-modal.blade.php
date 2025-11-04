<div class="modal fade" id="{{ isset($data->id) ? 'EditModal' : 'AddModal' }}" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="sh_sub_title modal-title">{{ isset($data->id) ? 'Edit' : 'Add' }} {{ $title ?? '' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1" method="POST" action="javascript:void(0)"
                    id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}" data-id="{{ $data->id ?? '' }}">
                    @csrf
                    @if (isset($type))
                        <input type="hidden" name="parent_type" value="{{ $type }}">
                    @endif
                    @if (isset($data->id))
                        @method('PATCH')
                    @endif
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="code">Code <span class="required-hash">*</span></label>
                                <input id="code" type="text" class="sh_dec form-control" name="code"
                                    placeholder="Enter code" value="{{ $data->code ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Name <span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name"
                                    placeholder="Enter name" value="{{ $data->name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="sku">SKU </label>
                                <input id="sku" type="text" class="sh_dec form-control" name="sku"
                                    placeholder="Enter SKU" value="{{ $data->sku ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="brand_name">Brand Name </label>
                                <input id="brand_name" type="text" class="sh_dec form-control" name="brand_name"
                                    placeholder="Enter Brand  Name" value="{{ $data->brand_name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Company Name <span
                                        class="required-hash">*</span></label>
                                <input id="company_name" type="text" class="sh_dec form-control" name="company_name"
                                    placeholder="Enter company name" value="{{ $data->company_name ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="description">Description <span
                                        class="required-hash">*</span></label>
                                <textarea name="description" maxlength="500" class="sh_dec form-control" id="description" cols="15" rows="3">{{ $data->description ?? '' }}</textarea>

                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="term_of_use">Terms & Conditions <span
                                        class="required-hash">*</span></label>
                                <textarea name="term_of_use" maxlength="5000" class="sh_dec form-control" id="term_of_use" cols="15"
                                    rows="3">{{ $data->term_of_use ?? '' }}</textarea>

                            </div>
                        </div>
                        {{-- <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="how_to_use">How To Use <span
                                        class="required-hash">*</span></label>
                                <textarea name="how_to_use" maxlength="5000" class="sh_dec form-control" id="how_to_use"
                                    cols="15" rows="3">{{ $data->how_to_use ?? '' }}</textarea>

                    </div>
            </div> --}}
                        <hr class="dashed">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec" for="no_of_keys"> Amount <span
                                        class="required-hash">*</span></label>
                                <input id="no_of_keys" type="number" min="0" class="sh_dec form-control"
                                    name="no_of_keys" placeholder="Enter Amount Of Reward"
                                    value="{{ $data->no_of_keys ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec" for="quantity">Quantity <span
                                        class="required-hash">*</span></label>
                                <input id="quantity" type="text" class="sh_dec form-control" name="quantity"
                                    placeholder="Enter Quantity" value="{{ $data->quantity ?? '' }}">
                                <span class="text-muted sh_dec_s">Add 0 for unlimited stock</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                @php $labels = [""] @endphp
                                @if (isset($data) && $data->labels)
                                    @foreach ($data->labels as $item)
                                        @php
                                            $labels[] = $item;

                                        @endphp
                                    @endforeach
                                @endif
                                <label class="sh_dec" for="labels">Labels </label>
                                <input id="labels" type="text"
                                    class="sh_dec form-control select2-tags"name="labels" placeholder="Enter labels"
                                    value=" @php echo implode(",",$labels) @endphp "
                                    data-value=" @php echo implode(",",$labels) @endphp ">
                                {{-- <select name="labels[]" class="select2-tags form-control sh_dec" id="labels" multiple>
                        @if (isset($data->labels))
                        @foreach ($data->labels as $item)
                        <option class="sh_dec" value="{{$item}}" selected>{{$item}}</option>

                        @endforeach
                        @endif
                    </select> --}}
                                <div class="sh_dec_s error" id="labels_error"></div>

                            </div>
                        </div>


                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="reward_type"> Reward Type <span
                                        class="required-hash">*</span></label>
                                <select class="sh_dec form-select reward_type " name="reward_type">
                                    <option class="sh_dec" value="0"
                                        {{ isset($data->reward_type) && $data->reward_type == '0' ? 'selected' : '' }}>
                                        Cash</option>
                                    <option class="sh_dec" value="1"
                                        {{ isset($data->reward_type) && $data->reward_type == '1' ? 'selected' : '' }}>
                                        Product</option>
                                </select>
                                <div class="sh_dec_s error" id="reward_type_error"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 reward-type-amount"
                            @if (isset($data->reward_type) && $data->reward_type == 1) style="display:none" @endif>
                            <div class="mb-3">
                                <label class="sh_dec" for="amount">Amount <span
                                        class="required-hash">*</span></label>
                                <input id="amount" type="number" class="sh_dec form-control" name="amount"
                                    placeholder="Enter Amount" value="{{ $data->amount ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 reward-type-product"
                            @if (!isset($data->reward_type)) style="display:none" @endif
                            @if (isset($data->reward_type) && $data->reward_type == 0) style="display:none" @endif>
                            <div class="mb-3">
                                <label class="sh_dec" for="amount">Product Name <span
                                        class="required-hash">*</span></label>
                                <input id="product_name" type="text" class="sh_dec form-control"
                                    name="product_name" placeholder="Enter Product Name"
                                    value="{{ $data->product_name ?? '' }}">
                            </div>
                        </div>

                        <hr class="dashed">

                        <div class="col-12 col-md-3">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="start_date">Start Date <span
                                        class="required-hash">*</span></label>
                                <input id="start_date" type="date"
                                    @if (!isset($data->start_date)) min="{{ date('Y-m-d') }}" @endif
                                    class="form-control" name="start_date"
                                    value="{{ isset($data->start_date) ? $data->start_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="end_date">End Date</label>
                                <input id="end_date" type="date"
                                    @if (!isset($data->start_date)) min="{{ date('Y-m-d') }}" @endif
                                    class="form-control" name="end_date"
                                    value="{{ isset($data->end_date) ? $data->end_date->format('Y-m-d') : '' }}">
                                <span class="text-muted sh_dec_s">Leave Blank for no expiration</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-5">
                            <div class="mb-3">
                                <label class="sh_dec" for="expiry_day">Purchases Expiry </label>
                                <div class="input-group mb-2 mr-sm-2">
                                    <div class="input-group-prepend">
                                        <div class="sh_dec input-group-text">Days</div>
                                    </div>
                                    <input id="expiry_day" type="text" class="sh_dec form-control"
                                        name="expiry_day" placeholder="Enter Days"
                                        value="{{ $data->expiry_day ?? '' }}">
                                </div>
                                <span class="sh_dec_s text-muted">Add 0 for to use default expiration date of reward.
                                </span>
                                <br>
                                <span class="sh_dec_s text-muted">if End Date is not provided then this filed is
                                    required
                                </span>


                            </div>
                        </div>
                        <div class="row @if ($type === 'campaign-voucher') d-none @endif">

                            <div class="col-12 col-md-6">
                                <div class="mb-3 sh_dec">
                                    <label class="sh_dec" for="start_time">Start Time </label>
                                    <input id="start_time" type="time" @if (!isset($data->start_time))  @endif
                                        class="form-control" name="start_time"
                                        value="{{ isset($data->start_time) ? $data->start_time : '' }}">
                                    <div class="text-muted clear-time" style="cursor:pointer ">Click Here to remove
                                        Start
                                        and End Time</div>
                                    <div class="sh_dec_s error" id="start_error"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3 sh_dec">
                                    <label class="sh_dec" for="end_time">End Time </label>
                                    <input id="end_time" type="time" @if (!isset($data->end_time))  @endif
                                        class="form-control" name="end_time"
                                        value="{{ isset($data->end_time) ? $data->end_time : '' }}">
                                    <div class="sh_dec_s error" id="end_time_error"></div>
                                </div>

                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group form-check mb-3 pt-3">
                                    <input type="checkbox" name="countdown" value="1" class="form-check-input"
                                        id="countdown"
                                        {{ isset($data->countdown) && $data->countdown == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="countdown">Check for display Countdown on App
                                        side </label>
                                    <span class="sh_dec_s text-muted">Only applicable if End Date is provided.</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="sh_dec" for="status">Days</label>
                                    <select class="sh_dec form-select  select-multiple" name="days[]" id="days"
                                        multiple style="width: 100%">
                                        <option class="sh_dec" value="0"
                                            {{ isset($data->days) && in_array(0, $data->days) ? 'selected' : '' }}>
                                            Sunday</option>
                                        <option class="sh_dec" value="1"
                                            {{ isset($data->days) && in_array(1, $data->days) ? 'selected' : '' }}>
                                            Monday</option>
                                        <option class="sh_dec" value="2"
                                            {{ isset($data->days) && in_array(2, $data->days) ? 'selected' : '' }}>
                                            Tuesday</option>
                                        <option class="sh_dec" value="3"
                                            {{ isset($data->days) && in_array(3, $data->days) ? 'selected' : '' }}>
                                            Wednesday</option>
                                        <option class="sh_dec" value="4"
                                            {{ isset($data->days) && in_array(4, $data->days) ? 'selected' : '' }}>
                                            Thursday</option>
                                        <option class="sh_dec" value="5"
                                            {{ isset($data->days) && in_array(5, $data->days) ? 'selected' : '' }}>
                                            Friday</option>
                                        <option class="sh_dec" value="6"
                                            {{ isset($data->days) && in_array(6, $data->days) ? 'selected' : '' }}>
                                            Saturday</option>

                                    </select>
                                    <div class="sh_dec_s error" id="days_error"></div>
                                    <span class="sh_dec_s text-muted">Leave for display all days. </span>
                                </div>
                            </div>
                        </div>


                        <hr class="dashed">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="image_1">Image for Rewards Catalog <span
                                        class="required-hash">*</span> </label>
                                <input id="image_1" type="file" accept="image/*" class="sh_dec form-control"
                                    name="image_1" value="{{ $data->image_1 ?? '' }}">
                                <span class="text-muted sh_dec_s">1000px x 750px</span>
                            </div>
                            @if (isset($data->image_1))
                                <a href='{{ asset("images/$data->image_1") }}' data-lightbox='set-10'>
                                    <img src='{{ asset("images/$data->image_1") }}' alt="" srcset=""
                                        height="50" width="50">
                                </a>
                            @endif
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="image_2">Image for Reward Detailed Page </label>
                                <input id="image_2" type="file" accept="image/*" class="sh_dec form-control"
                                    name="image_2" value="{{ $data->image_2 ?? '' }}">
                                <span class="sh_dec_s text-muted">1000px x 750px</span>
                            </div>
                            @if (isset($data->image_2))
                                <a href='{{ asset("images/$data->image_2") }}' data-lightbox='set-10'>
                                    <img src='{{ asset("images/$data->image_2") }}' alt="" srcset=""
                                        height="50" width="50">
                                </a>
                            @endif
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec" for="status"> Status <span
                                        class="required-hash">*</span></label>
                                <select class="sh_dec form-select form-control " name="status">
                                    <option class="sh_dec" value="Active"
                                        {{ isset($data->status) && $data->status == 'Active' ? 'selected' : '' }}>
                                        Active</option>
                                    <option class="sh_dec" value="Disabled"
                                        {{ isset($data->status) && $data->status == 'Disabled' ? 'selected' : '' }}>
                                        Disabled</option>
                                </select>
                                <div class="sh_dec_s error" id="status_error"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 @if ($type === 'campaign-voucher') d-none @endif">
                            <div class="mb-3">
                                <label class="sh_dec" for="status"> To be featured in Homepage <span
                                        class="required-hash">*</span></label>
                                <select class="sh_dec form-select " name="is_featured" id="is_featured">
                                    <option class="sh_dec" value="0"
                                        {{ isset($data->is_featured) && $data->is_featured == '0' ? 'selected' : '' }}>
                                        No</option>
                                    <option class="sh_dec" value="1"
                                        {{ isset($data->is_featured) && $data->is_featured == '1' ? 'selected' : '' }}>
                                        Yes</option>
                                </select>
                                <div class="sh_dec_s error" id="is_featured_error"></div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 is-featured-div"
                            @if (isset($data->is_featured) && $data->is_featured == '1') style="display:block" @else  style="display:none" @endif>
                            <div class="mb-3">
                                <label class="sh_dec" for="image_3">Image for featured Homepage </label>
                                <input id="image_3" type="file" accept="image/*" class="sh_dec form-control"
                                    name="image_3" value="{{ $data->image_3 ?? '' }}">
                                <span class="sh_dec_s text-muted">200px x 115px</span>
                            </div>
                            @if (isset($data->image_3))
                                <a href='{{ asset("images/$data->image_3") }}' data-lightbox='set-10'>
                                    <img src='{{ asset("images/$data->image_3") }}' alt="" srcset=""
                                        height="50" width="50">
                                </a>
                            @endif
                        </div>


                        {{-- <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec" for="status"> Type <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select col-12" name="type" id="type">
                                    <option class="sh_dec" value="0" {{ (isset($data->type) && $data->type == '0') ?
                                        'selected' : '' }} >Shilla Rewards Catalog - Redemption Voucher</option>
            <option class="sh_dec" value="1" {{ (isset($data->type) && $data->type ==
                                        '1') ? 'selected' : '' }}>Shilla Campaign - Membership Ewallet Voucher:
            </option>
            </select>
            <div class="sh_dec_s error" id="type_error"></div>
        </div>
    </div> --}}


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
