@extends('layouts.master-layouts')

@section('title')
     E-Voucher: Digital Voucher
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
            {{ $type === 'campaign-voucher' ? 'Campaign Voucher Management' : 'E-Voucher: Digital Voucher Management' }}
        @endslot
    @endcomponent
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
            <div>
                <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddMemberVoucher">Push Voucher By Member ID</button>
                <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddParameterVoucher">Push Voucher By Parameter</button>
            </div>
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
                            <th data-field="quantity" data-filter-control="input" data-sortable="true">Total</th>
                            <th data-field="redeemed">Redeemed</th>
                            <th data-field="duration">Publish Date Duration</th>
                            <th data-field="image">Image</th>
                            <th data-field="cso_method">CSO Method</th>
                            <th data-field="is_draft">Is Draft</th>
                            <th data-field="status">Status</th>
                            <th data-field="created_at">Created On</th>

                            <th class="text-center" data-field="action" data-searchable="false">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
       <!--Push Parameter Voucher-->
  
    <!-- Create -->
    @can("$permission_prefix-create")
        @include('admin.evoucher.add-edit-modal')
    @endcan

    <div class="modal fade" id="AddParameterVoucher" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">   
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="sh_sub_title modal-title">Push Voucher By Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto;  max-height: 800px;">
                    <form enctype="multipart/form-data" class="z-index-1" method="POST" action="javascript:void(0)" id="AddParameterVoucherForm">
                        @csrf
                        <div class="row">                            
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="sh_dec" for="merchant_id">Push Voucher <span class="required-hash">*</span></label>
                                    <textarea id="voucher" type="text" class="sh_dec form-control" name="voucher" placeholder="Enter Push Voucher" readonly></textarea>                             
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="sh_dec" for="reward_id">Attached Voucher<span class="required-hash">*</span></label>
                                    <select class="sh_dec form-select reward_id" name="reward_id">
                                        <option class="sh_dec" value="">Select Attached Voucher</option>
                                         @if (isset($parameterReward))                                        
                                            @foreach ($parameterReward as $reward)
                                                <option value="{{ $reward->id }}" {{ isset($data) && $data->reward_id == $reward->id ? 'selected' : '' }}>
                                                    {{ $reward->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>    
                                </div>
                            </div>                           
                           
                            <!--- Interest Groups-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Interest Groups<span class="required-hash">*</span></label>

                                <div class="col-md-9 d-flex flex-wrap gap-3">

                                    @if(!empty($master_interest_groups) && count($master_interest_groups))

                                        <select class="form-select interest-group"
                                                name="interest_group[]"
                                                multiple
                                                style="width:100%">

                                            @foreach ($master_interest_groups as $group)
                                                <option value="{{ $group->id }}">
                                                    {{ $group->interest_group_name }}
                                                </option>
                                            @endforeach

                                        </select>

                                    @else
                                        <span class="text-muted">No interest group data available</span>
                                    @endif

                                </div>
                                <small class="text-danger d-block" data-field-validate="interest_group"></small>
                            </div>

                            <!--publish channel-->
                           <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Publish Channel<span class="required-hash">*</span></label>
                                <div class="col-md-9 d-flex flex-wrap gap-3">
                                    @if(count($master_membership_codes) > 0)
                                        <label class="d-flex align-items-center gap-1">
                                            <input type="checkbox"   name="publish_channels[]" value="All" class="form-check-input"{{ in_array('All', []) ? 'checked' : '' }}>
                                            All
                                        </label>
                                        @foreach ($master_membership_codes as $master_membership_code)
                                            <label class="d-flex align-items-center gap-1">
                                                <input type="checkbox"   name="publish_channels[]" value="{{ $master_membership_code->id }}" class="form-check-input"{{ in_array($master_membership_code->id, []) ? 'checked' : '' }}>
                                                {{ $master_membership_code->membershiptype_id }}
                                            </label>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No card type data available</span>
                                    @endif
                                </div>
                                <small class="text-danger d-block" data-field-validate="publish_channels"></small>
                            </div>

                            <!--card Type-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Card Type<span class="required-hash">*</span></label>
                                <div class="col-md-9 d-flex flex-wrap gap-3">
                                    @if(count($master_card_types) > 0)
                                        <label class="d-flex align-items-center gap-1">
                                            <input type="checkbox"   name="card_types[]" value="All" class="form-check-input"{{ in_array('All', []) ? 'checked' : '' }}>
                                            All
                                        </label>
                                        @foreach ($master_card_types as $master_card_type)
                                            <label class="d-flex align-items-center gap-1">
                                                <input type="checkbox"   name="card_types[]" value="{{ $master_card_type->id }}" class="form-check-input"{{ in_array($master_card_type->name, []) ? 'checked' : '' }}>
                                                {{ $master_card_type->card_type }} ({{ $master_card_type->card_description }})
                                            </label>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No card type data available</span>
                                    @endif
                                </div>
                                <small class="text-danger d-block" data-field-validate="card_types"></small>
                            </div>

                            <!--Marital Status-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Marital Status<span class="required-hash">*</span></label>
                                <div class="col-md-9 d-flex flex-wrap gap-3">
                                    @if(count($master_marital_statuses) > 0)
                                        <label class="d-flex align-items-center gap-1">
                                            <input type="checkbox"   name="marital_status[]" value="All" class="form-check-input"{{ in_array('All', []) ? 'checked' : '' }}>
                                            All
                                        </label>
                                        @foreach ($master_marital_statuses as $master_marital_status)
                                            <label class="d-flex align-items-center gap-1">
                                                <input type="checkbox"   name="marital_status[]" value="{{ $master_marital_status->id }}" class="form-check-input"{{ in_array($master_marital_status->name, []) ? 'checked' : '' }}>
                                                {{ $master_marital_status->label }}
                                            </label>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No marital status data available</span>
                                    @endif
                                </div>
                                <small class="text-danger d-block" data-field-validate="marital_status"></small>
                            </div>

                            <!--Gender-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Gender<span class="required-hash">*</span></label>
                                <div class="col-md-9 d-flex flex-wrap gap-3">
                                    @if(count($master_genders) > 0)
                                        <label class="d-flex align-items-center gap-1">
                                            <input type="checkbox"   name="gender[]" value="All" class="form-check-input"{{ in_array('All', []) ? 'checked' : '' }}>
                                            All
                                        </label>
                                        @foreach ($master_genders as $master_gender)
                                            <label class="d-flex align-items-center gap-1">
                                                <input type="checkbox"   name="gender[]" value="{{ $master_gender->id }}" class="form-check-input"{{ in_array($master_gender->name, []) ? 'checked' : '' }}>
                                                {{ $master_gender->label }}
                                            </label>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No gender data available</span>
                                    @endif
                                </div>
                                <small class="text-danger d-block" data-field-validate="gender"></small>
                            </div>

                            <!---Age-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Age<span class="required-hash">*</span></label>
                                <div class="col-md-9 d-flex flex-wrap gap-3 align-items-center">

                                    @php
                                        $selectedMode = $data->age_mode ?? 'All';
                                        $ageFrom = $data->age_from ?? '';
                                        $ageTo   = $data->age_to ?? '';
                                    @endphp

                                    <label class="d-flex align-items-center gap-1">
                                        <input type="radio" name="age_mode" value="All"
                                            class="form-check-input"
                                            {{ $selectedMode === 'All' ? 'checked' : '' }}>
                                        All
                                    </label>

                                    <label class="d-flex align-items-center gap-1">
                                        <input type="radio" name="age_mode" value="custom"
                                            class="form-check-input age-select"
                                            {{ $selectedMode === 'custom' ? 'checked' : '' }}>
                                        Select Age
                                    </label>

                                    <!-- From Age -->
                                    <label for="">From</label>
                                    <select name="age_from" class="form-select age-range" style="width:120px;" {{ $selectedMode === 'custom' ? '' : 'disabled' }}>
                                        <option value="">From</option>
                                        @for ($i = 1; $i <= 100; $i++)
                                            <option value="{{ $i }}" {{ $ageFrom == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>

                                    <!-- To Age -->
                                    <label for="">To</label>
                                    <select name="age_to" class="form-select age-range" style="width:120px;" {{ $selectedMode === 'custom' ? '' : 'disabled' }}>
                                        <option value="">To</option>
                                        @for ($i = 1; $i <= 100; $i++)
                                            <option value="{{ $i }}" {{ $ageTo == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>

                                </div>
                            </div>

                            <!--- Zones-->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Zones<span class="required-hash">*</span></label>
                                <div class="col-md-9 d-flex flex-wrap gap-3">
                                    @if(count($master_zones) > 0)
                                        <label class="d-flex align-items-center gap-1">
                                            <input type="checkbox"   name="zone[]" value="All" class="form-check-input"{{ in_array('All', []) ? 'checked' : '' }}>
                                            All
                                        </label>
                                        @foreach ($master_zones as $master_zone)
                                            <label class="d-flex align-items-center gap-1">
                                                <input type="checkbox"   name="zone[]" value="{{ $master_zone->id }}" class="form-check-input"{{ in_array($master_zone->zone_code, []) ? 'checked' : '' }}>
                                                {{ $master_zone->zone_name }}
                                            </label>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No interest group data available</span>
                                    @endif
                                </div>
                                <small class="text-danger d-block" data-field-validate="zone"></small>
                            </div>

                            <!-- Membership Joining Date -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Membership Joining Date<span class="required-hash">*</span> :</label>
                                <div class="col-md-9">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 d-flex align-items-center gap-2">
                                            <label class="mb-0">From</label>
                                            <input type="text" name="membership_join_from" class="form-control membership-month-from" placeholder="yyyy-MM">
                                        </div>
                                        <div class="col-md-6 d-flex align-items-center gap-2">
                                            <label class="mb-0">To</label>
                                            <input type="text" name="membership_join_to" class="form-control membership-month-to" placeholder="yyyy-MM">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Membership Expiry -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Membership Expiry<span class="required-hash">*</span> :</label>
                                <div class="col-md-9">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 d-flex align-items-center gap-2">
                                            <label class="mb-0">From</label>
                                            <input type="text" name="membership_expiry_from" class="form-control membership-month-from" placeholder="yyyy-MM">
                                        </div>
                                        <div class="col-md-6 d-flex align-items-center gap-2">
                                            <label class="mb-0">To</label>
                                            <input type="text" name="membership_expiry_to" class="form-control membership-month-to" placeholder="yyyy-MM">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Membership Renewable Date -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-3 fw-bold">Membership Renewable Date<span class="required-hash">*</span> :</label>
                                <div class="col-md-9">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 d-flex align-items-center gap-2">
                                            <label class="mb-0">From</label>
                                            <input type="text" name="membership_renewable_from" class="form-control membership-month-from" placeholder="yyyy-MM">
                                        </div>
                                        <div class="col-md-6 d-flex align-items-center gap-2">
                                            <label class="mb-0">To</label>
                                            <input type="text" name="membership_renewable_to" class="form-control membership-month-to" placeholder="yyyy-MM">
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
                    <form enctype="multipart/form-data" class="z-index-1" method="POST" action="{{ url('admin/evoucher/push-member-voucher') }}" id="member_voucher">
                        @csrf

                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="sh_dec" for="how_to_use">Push Voucher <span class="required-hash">*</span></label>
                                    <textarea id="push_voucher" type="text" class="sh_dec form-control" name="push_voucher" placeholder="Enter Push Voucher" readonly></textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="sh_dec" for="reward_type">Import Member ID <span class="required-hash">*</span></label>   
                                    <input id="memberId" type="file" class="form-control" name="memberId" accept=".xlsx,.xls">
                                    <div class="mt-1">
                                        <label class="small text-muted">
                                            Download demo file:
                                            <a href="{{ asset('demo-push-voucher.xlsx') }}" download class="text-primary fw-bold">
                                                Click here
                                            </a>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="sh_dec" for="reward_id">Attached Voucher<span class="required-hash">*</span></label>
                                    <select class="sh_dec form-select reward_id" name="reward_id">
                                        <option class="sh_dec" value="">Select Attached Voucher</option>
                                         @if (isset($memberReward))
                                            @foreach ($memberReward as $reward)
                                                <option value="{{ $reward->id }}" {{ isset($data) && $data->reward_id == $reward->id ? 'selected' : '' }}>
                                                    {{ $reward->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="sh_dec" for="method">Methods<span class="required-hash">*</span></label>
                                    <select class="sh_dec form-select method" name="method">
                                        <option class="sh_dec" value="">Select Method</option>
                                        @foreach (['pushWallet' => 'Push to Wallet', 'pushCatalogue' => 'Push to Catalogue'] as $key => $label)
                                            <option value="{{ $key }}" {{ isset($data) && $data->reward_id == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

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
        let participatingLocations = {};

        document.getElementById('csvFile').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();

            reader.onload = function (evt) {
                const data = new Uint8Array(evt.target.result);
                const workbook = XLSX.read(data, { type: 'array' });

                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const rows = XLSX.utils.sheet_to_json(firstSheet, { defval: '' });

                const count = rows.length;

                // show inventory field
                const inventoryDiv = document.querySelector('.inventory_qty');
                inventoryDiv.style.display = 'block';

                const inventoryInput = document.getElementById('inventory_qty');
                calculateSetQty();
                inventoryInput.value = count;

                inventoryInput.readOnly = true;

                $('#uploadedFileLink').text(file.name).attr('href', 'javascript:void(0)');
                $('#uploadedFile').removeClass('d-none').addClass('d-flex');            
            };

            reader.readAsArrayBuffer(file);
        });
        
        $(document).on('click', '#removeCsvFile', function () {
            $('#csvFile').val('');
            $('#uploadedFileLink').text('').attr('href', 'javascript:void(0)');
            $('#uploadedFile').removeClass('d-flex').addClass('d-none');
        });

    </script>

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
       function imagePreview(inputSelector, previewSelector) {
            $(document).on("change", inputSelector, function () {
                let file = this.files[0];
                let preview = $(previewSelector);

                if (!file) {
                    preview.attr("src", "").hide();
                    return;
                }

                let reader = new FileReader();
                reader.onload = function (e) {
                    preview.attr("src", e.target.result).show();
                };

                reader.readAsDataURL(file);
            });
        }

        imagePreview("#voucher_image", "#voucher_image_preview");
        imagePreview("#voucher_detail_img", "#voucher_detail_img_preview");

        $(document).on("change", ".inventory_type", function () {
            let modal = $(this).closest(".modal");
            toggleInventoryFields(modal);
        });

         $(document).on("change", ".clearing_method", function () {
            let modal = $(this).closest(".modal");
            toggleClearingFields(modal);
        });

        $(document).on('change', '#AddModal #participating_merchant_id', function () {

            const modal = $(this).closest('.modal');
            let merchantIds = $(this).val();

            if (!merchantIds || merchantIds.length === 0) {
                modal.find("#participating_merchant_location").empty();
                modal.find("#participating_section").hide();
                return;
            }

            if (!Array.isArray(merchantIds)) {
                merchantIds = [merchantIds];
            }

            loadParticipatingMerchantLocations(modal, merchantIds);
        });
        
        $(document).on('shown.bs.modal', '#AddModal', function () {
            initEditor();
            $('#clear_voucher_detail_img').hide();
            $('#clear_voucher_image').hide();
            initFlatpickr();
            initFlatpickrDate();
        });
    </script>
    
    <script>
        function initFlatpickr() {
            
            // bindStartEndFlatpickr(
            //     'input[name="publish_start"]',
            //     'input[name="publish_end"]'
            // );
            bindStartEndFlatpickr(
                'input[name="sales_start"]',
                'input[name="sales_end"]'
            );
            // bindStartEndFlatpickr(
            //     'input[name="redemption_start_date"]',
            //     'input[name="redemption_end_date"]'
            // );
        }


        // $(document).on("change", ".reward_id", function () {
        //     let id = $(this).val();
        //     let modal = $(this).closest(".modal"); // 🔥 key fix
        //     console.log(modal,'modal');
        
        //     let $publishStart = modal.find("input[name='publish_start_date']");
        //     let $publishEnd   = modal.find("input[name='publish_end_date']");
        
        //     if (!id) {
        //         $publishStart.val("");
        //         $publishEnd.val("");
        //         return;
        //     }
        
        //     $.ajax({
        //         url: "{{ url('admin/reward/get-dates') }}/" + id,
        //         type: "GET",
        //         success: function (res) {
        //             if (res.publish_start) {
        //                 $publishStart.val(res.publish_start);
        //             }
        //             if (res.publish_end) {
        //                 $publishEnd.val(res.publish_end);
        //             }
        //         }
        //     });
        // });

        document.getElementById("memberId").addEventListener("change", function (e) {

            let file = e.target.files[0];
            if (!file) return;

            let reader = new FileReader();

            reader.onload = function (event) {

                try {
                    let data = new Uint8Array(event.target.result);
                    let workbook = XLSX.read(data, { type: 'array' });

                    let firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    let rows = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });


                    let ids = [];

                    rows.forEach(row => {
                        let memberId = row[0];

                        if (
                            memberId &&
                            memberId.toString().trim() !== "" &&
                            memberId.toString().toLowerCase() !== "memberid"
                        ) {
                            ids.push(memberId);
                        }
                    });


                    document.getElementById("push_voucher").value = ids.join(", ");

                } catch (err) {
                    console.error("Excel read error:", err);
                }
            };

            reader.readAsArrayBuffer(file);
        });

        $(document).on("submit", "#member_voucher", function (e) {
            e.preventDefault();

            let form = $(this)[0];
            let formData = new FormData(form);

            $.ajax({
                url: form.action,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,              
                success: function (res) {
                    if (res.status == "success") { 
                        show_message(res.status, res.message);   
                        $("#member_voucher")[0].reset();
                        $("#push_voucher").val("");
                        $("#AddMemberVoucher").modal('hide');
                    } else {                   
                    }
                },
                error: function (response) {                 
                        
                    $(".error").html(""); // clear previous errors
                                
                    show_errors(response.responseJSON.errors);
                }
                
            });
        });

        $(document).on("submit", "#AddParameterVoucherForm", function (e) {
            e.preventDefault();

            let form = $(this)[0];
            let formData = new FormData(form);

            $.ajax({
                url: "{{ url('admin/evoucher/push-parameter-voucher') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,

                beforeSend: function () {
                    // Optional loading state
                },

                success: function (res) {
                    if (res.status === "success") {
                        show_message(res.status, res.message); 

                        $("#AddParameterVoucherForm")[0].reset();
                        $("#AddParameterVoucher").modal("hide");
                    }
                },

                error: function (xhr) {
                    if (xhr.status === 422) {
                        $('[data-field-validate]').html('');
                        $('.field-error').remove(); // remove appended single errors

                        $.each(xhr.responseJSON.errors, function (key, value) {

                            let baseKey = key.includes('.') ? key.split('.')[0] : key;
                            let errorContainer = $('[data-field-validate="'+baseKey+'"]');
                            if (errorContainer.length) {
                                errorContainer.html(value[0]);
                            } else {
                                let field = $('[name="'+baseKey+'"]');
                                if (field.length) {
                                    field.last().after(
                                        '<small class="text-danger field-error d-block">'+value[0]+'</small>'
                                    );
                                }
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: xhr.responseJSON?.message || "Something went wrong",
                        });
                    }
                }
            });
        });

        function resetFormById() {

            const modal = $('#AddModal');

            // -------------------------------
            // RESET FORM
            // -------------------------------
            const form = document.getElementById('add_frm');
            if (!form) return;

            form.reset();

            // -------------------------------
            // HIDE SECTIONS
            // -------------------------------
            modal.find(".participating_merchant").hide();
            modal.find("#voucher_image_preview").hide();
            modal.find("#participating_section").hide();
            modal.find("#participating_merchant_location").empty();
            modal.find(".file").hide();
            modal.find(".inventory_qty").hide();
            modal.find('.club-location-error').text('');
            // -------------------------------
            // 🔥 RESET SELECTED OUTLETS STATE
            // -------------------------------
            window.selectedOutletMapMerchant = {};               // clear JS memory
            modal.find("#selected_locations_summary").empty();
            modal.find("#selected_locations_wrapper").hide();
            modal.find("#selected_locations_hidden").empty();

            // -------------------------------
            // CLEAR FILE INPUTS
            // -------------------------------
            form.querySelectorAll('input[type="file"]').forEach(file => {
                file.value = '';
            });

            // -------------------------------
            // REMOVE VALIDATION ERRORS
            // -------------------------------
            modal.find('.is-invalid').removeClass('is-invalid');
            modal.find('.invalid-feedback').remove();

        }

        function formatToAmPm(datetimeLocal) {
            // Example input: 1979-08-16T04:12
            if (!datetimeLocal) return '';

            let d = new Date(datetimeLocal);
            if (isNaN(d.getTime())) return '';

            let yyyy = d.getFullYear();
            let mm   = String(d.getMonth() + 1).padStart(2, '0');
            let dd   = String(d.getDate()).padStart(2, '0');

            let hours = d.getHours();
            let minutes = String(d.getMinutes()).padStart(2, '0');
            let seconds = String(d.getSeconds()).padStart(2, '0');

            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12; // convert 0 → 12
            hours = String(hours).padStart(2, '0');

            return `${yyyy}-${mm}-${dd} ${hours}:${minutes}:${seconds} ${ampm}`;
        }
   
        $(document).on('change', 'input[type="checkbox"]', function () {

            let name = $(this).attr('name'); // e.g. publish_channels[]
            let $group = $('input[name="' + name + '"]');

            let $allCheckbox = $group.filter('[value="All"]');
            let $others = $group.not('[value="All"]');

            // If "All" is clicked
            if ($(this).val() === 'All') {
                let isChecked = $(this).is(':checked');
                $others.prop('checked', isChecked);
            }
            // If any other checkbox is clicked
            else {
                let allChecked = $others.length === $others.filter(':checked').length;
                $allCheckbox.prop('checked', allChecked);
            }
        });

        $(document).on('change', 'input[name="age_mode"]', function () {

            if ($(this).val() === 'custom') {
                $('.age-range').prop('disabled', false);
            } else {
                $('.age-range').prop('disabled', true).val('');
            }
        });

        $(document).on('change', 'select[name="age_from"]', function () {

            let fromVal = parseInt($(this).val(), 10);
            let $toSelect = $('select[name="age_to"]');

            // Reset To
            $toSelect.val('');

            // If no From selected, enable all To options
            if (isNaN(fromVal)) {
                $toSelect.find('option').prop('disabled', false);
                return;
            }

            // Disable To options less than From
            $toSelect.find('option').each(function () {
                let optionVal = parseInt($(this).val(), 10);

                if (isNaN(optionVal)) {
                    // Keep placeholder enabled
                    $(this).prop('disabled', false);
                } else {
                    $(this).prop('disabled', optionVal < fromVal);
                }
            });
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

        $('#AddParameterVoucher').on('shown.bs.modal', function () 
        {    
            // Interest Group Select2
            $('.interest-group').select2({
                dropdownParent: $('#AddParameterVoucher'),
                placeholder: "Select Interest Groups",
                width: '100%',
            });

            // Month pickers — FROM fields
            document.querySelectorAll('#AddParameterVoucher .membership-month-from').forEach(function (fromEl) {
                const row = fromEl.closest('.row');
                const toEl = row.querySelector('.membership-month-to');

                if (fromEl._flatpickr) fromEl._flatpickr.destroy();
                if (toEl && toEl._flatpickr) toEl._flatpickr.destroy();

                let toPicker;

                flatpickr(fromEl, {
                    plugins: [new monthSelectPlugin({ shorthand: true, dateFormat: "Y-m", altFormat: "F Y", theme: "light" })],
                    allowInput: false,
                    onChange(selectedDates) {
                        if (!selectedDates.length || !toPicker) return;
                        toPicker.set("minDate", selectedDates[0]);
                        toEl.removeAttribute("disabled");
                    }
                });

                if (toEl) {
                    toPicker = flatpickr(toEl, {
                        plugins: [new monthSelectPlugin({ shorthand: true, dateFormat: "Y-m", altFormat: "F Y", theme: "light" })],
                        allowInput: false,
                    });
                    toEl.setAttribute("disabled", true);
                }
            });
        });

        $('#AddParameterVoucher').on('hidden.bs.modal', function () {

            // Reset form
            document.getElementById('AddParameterVoucherForm').reset();

            // Clear all validation error messages
            $('[data-field-validate]').html('');
            $('.field-error').remove();

            // Clear textarea
            $('#voucher').val('');

            // Reset Select2
            $('.interest-group').val(null).trigger('change');

            // Reset checkboxes
            $('#AddParameterVoucher input[type="checkbox"]').prop('checked', false);

            // Reset radio to default All
            $('#AddParameterVoucher input[name="age_mode"][value="All"]').prop('checked', true);

            // Disable age range
            $('#AddParameterVoucher .age-range').prop('disabled', true).val('');

            // Destroy flatpickr + disable TO fields
            const membershipSelectors = [
                {
                    from: 'input[name="membership_join_from"]',
                    to:   'input[name="membership_join_to"]'
                },
                {
                    from: 'input[name="membership_expiry_from"]',
                    to:   'input[name="membership_expiry_to"]'
                },
                {
                    from: 'input[name="membership_renewable_from"]',
                    to:   'input[name="membership_renewable_to"]'
                },
            ];

            membershipSelectors.forEach(function (group) {

                const fromEl = document.querySelector('#AddParameterVoucher ' + group.from);
                const toEl   = document.querySelector('#AddParameterVoucher ' + group.to);

                if (fromEl && fromEl._flatpickr) {
                    fromEl._flatpickr.clear();
                    fromEl._flatpickr.destroy();
                }

                if (toEl && toEl._flatpickr) {
                    toEl._flatpickr.clear();
                    toEl._flatpickr.destroy();
                }

                // Reset input values + disable TO
                if (fromEl) fromEl.value = '';
                if (toEl) {
                    toEl.value = '';
                    toEl.setAttribute('disabled', true);
                }
            });
        });

    </script>
@endsection
