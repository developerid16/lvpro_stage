@extends('layouts.master-layouts')

@section('title')
     E-Voucher Management Listing
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
           E-Voucher Management Listing
        @endslot
    @endcomponent
    <style>
        .spin{
            display: none !important;
        }
    </style>
    <div class="card">      
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
                            <th data-field="balance" data-filter-control="input" data-sortable="true">Balance</th>
                            <th data-field="quantity" data-filter-control="input" data-sortable="true">Total</th>
                            <th data-field="issuance" data-filter-control="input" data-sortable="true">Issuance</th>
                            <th data-field="redeemed">Redeemed</th>
                            <th data-field="duration">Publish Date Duration</th>
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

    <!-- Create -->
    @can("$permission_prefix-create")
        @include('admin.evoucher.add-edit-modal')
    @endcan  
  


@endsection

@section('script')   

    <script>
        var participatingLocations = {};
        var ModuleBaseUrl = "{{ $module_base_url }}/";
        var RewardBaseUrl = "{{ $reward_base_url }}/";
        var DataTableUrl = RewardBaseUrl + "datatable";

        var digitalMerChants = [];

        function ajaxRequest(params) {
            $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
                $('.fixed-table-body .fixed-table-loading').removeClass('open');
                params.success(res)
            })
        }  

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
                        // $this.prop('checked', !$this.is(':checked'));
                        show_message(response.status, response.message);
                        location.reload();
                    }
                },
                error: function () {
                    // $this.prop('checked', !$this.is(':checked'));
                    show_message(false, 'Server error');
                }
            });

        });

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

       
        let selectedType = 'plus'; // default
        originalQty = parseInt($('#adjust_qty').val()) || 0;
        $('#proceedAdjustment').addClass('d-none'); // hide initially

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
            
            // 🔥 Show button only if changed
            if (currentQty !== originalQty) {
                $('#proceedAdjustment').removeClass('d-none');
            } else {
                $('#proceedAdjustment').addClass('d-none');
            }


            // Active styling
            $('#qty_plus_action, #qty_minus_action').removeClass('active');
            $(this).addClass('active');
        });


        $(document).on('click', '.stock-adjustment', function () {

            $('#qty_error').text('').addClass('d-none');
            let id        = $(this).data('id');
            let name      = $(this).data('name');
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

            $('#stockAdjustmentModal').modal('show');
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
        
        $(document).on("change", ".voucher_detail_img", function (e) {
            const modal = $(this).closest('.modal');
            const file = this.files[0];

            if (!file) return;

            const preview = modal.find('#voucher_detail_img_preview');
            const clearBtn = modal.find('#clear_voucher_detail_img');

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.attr('src', e.target.result).show();
                clearBtn.show();
            };

            reader.readAsDataURL(file);
        });

       
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

            const modal      = $(this).closest('.modal');   // ✅ modal context
            const merchantIds = $(this).val();               // array or null

            if (merchantIds && merchantIds.length > 0) {
                modal.find("#participating_section").show();
                modal.find("#participating_merchant_location").show();

                loadParticipatingMerchantLocations(modal,merchantIds);
            } else {
                // only hide LEFT section, NOT selected summary
                modal.find("#participating_merchant_location").empty();
                modal.find("#participating_section").hide();
            }
        });
        
        $(document).on('shown.bs.modal', '#EditModal', function () {
            initEditor();
            initFlatpickr();    
            initFlatpickrDate();          
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
            bindStartEndFlatpickr(
                'input[name="redemption_start_date"]',
                'input[name="redemption_end_date"]'
            );
        }
    </script>
    
  
@endsection
