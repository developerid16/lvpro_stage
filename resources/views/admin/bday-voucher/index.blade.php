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
                            <th data-field="reward_type" data-filter-control="input" data-sortable="true" data-escape="true">Reward Type</th>
                            <th data-field="no_of_keys" data-filter-control="input" data-sortable="true">Amount</th>
                            <th data-field="balance">Balance</th>
                            <th data-field="quantity" data-filter-control="input" data-sortable="true">Total</th>
                            <th data-field="total_redeemed" data-filter-control="input" data-sortable="true">Issuance</th>
                            <th data-field="redeemed">Redeemed</th>
                            <th data-field="duration">Duration</th>
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
        @include('admin.bday-voucher.add-edit-modal')
    @endcan
    <!-- end modal -->

      <!--Push Parameter Voucher-->
    <div class="modal fade" id="AddParameterVoucher" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">   
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="sh_sub_title modal-title">Push Voucher By Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto;  max-height: 800px;">
                    <form enctype="multipart/form-data" class="z-index-1" method="POST" action="javascript:void(0)" id="add_frm">
                        @csrf                      
                        <div class="row">                            
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="sh_dec" for="merchant_id">Push Voucher <span class="required-hash">*</span></label>
                                    <textarea name="voucher" id="voucher" cols="30" rows="10"></textarea>                              
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="sh_dec" for="reward_id">Attached Voucher<span class="required-hash">*</span></label>
                                    <select class="sh_dec form-select reward_id" name="reward_id">
                                        <option class="sh_dec" value="">Select Attached Voucher</option>
                                         @if (isset($rewards))                                        
                                            @foreach ($rewards as $reward)
                                                <option value="{{ $reward->id }}" {{ isset($data) && $data->reward_id == $reward->id ? 'selected' : '' }}>
                                                    {{ $reward->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>    
                                </div>
                            </div>                           
                           
                           <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Publish Channel</label>
                                <div class="col-md-8 d-flex flex-wrap gap-3">
                                    @php
                                        $publishChannels = [
                                            'All', 'AS', 'AT', 'AV', 'JH', 'JL', 'JV', 
                                            'LF', 'OA', 'OE', 'OF', 'SH', 'SL', 'SV', 
                                            'VT', 'VR', 'FA'
                                        ];

                                        $selected = isset($data) ? explode(',', $data->publish_channels ?? '') : [];
                                    @endphp
                                    @foreach ($publishChannels as $channel)
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox" name="publish_channels[]" value="{{ $channel }}" class="form-check-input me-1" {{ in_array($channel, $selected) ? 'checked' : '' }}>
                                            <label class="m-0">{{ $channel }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!--card Type-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Card Type</label>
                                <div class="col-md-8 d-flex flex-wrap gap-3">
                                    @php
                                        $cardTypes = ['All', 'Credit Card', 'Debit Card', 'Safra'];
                                        $selected = isset($data) ? explode(',', $data->card_types ?? '') : [];
                                    @endphp
                                    @foreach ($cardTypes as $type)
                                        <label class="d-flex align-items-center gap-1">
                                            <input type="checkbox" name="card_types[]" value="{{ $type }}"  class="form-check-input" {{ in_array($type, $selected) ? 'checked' : '' }}>
                                            {{ $type }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                           <!--Dependent Type-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Dependent Type</label>
                                <div class="col-md-8 d-flex flex-wrap gap-3">
                                    @php
                                        $dependentTypes = ['All', 'Spouse', 'Child'];
                                        $selected = isset($data) ? explode(',', $data->dependent_types ?? '') : [];
                                    @endphp
                                    @foreach ($dependentTypes as $type)
                                        <label class="d-flex align-items-center gap-1">
                                            <input type="checkbox"  name="dependent_types[]" value="{{ $type }}" class="form-check-input" {{ in_array($type, $selected) ? 'checked' : '' }}>
                                            {{ $type }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!--Marital Status-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Marital Status</label>
                                <div class="col-md-8 d-flex flex-wrap gap-3">
                                    @php
                                        $maritalStatus = ['All', 'Single', 'Married', 'Divorced', 'Widowed', 'Not Stated'];
                                        $selected = isset($data) ? explode(',', $data->marital_status ?? '') : [];
                                    @endphp
                                    @foreach ($maritalStatus as $status)
                                        <label class="d-flex align-items-center gap-1">
                                            <input type="checkbox"  name="marital_status[]" value="{{ $status }}"   class="form-check-input" {{ in_array($status, $selected) ? 'checked' : '' }}>
                                            {{ $status }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!--Gender-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Gender</label>
                                <div class="col-md-8 d-flex flex-wrap gap-3">
                                    @php
                                        $genders = ['All', 'Male', 'Female'];
                                        $selected = isset($data) ? explode(',', $data->gender ?? '') : [];
                                    @endphp

                                    @foreach ($genders as $gender)
                                        <label class="d-flex align-items-center gap-1">
                                            <input type="checkbox"   name="gender[]" value="{{ $gender }}" class="form-check-input"{{ in_array($gender, $selected) ? 'checked' : '' }}>
                                            {{ $gender }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>


                            <!---Age-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-4 fw-bold">Age</label>
                                <div class="col-md-8 d-flex flex-wrap gap-3 align-items-center">
                                    @php
                                        $selectedMode = $data->age_mode ?? 'All';
                                    @endphp
                                    <label class="d-flex align-items-center gap-1">
                                        <input type="radio" name="age_mode" value="All" class="form-check-input"  {{ $selectedMode == 'All' ? 'checked' : '' }}>
                                        All
                                    </label>
                                    <label class="d-flex align-items-center gap-1">
                                        <input type="radio" name="age_mode" value="custom" class="form-check-input age-select" {{ $selectedMode == 'custom' ? 'checked' : '' }}>
                                        Select Age
                                    </label>
                                    <input type="date" name="age_from" class="form-control age-range"  value="{{ $data->age_from ?? '' }}"   style="width: 150px;"{{ $selectedMode == 'custom' ? '' : 'disabled' }}>
                                    <input type="date"   name="age_to"  class="form-control age-range" value="{{ $data->age_to ?? '' }}"   style="width: 150px;" {{ $selectedMode == 'custom' ? '' : 'disabled' }}>
                                </div>
                            </div>


                           
                            <!-- 🔥 LOCATION DATE BLOCK — insert before the Usual Price field -->
                            <div id="location_date_container" class="col-12">
                                <label class="sh_dec"><b>Date & Time</b></label>

                                <div class="location-date-block mt-2" data-location-id="1" style="padding:10px; border:1px dashed #e0e0e0;">
                                    
                                    <div class="row">

                                        <div class="col-12 col-md-3">
                                            <div class="mb-3 sh_dec">
                                                <label class="sh_dec font-12">Publish Start Date & Time <span class="required-hash">*</span></label>
                                                <input type="datetime-local"  class="form-control" name="publish_start"   value="{{ isset($data->publish_start_date) ? $data->publish_start_date . 'T' . $data->publish_start_time : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <div class="mb-3 sh_dec">
                                                <label class="sh_dec font-12">Publish End Date & Time</label>
                                                <input type="datetime-local" class="form-control"  name="publish_end"  value="{{ isset($data->publish_end_date) ? $data->publish_end_date . 'T' . $data->publish_end_time : '' }}">
                                            </div>
                                        </div>

                                        <!-- Sales fields -->
                                        <div class="col-12 col-md-3">
                                            <div class="mb-3 sh_dec">
                                                <label class="sh_dec font-12">Sales Start Date & Time <span class="required-hash">*</span></label>
                                                <input type="datetime-local"  class="form-control" name="sales_start" value="{{ isset($data->sales_start_date) ? $data->sales_start_date . 'T' . $data->sales_start_time : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <div class="mb-3 sh_dec">
                                                <label class="sh_dec font-12">Sales End Date & Time</label>
                                                <input type="datetime-local" class="form-control" name="sales_end"  value="{{ isset($data->sales_end_date) ? $data->sales_end_date . 'T' . $data->sales_end_time : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>                                  
                        </div>
                        <div class="row">
                            <div class="col-6 mt-3 d-grid">
                                <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" data-bs-dismiss="modal" aria-label="Close">Back</button>
                            </div>
                            <div class="col-6 mt-3 d-grid">
                                <button class="sh_btn btn btn-primary waves-effect waves-light" type="submit">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--Push member voucher-->
    <div class="modal fade" id="AddMemberVoucher" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">   
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="sh_sub_title modal-title">Push Voucher By Member ID</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto;  max-height: 800px;">
                    <form enctype="multipart/form-data" class="z-index-1" method="POST" action="javascript:void(0)" id="add_frm">
                        @csrf
                       
                       
                        <div class="row">                            
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="sh_dec" for="merchant_id">Push Voucher <span class="required-hash">*</span></label>
                                    <textarea name="voucher" id="voucher" cols="30" rows="5" readonly></textarea>                              
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="sh_dec" for="reward_type">Import Member ID <span class="required-hash">*</span></label>   
                                    <input id="memberId" type="file" class="sh_dec form-control" name="memberId" accept=".xlxs,.xls">
                                </div>
                            </div>
                            
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="sh_dec" for="reward_id">Attached Voucher<span class="required-hash">*</span></label>
                                    <select class="sh_dec form-select reward_id" name="reward_id">
                                        <option class="sh_dec" value="">Select Attached Voucher</option>
                                         @if (isset($rewards))                                        
                                            @foreach ($rewards as $reward)
                                                <option value="{{ $reward->id }}" {{ isset($data) && $data->reward_id == $reward->id ? 'selected' : '' }}>
                                                    {{ $reward->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>    
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
                                                <label class="sh_dec font-12">Redemption Start Date & Time <span class="required-hash">*</span></label>
                                                <input type="datetime-local"  class="form-control" name="sales_start" value="{{ isset($data->sales_start_date) ? $data->sales_start_date . 'T' . $data->sales_start_time : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3 sh_dec">
                                                <label class="sh_dec font-12">Redemption End Date & Time</label>
                                                <input type="datetime-local" class="form-control" name="sales_end"  value="{{ isset($data->sales_end_date) ? $data->sales_end_date . 'T' . $data->sales_end_time : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                            
                                                                    
                        {{-- </div> --}}
                        <div class="row">
                            <div class="col-6 mt-3 d-grid">
                                <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" data-bs-dismiss="modal" aria-label="Close">Back</button>
                            </div>
                            <div class="col-6 mt-3 d-grid">
                                <button class="sh_btn btn btn-primary waves-effect waves-light" type="submit">Send Push Voucher</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

  

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
                                                <span class="fw-bold">Outlet ${i}:</span> ${loc.name}
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
                url: "{{ url('admin/bday-voucher/get-club-locations') }}",
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
