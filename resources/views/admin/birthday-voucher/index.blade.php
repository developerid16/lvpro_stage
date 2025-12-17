@extends('layouts.master-layouts')

@section('title')
    Birthday Voucher
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
            {{ $type === 'campaign-voucher' ? 'Campaign Voucher Management' : 'Birthday Voucher Management' }}
        @endslot
    @endcomponent


    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
            {{-- <h4 class="card-title mb-0">Rewards</h4> --}}
           
            @can("$permission_prefix-create")
            <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal" onclick="resetFormById()"><i class="mdi mdi-plus"></i>Add New</button>
            @endcan
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
                            <th data-field="no_of_keys" data-filter-control="input" data-sortable="true">Amount</th>
                            <th data-field="balance">Balance</th>
                            <th data-field="quantity" data-filter-control="input" data-sortable="true">Total</th>
                            <th data-field="total_redeemed" data-filter-control="input" data-sortable="true">Issuance</th>
                            <th data-field="redeemed">Redeemed</th>
                            <th data-field="duration">Duration</th>
                            <th data-field="month">Voucher Creation</th>
                            <th data-field="image">Image</th>
                            <th data-field="created_at">Created On</th>

                            {{-- <th data-field="status" data-filter-control="select" data-sortable="false">Status</th> --}}

                            <th class="text-center" data-field="action" data-searchable="false">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Create -->
    @can("$permission_prefix-create")
        @include('admin.birthday-voucher.add-edit-modal')
    @endcan
    <!-- end modal -->

  
  

@endsection

@section('script')
    <script>
        var ModuleBaseUrl = "{{ $module_base_url }}/";
        var type = "{{ $type }}";
        var DataTableUrl = ModuleBaseUrl + "datatable";
        var digitalMerChants = [];

        function ajaxRequest(params) {
            params.data.type = type
            $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
                $('.fixed-table-body .fixed-table-loading').removeClass('open');
                params.success(res)
            })
        }  
       
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

        function toggleInventoryFields(modal) {
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

        function toggleClearingFields(modal) {
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
                // External Code OR Merchant Code → show PARTICIPATING MERCHANT
                merchantField.show();
                locationField.hide();
            }
        }

        $(document).on("change", ".clearing_method", function () {
            let modal = $(this).closest(".modal");
            toggleClearingFields(modal);
        });

        $('#participating_merchant_id').on('change', function () {
            let merchantId = $(this).val();

            if (merchantId) {
                $("#participating_merchant_location").show();
                loadParticipatingMerchantLocations(merchantId);
            } else {
                $("#participating_merchant_location").hide();
            }
        });

        function loadParticipatingMerchantLocations(merchantId) {
            $.ajax({
                url: "{{ url('admin/reward/get-participating-merchant-locations') }}/" + merchantId,
                type: "GET",
                success: function (res) {

                    if (res.status === 'success') {

                        let html = '';
                        let i = 1;

                        html += `<label class="sh_dec"><b>Participating Merchant Outlets</b></label>`;

                        // Wrapper ONLY ONCE
                        html += `<div id="participating_location_wrapper" class="row gx-3 gy-3">`;

                        res.locations.forEach(loc => {

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
                                                class="form-check-input">
                                        </div>
                                    </div>
                                </div>
                            `;

                            i++;
                        });

                        html += `
                            </div><div id="participating_merchant_locations_error" class="text-danger mt-1"></div>`; // close row wrapper

                        $("#participating_merchant_location").html(html);
                    }

                }
            });
        }
    </script>
    <script>
        $(document).on("change", ".reward_id", function () {
            let id = $(this).val();

            if (!id) {
                $("input[name='publish_start']").val("");
                $("input[name='publish_end']").val("");
                return;
            }

            $.ajax({
                url: "{{ url('admin/reward/get-dates') }}/" + id,
                type: "GET",
                success: function (res) {
                    if (res.publish_start) {
                        $("input[name='publish_start']").val(res.publish_start);
                    }

                    if (res.publish_end) {
                        $("input[name='publish_end']").val(res.publish_end);
                    }
                }
            });
        });

        $(document).on("change", "#merchant_id", function () {

            let merchantId = $(this).val();
            let locationDropdown = $(".club_location");

            locationDropdown.html('<option value="">Loading...</option>');

            if (!merchantId) {
                locationDropdown.html('<option value="">Select Club Location</option>');
                return;
            }

            $.ajax({
                url: "{{ url('admin/birthday-voucher/get-club-locations') }}",
                type: "GET",
                data: { merchant_id: merchantId },
                success: function (res) {

                    console.log(res.data,'res');
                    
                    locationDropdown.empty();
                    locationDropdown.append('<option value="">Select Club Location</option>');

                    if (res.status === 'success' && res.data.length > 0) {

                        res.data.forEach(function (loc) {
                            locationDropdown.append(
                                `<option value="${loc.id}">${loc.name}</option>`
                            );
                        });

                    } else {
                        locationDropdown.append('<option value="">No locations found</option>');
                    }
                },
                error: function () {
                    locationDropdown.html('<option value="">Error loading locations</option>');
                }
            });
        });

        function resetFormById() {
            let modal = $('#AddModal').closest(".modal");
        
            $(".participating_merchant").hide();
            $("#voucher_image_preview").hide();
            $("#participating_merchant_location").hide();
            $(".file").hide();
            $(".inventory_qty").hide();
            $('.club_location').val(null).trigger('change');
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

   
    </script>
    <script src="{{ URL::asset('build/js/crud.js') }}"></script>
@endsection
