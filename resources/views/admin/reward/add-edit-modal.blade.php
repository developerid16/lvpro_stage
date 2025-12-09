 @php
    $data = $data ?? null;
@endphp


<script>
    $(document).on('shown.bs.modal', '#EditModal', function () {

        let rewardType = $('#EditModal .reward_type').val();
        let merchantId = $('#EditModal #merchant_id').val();
        let modal = $(this).closest('.modal');
        console.log(merchantId, rewardType);
        
        if (rewardType == "1" && merchantId) {
            $("#EditModal #physical").show();
            $("#EditModal #location_section").show();
            editLoadLocations(modal, merchantId); // will use savedLocations variable inside modal
        }
    });

    // 1️⃣ Handle reward type change
    $(document).on('change', '.reward_type', function () {

        let modal = $(this).closest('.modal');

        let type = $(this).val();
        let merchantId = modal.find('#merchant_id').val();

        let physical = modal.find('#physical');
        let locationSection = modal.find('#location_section');
        let locationWrapper = modal.find('#location_wrapper');

        if (type === "1") {
            physical.show();
            locationSection.show();

            if (merchantId) {
                editLoadLocations(modal, merchantId);
            }

        } else {
            physical.hide();
            locationSection.hide();
            locationWrapper.html("");
        }
    });


    // 2️⃣ Handle merchant change
    $(document).on('change', '#merchant_id', function () {

        let modal = $(this).closest('.modal');
        let merchantId = $(this).val();
        let rewardType = modal.find('.reward_type').val();

        let locationSection = modal.find('#location_section');
        let locationWrapper = modal.find('#location_wrapper');

        locationWrapper.html("");

        if (rewardType == "1" && merchantId) {
            locationSection.show();
            editLoadLocations(modal, merchantId);
        } else {
            locationSection.hide();
        }
    });


    // 3️⃣ Unified loader (Add + Edit)
    function editLoadLocations(modal, merchantId) {

        modal.find('#location_wrapper').html("");

        $.ajax({
            url: "{{ url('admin/reward/get-locations') }}/" + merchantId,
            type: "GET",
            success: function (res) {

                if (res.status !== 'success') return;

                let html = `<label class="sh_dec"><b>Locations</b></label>`;
                html += `<div id="location_wrapper">`;

                let i = 1;

                res.locations.forEach(loc => {

                    // Add Mode → savedLocations empty
                    // Edit Mode → savedLocations contains stored qty
                    let isChecked = savedLocations[loc.id] ? 'checked' : '';
                    let qtyValue = savedLocations[loc.id] ?? '';

                    html += `
                        <div class="location-box row align-items-center py-2 mb-2" style="border-bottom:1px solid #ddd;">

                            <div class="col-md-4 col-12">
                                <label class="mb-0 me-3">
                                    <span class="fw-bold">Location ${i}:</span> ${loc.name}
                                </label>
                                <input type="checkbox" name="locations[${loc.id}][selected]" value="1" ${isChecked}>
                            </div>

                            <div class="col-md-6 col-12 mt-2 d-flex">
                                <label class="mb-1 me-3 pt-2">Inventory Qty</label>
                                <input type="number" class="form-control"
                                    name="locations[${loc.id}][inventory_qty]"
                                    value="${qtyValue}"
                                    placeholder="Enter Qty" style="max-width:200px">
                            </div>

                        </div>
                    `;

                    i++;
                });

                html += `</div>`;

                modal.find('#location_section').html(html);
            }
        });
    }

</script>


<div class="modal fade" id="{{ isset($data->id) ? 'EditModal' : 'AddModal' }}" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">   
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="sh_sub_title modal-title">{{ isset($data->id) ? 'Edit' : 'Add' }} {{ $title ?? '' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="overflow-y: auto;  max-height: 800px;">
                <form enctype="multipart/form-data" class="z-index-1" method="POST" action="javascript:void(0)"
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
                                <label class="sh_dec" for="voucher_image">Voucher Image <span class="required-hash">*</span></label>

                                <input id="voucher_image" type="file" class="sh_dec form-control"
                                    name="voucher_image" accept=".png,.jpg,.jpeg">

                                <span class="text-secondary">(316 px X 140 px)</span>
                                <!-- 🔥 PREVIEW IMAGE -->
                                <img id="voucher_image_preview"
                                    src="{{ isset($data->voucher_image) ? asset('reward_images/'.$data->voucher_image) : '' }}"
                                    style="max-width:50px; margin-top:10px; display: {{ isset($data->voucher_image) ? 'block' : 'none' }};">
                            </div>

                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Reward Name <span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name" placeholder="Enter name" value="{{ $data->name ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec" for="description">Description <span class="required-hash">*</span></label>
                                <textarea id="description" type="text" class="sh_dec form-control" name="description"  placeholder="Enter description" value="{{ $data->description ?? '' }}">{{ $data->description ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec" for="term_of_use">Voucher T&C <span class="required-hash">*</span></label>
                                <textarea id="term_of_use" type="text" class="sh_dec form-control" name="term_of_use"  placeholder="Enter Voucher T&C" value="{{ $data->term_of_use ?? '' }}">{{ $data->term_of_use ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="sh_dec" for="how_to_use">How to use <span class="required-hash">*</span></label>
                                <textarea id="how_to_use" type="text" class="sh_dec form-control" name="how_to_use" placeholder="Enter How to use" value="{{ $data->how_to_use ?? '' }}">{{ $data->how_to_use ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="reward_type">Voucher Type <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select reward_type" name="reward_type">
                                    <option class="sh_dec" value="">Select Voucher Type</option>
                                    <option class="sh_dec" value="0" {{ isset($data->reward_type) && $data->reward_type == '0' ? 'selected' : '' }}> Digital Voucher</option>
                                    <option class="sh_dec" value="1" {{ isset($data->reward_type) && $data->reward_type == '1' ? 'selected' : '' }}> Physical Voucher</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="merchant_id">Merchant <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select" name="merchant_id" id="merchant_id">
                                    <option value="">Select Merchant</option>
                                    @if (isset($merchants))                                        
                                        @foreach ($merchants as $merchant)
                                            <option value="{{ $merchant->id }}" {{ isset($data) && $data->merchant_id == $merchant->id ? 'selected' : '' }}>
                                                {{ $merchant->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>                                
                            </div>
                        </div>

                        <!-- 🔥 LOCATION DATE BLOCK — insert before the Usual Price field -->
                        <div id="location_date_container" class="col-12 mt-3">
                            <label class="sh_dec"><b>Date & Time</b></label>

                            <div class="location-date-block mt-2" data-location-id="1" style="padding:10px; border:1px dashed #e0e0e0;">
                                
                                <div class="row">

                                    <div class="col-12 col-md-3">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec">Publish Start Date <span class="required-hash">*</span></label>
                                            <input type="date" class="form-control" name="publish_start_date" value="{{ $data->publish_start_date ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec">Start Time</label>
                                            <input type="time" class="form-control"name="publish_start_time" value="{{ $data->publish_start_time ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec">Publish End Date</label>
                                            <input type="date" class="form-control"  name="publish_end_date" value="{{ $data->publish_end_date ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec">End Time</label>
                                            <input type="time" class="form-control"  name="publish_end_time" value="{{ $data->publish_end_time ?? '' }}">
                                        </div>
                                    </div>

                                    <!-- Sales fields -->
                                    <div class="col-12 col-md-3">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec">Sales Start Date <span class="required-hash">*</span></label>
                                            <input type="date" class="form-control"  name="sales_start_date" value="{{ $data->sales_start_date ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec">Start Time</label>
                                            <input type="time" class="form-control" name="sales_start_time" value="{{ $data->sales_start_time ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec">Sales End Date</label>
                                            <input type="date" class="form-control"  name="sales_end_date" value="{{ $data->sales_end_date ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec">End Time</label>
                                            <input type="time" class="form-control"   name="sales_end_time" value="{{ $data->sales_end_time ?? '' }}"> 
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="col-12 col-md-6 ">
                            <div class="mb-3">
                                <label class="sh_dec" for="usual_price">Usual Price($) <span class="required-hash">*</span></label>
                                <input id="usual_price" type="number" class="sh_dec form-control" name="usual_price"  placeholder="Enter Usual Price" value="{{ $data->usual_price ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="max_quantity">Maximum<span class="required-hash">*</span></label>
                                <input id="max_quantity" type="number" class="sh_dec form-control" name="max_quantity"   placeholder="Enter Maximum Order" value="{{ $data->max_quantity ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-12 ">
                            <div class="row">
                                <div class="col-12">
                                    <label class="sh_dec" for="amount"> <b> Tier Rates </b></label>
                                </div>
                                @foreach ($tiers as $key => $tier)
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="sh_dec" for="tier_{{ $tier->id }}">{{ $tier->tier_name }}  Price <span class="required-hash">*</span></label>
                                            <input id="tier_{{ $tier->id }}" type="number" class="sh_dec form-control" name="tier_{{ $tier->id }}"  placeholder="Enter {{ $tier->tier_name }} Price"   value="{{ isset($data->tierRates[$key]['price']) ? $data->tierRates[$key]['price'] : '' }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="location_section" style="display:none;">
                            
                        </div>

                        <div id="physical" style="display:none;">

                            <!-- Low Stock Reminder -->
                            <div class="row align-items-center mb-3">
                                <label class="sh_dec"><b>Low Stock Reminder Threshold</b></label>

                                <div class="col-md-3">
                                    <input type="number" class="form-control"
                                        name="low_stock_1"  placeholder="Reminder 1"   value="{{ $data->low_stock_1 ?? '' }}">
                                </div>

                                <div class="col-md-3">
                                    <input type="number" class="form-control"  name="low_stock_2"    placeholder="Reminder 2"   value="{{ $data->low_stock_2 ?? '' }}">
                                </div>
                            </div>

                            <!-- Friendly URL -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Friendly URL Name</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control"   name="friendly_url" readonly    value="{{ $data->friendly_url ?? '' }}">
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Category</label>
                                <div class="col-md-6">
                                    <select class="form-select" name="category_id" readonly>
                                        <option value="">Select Category</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Club Classification -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Club Classification Type</label>
                                <div class="col-md-6">
                                    <select class="form-select" name="club_classification_id" readonly>
                                        <option value="">Select</option>

                                    </select>
                                </div>
                            </div>

                            <!-- FABS Category -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">FABS Categories</label>
                                <div class="col-md-6">
                                    <select class="form-select" name="fabs_category_id" readonly>
                                        <option value="">Select</option>                                     

                                    </select>
                                </div>
                            </div>

                            <!-- SMC Classification -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">SMC Classification Type</label>
                                <div class="col-md-6">
                                    <select class="form-select" name="smc_classification_id" readonly>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <!-- AX Item Code -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">AX Item Code</label>
                                <div class="col-md-6">
                                    <input type="text" name="ax_item_code" class="form-control" readonly  value="{{ $data->ax_item_code ?? '' }}">
                                </div>
                            </div>

                            <!-- Publish Channel -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Publish Channel</label>

                                <div class="col-md-3">
                                    <label>
                                        <input type="checkbox" name="publish_independent" value="1" {{ isset($data) && $data->publish_independent ? 'checked' : '' }}>
                                        Independent
                                    </label>
                                </div>

                                <div class="col-md-3">
                                    <label>
                                        <input type="checkbox" name="publish_inhouse" value="1" {{ isset($data) && $data->publish_inhouse ? 'checked' : '' }}>
                                        In-House
                                    </label>
                                </div>
                            </div>

                            <!-- Send Reminder -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Send Reminder</label>

                                <div class="col-md-3">
                                    <label>
                                        <input type="checkbox" name="send_reminder" value="1"  {{ isset($data) && $data->send_reminder ? 'checked' : '' }}>
                                        Reminder
                                    </label>
                                </div>
                            </div>

                        </div>


                        
                    </div>
                    <div class="row">
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


