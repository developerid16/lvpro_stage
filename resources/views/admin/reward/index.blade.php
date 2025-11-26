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
    </script>
    <script src="{{ URL::asset('build/js/crud.js') }}"></script>
@endsection
