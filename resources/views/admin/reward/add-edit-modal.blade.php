 @php
    $data = $data ?? null;
@endphp
<script>
    $(document).on('submit', '#edit_frm, #add_frm', function (e) {
        console.log('FORM SUBMIT TRIGGERED');
    });

    $(document).on('shown.bs.modal', '#EditModal', function () {
        let rewardType = $('#EditModal .reward_type').val();
        
        let merchantId = $('#EditModal #merchant_id').val();
        let modal = $(this).closest('.modal');
        initFlatpickr(this);
        initFlatpickrDate(this);    
        $(".max_order").hide();
        $("#common_section").show();
        if (rewardType == "1") {
            
            $(".max_order").hide(); // also show location section
            $(".max_qty").show(); // also show location section
            $("#EditModal #physical").show();
            $('#EditModal #collection_reminder_title').text('Send Collection Reminder');
            $('#EditModal #collection_reminder_label').contents().last()[0].textContent = ' Collection Reminder';
        }

        if (rewardType == "1" && merchantId) {
            
         
            $("#EditModal #location_section").show();           
            editLoadLocations(modal, merchantId); // will use savedLocations variable inside modal
        }
        
        if (rewardType == "0") {
            
            $('#EditModal #collection_reminder_title').text('Send Reminder');
            $('#EditModal #collection_reminder_label').contents().last()[0].textContent = ' Reminder';
            $(".max_qty").hide(); // also show location section
            $(".max_order").show(); // also show location section
            $("#EditModal #digital").show();
            $("#EditModal #location_section").show();
            editToggleInventoryFields(modal);
            editToggleClearingFields(modal);

            if (modal.attr("id") === "EditModal") {
                editParticipatingMerchantLocations(modal);
            }

              // merchant selected later
            modal.find("#participating_merchant_id").on("change", function () {

                const merchantId = $(this).val();
                loadParticipatingMerchantLocations(modal, merchantId);
            });  
        }        
    
    });

    $(document).on("change", ".voucher_image", function (e) {

        const modal = $('#EditModal');
        const file = e.target.files[0];

        const preview = modal.find('#voucher_image_preview');
        const clearBtn = modal.find('#clear_voucher_image');

        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            preview
                .attr('src', event.target.result) // data:image/*
                .show();

            clearBtn.show();
        };

        reader.readAsDataURL(file);
    });

    $(document).on('click', '#clear_voucher_image', function () {

        const modal = $(this).closest('.modal'); // auto-detect modal
        const input = modal.find('#voucher_image')[0];
        const preview = modal.find('#voucher_image_preview');

        if (input) {
            input.value = '';   // reset file input
        }

        preview.attr('src', '').hide();
        $(this).hide();
    });
    $(document).on("change", ".voucher_detail_img", function (e) {

        const modal = $('#EditModal');
        const file = e.target.files[0];

        const preview = modal.find('#voucher_detail_img_preview');
        const clearBtn = modal.find('#clear_voucher_detail_img');

        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            preview
                .attr('src', event.target.result) // data:image/*
                .show();

            clearBtn.show();
        };

        reader.readAsDataURL(file);
    });

    $(document).on('click', '#clear_voucher_detail_img', function () {

        const modal = $(this).closest('.modal'); // auto-detect modal
        const input = modal.find('#voucher_detail_img')[0];
        const preview = modal.find('#voucher_detail_img_preview');

        if (input) {
            input.value = '';   // reset file input
        }

        preview.attr('src', '').hide();
        $(this).hide();
    });

    function initFlatpickr(modal) {
        bindStartEndFlatpickrEdit(
            modal,
            'input[name="publish_start"]',
            'input[name="publish_end"]'
        );

        bindStartEndFlatpickrEdit(
            modal,
            'input[name="sales_start"]',
            'input[name="sales_end"]'
        );
    }

    function calculateVoucherSet() {
        let usualPrice   = parseFloat($('#EditModal #usual_price').val());
        let voucherValue = parseFloat($('#EditModal #voucher_value').val());
        if (isNaN(usualPrice) || isNaN(voucherValue) || voucherValue <= 0) {
            $('#EditModal #voucher_set').val('');
            return;
        }
        let voucherSet = usualPrice / voucherValue;
        $('#EditModal #voucher_set').val(Math.floor(voucherSet));        
    }

    $(document).on('change', '.reward_type', function () {

        let modal = $(this).closest('.modal');

        let type = $(this).val();
        let merchantId = modal.find('#merchant_id').val();

        let physical = modal.find('#physical');
        let locationSection = modal.find('#location_section');
        let locationWrapper = modal.find('#location_wrapper');

        if (type === "1") {
            $('#EditModal #collection_reminder_title').text('Send Collection Reminder');
            $('#EditModal #collection_reminder_label').contents().last()[0].textContent = ' Collection Reminder';
            physical.show();
            locationSection.show();

            if (merchantId) {
                editLoadLocations(modal, merchantId);
            }

        } else {
            $('#EditModal #collection_reminder_title').text('Send Reminder');
            $('#EditModal #collection_reminder_label').contents().last()[0].textContent = ' Reminder';
            physical.hide();
            locationSection.hide();
            locationWrapper.html("");
        }
    });

    //physical reward
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

    function editLoadLocations(modal, merchantId) {

        modal.find('#location_wrapper').html("");

        $.ajax({
            url: "{{ url('admin/reward/get-locations') }}/" + merchantId,
            type: "GET",
            success: function (res) {

                if (res.status !== 'success') return;

                let html = `<label class="sh_dec"><b>Locations </b> <span  style="color:#f46a6a;">*</span></label>`;
                html += `<div id="location_wrapper" class="row gx-3 gy-3">`;   // UPDATED wrapper

                let i = 1;

                res.locations.forEach(loc => {

                    let isChecked = savedLocations[loc.id] ? 'checked' : '';
                    let qtyValue = savedLocations[loc.id] ?? '';

                    html += `
                        <div class="col-md-6 col-12">
                            <div class="location-box d-flex align-items-center p-2"
                                        style="border:1px solid #e9e9e9; border-radius:6px;">

                                <div class="d-flex align-items-center me-auto">
                                    <label class="mb-0 me-2 font-12" style="margin-top: 4px;">
                                        <span class="fw-bold"></span> ${loc.name}
                                    </label>
                                    <input type="checkbox" 
                                        name="locations[${loc.id}][selected]" 
                                        value="1" ${isChecked} class="form-check-input">
                                </div>

                                <div class="d-flex align-items-center ms-3">
                                    <label class="mb-0 me-2 font-12">Inventory Qty</label>
                                    <input type="number" min="0"
                                        class="form-control"
                                        name="locations[${loc.id}][inventory_qty]"
                                        value="${qtyValue}"
                                        placeholder="Qty"
                                        style="max-width:100px">
                                </div>


                            </div>
                        </div>
                    `;

                    i++;
                });

                html += `</div><div id="locations_error" class="text-danger mt-1"></div>`;

                modal.find('#location_section').html(html);

            }
                
        });
    }

    //Digital Reward
    $(document).on("change", ".clearing_method", function () {
        let modal = $(this).closest(".modal");
        editToggleClearingFields(modal);
        editToggleInventoryFields(modal);
    });  
    
    $(document).on('input', '#usual_price, #voucher_value', function () {
        calculateVoucherSet();
    });

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

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Reward Name <span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name" placeholder="Enter name" value="{{ $data->name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="voucher_image">
                                    Voucher Catalogue Image <span class="required-hash">*</span>
                                </label>
                                <input id="voucher_image" type="file" class="sh_dec form-control voucher_image" name="voucher_image" accept=".png,.jpg,.jpeg">
                                <span class="text-secondary">(100 px X 100 px)</span>
                                <div class="d-flex justify-items-start gap-2">
                                    <img id="voucher_image_preview" src="{{ isset($data->voucher_image) ? asset('uploads/image/'.$data->voucher_image) : '' }}" style="max-width:50px; display: {{ isset($data->voucher_image) ? 'block' : 'none' }};">
                                    <a href="javascript:void(0);" class="text-danger" id="clear_voucher_image" style="display:none;">
                                        Clear
                                    </a>
                                </div>
                            </div>
                        </div> 

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="voucher_detail_img">
                                    Voucher Details Image <span class="required-hash">*</span>
                                </label>
                                <input id="voucher_detail_img" type="file" class="sh_dec form-control voucher_detail_img" name="voucher_detail_img" accept=".png,.jpg,.jpeg">
                                <span class="text-secondary">(351 px X 190 px)</span>
                                <div class="d-flex justify-items-start gap-2">
                                    <img id="voucher_detail_img_preview" src="{{ isset($data->voucher_detail_img) ? asset('uploads/image/'.$data->voucher_detail_img) : '' }}" style="max-width:50px; display: {{ isset($data->voucher_detail_img) ? 'block' : 'none' }};">
                                    <a href="javascript:void(0);" class="text-danger" id="clear_voucher_detail_img" style="display:none;">
                                        Clear
                                    </a>
                                </div>
                            </div>
                        </div>                       

                     
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="description">Description <span class="required-hash">*</span></label>
                                <textarea id="description" type="text" class="sh_dec form-control" name="description"  placeholder="Enter description" value="{{ $data->description ?? '' }}">{{ $data->description ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="term_of_use">Voucher T&C <span class="required-hash">*</span></label>
                                <textarea id="term_of_use" type="text" class="sh_dec form-control" name="term_of_use"  placeholder="Enter Voucher T&C" value="{{ $data->term_of_use ?? '' }}">{{ $data->term_of_use ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="how_to_use">How to use <span class="required-hash">*</span></label>
                                <textarea id="how_to_use" type="text" class="sh_dec form-control" name="how_to_use" placeholder="Enter How to use" value="{{ $data->how_to_use ?? '' }}">{{ $data->how_to_use ?? '' }}</textarea>
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
                                <label class="sh_dec" for="voucher_validity">Voucher Validity <span class="required-hash">*</span></label>
                                <input id="voucher_validity" type="text" class="sh_dec form-control js-flat-date" name="voucher_validity"
                                    value="{{ isset($data->voucher_validity) ? \Carbon\Carbon::parse($data->voucher_validity)->format('Y-m-d') : '' }}" placeholder="YYYY-MM-DD"/>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="where_use">Where To Use <span class="required-hash">*</span></label>
                                <input id="where_use" type="text" class="sh_dec form-control" name="where_use"
                                    value="{{ isset($data->where_use) ?  $data->where_use : '' }}" placeholder="Where To Use"/>
                            </div>
                        </div>
                        
                        

                        <!-- 🔥 LOCATION DATE BLOCK — insert before the Usual Price field -->
                        <div id="location_date_container" class="col-12">
                            <input type="hidden" id="publish_start_original" value="{{ $data->publish_start_date ?? '' }} {{ $data->publish_start_time ?? '' }}">
                            <input type="hidden" id="publish_end_original" value="{{ $data->publish_end_date ?? '' }} {{ $data->publish_end_time ?? '' }}">
                            <input type="hidden" id="sales_start_original" value="{{ $data->sales_start_date ?? '' }} {{ $data->sales_start_time ?? '' }}">
                            <input type="hidden" id="sales_end_original" value="{{ $data->sales_end_date ?? '' }} {{ $data->sales_end_time ?? '' }}">

                            <label class="sh_dec"><b>Date & Time</b></label>

                            <div class="location-date-block mt-2" data-location-id="1" style="padding:10px; border:1px dashed #e0e0e0;">
                                
                                <div class="row">

                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">Publish Start Date & Time <span class="required-hash">*</span></label>
                                            <input type="text"  class="form-control" name="publish_start"  id="publish_start"  value="{{ isset($data->publish_start_date) ? $data->publish_start_date . ' ' . $data->publish_start_time : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">Publish End Date & Time <span class="required-hash">*</span></label>
                                            <input type="text" class="form-control"  name="publish_end"  value="{{ isset($data->publish_end_date) ? $data->publish_end_date . ' ' . $data->publish_end_time : '' }}">
                                        </div>
                                    </div>

                                    <!-- Sales fields -->
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">Sales Start Date & Time <span class="required-hash">*</span></label>
                                            <input type="text"  class="form-control" name="sales_start" value="{{ isset($data->sales_start_date) ? $data->sales_start_date . ' ' . $data->sales_start_time : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">Sales End Date & Time <span class="required-hash">*</span></label>
                                            <input type="text" class="form-control" name="sales_end"  value="{{ isset($data->sales_end_date) ? $data->sales_end_date . ' ' . $data->sales_end_time : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 ">
                            <div class="mb-3">
                                <label class="sh_dec" for="usual_price">Usual Price($) <span class="required-hash">*</span></label>
                                <input id="usual_price" type="number" min="0" class="sh_dec form-control" name="usual_price"  placeholder="Enter Usual Price" value="{{ $data->usual_price ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 max_qty">
                            <div class="mb-3">
                                <label class="sh_dec" for="max_quantity">Maximum Quantity <span class="required-hash">*</span></label>
                                <input id="max_quantity" type="number" min="0" class="sh_dec form-control" name="max_quantity_physical"   placeholder="Enter Maximum Quantity" value="{{ $data->max_quantity ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 max_order">
                            <div class="mb-3">
                                <label class="sh_dec" for="max_order">Maximum Order <span class="required-hash">*</span></label>
                                <input id="max_order" type="number" min="0" class="sh_dec form-control" name="max_order"   placeholder="Enter Maximum Order" value="{{ $data->max_order ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-12 ">
                            <div class="row">
                                <div class="col-12">
                                    <label class="sh_dec" for="amount"> <b> Tier Rates </b></label>
                                </div>
                                @foreach ($tiers as $key => $tier)
                                @php
                                    $price = '';
                                    if($data){
                                        foreach ($data->tierRates as $rate) {
                                            if ($rate->tier_id == $tier->id) {
                                                $price = $rate->price;
                                                break;
                                            }
                                        }
                                    }
                                @endphp

                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label class="sh_dec" for="tier_{{ $tier->id }}">{{ $tier->tier_name }}  Price <span class="required-hash">*</span></label>
                                            <input id="tier_{{ $tier->id }}" type="number" min="0" class="sh_dec form-control" name="tier_{{ $tier->id }}"  placeholder="Enter {{ $tier->tier_name }} Price"  
                                             value="{{ $price }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!--Merchant Locations-->
                        <div id="location_section" class="mt-2 mb-2" style="display:none;">                            
                        </div>
                        <div id="physical" >                          
                        </div>

                        <div id="digital" style="display:none; margin-top: 10px; border: #e0e0e0 1px dashed; padding-top: 10px;">

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="max_quantity">Maximum Quantity <span class="required-hash">*</span></label>
                                        <input id="max_quantity" type="number" min="0" class="sh_dec form-control" name="max_quantity_digital"   placeholder="Enter Maximum Quantity" value="{{ $data->max_quantity ?? '' }}">
                                    </div>
                                </div>
                               
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="inventory_type">Inventory Type <span class="required-hash">*</span></label>
                                        <select class="sh_dec form-select inventory_type" name="inventory_type">
                                            <option class="sh_dec" value="">Select Voucher Type</option>
                                            <option class="sh_dec" value="0" {{ isset($data->inventory_type) && $data->inventory_type == '0' ? 'selected' : '' }}> Non Merchant</option>
                                            <option class="sh_dec" value="1" {{ isset($data->inventory_type) && $data->inventory_type == '1' ? 'selected' : '' }}> Merchant</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 file" style="display: none">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="csvFile">File <span class="required-hash">*</span></label>    
                                        <input id="csvFile" type="file" class="sh_dec form-control" name="csvFile" accept=".xlsx,.xls">
                                        <div class="d-flex justify-content-between">
                                            <div class="mt-1">
                                                <label class="small text-muted">
                                                    Download demo file:
                                                    <a href="{{ asset('demo-reward.xlsx') }}" download class="text-primary fw-bold">
                                                        Click here
                                                    </a>
                                                </label>
                                            </div>
                                            @if(isset($data->csvFile))
                                                <div class="mt-1">
                                                    <a href="{{ asset('reward_voucher/'.$data->csvFile) }}" target="_blank" class="text-primary">
                                                        {{ $data->csvFile }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 inventory_qty" style="display: none">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="inventory_qty">Inventory Qty <span class="required-hash">*</span></label>    
                                        <input id="inventory_qty" type="number" min="0" placeholder="Enter Inventory Qty" class="sh_dec form-control"   name="inventory_qty" value="{{ $data->inventory_qty ?? '' }}"> 
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="voucher_value">Voucher Value <span class="required-hash">*</span></label>    
                                        <input id="voucher_value" type="number" min="0" placeholder="Enter Voucher Value" class="sh_dec form-control"   name="voucher_value" value="{{ $data->voucher_value ?? '' }}"> 
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="voucher_set">Voucher Set <span class="required-hash">*</span></label>    
                                        <input id="voucher_set" type="number" min="0" readonly  placeholder="Enter Voucher Set" class="sh_dec form-control"   name="voucher_set" value="{{ $data->voucher_set ?? '' }}"> 
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="clearing_method">Clearing Menthods <span class="required-hash">*</span></label>
                                        <select class="sh_dec form-select clearing_method " name="clearing_method" id="clearing_method">

                                            <option class="sh_dec" value="">Select Clearing Method</option>
                                            <option class="sh_dec" value="0" {{ isset($data->clearing_method) && $data->clearing_method == '0' ? 'selected' : '' }}>
                                                QR
                                            </option>                                            
                                            <option class="sh_dec" value="1" {{ isset($data->clearing_method) && $data->clearing_method == '1' ? 'selected' : '' }}>
                                                Barcode 
                                            </option>
                                            <option class="sh_dec" value="4" {{ isset($data->clearing_method) && $data->clearing_method == '4' ? 'selected' : '' }}>
                                                External Code 
                                            </option>
                                            <option class="sh_dec" value="3" {{ isset($data->clearing_method) && $data->clearing_method == '3' ? 'selected' : '' }}>
                                                External Link 
                                            </option>
                                            <option class="sh_dec" value="2" {{ isset($data->clearing_method) && $data->clearing_method == '2' ? 'selected' : '' }}>
                                                Merchant Code 
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 location_text" style="display: none">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="location_text">Location <span class="required-hash">*</span></label>    
                                        <input id="location_text" type="text" class="sh_dec form-control"   name="location_text" value="{{ $location_text ?? '' }}"> 
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 participating_merchant" style="display: none">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="participating_merchant_id">Participating Merchant <span class="required-hash">*</span></label>
                                        <select class="sh_dec form-select" name="participating_merchant_id" id="participating_merchant_id">
                                            <option value="">Select Participating Merchant</option>
                                            @if (isset($participating_merchants))                                        
                                                @foreach ($participating_merchants as $merchant)
                                                    <option  value="{{ $merchant->id }}">
                                                        {{ $merchant->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>                                
                                    </div>
                                </div>

                            </div>                           

                            <div class="row mt-3" id="participating_section" style="display:none;">
                                <div id="selected_locations_hidden"></div>

                                <!-- LEFT: Locations -->
                                <div class="col-md-7">
                                    <div id="participating_merchant_location"></div>
                                </div>

                                <!-- RIGHT: Selected Outlets -->
                                <div class="col-md-5 mb-2">
                                    <div id="selected_locations_wrapper" style="display:none;">
                                        <label class="sh_dec fw-bold">Selected Outlets</label>
                                        <div id="selected_locations_summary"
                                            class="form-control"
                                            style="min-height:120px; background:#f8f9fa;">
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div id="common_section" style="margin-top: 10px; border: #e0e0e0 1px dashed; padding-top: 10px;">
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Hide Quantity</label>
                                <div class="col-md-3">
                                    <label>
                                        <input type="checkbox" name="hide_quantity" value="1"  {{ isset($data) && $data->hide_quantity ? 'checked' : '' }} class="form-check-input">
                                        <span class="mt-1">Hide Quantity</span>
                                    </label>
                                </div>
                            </div>
                            <label for="" class="mb-2"><span class=""><b>Clearing Method:</b></span> CSO Issuance</label>
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Low Stock Reminder Threshold</label>

                                <div class="col-md-6 d-flex">
                                    <div class="me-3">
                                        <label class="sh_dec">Low Stock Reminder 1 <span class="required-hash"></span></label>
                                        <input type="number" min="0" class="form-control" name="low_stock_1"placeholder="Low Stock Reminder 1" value="{{ $data->low_stock_1 ?? '' }}">
                                    </div>
                                    <div>
                                        <label class="sh_dec">Low Stock Reminder 2 <span class="required-hash"></span></label>
                                        <input type="number" min="0" class="form-control"  name="low_stock_2"placeholder="Low Stock Reminder 2" value="{{ $data->low_stock_2 ?? '' }}">
                                    </div>
                                </div>                               
                            </div>

                            <!-- Friendly URL -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Friendly URL Name</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control"   name="friendly_url"    value="{{ $data->friendly_url ?? '' }}">
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Category</label>
                                <div class="col-md-6">
                                    <select class="form-select" name="category_id">
                                        <option value="">Select Category</option>
                                        @if (isset($category))                                        
                                            @foreach ($category as $cat)
                                                <option value="{{ $cat->id }}" {{ isset($data) && $data->category_id == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>


                            <!-- FABS Category -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">FABS Categories Information</label>
                                <div class="col-md-6">
                                    <select class="form-select" name="fabs_category_id" readonly>
                                        <option value="">Select</option>                                     

                                    </select>
                                </div>
                            </div>

                            <!-- AX Item Code -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">AX Item Code</label>
                                <div class="col-md-6">
                                    <input type="text" name="ax_item_code" class="form-control" readonly  value="{{ $data->ax_item_code ?? '' }}">
                                </div>
                            </div>

                            <!-- Publish Channel -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Publish Channel</label>

                                <div class="col-md-3">
                                    <label>
                                        <input type="checkbox" name="publish_independent" value="1" {{ isset($data) && $data->publish_independent ? 'checked' : '' }} class="form-check-input">
                                        Internet
                                    </label>
                                </div>

                                <div class="col-md-3">
                                    <label>
                                        <input type="checkbox" name="publish_inhouse" value="1" {{ isset($data) && $data->publish_inhouse ? 'checked' : '' }} class="form-check-input">
                                        Inhouse
                                    </label>
                                </div>
                            </div>

                           <!-- Send Reminder -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold" id="collection_reminder_title"> Send Collection Reminder </label>

                                <div class="col-md-3">
                                    <label id="collection_reminder_label">
                                        <input type="checkbox"   name="send_reminder" value="1" {{ isset($data) && $data->send_reminder ? 'checked' : '' }} class="form-check-input">
                                        Collection Reminder
                                    </label>
                                </div>
                            </div>


                            <!-- All other common fields here -->
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


