 @php
    $existingLocationsData = $existingLocationsData ?? [];
    $selectedCompanies = $selectedCompanies ?? (isset($data->company_id) ? explode(',', $data->company_id) : []);
    $data = $data ?? null;
@endphp
<script>   
    function initRewardModal() {
        const existingLocationsData = @json($existingLocationsData);
        const selectedCompanies     = @json($selectedCompanies);
        const rewardType            = @json($data->reward_type ?? '');
        const inventoryType         = @json($data->inventory_type ?? '');
        toggleRewardType(rewardType);
        toggleInventory(inventoryType);
       

        // Init Select2 inside modal
        $('#{{ isset($data->id) ? "EditModal" : "AddModal" }} .select2').select2({
            dropdownParent: $('#{{ isset($data->id) ? "EditModal" : "AddModal" }}')
        });

        // -------------------
        // Reward Type Toggle
        // -------------------
        function toggleRewardType(type) {

            $(".digital-voucher-div, .physical-voucher-div").removeAttr("style");

            if (type == "0") {
                $(".digital-voucher-div").removeClass("d-none");
                $(".physical-voucher-div").addClass("d-none");

                loadLocationsDigital(true);

            } else if (type == "1") {
                $(".physical-voucher-div").removeClass("d-none");
                $(".digital-voucher-div").addClass("d-none");

                loadLocationsPhysical(true);
            }
        }


        $(".reward_type").on("change", function () {
            toggleRewardType($(this).val());
        });

        // -------------------------
        // Inventory (File / Qty)
        // -------------------------
       

        function toggleInventory(type) {
            if (type == 0) {
                $(".InventoryQtyDiv").removeClass("d-none");
                $(".InventoryFileDiv").addClass("d-none");
            } 
            else if (type == 1) {
                $(".InventoryQtyDiv").addClass("d-none");
                $(".InventoryFileDiv").removeClass("d-none");
            } 
        }


        $(".inventory_type").on("change", function () {
            toggleInventory($(this).val());
        });

        // -------------------------
        // Load Locations Digital
        // -------------------------
        function loadLocationsDigital(isEdit=false) {

            let modal = $("#EditModal");   // ensure we target the edit modal only
            let ids = modal.find("#company_ids").select2('val');

            if (!ids || ids.length === 0) return;

            $.ajax({
                url: ModuleBaseUrl + "locations-by-company",
                type: "GET",
                data: { company_id: JSON.stringify(ids), type: "digital" },
                traditional: true,
                success: function (response) {
                    modal.find("#locations_for_digital").html(response.html);
                    if (isEdit) applyEditLocationSelections(existingLocationsData, 'digital');
                }
            });
        }

        // -------------------------
        // Load Locations Physical
        // -------------------------
        function loadLocationsPhysical(isEdit = false) {

            let modal = $("#EditModal");   // ensure we target the edit modal only
            let ids = modal.find("#company_id").select2('val');

            console.log("Selected:", ids);

            if (!ids || ids.length === 0) return;

            $.ajax({
                url: ModuleBaseUrl + "locations-by-company",
                type: "GET",
                data: { company_id: JSON.stringify(ids),  type: "physical" },
                traditional: true,
                success: function (response) {

                    console.log("Response:", response);

                    // ⭐ append into correct modal, not global DOM
                    modal.find("#locations").html(response.html);

                    if (isEdit) {
                        applyEditLocationSelections(existingLocationsData,'physical');
                    }
                }
            });
        }


        // --------------------------------------------------------
        // APPLY EXISTING LOCATION DATE BLOCKS (EDIT MODE)
        // --------------------------------------------------------
        function applyEditLocationSelections(existingLocationsData,type) {
            if (!existingLocationsData || Object.keys(existingLocationsData).length === 0) {
                return;
            }

            Object.keys(existingLocationsData).forEach(function (locId) {

                let data = existingLocationsData[locId];
                var checkbox = '';
                // 1️⃣ Locate checkbox
                if(type == 'physical'){
                     checkbox = $(`input[name="locations[${locId}][selected]"]`);
                }
                else if(type == 'digital'){
                     checkbox = $(`input[name="locations_digital[${locId}][selected]"]`);
                }

                if (checkbox.length) {
                    checkbox.prop("checked", true);
                } else {
                    console.log("Checkbox NOT found for location " + locId);
                    return;
                }

                // 2️⃣ Locate parent row
                let row = checkbox.closest(".row.mb-2");

                // 3️⃣ Fill inventory qty (PHYSICAL voucher)
                if (data.qty !== undefined) {
                    let qtyInput = $(`input[name="locations[${locId}][inventory_qty]"]`);
                    qtyInput.val(data.qty);
                }

                // 4️⃣ Append date block (DIGITAL)
                if (row.length && data.publish_start_date !== undefined) {
                    row.after(buildPrefilledDateBlock(locId, data));
                }
            });
        }

        function buildPrefilledDateBlock(locationId, values) {
            return `
        <div class="location-date-block mt-2" data-location-id="${locationId}"
            style="padding:10px; border:1px dashed #e0e0e0;">

            <div class="row">

                <div class="col-md-3 mb-3">
                    <label>Publish Start Date *</label>
                    <input type="date" class="form-control"
                        name="locations_digital[${locationId}][publish_start_date]"
                        value="${values.publish_start_date ?? ''}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Publish Start Time *</label>
                    <input type="time" class="form-control"
                        name="locations_digital[${locationId}][publish_start_time]"
                        value="${values.publish_start_time ?? ''}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Publish End Date</label>
                    <input type="date" class="form-control"
                        name="locations_digital[${locationId}][publish_end_date]"
                        value="${values.publish_end_date ?? ''}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Publish End Time</label>
                    <input type="time" class="form-control"
                        name="locations_digital[${locationId}][publish_end_time]"
                        value="${values.publish_end_time ?? ''}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Sales Start Date *</label>
                    <input type="date" class="form-control"
                        name="locations_digital[${locationId}][sales_start_date]"
                        value="${values.sales_start_date ?? ''}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Sales Start Time *</label>
                    <input type="time" class="form-control"
                        name="locations_digital[${locationId}][sales_start_time]"
                        value="${values.sales_start_time ?? ''}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Sales End Date</label>
                    <input type="date" class="form-control"
                        name="locations_digital[${locationId}][sales_end_date]"
                        value="${values.sales_end_date ?? ''}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Sales End Time</label>
                    <input type="time" class="form-control"
                        name="locations_digital[${locationId}][sales_end_time]"
                        value="${values.sales_end_time ?? ''}">
                </div>

            </div>
        </div>`;
        }
    
        $(document).on("change", "#EditModal #company_id", function () {
            loadLocationsByCompanySearch();
        });

        $(document).on("change", "#EditModal #company_ids", function () {
            loadLocationsByCompanyForDigitalSearch();
        });

        function loadLocationsByCompanySearch() {

            let modal = $("#EditModal");
            let container = modal.find("#locations");
            let methodBlock = container.find(".method"); // ⭐ FINAL BLOCK
            let companyId = modal.find("#company_id").select2("val");

            if (!companyId || companyId.length === 0) return;

            $.ajax({
                url: ModuleBaseUrl + "locations-by-company",
                type: "GET",
                data: { companyId: JSON.stringify(ids), type: "physical" },
                traditional: true,
                success: function (response) {

                    let temp = $("<div>").html(response.html);

                    temp.find(".row.mb-2").each(function () {

                        let newRow = $(this);
                        let newLocId = getLocationId(newRow);
                        if (!newLocId) return;

                        // Check if already exists
                        let exists = container.find(`input[name="locations[${newLocId}][selected]"]`).length > 0;

                        if (!exists) {
                            methodBlock.before(newRow); // ⭐ insert above `.method`
                        }
                    });
                }
            });
        }

        function getLocationId(row) {
            let checkbox = row.find("input[type='checkbox'][name^='locations']");
            if (!checkbox.length) return null;

            let name = checkbox.attr("name"); // locations[1][selected]
            let match = name.match(/locations\[(\d+)\]/);

            return match ? match[1] : null;
        }
      
        function loadLocationsByCompanyForDigitalSearch() {

            let modal = $("#EditModal");
            let container = modal.find("#locations_for_digital");
            let methodBlock = container.find(".method"); 
            let companyId = modal.find("#company_ids").select2("val");

            if (!companyId || companyId.length === 0) return;

            $.ajax({
                url: ModuleBaseUrl + "locations-by-company",
                type: "GET",
                data: { company_id: companyId, type: "digital" },
                traditional: true,
                success: function (response) {

                    let temp = $("<div>").html(response.html);

                    temp.find(".row.mb-2").each(function () {

                        let newRow = $(this);
                        let newLocId = getDigitalLocationId(newRow);
                        if (!newLocId) return;

                        // Check if already exists
                        let exists = container.find(`input[name="locations_digital[${newLocId}][selected]"]`).length > 0;

                        if (!exists) {
                            methodBlock.before(newRow);
                        }
                    });
                }
            });
        }

        function getDigitalLocationId(row) {
            let checkbox = row.find("input[type='checkbox'][name^='locations_digital']");
            if (!checkbox.length) return null;

            let name = checkbox.attr("name"); // locations_digital[3][selected]
            let match = name.match(/locations_digital\[(\d+)\]/);

            return match ? match[1] : null;
        }

    }

    $(".InventoryQtyDiv").addClass("d-none");
    $(".InventoryFileDiv").addClass("d-none");   
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
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="description">Description <span class="required-hash">*</span></label>
                                <input id="description" type="text" class="sh_dec form-control" name="description"
                                    placeholder="Enter description" value="{{ $data->description ?? '' }}">
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

                                @foreach ($tiers as $key => $tier)
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="sh_dec" for="tier_{{ $tier->id }}">{{ $tier->tier_name }}
                                                Price <span class="required-hash">*</span></label>
                                            <input id="tier_{{ $tier->id }}" type="number"
                                                class="sh_dec form-control" name="tier_{{ $tier->id }}"
                                                placeholder="Enter {{ $tier->tier_name }} Price"
                                                value="{{ isset($data->tierRates[$key]['price']) ? $data->tierRates[$key]['price'] : '' }}">
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>

                        <hr class="dashed">

                        <div class="col-12 physical-voucher-div d-none">

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="company_id">Merchant <span
                                                class="required-hash">*</span></label>
                                        <select class="sh_dec form-select select2" name="company_id[]" id="company_id"
                                             multiple>
                                            <option value="">Select Merchant</option>
                                            @if (isset($companies))
                                              
                                                @foreach ($companies as $company)
                                                    <option class="sh_dec" value="{{ $company->id }}"
                                                        @if(in_array($company->id, $selectedCompanies)) selected @endif>
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

                        <div class="col-12 digital-voucher-div d-none">

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="sh_dec" for="company_id">Participating merchant <span class="required-hash">*</span></label>
                                        <select class="sh_dec form-select select2" name="company_id[]" id="company_ids" multiple>
                                        {{-- <select class="sh_dec form-select select2" name="company_id[]" id="company_ids" @disabled(isset($data->id)) multiple></select> --}}
                                            <option value="">Select merchant</option>
                                            @if (isset($companies))
                                              
                                                @foreach ($companies as $company)
                                                    <option class="sh_dec" value="{{ $company->id }}"
                                                        @if(in_array($company->id, $selectedCompanies)) selected @endif>
                                                        {{ $company->name }}
                                                    </option>
                                                @endforeach

                                            @endif
                                        </select>
                                        <div class="sh_dec_s error" id="company_id_error"></div>
                                    </div>
                                </div>
                                <div class="col-12" id="locations_for_digital">
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
                                        <div class="sh_dec_s error" id="clearing_method_error"></div>
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

                                    <div class="mb-3 InventoryQtyDiv" id="InventoryQtyDiv">
                                        <label class="sh_dec" for="inventory_qty"> Inventory Quantity <span
                                                class="required-hash">*</span></label>
                                        <input id="inventory_qty" type="number" class="sh_dec form-control"
                                            name="inventory_qty" placeholder="Enter Inventory Quantity"
                                            value="{{ $data->inventory_qty ?? '' }}">
                                    </div>
                                    <div class="mb-3 InventoryFileDiv" id="InventoryFileDiv">
                                        <label class="sh_dec" for="csvFile">File <span class="required-hash">*</span></label>

                                        
                                        {{-- File input (used for uploading replacement) --}}
                                        <input id="csvFile" 
                                        type="file" 
                                        class="sh_dec form-control" 
                                        name="csvFile" 
                                        accept=".csv,.xls,.xlsx">
                                        {{-- If file exists, show file name + link --}}
                                        @if(!empty($data->csvFile))
                                            <div class="mb-2">
                                                <strong>Current File:</strong>
                                                <a href="{{ asset('rewardvoucher/' . $data->csvFile) }}" 
                                                target="_blank" 
                                                class="text-primary">
                                                    {{ $data->csvFile }}
                                                </a>
                                            </div>
                                        @endif
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

