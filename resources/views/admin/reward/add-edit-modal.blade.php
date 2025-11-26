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
                                <label class="sh_dec" for="name">Name <span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name"
                                    placeholder="Enter name" value="{{ $data->name ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="reward_type"> Reward Type <span
                                        class="required-hash">*</span></label>
                                <select class="sh_dec form-select reward_type " name="reward_type">
                                    <option class="sh_dec" value="">Select Reward Type</option>
                                    <option class="sh_dec" value="0"
                                        {{ isset($data->reward_type) && $data->reward_type == '0' ? 'selected' : '' }}>
                                        Digital Voucher</option>
                                    <option class="sh_dec" value="1"
                                        {{ isset($data->reward_type) && $data->reward_type == '1' ? 'selected' : '' }}>
                                        Physical Voucher</option>
                                </select>
                                <div class="sh_dec_s error" id="reward_type_error"></div>
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="start_date">Publish Start Date <span
                                        class="required-hash">*</span></label>
                                <input id="start_date" type="date"
                                    @if (!isset($data->start_date)) min="{{ date('Y-m-d') }}" @endif
                                    class="form-control" name="start_date"
                                    value="{{ isset($data->start_date) ? $data->start_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="start_time">Start Time </label>
                                <input id="start_time" type="time" @if (!isset($data->start_time))  @endif
                                    class="form-control" name="start_time"
                                    value="{{ isset($data->start_time) ? $data->start_time : '' }}">

                                <div class="sh_dec_s error" id="start_error"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="end_date">Publish End Date</label>
                                <input id="end_date" type="date"
                                    @if (!isset($data->start_date)) min="{{ date('Y-m-d') }}" @endif
                                    class="form-control" name="end_date"
                                    value="{{ isset($data->end_date) ? $data->end_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="end_time">End Time </label>
                                <input id="end_time" type="time" @if (!isset($data->end_time))  @endif
                                    class="form-control" name="end_time"
                                    value="{{ isset($data->end_time) ? $data->end_time : '' }}">
                                <div class="sh_dec_s error" id="end_time_error"></div>
                            </div>

                        </div>

                        <div class="col-12 col-md-3">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="start_date">Sales Start Date <span
                                        class="required-hash">*</span></label>
                                <input id="start_date" type="date"
                                    @if (!isset($data->start_date)) min="{{ date('Y-m-d') }}" @endif
                                    class="form-control" name="start_date"
                                    value="{{ isset($data->start_date) ? $data->start_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="start_time">Start Time </label>
                                <input id="start_time" type="time" @if (!isset($data->start_time))  @endif
                                    class="form-control" name="start_time"
                                    value="{{ isset($data->start_time) ? $data->start_time : '' }}">

                                <div class="sh_dec_s error" id="start_error"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="end_date">Sales End Date</label>
                                <input id="end_date" type="date"
                                    @if (!isset($data->start_date)) min="{{ date('Y-m-d') }}" @endif
                                    class="form-control" name="end_date"
                                    value="{{ isset($data->end_date) ? $data->end_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec" for="end_time">End Time </label>
                                <input id="end_time" type="time" @if (!isset($data->end_time))  @endif
                                    class="form-control" name="end_time"
                                    value="{{ isset($data->end_time) ? $data->end_time : '' }}">
                                <div class="sh_dec_s error" id="end_time_error"></div>
                            </div>

                        </div>

                        <div class="col-12 col-md-6 ">
                            <div class="mb-3">
                                <label class="sh_dec" for="amount">Usual Price <span
                                        class="required-hash">*</span></label>
                                <input id="amount" type="number" class="sh_dec form-control" name="amount"
                                    placeholder="Enter Usual Price" value="{{ $data->amount ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="quantity">Maximum Order <span
                                        class="required-hash">*</span></label>
                                <input id="quantity" type="text" class="sh_dec form-control" name="quantity"
                                    placeholder="Enter Maximum Order" value="{{ $data->quantity ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-12 ">
                            <div class="row">
                                <div class="col-12">

                                    <label class="sh_dec" for="amount"> <b> Tier Rates </b></label>
                                </div>

                                @foreach ($tiers as $tier)
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="sh_dec" for="tier_{{ $tier->id }}">{{ $tier->name }}
                                                Price <span class="required-hash">*</span></label>
                                            <input id="tier_{{ $tier->id }}" type="number"
                                                class="sh_dec form-control" name="tier_{{ $tier->id }}"
                                                placeholder="Enter {{ $tier->name }} Price"
                                                value="{{ isset($data->tier_rates[$tier->id]) ? $data->tier_rates[$tier->id] : '' }}">
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>

                        <hr class="dashed">

                        <div class="col-12 physical-voucher-div  "
                            @if (isset($data->reward_type) && $data->reward_type == '0') style="display:none;" @else style="display:none;" @endif>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="company_id">Merchant <span
                                                class="required-hash">*</span></label>
                                        <select class="sh_dec form-select select2" name="company_id" id="company_id"
                                            @disabled(isset($data->id)) multiple>
                                            <option value="">Select Merchant</option>
                                            @if (isset($companies))
                                                @foreach ($companies as $company)
                                                    <option class="sh_dec" value="{{ $company->id }}"
                                                        {{ isset($data->company_id) && $data->company_id == $company->id ? 'selected' : '' }}>
                                                        {{ $company->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="sh_dec_s error" id="company_id_error"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div id="locations">

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 digital-voucher-div"
                            @if (isset($data->reward_type) && $data->reward_type == '0') style="display:none;" @else style="display:none;" @endif>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="company_id">Participating merchant <span
                                                class="required-hash">*</span></label>
                                        <select class="sh_dec form-select" name="company_id" id="company_ids"
                                            @disabled(isset($data->id))>
                                            <option value="">Select merchant</option>
                                            @if (isset($companies))
                                                @foreach ($companies as $company)
                                                    <option class="sh_dec" value="{{ $company->id }}"
                                                        {{ isset($data->company_id) && $data->company_id == $company->id ? 'selected' : '' }}>
                                                        {{ $company->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="sh_dec_s error" id="company_id_error"></div>
                                    </div>
                                </div>
                                <div class="col-6" id="locations_for_digital">

                                </div>

                                <div class="col-1">
                                    <button class="btn btn-icon btn-sm btn-primary" type="button"
                                        onclick="moveSelectedLocation()">
                                        <i class="mdi mdi-arrow-collapse-right"></i>
                                    </button>
                                </div>
                                <div class="col-5">
                                    <b>Selected Merchants</b>
                                    <div id="selected_locations_for_digital">
                                    </div>
                                </div>
                            </div>
                            <hr class="dashed">

                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="expiry_day">Voucher Validity </label>
                                        <div class="input-group mb-2 mr-sm-2">
                                            <div class="input-group-prepend">
                                                <div class="sh_dec input-group-text">Days</div>
                                            </div>
                                            <input id="expiry_day" type="text" class="sh_dec form-control"
                                                name="expiry_day" placeholder="Enter Days"
                                                value="{{ $data->expiry_day ?? '' }}">
                                        </div>
                                        <span class="sh_dec_s text-muted">Add 0 for to use default expiration date of
                                            reward.
                                        </span>
                                        <br>
                                        <span class="sh_dec_s text-muted">if End Date is not provided then this field
                                            is
                                            required
                                        </span>

                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="clearing_method"> Clearing Method <span
                                                class="required-hash">*</span></label>
                                        <select class="sh_dec form-select clearing_method " name="clearing_method"
                                            id="clearing_method">

                                            <option class="sh_dec" value="">Select Clearing Method</option>
                                            <option class="sh_dec" value="0"
                                                {{ isset($data->clearing_method) && $data->clearing_method == '0' ? 'selected' : '' }}>
                                                QR</option>
                                            <option class="sh_dec" value="1"
                                                {{ isset($data->clearing_method) && $data->clearing_method == '1' ? 'selected' : '' }}>
                                                Barcode </option>
                                            <option class="sh_dec" value="1"
                                                {{ isset($data->clearing_method) && $data->clearing_method == '1' ? 'selected' : '' }}>
                                                Barcode </option>
                                            <option class="sh_dec" value="2"
                                                {{ isset($data->clearing_method) && $data->clearing_method == '2' ? 'selected' : '' }}>
                                                Coov Code </option>
                                            <option class="sh_dec" value="3"
                                                {{ isset($data->clearing_method) && $data->clearing_method == '3' ? 'selected' : '' }}>
                                                Participant & Merchant Redemption Code </option>
                                        </select>
                                        <div class="sh_dec_s error" id="inventory_type_error"></div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="inventory_type"> Inventory Type <span
                                                class="required-hash">*</span></label>
                                        <select class="sh_dec form-select inventory_type " name="inventory_type"
                                            id="inventory_type">
                                            <option class="sh_dec" value="">Select Inventory Type</option>
                                            <option class="sh_dec" value="0"
                                                {{ isset($data->inventory_type) && $data->inventory_type == '0' ? 'selected' : '' }}>
                                                Non-Merchant</option>
                                            <option class="sh_dec" value="1"
                                                {{ isset($data->inventory_type) && $data->inventory_type == '1' ? 'selected' : '' }}>
                                                merchant </option>
                                        </select>
                                        <div class="sh_dec_s error" id="inventory_type_error"></div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">

                                    <div class="mb-3 " id="InventoryQtyDiv">
                                        <label class="sh_dec" for="inventory_qty"> Inventory Quantity <span
                                                class="required-hash">*</span></label>
                                        <input id="inventory_qty" type="number" class="sh_dec form-control"
                                            name="inventory_qty" placeholder="Enter Inventory Quantity"
                                            value="{{ $data->inventory_qty ?? '' }}">
                                    </div>
                                    <div class="mb-3" id="InventoryFileDiv">
                                        <label class="sh_dec" for="file">File <span
                                                class="required-hash">*</span> </label>
                                        <input id="file" type="file" class="sh_dec form-control"
                                            name="file" value="{{ $data->file ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 ">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="voucher_value">Voucher Value <span
                                                class="required-hash">*</span></label>
                                        <input id="voucher_value" type="number" class="sh_dec form-control"
                                            name="voucher_value" placeholder="Enter Voucher Value"
                                            value="{{ $data->voucher_value ?? '' }}" onchange="voucherValueChange()">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 ">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="voucher_set">Voucher set <span
                                                class="required-hash">*</span></label>
                                        <input id="voucher_set" type="number" class="sh_dec form-control"
                                            name="voucher_set" placeholder="Enter Voucher set"
                                            value="{{ $data->voucher_set ?? '' }}" readonly>
                                    </div>
                                </div>
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
