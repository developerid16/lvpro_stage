@extends('layouts.master-layouts')

@section('title')
    Rewards
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
            {{ $type === 'campaign-voucher' ? 'Campaign Voucher Management' : 'Rewards Management' }}
        @endslot
    @endcomponent


    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
            {{-- <h4 class="card-title mb-0">Rewards</h4> --}}
            @can("$permission_prefix-create")
                <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"><i
                        class="mdi mdi-plus"></i>
                    Add New</button>
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
                            <th data-field="code" data-filter-control="input" data-sortable="true" data-escape="true">Code
                            </th>
                            <th data-field="name" data-filter-control="input" data-sortable="true" data-escape="true">Name
                            </th>
                            <th data-field="no_of_keys" data-filter-control="input" data-sortable="true">Amount</th>
                            <th data-field="balance">Balance</th>
                            <th data-field="quantity" data-filter-control="input" data-sortable="true">Total</th>
                            <th data-field="total_redeemed" data-filter-control="input" data-sortable="true">Issuance</th>
                            <th data-field="redeemed">Redeemed</th>
                            <th data-field="duration">Duration</th>
                            <th data-field="image">Image</th>
                            <th data-field="created_at">Created On</th>

                            <th data-field="status" data-filter-control="select" data-sortable="false">Status</th>

                            <th class="text-center" data-field="action" data-searchable="false">Action</th>
                        </tr>
                    </thead>
                </table>
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

        function voucherValueChange() {
            var vv = $('#voucher_value').val();
            var amount = $('#amount').val();
            if(vv < 1 || amount < 1){
                $('#voucher_set').val(0);
                return;
            }
            $('#voucher_set').val(Math.floor(amount / vv));
        }
        // Function to load locations by company
        function loadLocationsByCompany() {
            var companyId = $('#company_id').val();
            var locations = $('#locations');
            var selectedLocations = [];


            locations.empty();


            if (companyId) {
                $.ajax({
                    url: ModuleBaseUrl + 'locations-by-company',
                    type: 'GET',
                    data: {
                        company_id: companyId,
                        type: 'physical'
                    },
                    success: function(response) {
                        if (response.status === 'success' && response.locations.length > 0) {
                            locations.html(response.html);
                        } else {

                            // locationSelect.append('<option value="">No locations available</option>');
                        }
                    },
                    error: function() {
                        //  locationSelect.empty();
                        // locationSelect.append('<option value="">Error loading locations</option>');
                    }
                });
            } else {
                // locationSelect.empty();
                // locationSelect.append('<option value="">Select Company First</option>');
            }
        }

        function loadLocationsByCompanyForDigital() {
            var companyId = $('#company_ids').val();
            var locations = $('#locations_for_digital');
            var selectedLocations = [];


            locations.empty();


            if (companyId) {
                $.ajax({
                    url: ModuleBaseUrl + 'locations-by-company',
                    type: 'GET',
                    data: {
                        company_id: companyId,
                        type: 'digital'
                    },
                    success: function(response) {
                        if (response.status === 'success' && response.locations.length > 0) {
                            locations.html(response.html);
                        } else {

                            // locationSelect.append('<option value="">No locations available</option>');
                        }
                    },
                    error: function() {
                        //  locationSelect.empty();
                        // locationSelect.append('<option value="">Error loading locations</option>');
                    }
                });
            } else {
                // locationSelect.empty();
                // locationSelect.append('<option value="">Select Company First</option>');
            }
        }

        function moveSelectedLocation() {

            var selectedLocations = [];
            $('.locations_digital_checkbox:checked').each(function() {
                selectedLocations.push({
                    value: $(this).val(),
                    id: $(this).attr('data-location-id'),
                    name: $(this).attr('data-location')
                });
            });
            if (selectedLocations.length > 0) {

                if (digitalMerChants.some(e => e.id === $('#company_ids').val())) {
                    // Update existing merchant entry

                    digitalMerChants.forEach(function(merchant) {
                        if (merchant.id === $('#company_ids').val()) {
                            merchant.locations = selectedLocations;
                        }
                    });
                } else {
                    // Add new merchant entry
                    digitalMerChants.push({
                        name: $('#company_ids option:selected').text(),
                        id: $('#company_ids').val(),
                        locations: selectedLocations
                    })
                }
            }
            $('#selected_locations_for_digital').empty();
            digitalMerChants.forEach(function(merchant, merchantIndex) {
                var merchantDiv = $('<div>').addClass('mb-3');
                var merchantLabel = $('<label>').text(merchant.name).addClass('form-label');
                merchantDiv.append(merchantLabel);
                merchant.locations.forEach(function(location, locationIndex) {
                    var locationP = $('<p>').addClass('d-flex align-items-center justify-content-between')
                        .html(
                            '<span>' + location.name + '</span>' +
                            '<i class="mdi mdi-delete text-danger remove-location" style="cursor: pointer;" data-merchant-id="' +
                            merchant.id + '" data-location-id="' + location.id + '"></i>'
                        );

                    merchantDiv.append(locationP);
                });
                $('#selected_locations_for_digital').append(merchantDiv);
            });
        }
        $(function() {
            $(document).on("change", ".reward_type", function() {
                var reward_type = $(this).val();
                if (reward_type == 1) {
                    $('.physical-voucher-div').show();
                    $('.digital-voucher-div').hide();
                } else {
                    $('.physical-voucher-div').hide();
                    $('.digital-voucher-div').show();
                    $('#InventoryFileDiv').hide();
                    $('#InventoryQtyDiv').hide();

                }

            })
            $(document).on("change", ".inventory_type", function() {
                var type = $(this).val();
                if (type == 0) {
                    $('#InventoryQtyDiv').show();
                    $('#InventoryFileDiv').hide();
                } else {
                    $('#InventoryQtyDiv').hide();
                    $('#InventoryFileDiv').show();
                }

            })
            $(document).on("change", "#is_featured", function() {
                $('.is-featured-div').toggle()


            })

            $(document).on("change", "#company_id", function() {
                loadLocationsByCompany();
            });

            $(document).on("change", "#company_ids", function() {
                loadLocationsByCompanyForDigital();
            });



            // Load locations when modal is opened if company is already selected
            $(document).on('shown.bs.modal', '#AddModal, #EditModal', function() {
                var companyId = $('#company_id').val();
                if (companyId) {
                    loadLocationsByCompany();
                }
            });

            $(document).on("click", ".clear-time", function() {
                $("input[name='start_time']").val("");
                $("input[name='end_time']").val("");
            })

            $(document).on("click", ".remove-location", function() {
                var merchantId = $(this).data('merchant-id');
                var locationId = $(this).data('location-id');

                // Uncheck the corresponding checkbox
                $('.locations_digital_checkbox[data-location-id="' + locationId + '"]').prop('checked',
                    false);

                digitalMerChants.forEach(function(merchant) {
                    if (merchant.id == merchantId) {
                        console.log(merchant.id == merchantId);
                        merchant.locations = merchant.locations.filter(function(location) {
                            return location.id != locationId;
                        });
                    }
                });


                digitalMerChants = digitalMerChants.filter(function(merchant) {
                    return merchant.locations.length > 0;
                });
                moveSelectedLocation();
            })



        });


        $(document).ready(function() {
            
            //add participating  merchant multi location
            // helper to build the date block HTML for a given location id
            function buildDateBlock(locationId) {
                return `
                <div class="location-date-block mt-2" data-location-id="${locationId}" style="padding:10px; border:1px dashed #e0e0e0;">
                    <div class="row">
                    <div class="col-12 col-md-3">
                        <div class="mb-3 sh_dec">
                        <label class="sh_dec">Publish Start Date <span class="required-hash">*</span></label>
                        <input type="date" class="form-control" name="locations_digital[${locationId}][publish_start_date]">
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="mb-3 sh_dec">
                        <label class="sh_dec">Start Time</label>
                        <input type="time" class="form-control" name="locations_digital[${locationId}][publish_start_time]">
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="mb-3 sh_dec">
                        <label class="sh_dec">Publish End Date</label>
                        <input type="date" class="form-control" name="locations_digital[${locationId}][publish_end_date]">
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="mb-3 sh_dec">
                        <label class="sh_dec">End Time</label>
                        <input type="time" class="form-control" name="locations_digital[${locationId}][publish_end_time]">
                        </div>
                    </div>

                    <!-- Sales fields -->
                    <div class="col-12 col-md-3">
                        <div class="mb-3 sh_dec">
                        <label class="sh_dec">Sales Start Date <span class="required-hash">*</span></label>
                        <input type="date" class="form-control" name="locations_digital[${locationId}][sales_start_date]">
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="mb-3 sh_dec">
                        <label class="sh_dec">Start Time</label>
                        <input type="time" class="form-control" name="locations_digital[${locationId}][sales_start_time]">
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="mb-3 sh_dec">
                        <label class="sh_dec">Sales End Date</label>
                        <input type="date" class="form-control" name="locations_digital[${locationId}][sales_end_date]">
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="mb-3 sh_dec">
                        <label class="sh_dec">End Time</label>
                        <input type="time" class="form-control" name="locations_digital[${locationId}][sales_end_time]">
                        </div>
                    </div>
                    </div>
                </div>
                `;
            }

            // toggle date block when checkbox changes
            $(document).on('change', '.locations_digital_checkbox', function() {
                var $cb = $(this);
                var locId = $cb.data('location-id');
                // the row wrapper — adapt to your markup: the checkbox lives inside a .row.mb-2
                var $row = $cb.closest('.row.mb-2');

                if ($cb.is(':checked')) {
                // avoid duplicating if already present
                if ($row.next('.location-date-block[data-location-id="'+locId+'"]').length === 0) {
                    // insert date block *after* this row (so it appears right below the checkbox row)
                    $row.after(buildDateBlock(locId));
                } else {
                    // if exists but hidden, just show
                    $row.next('.location-date-block[data-location-id="'+locId+'"]').show();
                }
                } else {
                // remove/hide the block when unchecked
                var $block = $row.next('.location-date-block[data-location-id="'+locId+'"]');
                if ($block.length) {
                    // clear inputs (optional) then remove
                    $block.find('input').val('');
                    $block.remove();
                }
                }
            });

            // If some checkboxes are pre-checked on page load, show their blocks
            $('.locations_digital_checkbox:checked').each(function() {
                var $cb = $(this);
                var locId = $cb.data('location-id');
                var $row = $cb.closest('.row.mb-2');
                if ($row.next('.location-date-block[data-location-id="'+locId+'"]').length === 0) {
                $row.after(buildDateBlock(locId));
                // optionally populate existing values if you have them (you can fill inputs server-side)
                }
            });        

            // add multiple for merchant
        
            $(function() {
             

                // Checkbox toggle logic
                $(document).on('change', '.form-check-input[name^="locations"][name$="[selected]"]', function() {

                    var $cb = $(this);
                    var row = $cb.closest('.row.mb-2');

                    // extract location ID from name="locations[1][selected]"
                    var name = $cb.attr('name');
                    var match = name.match(/locations\[(\d+)\]/);
                    if (!match) return;
                    var locId = match[1];

                    if ($cb.is(':checked')) {

                        // If block not exists -> append after row
                        if (row.next('.location-date-block[data-location-id="'+locId+'"]').length === 0) {

                            // existing data if edit mode
                            var values = (typeof existingLocationsData !== 'undefined' && existingLocationsData[locId])
                                            ? existingLocationsData[locId]
                                            : {};

                            row.after(buildDateBlock(locId, values));

                        } else {
                            // show if hidden
                            row.next('.location-date-block[data-location-id="'+locId+'"]').show();
                        }

                    } else {
                        // remove block
                        row.next('.location-date-block[data-location-id="'+locId+'"]').remove();
                    }
                });


                // On page load — show blocks for already checked locations (edit mode)
                $('.form-check-input[name^="locations"][name$="[selected]"]:checked').each(function() {

                    var $cb = $(this);
                    var row = $cb.closest('.row.mb-2');

                    var match = $cb.attr('name').match(/locations\[(\d+)\]/);
                    if (!match) return;
                    var locId = match[1];

                    var values = (typeof existingLocationsData !== 'undefined' && existingLocationsData[locId])
                                    ? existingLocationsData[locId]
                                    : {};

                    if (row.next('.location-date-block[data-location-id="'+locId+'"]').length === 0) {
                        row.after(buildDateBlock(locId, values));
                    }
                });

            });

    });
    </script>
   


    <script src="{{ URL::asset('build/js/crud.js') }}"></script>
@endsection
