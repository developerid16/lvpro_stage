@extends('layouts.master-layouts')

@section('title')
    Treats & Deals Management Listing
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Admin
        @endslot
        @slot('li_1_link')
            {{ url('/') }}
        @endslot
        @slot('title')
            Treats & Deals Management Listing
        @endslot
    @endcomponent


    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
          
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="sh_table table table-bordered" id="bstable" data-toggle="table"
                    data-page-list="[100, 500, 1000, 2000, All]" data-search-time-out="1200" data-page-size="100"
                    data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="true" data-search="false"
                    data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false"
                    data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                    <thead>
                        <tr>
                            <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75"
                                data-width-unit="px" data-searchable="false">Sr. No.</th>
                          
                            <th data-field="name" data-filter-control="input" data-sortable="true" data-escape="true">Name</th>
                            <th data-field="reward_type" data-filter-control="input" data-sortable="true" data-escape="true">Reward Type</th>
                            <th data-field="amount" data-sortable="true">Amount</th>
                            <th data-field="balance">Balance</th>
                            <th data-field="quantity" data-sortable="true">Total</th>
                            <th data-field="purchased" data-sortable="true">Issuance</th>
                            <th data-field="redeemed">Redeemed</th>
                            <th data-field="duration">Duration</th>
                            <th data-field="image">Image</th>
                            <th data-field="created_at">Created On</th>
                            <th data-field="is_featured">Is Featured</th>
                            <th data-field="hide_catalogue">
                                Hide From <br> Catalogue
                            </th> 
                            <th class="text-center" data-field="action" data-searchable="false">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="stockAdjustmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Inventory Management</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="stockAdjustmentForm">
                        <input type="hidden" id="adjust_id">
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3">Stock Details</h6>

                            <div class="table-responsive">
                                <table class="table align-middle mb-0" style="border: 1px solid #9fa8b6;">
                                    <tbody>
                                        <tr>
                                            <th width="35%" style="background:#f8f9fa; border: 1px solid #9fa8b6;">Name</th>
                                            <td id="show_name" style="border: 1px solid #9fa8b6;"></td>
                                        </tr>
                                        <tr>
                                            <th style="background:#f8f9fa; border: 1px solid #9fa8b6;">Original Quantity</th>
                                            <td style="border: 1px solid #9fa8b6;">
                                                <span class="badge bg-primary fs-6" id="inventory_qty"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="background:#f8f9fa; border: 1px solid #9fa8b6;">Current Quantity</th>
                                            <td style="border: 1px solid #9fa8b6;">
                                                <span class="badge bg-info fs-6" id="purchased_qty"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                       <div class="mb-3">
                            <label class="form-label fw-semibold">Adjust Quantity</label>

                            <div class="d-flex align-items-center gap-3">

                                <!-- Minus Button -->
                                <button type="button" class="btn btn-danger" id="qty_minus_action" value="minus"> <i class="mdi mdi-minus"></i></button>

                                <!-- Quantity Input -->
                                <input type="text" id="adjust_qty" class="form-control text-center" value="1" min="1" style="width:120px;">
                                
                                
                                <!-- Plus Button -->
                                <button type="button" class="btn btn-success" id="qty_plus_action" value="plus"><i class="mdi mdi-plus"></i></button>
                            </div>
                            <small id="qty_error" class="text-danger mt-1 d-none"></small>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="proceedAdjustment">Proceed</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="LocationStockModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Inventory Management</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="location_adjust_reward_id">

                    <div class="table-responsive">
                        <div class="mb-3 p-2 rounded">
                            <strong>Name: </strong>
                            <span id="location_reward_name" class="text-primary fw-semibold"></span>
                        </div>

                        <table class="table table-bordered align-middle text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th>Location</th>
                                    <th>Original Quantity</th>
                                    <th>Current Quantity</th>
                                    <th>Adjust Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="location_stock_table">
                                <!-- dynamic rows -->
                            </tbody>
                        </table>
                    </div>

                    <small id="location_qty_error" class="text-danger d-none"></small>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="locationProceedAdjustment">Proceed</button>
                </div>

            </div>
        </div>
    </div>


    <!-- Create -->
    @can("$permission_prefix-create")
        @include('admin.reward.add-edit-modal')
    @endcan
    <!-- end modal -->
@endsection

@section('script')

    <script>
        var participatingLocations = {};
        var ModuleBaseUrl = "{{ $module_base_url }}/";
        var RewardBaseUrl = "{{ $reward_base_url }}/";
        var DataTableUrl = RewardBaseUrl + "datatable";

        var digitalMerChants = [];
        let selectedType = 'plus'; // default
        

        function ajaxRequest(params) {
            $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
                $('.fixed-table-body .fixed-table-loading').removeClass('open');
                params.success(res)
            })
        }  

        $(document).on('change', '.featured-toggle-switch', function () {

            let rewardId = $(this).data('id');
            let isChecked = $(this).is(':checked') ? 1 : 0;
            let $switch = $(this);

            $.ajax({
                url: RewardBaseUrl + 'toggle-featured',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: rewardId,
                    is_featured: isChecked
                },
                success: function (res) {
                    show_message(true, 'Featured status updated');
                },
                error: function () {

                    // revert toggle if failed
                    $switch.prop('checked', !isChecked);

                    show_message(false, 'Server error');
                }
            });

        });


        $(document).on('change', '.hide-catalogue-switch', function () {
            let $this = $(this);

            $.ajax({
                url: RewardBaseUrl + 'hide-catalogue',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: $this.data('id'),
                    status: $this.is(':checked') ? 1 : 0
                },
                success: function (response) {
                    if (response.status) {
                        $this.prop('checked', !$this.is(':checked'));
                        show_message(response.status, response.message);
                        location.reload();
                    }
                },
                error: function () {
                    $this.prop('checked', !$this.is(':checked'));
                    show_message(false, 'Server error');
                }
            });

        });

        function buildLocationRow(location, hide_catalogue, hide_cat_time) {

            let disableAll   = false;
            let disableMinus = false;

            if (hide_catalogue == 0) {
                disableAll = true;
            }

            if (hide_catalogue == 1) {

                if (hide_cat_time) {

                    let hideDate  = new Date(hide_cat_time);
                    let allowTime = new Date(hideDate.getTime() + (60 * 60 * 1000));
                    let now       = new Date();

                    if (now < allowTime) {
                        disableMinus = true; // before 60 minutes
                    }

                } else {
                    disableMinus = true; // safety fallback
                }
            }

            return `
                <tr data-location-id="${location.id}">
                    <td>${location.name}</td>
                    <td>
                        <span class="badge bg-primary fs-6">${location.inventory_qty}</span>
                    </td>
                    <td>
                        <span class="badge bg-info fs-6">${location.current_qty}</span>
                    </td>
                    <td>
                        <div class="d-flex justify-content-center align-items-center gap-2">

                            <button type="button"
                                class="btn btn-sm btn-danger location-minus"
                                ${disableAll || disableMinus ? 'disabled' : ''}>
                                <i class="mdi mdi-minus"></i>
                            </button>

                            <input type="text"
                                class="form-control text-center location-qty"
                                value="${location.inventory_qty}"
                                style="width:90px;"
                                ${disableAll ? 'disabled' : ''}>

                            <button type="button"
                                class="btn btn-sm btn-success location-plus"
                                ${disableAll ? 'disabled' : ''}>
                                <i class="mdi mdi-plus"></i>
                            </button>

                        </div>
                    </td>
                </tr>
            `;
        }


        $('#qty_plus_action, #qty_minus_action').on('click', function () {

            selectedType = $(this).val(); // plus or minus

            let currentQty = parseInt($('#adjust_qty').val()) || 0;

            if (selectedType === 'plus') {
                currentQty += 1;
            } else {
                if (currentQty > 1) {
                    currentQty -= 1;
                }
            }

            $('#adjust_qty').val(currentQty);

            // Active styling
            $('#qty_plus_action, #qty_minus_action').removeClass('active');
            $(this).addClass('active');
        });

        $(document).on('click', '.location-plus', function () {

            let input = $(this).closest('tr').find('.location-qty');
            let value = parseInt(input.val()) || 0;
            input.val(value + 1);
        });

        $(document).on('click', '.location-minus', function () {

            let input = $(this).closest('tr').find('.location-qty');
            let value = parseInt(input.val()) || 0;

            if (value > 1) {
                input.val(value - 1);
            }
        });


        $(document).on('click', '.stock-adjustment', function () {

            let rewardId   = $(this).data('id');
            let rewardType = $(this).data('type');
            let name = $(this).data('name');
            if (rewardType == 0) {

                // Open normal stock modal
                $('#stockAdjustmentModal').modal('show');

            } else if (rewardType == 1) {

                $('#location_adjust_reward_id').val(rewardId);
                 $('#location_reward_name').text(name);
                $('#location_stock_table').html('');
                $('#location_qty_error').addClass('d-none').text('');

                // Fetch location stock via AJAX
                $.get(RewardBaseUrl + 'get-location-stock/' + rewardId, function (res) {

                    res.data.forEach(function (location) {
                        $('#location_stock_table').append(buildLocationRow(location,res.hide_catalogue,res.hide_cat_time));
                    });

                    $('#LocationStockModal').modal('show');
                });    
            }

            $('#qty_error').text('').addClass('d-none');
            let id        = $(this).data('id');
            let inventory = $(this).data('inventory');
            let current   = $(this).data('purchased');
            let hide      = $(this).data('hide');
            let hideTime  = $(this).data('hide-time');

            $('#adjust_id').val(id);
            $('#show_name').text(name);
            $('#inventory_qty').text(inventory);
            $('#purchased_qty').text(current);
            $('#adjust_qty').val(inventory);

            // Reset buttons
            $('#qty_plus_action, #qty_minus_action').removeClass('active');
            $('#qty_plus_action').addClass('active');
            selectedType = 'plus';

            // ===============================
            // CONDITION CHECK
            // ===============================

            if (hide == 0) {
                // Hide catalogue OFF → disable quantity
                $('#adjust_qty').prop('disabled', true);
                $('#qty_plus_action').prop('disabled', true);
                $('#qty_minus_action').prop('disabled', true);
            } 
            else {
                $('#adjust_qty').prop('disabled', false);
                $('#qty_plus_action').prop('disabled', false);
                $('#qty_minus_action').prop('disabled', false);

                if (hideTime) {
                    let hideDate = new Date(hideTime);
                    let allowTime = new Date(hideDate.getTime() + (60 * 60 * 1000));
                    let now = new Date();

                    if (now < allowTime) {
                        // Not allowed to minus yet
                        $('#qty_minus_action').prop('disabled', true);
                    }
                }
            }

        });

        
        $('#proceedAdjustment').on('click', function () {

            let type = selectedType;
            let qty  = parseInt($('#adjust_qty').val());
            let id = $('#adjust_id').val();
           $('#qty_error').addClass('d-none').text('');
            if (!qty || qty <= 0) {
                show_message(false, 'Enter valid quantity');
                return;
            }

            $.ajax({
                url: RewardBaseUrl + 'adjustment',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: id,
                    type: type,
                    qty: qty
                },
                success: function (res) {
                    if(res.status == true){
                        $('#qty_error').addClass('d-none').text('');
                        show_message(res.status, res.message);
                        $('#stockAdjustmentModal').modal('hide');
                    }else{
                        $('#qty_error').addClass('d-none').text(res.message);
                    }
                },
                 error: function (xhr) {

                    if (xhr.responseJSON && xhr.responseJSON.message) {

                        $('#qty_error')
                            .removeClass('d-none')
                            .text(xhr.responseJSON.message);

                    }
                }
            });
        });

        $('#locationProceedAdjustment').on('click', function () {

            let rewardId = $('#location_adjust_reward_id').val();
            let locations = [];

            $('#location_stock_table tr').each(function () {

                let locationId = $(this).data('location-id');
                let qty = $(this).find('.location-qty').val();

                locations.push({
                    location_id: locationId,
                    qty: qty
                });
            });

            $.ajax({
                url: RewardBaseUrl + 'location-stock-adjustment',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    reward_id: rewardId,
                    locations: locations
                },
                success: function (res) {

                    $('#LocationStockModal').modal('hide');
                    location.reload();
                },
                error: function (xhr) {

                    $('#location_qty_error')
                        .removeClass('d-none')
                        .text(xhr.responseJSON.message);
                }
            });
        });

       
        // Show live preview on file select
        $(document).on("change", "#voucher_image", function () {
            let preview = $("#voucher_image_preview");
            let file = this.files[0];

            if (file) {
                let reader = new FileReader();

                reader.onload = function (e) {
                    preview.attr("src", e.target.result).show();
                };

                reader.readAsDataURL(file);
            } else {
                // When user clears the file input
                preview.attr("src", "").hide();
            }
        });       
        
        $(document).on("change", "#voucher_detail_img", function () {
            let preview = $("#voucher_detail_img_preview");
            let file = this.files[0];

            if (file) {
                let reader = new FileReader();

                reader.onload = function (e) {
                    preview.attr("src", e.target.result).show();
                };

                reader.readAsDataURL(file);
            } else {
                // When user clears the file input
                preview.attr("src", "").hide();
            }
        });       

        $(".max_order").hide();
        $("#common_section").hide();

        $('.reward_type').on('change', function () {
            let type = $(this).val();
            $("#common_section").show(); // show physical fields
            if (type == "1") {
                $("#physical").show(); // show physical fields
                $("#location_section").show(); // also show location section
                $(".where_use").show(); // also show location section
                $(".max_qty").show(); // also show location section
                $(".max_order").hide(); // also show location section
                $("#digital").hide(); // show physical fields
                $("#participating_merchant_location").hide(); // also show location section
                $('#collection_reminder_title').html('Send Collection Reminder <span class="required-hash">*</span>');
                
                $('#collection_reminder_label').contents().last()[0].textContent = ' Collection Reminder';

                 let merchantId = $('#merchant_id').val();
                if(merchantId){
                    loadLocations(merchantId);
                }
                
            }else if (type == "0") {
                $("#digital").show(); // show physical fields
                $("#participating_merchant_location").show(); 
                 $(".where_use").hide();
                $(".max_qty").hide(); // also show location section
                $(".max_order").show(); // also show location section// also show location section
                $("#physical").hide(); // show physical fields
                $("#location_section").hide(); // also show location section
                $('#collection_reminder_title').html('Send Reminder <span class="required-hash">*</span>');

                $('#collection_reminder_label').contents().last()[0].textContent = ' Reminder';

            }else {
                $("#common_section").hide();
                $("#physical").hide();
                $("#digital").hide();
                $(".max_qty").show(); // also show location section
                $(".max_order").hide(); // also show location section
                $("#location_section").hide();
                $("#participating_merchant_location").hide();
                $("#location_wrapper").html("");
            }


        });     

        $('#merchant_id').on('change', function () {
            let merchantId = $(this).val();
            let rewardType = $('.reward_type').val();

            $("#location_wrapper").html("");

            if (rewardType == "1" && merchantId) {
                $("#location_section").show();
                loadLocations(merchantId);
            } else {
                $("#location_section").hide();
            }
        });


        function loadLocations(merchantId) {
            $("#location_wrapper").html("");

            $.ajax({
                url: "{{ url('admin/reward/get-locations') }}/" + merchantId,
                type: "GET",
                success: function (res) {
                    $("#location_wrapper").html("");
                    
                    if (res.status === 'success') {

                            let hideStatus = res.hide_catalogue; // get hide value

                            let html = '';
                            html += `<label class="sh_dec"><b>Locations </b><span style="color:#f46a6a;">*</span></label>`;
                            html += `<div class="row gx-3 gy-3">`;
                                

                            res.data.forEach(loc => {

                                let disabledAttr = hideStatus == 0 ? 'disabled' : '';

                                html += `
                                    <div class="col-md-6 col-12">
                                        <div class="location-box d-flex align-items-center p-2"
                                            style="border:1px solid #e9e9e9; border-radius:6px;">

                                            <div class="d-flex align-items-center me-auto">
                                                <label class="mb-0 me-2 font-12" style="margin-top:4px;">
                                                    ${loc.name}
                                                </label>
                                                <input type="checkbox" 
                                                    name="locations[${loc.id}][selected]" 
                                                    value="1"
                                                    class="form-check-input"
                                                    ${disabledAttr}>
                                            </div>

                                            <div class="d-flex align-items-center ms-3">
                                                <label class="mb-0 me-2 font-12">Inventory Qty</label>
                                                <input type="number" min="0"
                                                    class="form-control"
                                                    name="locations[${loc.id}][inventory_qty]"
                                                    placeholder="Qty"
                                                    style="max-width:100px"
                                                    ${disabledAttr}>
                                            </div>

                                        </div>
                                    </div>
                                `;
                            });

                            html += `</div><div id="locations_error" class="text-danger mt-1"></div>`;

                            $("#location_section").html(html);
                        }
                    


                }
            });
        }

        $(document).on('shown.bs.modal','#EditModal', function () {
            const $modal = $(this);

            function togglePhysicalSectionInModal() {
                const val = $modal.find('.reward_type').val();
                if (val === undefined) return;
                $modal.find('#physical').toggle(val == "1");
            }

            // bind change (scoped to this modal)
            $modal.find('.reward_type').off('change.togglePhysical').on('change.togglePhysical', togglePhysicalSectionInModal);

            // initial toggle for edit mode
            togglePhysicalSectionInModal();
        });

        $(document).on("change", ".inventory_type", function () {
            let modal = $(this).closest(".modal");
            toggleInventoryFields(modal);
        });
        
        $(document).on("change", ".clearing_method", function () {
            let modal = $(this).closest(".modal");
            toggleClearingFields(modal);
        });

        $(document).on('change', '#AddModal #participating_merchant_id', function () {
            const merchantIds = $(this).val(); // array or null
            const modal      = $(this).closest('.modal');
            if (merchantIds) {
                modal.find("#participating_section").show();
                modal.find("#participating_merchant_location").show();

                loadParticipatingMerchantLocations(modal,merchantIds);
            } else {
                modal.find("#participating_merchant_location").empty();
                modal.find("#participating_section").hide();
            }
        });    

        function resetFormById() {
            const modal = $('#AddModal');
            toggleInventoryFields(modal);
            toggleClearingFields(modal);
            $(".max_order").hide();
            $("#common_section").hide();
            $("#voucher_image_preview").hide();
            $("#voucher_detail_img_preview").hide();
            $(".file").hide();
            $(".inventory_qty").hide();
            $("#location_section").hide();
          

            window.selectedOutletMap = {};               // clear JS memory
            modal.find("#selected_locations_summary").empty();
            modal.find("#selected_locations_wrapper").hide();
            modal.find("#selected_locations_hidden").empty();
            let form = document.getElementById('add_frm');
            if (!form) return;

            // BASIC RESET
            form.reset();

            // CLEAR FILE INPUTS
            form.querySelectorAll('input[type="file"]').forEach(file => {
                file.value = '';
            });

        

            // OPTIONAL: hide error messages
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        }

        function calculateVoucherSet() {

            let usualPrice   = parseFloat($('#usual_price').val());
            let voucherValue = parseFloat($('#voucher_value').val());

            // Guard clauses
            if (isNaN(usualPrice) || isNaN(voucherValue) || voucherValue <= 0) {
                $('#voucher_set').val('');
                return;
            }

            let voucherSet = usualPrice / voucherValue;

            // If you want integer only (recommended)
            $('#voucher_set').val(Math.floor(voucherSet));
            calculateSetQty();
        }

        // Run on input change
        $(document).on('input', '#usual_price, #voucher_value', function () {
            calculateVoucherSet();
        });

        function initFlatpickr() {
            bindStartEndFlatpickr(
                'input[name="publish_start"]',
                'input[name="publish_end"]'
            );
            bindStartEndFlatpickr(
                'input[name="sales_start"]',
                'input[name="sales_end"]'
            );
        }       
        
        $(document).on('shown.bs.modal', '#AddModal', function () {
            initEditor();
            $('#clear_voucher_detail_img').hide();
            $('#clear_voucher_image').hide();
            $(".where_use").hide();
            initFlatpickr();
            initFlatpickrDate();
        });
      
        // when inventory changes
        $(document).on('input', '#inventory_qty', calculateSetQty);

        // when voucher_set changes
        $(document).on('input', '#voucher_set', calculateSetQty);

        $(document).on('input', '#voucher_value', calculateSetQty);

        function calculateSetQty() {
            let inventoryQty = parseFloat($('#inventory_qty').val());
            let voucherSet   = parseFloat($('#voucher_set').val());
            if (!isNaN(inventoryQty) && !isNaN(voucherSet) && voucherSet > 0) {
                $('#set_qty').val(Math.floor(inventoryQty / voucherSet));
            } else {
                $('#set_qty').val('');
            }
        }
    </script>     
@endsection
