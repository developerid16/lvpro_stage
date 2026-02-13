 @php
    $data = $data ?? null;
    $selectedLocation = $data->club_location ?? ''; // value from DB
@endphp
<script>   

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

    $(document).on('change', '#EditModal #csvFile', function () {
        if (this.files.length > 0) {
            $('#EditModal #uploadedFileLink').text(this.files[0].name).attr('href', 'javascript:void(0)');
            $('#EditModal #uploadedFile').removeClass('d-none').addClass('d-flex');
        }
    });

    $(document).on('click', '#EditModal #removeCsvFile', function () {
        $('#EditModal #csvFile').val('');
        $('#EditModal #uploadedFileLink').text('').attr('href', 'javascript:void(0)');
        $('#EditModal #uploadedFile').removeClass('d-flex').addClass('d-none');
    });
        
    $(document).on('shown.bs.modal', '#EditModal', function () {
        initTinyMCE();
        $(document).on('keyup change input','#EditModal #inventory_qty, #EditModal #voucher_set, #EditModal #voucher_value', editCalculateSetQty);

        let modal = $(this).closest('.modal');
        initFlatpickrDate(this);  
        bindMonthFlatpickrEdit(this, 'input[name="from_month"]', 'input[name="to_month"]'); 
        toggleFieldsBasedOnMonth(modal);
       
        editToggleInventoryFields(modal);
        editToggleClearingFields(modal);

        // if (modal.attr("id") === "EditModal" && window.selectedOutletMap && Object.keys(window.selectedOutletMap).length > 0) {
        //     editParticipatingMerchantLocations(modal);
        // }

        let merchantIds = modal.find('#participating_merchant_id').val();

        if (merchantIds && merchantIds.length > 0) {

            modal.find("#participating_section").show();
            modal.find("#participating_merchant_location").show();

            loadParticipatingMerchantLocationsBday(modal, merchantIds);
        }

        $('.club-location-error').text('');

        const $fileInput = $('#EditModal #csvFile');
        const $inventoryDiv = $('#EditModal .inventory_qty');
        const $inventoryInput = $('#EditModal #inventory_qty');

         // EDIT MODE
        if ($inventoryInput.val() !== '') {
            $inventoryDiv.show();
            $inventoryInput.prop('readonly', true);
        }

        // ON FILE CHANGE
        $fileInput.on('change', function () {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();

            reader.onload = function (e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });

                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const rows = XLSX.utils.sheet_to_json(firstSheet, { defval: '' });

                $inventoryDiv.show();
                $inventoryInput.val(rows.length).prop('readonly', true);
            };

            reader.readAsArrayBuffer(file);
        });
        
        forceInventoryReadonly(modal);
    });

    $(document).on('change', '#EditModal #participating_merchant_id', function () {

        $('.club-location-error').text('');

        const modal       = $(this).closest('.modal');
        const merchantIds = $(this).val();

        if (merchantIds && merchantIds.length > 0) {

            modal.find("#participating_section").show();
            modal.find("#participating_merchant_location").show();

            let clubId = modal.find('.merchant-dropdown').data('club'); 
            loadParticipatingMerchantLocationsBday(modal, clubId, merchantIds);

        } else {

            modal.find("#participating_merchant_location").empty();
            modal.find("#participating_section").hide();
        }
    });


    function toggleFieldsBasedOnMonth(modal) {

        let selectedMonth = "{{ $data->month ?? '' }}";
        let inventoryType = "{{ $data->inventory_type ?? '' }}"; // must be 0 or 1

        let today = new Date();
        let currentMonth = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');

        // =====================
        // RESET EVERYTHING
        // =====================
        modal.find('input, textarea, select, input[type="file"]').prop('disabled', false);

        modal.find('#month').addClass('readonly');
        // =====================
        // APPLY RULE
        // =====================
        if (selectedMonth === currentMonth) {

            // Disable EVERYTHING
            modal.find('input, textarea, select, input[type="file"]').prop('disabled', true);

            // Allow ONLY inventory_qty
            if(inventoryType == 0){
                modal.find('#inventory_qty').prop('disabled', false);
                modal.find('.inventory_type').prop('disabled', false);
                modal.find('.inventory_type').addClass('readonly');
            }

            // Always allow system fields
            modal.find('input[name="_token"], input[name="_method"], input[name="parent_type"]').prop('disabled', false);
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
                <form enctype="multipart/form-data" class="z-index-1" method="POST" action="javascript:void(0)" id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}" data-id="{{ $data->id ?? '' }}">
                    @csrf
                    @if (isset($type))
                        <input type="hidden" name="parent_type" value="{{ $type }}">
                    @endif
                    @if (isset($data->id))
                        @method('PATCH')
                    @endif
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3 sh_dec">
                                <label class="sh_dec">
                                    Reward Creation: From Month<span class="required-hash">*</span>
                                </label>
                                <div class="d-flex">
                                    <input type="text" id="from_month" class="form-control" name="from_month" value="{{ isset($data->from_month) ? $data->from_month : '' }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec">
                                    To Month<span class="required-hash">*</span>
                                </label>
                                <input type="text" id="to_month" class="form-control" name="to_month"value="{{ isset($data->to_month) ? $data->to_month : '' }}">
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
                                <label class="sh_dec" for="name">Reward Name <span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name" placeholder="Enter name" value="{{ $data->name ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec">Description <span class="required-hash">*</span></label>
                                <textarea class="sh_dec form-control wysiwyg" name="description" id="">
                                    {{ $data->description ?? '' }}
                                </textarea>
                            </div>
                        </div>

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec">How to use <span class="required-hash">*</span></label>
                                <textarea class="sh_dec form-control wysiwyg" name="how_to_use" id="">
                                    {{ $data->how_to_use ?? '' }}
                                </textarea>
                            </div>
                        </div>


                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec">Voucher T&C <span class="required-hash">*</span></label>
                                <textarea class="sh_dec form-control wysiwyg" name="term_of_use" id="">
                                    {{ $data->term_of_use ?? '' }}
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
                                <input id="" type="text" class="sh_dec form-control readonly" name="voucher_type" value="Birthday Voucher" readonly>                            
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
                                    value="{{ $data?->voucher_validity ?? '' }}"
                                    placeholder="YYYY-MM-DD"
                                />
                            </div>
                        </div>
                         <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="inventory_type">Inventory Type <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select inventory_type" name="inventory_type">
                                    <option class="sh_dec" value="">Select Voucher Type</option>
                                    <option class="sh_dec" value="0" {{ isset($data->inventory_type) && $data->inventory_type == '0' ? 'selected' : '' }}> System Generated Code</option>
                                    <option class="sh_dec" value="1" {{ isset($data->inventory_type) && $data->inventory_type == '1' ? 'selected' : '' }}> Merchant Uploaded Code</option>
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
                                <label class="sh_dec" for="inventory_qty">Inventory Quantity <span class="required-hash">*</span></label>    
                                <input id="inventory_qty" type="number" min="0"  placeholder="Enter Inventory Quantity" class="sh_dec form-control"   name="inventory_qty" value="{{ $data->inventory_qty ?? '' }}"> 
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
                                <input id="set_qty" type="number" min="0" readonly   placeholder="Voucher Set Quantity" class="sh_dec form-control"   name="set_qty" value="{{ $data->set_qty ?? '' }}"> 
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="clearing_method">Clearing Methods <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select clearing_method " name="clearing_method" id="clearing_method">
                                    <option class="sh_dec" value="">Select Clearing Method</option>                                   
                                    <option class="sh_dec" value="2" {{ isset($data->clearing_method) && $data->clearing_method == '2' ? 'selected' : '' }}>
                                        Merchant Code 
                                    </option>
                                </select>
                            </div>
                        </div>     
                    </div>

                    <div id="location_with_outlet" class="accordion">

                        @foreach($club_location as $club)

                        <div class="card mb-2 accordion-item">

                            <!-- CLUB HEADER -->
                            <div class="card-header d-flex justify-content-between align-items-center accordion-header"
                                id="heading_{{ $club->id }}">

                                <strong>{{ $club->name }}</strong>

                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center ms-3">
                                        <label class="mb-0 me-2 font-12">Inventory Quantity</label>
                                        <input type="number"
                                            min="0"
                                            class="form-control"
                                            name="locations[{{ $club->id }}][inventory_qty]"
                                            style="max-width: 56px; max-height: 30px;">
                                    </div>

                                    <!-- Toggle Button -->
                                    <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center"
                                            style="max-height: 30px; max-width: 30px;"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#club_body_{{ $club->id }}"
                                            aria-expanded="false">
                                        <i class="mdi mdi-chevron-down toggle-icon font-size-18"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- COLLAPSE SECTION -->
                            <div id="club_body_{{ $club->id }}"
                                class="accordion-collapse collapse club-body"
                                data-bs-parent="#location_with_outlet">

                                <div class="accordion-body p-3">

                                    <!-- Merchant Dropdown -->
                                    <div class="mb-2">
                                        <select class="form-select merchant-dropdown"
                                                data-club="{{ $club->id }}">
                                            <option value="">Select Participating Merchant</option>
                                            @foreach($participating_merchants as $pm)
                                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Outlet Container (UNCHANGED) -->
                                    <div class="outlet-container" id="outlet_container_{{ $club->id }}">
                                        <div class="row mt-3 participating-section">
                                            <div class="selected-locations-hidden"></div>

                                            <div class="col-md-7">
                                                <div class="participating-merchant-location"></div>
                                            </div>

                                            <div class="col-md-5 mb-2">
                                                <div class="selected-locations-wrapper" style="display:none;">
                                                    <label class="fw-bold">Selected Outlets</label>
                                                    <div class="selected-locations-summary form-control"
                                                        style="min-height:120px; background:#f8f9fa;">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                        @endforeach
                    </div>
                   <div class="club-location-error text-danger mb-2"></div>

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
                                <label class="sh_dec">Low Stock Reminder 1 <span class="required-hash"></span></label>
                                <input type="number" min="0" class="form-control" name="low_stock_1"placeholder="Low Stock Reminder 1" value="{{ $data->low_stock_1 ?? '' }}">
                            </div>
                            <div>
                                <label class="sh_dec">Low Stock Reminder 2 <span class="required-hash"></span></label>
                                <input type="number" min="0" class="form-control"  name="low_stock_2"placeholder="Low Stock Reminder 2" value="{{ $data->low_stock_2 ?? '' }}">
                            </div>
                        </div>                               
                    </div>
                                                     
                    {{-- <div class="row">
                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" type="reset" onclick="remove_errors()">Reset</button>
                        </div>
                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn btn btn-primary waves-effect waves-light" type="submit">Submit</button>
                        </div>
                    </div> --}}

                    <div class="row">
                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" type="reset" onclick="remove_errors()">Reset</button>
                        </div>

                        
                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn btn btn-primary waves-effect waves-light submit-btn"  name="" value="submit" type="button" id="submitBtn">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


