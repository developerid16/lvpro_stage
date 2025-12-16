 @php
    $data = $data ?? null;
@endphp
<script>
    $(document).on('shown.bs.modal', '#EditModal', function () {
        let modal = $(this).closest('.modal');
       
        editToggleInventoryFields(modal);
        editToggleClearingFields(modal);
        
    });

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
   
    $(document).on("shown.bs.modal", "#AddModal, #EditModal", function () {

        $(this).find("#participating_merchant_id").on("change", function () {

            let merchantId = $(this).val();
            let modal      = $(this).closest('.modal'); 

            if (merchantId) {
                modal.find("#participating_merchant_location").show();
                editParticipatingMerchantLocations(modal,merchantId);
            } else {
                modal.find("#participating_merchant_location").hide();
            }
        });
    });
    //Digital Reward
    $(document).on("change", ".clearing_method", function () {
        let modal = $(this).closest(".modal");
        editToggleClearingFields(modal);
        editToggleInventoryFields(modal);
    });

    function editParticipatingMerchantLocations(modal, merchantId) {
        modal.find('#participating_location_wrapper').html("");
        $.ajax({
            url: "{{ url('admin/reward/get-participating-merchant-locations') }}/" + merchantId,
            type: "GET",
            success: function (res) {

                if (res.status === 'success') {

                    let html = '';
                    let i = 1;

                    html += `<label class="sh_dec"><b>Participating Merchant Outlets</b></label>`;
                    html += `<div id="participating_location_wrapper" class="row gx-3 gy-3">`;

                    res.locations.forEach(loc => {

                        let isChecked = participatingLocations.includes(loc.id) ? 'checked' : '';
                        console.log(participatingLocations,'participatingLocations');
                        

                        html += `
                            <div class="col-md-4 col-12">
                                <div class="location-box d-flex align-items-center p-2"
                                    style="border:1px solid #e9e9e9; border-radius:6px;">

                                    <div class="d-flex align-items-center me-auto">
                                        <label class="mb-0 me-2 font-12" style="margin-top: 4px;">
                                            <span class="fw-bold"></span> ${loc.name}
                                        </label>
                                        
                                        <input type="checkbox" 
                                            name="participating_merchant_locations[${loc.id}][selected]" 
                                            value="1"
                                            class="form-check-input"
                                            ${isChecked}>
                                    </div>

                                </div>
                            </div>
                        `;

                        i++;
                    });

                    html += `</div><div id="participating_merchant_locations_error" class="text-danger mt-1"></div>`;
                    modal.find('#participating_merchant_location').html(html);
                }
            }

        });
    }

    function editToggleInventoryFields(modal) {
        let type = modal.find('.inventory_type').val();

        let fileField = modal.find('.file');
        let qtyField  = modal.find('.inventory_qty');

        if (type === "1") {
            fileField.show();
            qtyField.hide();
            qtyField.find("input").val(""); // clear
        } else if (type === "0") {
            qtyField.show();
            fileField.hide();
            fileField.find("input").val(""); // clear
        } else {
            // nothing selected → hide both
            fileField.hide();
            qtyField.hide();
        }
    }

    function editToggleClearingFields(modal) {
        let method = modal.find('.clearing_method').val();

        let locationField = modal.find('.location_text');
        let merchantField = modal.find('.participating_merchant');

        // Hide both first
        locationField.hide();
        merchantField.hide();
        $("#participating_merchant_location").hide();
        if (["0", "1", "3"].includes(method)) {
            // QR, Barcode, External Link → show LOCATION
            locationField.show();
            merchantField.hide();
        } 
        else if (["2"].includes(method)) {
            $("#EditModal #participating_merchant_location").show();
            let participatingMerchantId = $('#EditModal #participating_merchant_id').val();
            editParticipatingMerchantLocations(modal, participatingMerchantId); // will use savedLocations variable inside modal
            // External Code OR Merchant Code → show PARTICIPATING MERCHANT
            merchantField.show();
            locationField.hide();
        }
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
                                    src="{{ isset($data->voucher_image) ? asset('uploads/image/'.$data->voucher_image) : '' }}"
                                    style="max-width:50px; margin-top:10px; display: {{ isset($data->voucher_image) ? 'block' : 'none' }};">
                            </div>

                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Reward Name <span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name" placeholder="Enter name" value="{{ $data->name ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="description">Description <span class="required-hash">*</span></label>
                                <textarea id="description" type="text" class="sh_dec form-control" name="description"  placeholder="Enter description" value="{{ $data->description ?? '' }}">{{ $data->description ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="term_of_use">Voucher T&C <span class="required-hash">*</span></label>
                                <textarea id="term_of_use" type="text" class="sh_dec form-control" name="term_of_use"  placeholder="Enter Voucher T&C" value="{{ $data->term_of_use ?? '' }}">{{ $data->term_of_use ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
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
                                <input id="" type="text" class="sh_dec form-control" name="voucher_type" value="e-Voucher" readonly>                            
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <label class="col-md-4 fw-bold">Direct Utilization</label>
                            <div class="col-md-3">                               
                                <label>
                                    <input type="checkbox" name="direct_utilization" value="1"  {{ isset($data) && $data->direct_utilization ? 'checked' : '' }} class="form-check-input">
                                    <span class="mt-1">Direct Utilization</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 ">
                            <div class="col-md-3">
                            </div>
                        </div>

                        <!-- 🔥 LOCATION DATE BLOCK — insert before the Usual Price field -->
                        <div id="location_date_container" class="col-12">
                            <label class="sh_dec"><b>Date & Time</b></label>

                            <div class="location-date-block mt-2" data-location-id="1" style="padding:10px; border:1px dashed #e0e0e0;">
                                
                                <div class="row">

                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">Publish Start Date & Time <span class="required-hash">*</span></label>
                                            <input type="datetime-local"  class="form-control" name="publish_start"   value="{{ isset($data->publish_start_date) ? $data->publish_start_date . 'T' . $data->publish_start_time : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">Publish End Date & Time</label>
                                            <input type="datetime-local" class="form-control"  name="publish_end"  value="{{ isset($data->publish_end_date) ? $data->publish_end_date . 'T' . $data->publish_end_time : '' }}">
                                        </div>
                                    </div>

                                    <!-- Sales fields -->
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">Sales Start Date & Time <span class="required-hash">*</span></label>
                                            <input type="datetime-local"  class="form-control" name="sales_start" value="{{ isset($data->sales_start_date) ? $data->sales_start_date . 'T' . $data->sales_start_time : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">Sales End Date & Time</label>
                                            <input type="datetime-local" class="form-control" name="sales_end"  value="{{ isset($data->sales_end_date) ? $data->sales_end_date . 'T' . $data->sales_end_time : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                       
                        <div class="col-12 col-md-6 max_qty">
                            <div class="mb-3">
                                <label class="sh_dec" for="max_quantity">Maximum Quantity<span class="required-hash">*</span></label>
                                <input id="max_quantity" type="number" class="sh_dec form-control" name="max_quantity"   placeholder="Enter Maximum Quantity" value="{{ $data->max_quantity ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="voucher_validity">Voucher Validity<span class="required-hash">*</span></label>
                                <input id="voucher_validity" type="date"  class="sh_dec form-control"  name="voucher_validity" value="{{ isset($data->voucher_validity) ? \Carbon\Carbon::parse($data->voucher_validity)->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="inventory_type">Inventory Type<span class="required-hash">*</span></label>
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
                                <input id="csvFile" type="file" class="sh_dec form-control" name="csvFile" accept=".xlxs,.xls">

                                @if(isset($data->csvFile))
                                    <div class="mt-2">
                                        <a href="{{ asset('reward_voucher/'.$data->csvFile) }}" target="_blank" class="text-primary">
                                            {{ $data->csvFile }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 col-md-6 inventory_qty" style="display: none">
                            <div class="mb-3">
                                <label class="sh_dec" for="inventory_qty">Inventory Qty <span class="required-hash">*</span></label>    
                                <input id="inventory_qty" type="number"  placeholder="Enter Inventory Qty" class="sh_dec form-control"   name="inventory_qty" value="{{ $data->inventory_qty ?? '' }}"> 
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="voucher_value">Voucher Value<span class="required-hash">*</span></label>    
                                <input id="voucher_value" type="number"  placeholder="Enter Voucher Value" class="sh_dec form-control"   name="voucher_value" value="{{ $data->voucher_value ?? '' }}"> 
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="voucher_set">Voucher Set<span class="required-hash">*</span></label>    
                                <input id="voucher_set" type="number"  placeholder="Enter Voucher Set" class="sh_dec form-control"   name="voucher_set" value="{{ $data->voucher_set ?? '' }}"> 
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="clearing_method">Clearing Menthods<span class="required-hash">*</span></label>
                                <select class="sh_dec form-select clearing_method " name="clearing_method" id="clearing_method">

                                    <option class="sh_dec" value="">Select Clearing Method</option>
                                    <option class="sh_dec" value="0" {{ isset($data->clearing_method) && $data->clearing_method == '0' ? 'selected' : '' }}>
                                        QR
                                    </option>                                            
                                    <option class="sh_dec" value="1" {{ isset($data->clearing_method) && $data->clearing_method == '1' ? 'selected' : '' }}>
                                        Barcode 
                                    </option>
                                    <option class="sh_dec" value="2" {{ isset($data->clearing_method) && $data->clearing_method == '2' ? 'selected' : '' }}>
                                        External Code 
                                    </option>
                                    <option class="sh_dec" value="3" {{ isset($data->clearing_method) && $data->clearing_method == '3' ? 'selected' : '' }}>
                                        External Link 
                                    </option>
                                    <option class="sh_dec" value="4" {{ isset($data->clearing_method) && $data->clearing_method == '4' ? 'selected' : '' }}>
                                        Merchant Code 
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 location_text" style="display: none">
                            <div class="mb-3">
                                <label class="sh_dec" for="location_text">Location<span class="required-hash">*</span></label>    
                                <input id="location_text" type="text" class="sh_dec form-control"   name="location_text" value="{{ $data->location_text ?? '' }}"> 
                            </div>
                        </div>
                        <div class="col-12 col-md-6 participating_merchant" style="display: none">
                            <div class="mb-3">
                                <label class="sh_dec" for="participating_merchant_id">Participating Merchant <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select" name="participating_merchant_id" id="participating_merchant_id">
                                    <option value="">Select Participating Merchant</option>
                                    @if (isset($participating_merchants))                                        
                                        @foreach ($participating_merchants as $merchant)
                                            <option value="{{ $merchant->id }}" {{ isset($data) && $data->participating_merchant_id == $merchant->id ? 'selected' : '' }}>
                                                {{ $merchant->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>                                
                            </div>
                        </div>
                    </div>
                    <div id="participating_merchant_location" class="mt-2 mb-3" style="display:none;"> 
                    </div> 
                    <div class="row align-items-center mb-3">
                        <label class="col-md-4 fw-bold">Hide Quantity</label>
                        <div class="col-md-3">
                            <label>
                                <input type="checkbox" name="hide_quantity" value="1"  {{ isset($data) && $data->hide_quantity ? 'checked' : '' }} class="form-check-input">
                                <span class="mt-1">Hide Quantity</span>
                            </label>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="col-md-4 fw-bold">Low Stock Reminder Threshold</label>

                        <div class="col-md-6 d-flex">
                            <div class="me-3">
                                <label class="sh_dec">Low Stock Reminder 1</label>
                                <input type="number" class="form-control" name="low_stock_1"placeholder="Low Stock Reminder 1" value="{{ $data->low_stock_1 ?? '' }}">
                            </div>
                            <div>
                                <label class="sh_dec">Low Stock Reminder 2</label>
                                <input type="number" class="form-control"  name="low_stock_2"placeholder="Low Stock Reminder 2" value="{{ $data->low_stock_2 ?? '' }}">
                            </div>
                        </div>                               
                    </div>

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


