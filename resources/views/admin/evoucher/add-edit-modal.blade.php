 @php
    $data = $data ?? null;
@endphp

<script>
  

   
    $(document).on('shown.bs.modal', '#EditModal', function () {
        let modal = $(this).closest('.modal');
        // tinymce.init({ selector: "textarea.wysiwyg" });

        editToggleInventoryFields(modal);
        editToggleClearingFields(modal);
        initFlatpickr(this);
        initFlatpickrDate(this);  

        const merchantId = modal.find("#participating_merchant_id").val();
        $(document).on('keyup change input','#EditModal #inventory_qty, #EditModal #voucher_set, #EditModal #voucher_value', editCalculateSetQty);

        // EDIT MODE
        if (modal.attr("id") === "EditModal") {
            editParticipatingMerchantLocations(modal);
        }

        // merchant selected later
        modal.find("#participating_merchant_id").on("change", function () {

            const merchantId = $(this).val();
            loadParticipatingMerchantLocations(modal, merchantId);
        });  
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
  
    //Digital Reward
    $(document).on("change", "#EditModal .clearing_method", function () {
        let modal = $(this).closest(".modal");
        editToggleClearingFields(modal);
        editToggleInventoryFields(modal);
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
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="cso_method">CSO Method<span class="required-hash">*</span></label>
                                <select class="sh_dec form-select cso_method" name="cso_method">
                                    {{-- <option class="sh_dec" value="">Select CSO Method</option> --}}
                                    <option class="sh_dec" value="4" {{ isset($data->cso_method) && $data->cso_method == '4' ? 'selected' : '' }}> App/Web</option>
                                    <option class="sh_dec" value="0" {{ isset($data->cso_method) && $data->cso_method == '0' ? 'selected' : '' }}> CSO Issuance</option>
                                    <option class="sh_dec" value="1" {{ isset($data->cso_method) && $data->cso_method == '1' ? 'selected' : '' }}> Push Voucher by Member ID</option>
                                    <option class="sh_dec" value="2" {{ isset($data->cso_method) && $data->cso_method == '2' ? 'selected' : '' }}> Push Voucher by Parameter</option>
                                    <option class="sh_dec" value="3" {{ isset($data->cso_method) && $data->cso_method == '3' ? 'selected' : '' }}> Push by API SRP</option>
                                </select>
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
                                <label class="sh_dec" for="voucher_image">
                                    Voucher Catalogue Image <span class="required-hash">*</span>
                                </label>
                                <input id="voucher_image" type="file" class="sh_dec form-control voucher_image" name="voucher_image" accept=".png,.jpg,.jpeg">
                                <div class="d-flex justify-content-between mt-1">
                                    <span class="text-secondary">(100 px X 100 px)</span>
                                    <div class="position-relative d-inline-block">
                                        <img id="voucher_image_preview" src="{{ !empty($data?->voucher_image) ? asset('uploads/image/'.$data->voucher_image) : asset('uploads/image/no-image.png') }}" style="max-width:50px;"  alt="Voucher Image" />
                                        <a href="javascript:void(0);" id="clear_voucher_image" class="btn btn-sm btn-danger position-absolute top-0 end-0 translate-middle p-0 img-delete-btn" style="  display:none;"> ✖</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="voucher_detail_img">
                                    Voucher Details Image <span class="required-hash">*</span>
                                </label>
                                <input id="voucher_detail_img" type="file" class="sh_dec form-control voucher_detail_img" name="voucher_detail_img" accept=".png,.jpg,.jpeg">
                                <div class="d-flex justify-content-between mt-1">
                                    <span class="text-secondary">(351 px X 190 px)</span>
                                    <div class="position-relative d-inline-block">
                                        <img id="voucher_detail_img_preview" src="{{ !empty($data?->voucher_detail_img) ? asset('uploads/image/'.$data->voucher_detail_img) : asset('uploads/image/no-image.png') }}" style="max-width:50px;"  alt="Voucher Detail Image"/>
                                        <a href="javascript:void(0);" id="clear_voucher_detail_img" class="btn btn-sm btn-danger position-absolute top-0 end-0 translate-middle p-0 img-delete-btn" style="  display:none;"> ✖</a>
                                    </div>
                                </div>
                            </div>
                        </div>                       
  
                       

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec">Description <span class="required-hash">*</span></label>
                                <textarea class="sh_dec form-control wysiwyg" name="description">
                                    {{ $data->description ?? '' }}
                                </textarea>
                            </div>
                        </div>

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec">Voucher T&C <span class="required-hash">*</span></label>
                                <textarea class="sh_dec form-control wysiwyg" name="term_of_use">
                                    {{ $data->term_of_use ?? '' }}
                                </textarea>
                            </div>
                        </div>

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec">How to use <span class="required-hash">*</span></label>
                                <textarea class="sh_dec form-control wysiwyg" name="how_to_use">
                                    {{ $data->how_to_use ?? '' }}
                                </textarea>
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
                                <input id="" type="text" class="sh_dec form-control readonly" name="voucher_type" value="e-Voucher" readonly>                            
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
                                            <input type="text"  class="form-control" name="publish_start" value="{{ isset($data->publish_start_date) ? $data->publish_start_date . ' ' . $data->publish_start_time : '' }}">
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
                                    <div class="col-12 col-md-12">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">Days <span class="required-hash">*</span></label>

                                            @php
                                                $selectedDays = is_array($data->days ?? null)
                                                    ? $data->days
                                                    : json_decode($data->days ?? '[]', true);
                                            @endphp

                                            <select name="days[]" id="days" class="sh_dec form-select select-multiple" multiple>
                                                <option value="Monday"    {{ in_array('Monday', $selectedDays) ? 'selected' : '' }}>Monday</option>
                                                <option value="Tuesday"   {{ in_array('Tuesday', $selectedDays) ? 'selected' : '' }}>Tuesday</option>
                                                <option value="Wednesday" {{ in_array('Wednesday', $selectedDays) ? 'selected' : '' }}>Wednesday</option>
                                                <option value="Thursday"  {{ in_array('Thursday', $selectedDays) ? 'selected' : '' }}>Thursday</option>
                                                <option value="Friday"    {{ in_array('Friday', $selectedDays) ? 'selected' : '' }}>Friday</option>
                                                <option value="Saturday"  {{ in_array('Saturday', $selectedDays) ? 'selected' : '' }}>Saturday</option>
                                                <option value="Sunday"    {{ in_array('Sunday', $selectedDays) ? 'selected' : '' }}>Sunday</option>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">Start Time <span class="required-hash">*</span></label>
                                            <input type="time"  class="form-control" name="start_time" value="{{ isset($data->start_time) ? $data->start_time  : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3 sh_dec">
                                            <label class="sh_dec font-12">End Time <span class="required-hash">*</span></label>
                                            <input type="time" class="form-control" name="end_time"  value="{{ isset($data->end_time) ? $data->end_time  : '' }}">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                       
                        <div class="col-12 col-md-6 max_qty">
                            <div class="mb-3">
                                <label class="sh_dec" for="max_quantity">Maximum Quantity (Per User)  <span class="required-hash">*</span></label>
                                <input id="max_quantity" type="number" min="0" class="sh_dec form-control" name="max_quantity"   placeholder="Enter Maximum Quantity" value="{{ $data->max_quantity ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="voucher_validity">Voucher Validity <span class="required-hash">*</span></label>
                                <input
                                    id="voucher_validity"
                                    type="text"
                                    class="sh_dec form-control js-flat-date"
                                    name="voucher_validity"
                                    value="{{ isset($data->voucher_validity) ? \Carbon\Carbon::parse($data->voucher_validity)->format('Y-m-d') : '' }}"
                                    placeholder="YYYY-MM-DD"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="inventory_type">Inventory Type (Total)<span class="required-hash">*</span></label>
                                <select class="sh_dec form-select inventory_type" name="inventory_type">
                                    <option class="sh_dec" value="">Select Voucher Type</option>
                                    <option class="sh_dec" value="0" {{ isset($data->inventory_type) && $data->inventory_type == '0' ? 'selected' : '' }}> Non Merchant</option>
                                    <option class="sh_dec" value="1" {{ isset($data->inventory_type) && $data->inventory_type == '1' ? 'selected' : '' }}> Merchant</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-6 file" style="display:none">
                            <div class="mb-3">
                                <label class="sh_dec" for="csvFile"> File <span class="required-hash">*</span> </label>
                                <input id="csvFile" type="file" class="sh_dec form-control" name="csvFile" accept=".xlsx,.xls,.csv">
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <div>
                                        <label class="small text-muted">  Download demo file:
                                            <a href="{{ asset('demo-reward.xlsx') }}" download class="text-primary fw-bold"> Click here </a>
                                        </label>
                                    </div>
                                    <!-- uploaded / selected file -->
                                    <div id="uploadedFile" class="align-items-center gap-2 {{ isset($data->csvFile) ? 'd-flex' : 'd-none' }}">
                                        <a id="uploadedFileLink" href="{{ isset($data->csvFile) ? asset('reward_voucher/'.$data->csvFile) : 'javascript:void(0)' }}" target="_blank" class="text-primary fw-bold"> {{ $data->csvFile ?? '' }} </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" id="removeCsvFile"  title="Remove file"> 🗑 </button>
                                    </div>
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
                                <label class="sh_dec" for="voucher_value">Voucher Value ($) <span class="required-hash">*</span></label>    
                                <input id="voucher_value" type="number" min="0"  placeholder="Enter Voucher Value" class="sh_dec form-control"   name="voucher_value" value="{{ $data->voucher_value ?? '' }}"> 
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="voucher_set">Voucher Set (Per Transaction) <span class="required-hash">*</span></label>    
                                <input id="voucher_set" type="number" min="0"  placeholder="Enter Voucher Set" class="sh_dec form-control"   name="voucher_set" value="{{ $data->voucher_set ?? '' }}"> 
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="set_qty">Voucher Set Quantity <span class="required-hash">*</span></label>    
                                <input id="set_qty" type="number" min="0" readonly  placeholder="Voucher Set Quantity" class="sh_dec form-control"   name="set_qty" value="{{ $data->set_qty ?? '' }}"> 
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
                                            <option  value="{{ $merchant->id }}" >
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
                                <label class="sh_dec">Low Stock Reminder 1 <span class="required-hash">*</span></label>
                                <input type="number" min="0" class="form-control" name="low_stock_1"placeholder="Low Stock Reminder 1" value="{{ $data->low_stock_1 ?? '' }}">
                            </div>
                            <div>
                                <label class="sh_dec">Low Stock Reminder 2 <span class="required-hash">*</span></label>
                                <input type="number" min="0" class="form-control"  name="low_stock_2"placeholder="Low Stock Reminder 2" value="{{ $data->low_stock_2 ?? '' }}">
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

                    <input type="hidden" name="action" id="action"  value="{{ isset($data) && $data->is_draft != 0 ? 'draft' : 'submit' }}">

                                                                
                    <div class="row">
                        <div class="col-4 mt-3 d-grid">
                            <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" type="reset" onclick="remove_errors()">Reset</button>
                        </div>

                        <div class="col-4 mt-3 d-grid">
                            <button class="sh_btn btn btn-primary waves-effect waves-light submit-btn" name=""  value="draft" type="button" id="saveDraftBtn">Save as Draft</button>
                        </div>
                        <div class="col-4 mt-3 d-grid">
                            <button class="sh_btn btn btn-primary waves-effect waves-light submit-btn"  name="" value="submit" type="button" id="submitBtn">Submit</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


