@extends('layouts.master-layouts')

@section('title') eVoucher Update Request @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') eVoucher Update Request @endslot
@endcomponent

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
      
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-id-field="id" data-toggle="table" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="false" data-search="false" data-total-field="count" data-data-field="items" data-page-size="100" data-page-list="[100, 500, 1000, 2000, All]" data-filter-control="true">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-width="70">Sr. No.</th>

                        <th data-field="month" data-sortable="true">
                            Month
                        </th>

                        <th data-field="requester" data-filter-control="input">
                            Requester
                        </th>
                          <th data-field="created_at">
                            Requested Date
                        </th>

                        <th data-field="type" data-filter-control="input">
                            Type
                        </th>                      
                        <th data-field="reward_type" data-filter-control="input">
                            Reward Type
                        </th>                      
                        <th data-field="name" data-filter-control="input">
                            Reward Name
                        </th>                      

                        <th data-field="inventory_type">
                            Inventory Type
                        </th>

                        <th data-field="inventory_qty">
                            Inventory Qty
                        </th>

                        <th data-field="voucher_value">
                            Voucher Value
                        </th>

                        <th data-field="clearing_method">
                            Clearing Method
                        </th>

                        <th data-field="status">
                            Status
                        </th>

                      
                        <th data-field="action" class="text-center" data-searchable="false">
                            Action
                        </th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
    
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reward Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="viewForm">
                    <div class="row g-3" id="viewContainer"></div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- end modal -->
@endsection

@section('script')
<script>
    const csrf = $('meta[name="csrf-token"]').attr('content');
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }

    $(document).on("click", ".approve_btn", function () {

        let id = $(this).data("id");

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to approve this eVoucher update request?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'No, cancel',
            reverseButtons: true
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ url('admin/reward-update-request/approve') }}",
                    type: "POST",
                    data: {
                        id: id,
                        _token: csrf
                    },
                    success: function (response) {
                        console.log(response,'res');
                        
                        Swal.fire('Approved!', response.message, 'success');
                        $('#bstable').bootstrapTable('refresh');
                    },
                    error: function (xhr) {
                        console.log(xhr.responseJSON);

                        Swal.fire(
                            'Error',
                            xhr.responseJSON?.message || xhr.responseText,
                            'error'
                        );
                    }
                });

            }
        });
    });

    $(document).on("click", ".reject_btn", function () {

        let id = $(this).data("id");

        Swal.fire({
            title: 'Reject Request',
            text: 'Please enter reason for rejection',
            input: 'textarea',
            inputPlaceholder: 'Write rejection reason here...',
            inputAttributes: {
                'aria-label': 'Type your reason here'
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'Reason is required!';
                }
            }
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ url('admin/reward-update-request') }}/" + id + "/reject",
                    type: "POST",
                    data: {
                        id: id,
                        note: result.value,   // 👈 sending reason
                        _token: csrf
                    },
                    success: function (response) {
                        Swal.fire('Rejected!', response.message, 'success');
                        $('#bstable').bootstrapTable('refresh');
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '.view_btn', function () {
        const id = $(this).data('id');

        $('#viewModal').modal('show');

        // disable everything
        $('#rewardViewForm').find('input, textarea, select, button').prop('disabled', true);

        // allow close
        $('#rewardViewForm .btn-close').prop('disabled', false);

       $.get(ModuleBaseUrl + id, function (res) {
            if (res.status !== 'success') return;

            const d = res.data;
            $('#viewContainer').html(''); // reset

            appendImage('Voucher Catalogue Image', d.voucher_image);
            appendImage('Voucher Details Image', d.voucher_detail_img);
            if(d.type == 2){//bday 
                appendField('From Month', d.from_month);
                appendField('To Month', d.to_month);
            }
            appendField('Status', d.status);
            // appendField('Requested By', d.requester?.name);
            appendField('Reward Name', d.name);
            if(d.type == 1){//evoucher
                appendField('CSO Method', d.cso_method);
            }
            appendHtmlField('Description', d.description);
            appendHtmlField('Voucher T&C', d.term_of_use);
            appendHtmlField('How To Use', d.how_to_use);

            appendField('Merchant', d.merchant_name ?? d.merchant_id);
            if(d.type == 1){//evoucher
                appendField('Voucher Type', 'E-Voucher');
                appendField('Direct Utilization', d.direct_utilization ? 'Yes' : null);
            }
            if(d.type == 0){//t&d
                appendField('Voucher Type', d.reward_type == 0 ? 'Digital' : 'Physical');
                appendField('Voucher Validity',formatDateOnly(d.voucher_validity));


                if(d.reward_type == 1){//physical
                    appendField('Where To Use', d.where_use);
                }
            }
            
            appendField('Publish Start Date & Time',formatDateTime(d.publish_start_date, d.publish_start_time));
            appendField('Publish End Date & Time',formatDateTime(d.publish_end_date, d.publish_end_time));
            appendField('Sales Start Date & Time',formatDateTime(d.sales_start_date, d.sales_start_time));
            appendField('Sales End Date & Time', formatDateTime(d.sales_end_date, d.sales_end_time));

            if(d.type == 1){//evoucher
                appendField('Days', d.days ? (Array.isArray(d.days) ? d.days.join(', ') : JSON.parse(d.days).join(', ')) : null);
                appendField('Start Time', d.start_time);
                appendField('End Time', d.end_time);
                appendField('Maximum Quantity (Per User)', d.max_quantity);
                appendField('Voucher Validity',formatDateOnly(d.voucher_validity));


            }
            
            if(d.type == 0){//t&d

                appendTierRates(d.tier_rates);
                if(d.reward_type == 1){//physical
                   appendLocations(d.locations);
                }
                appendField('Usual Price', d.usual_price);
            }
            if(d.type != 2){//bday 
                appendField('Maximum Quantity (Per User)', d.max_quantity);
            }
            
            if(d.reward_type == 0){//digital
                appendField('Inventory Type', d.inventory_type == 0 ? 'Non Merchant' : 'Merchant');
                if(d.inventory_type == 0){//non merchant
                    appendField('Inventory Quantity', d.inventory_qty);
                }
                if(d.inventory_type == 1){//merchant
                    appendField('File', d.csvFile);
                }
                appendField('Voucher Value', d.voucher_value);
                appendField('Voucher Set', d.voucher_set);
                appendField('Set Quantity', d.set_qty);
                appendField('Clearing Method', getClearingMethodLabel(d.clearing_method));
            }

            if(d.clearing_method == 2){//merchant code
                appendOutlets(d.merchant_locations);
            }else{
                appendField('Location', d.custom_location?.name);
            }                

            appendField('Hide Quantity', d.hide_quantity ? 'Yes' : null);
            appendField('Low Stock 1', d.low_stock_1);
            appendField('Low Stock 2', d.low_stock_2);
            appendField('Friendly URL Name', d.friendly_url);
            appendField('Category', d.category);

            if(d.type == 0){//t&d
                appendField('FABS Categories Information', d.fabs_category_id);
                appendField('AX Item Code', d.ax_item_code);
                appendSwitch('Publish Channel Internet', d.publish_independent);
                appendSwitch('Publish Channel Inhouse', d.publish_inhouse);
                appendSwitch('Send Collection Reminder', d.send_reminder);
            }  
            if(d.type != 2){//bday 

                appendSwitch('Suspend Deal', d.suspend_deal);
                appendSwitch('Suspend Voucher', d.suspend_voucher);
                appendSwitch('Is Featured', d.is_featured);
                appendSwitch('Hide From Catalogue', d.hide_catalogue);
            }
        });

    });

    function appendOutlets(outlets) {
        if (!outlets.length) return;

        let rows = outlets.map(o => `
            <tr>
                <td>${o.name}</td>
            </tr>
        `).join('');

        $('#viewContainer').append(`
            <div class="col-md-12">
                <label class="form-label">Outlets</label>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr><th>Outlet Name</th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `);
    }


    function appendLocations(locations) {
        if (!Array.isArray(locations) || locations.length === 0) return;

        let rows = locations.map(loc => `
            <tr>
                <td>${loc.name}</td>
                <td>${loc.inventory_qty}</td>
            </tr>
        `).join('');

        $('#viewContainer').append(`
            <div class="col-md-12">
                <label class="form-label">Locations</label>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Inventory Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
            </div>
        `);
    }

    function appendTierRates(tierRates) {
        if (!Array.isArray(tierRates) || tierRates.length === 0) return;

        let rows = tierRates.map(t => `
            <tr>
                <td>${t.tier?.tier_name ?? '-'}</td>
                <td>${t.price}</td>
            </tr>
        `).join('');

        $('#viewContainer').append(`
            <div class="col-md-12">
                <label class="form-label">Tier Rates</label>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Tier</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `);
    }

    function formatDateOnly(date) {
        if (!date) return null;

        const d = new Date(date);
        if (isNaN(d)) return date;

        const year  = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day   = String(d.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`; // Y-m-d
    }

    function formatDateTime(date, time) {
        if (!date) return null;

        // extract YYYY-MM-DD safely
        const d = new Date(date);
        if (isNaN(d)) return null;

        const year  = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day   = String(d.getDate()).padStart(2, '0');

        return `${year}-${month}-${day} ${time ?? '00:00:00'}`;
    }

    function getClearingMethodLabel(val) {
        const map = {
            0: 'QR',
            1: 'Barcode',
            2: 'Merchant Code',
            3: 'External Link',
            4: 'External Code',
        };
        return map[val] ?? val;
    }

    function appendField(label, value) {
        if (value === null || value === undefined || value === '' || value === '-') return;

        $('#viewContainer').append(`
            <div class="col-md-6">
                <label class="form-label">${label}</label>
                <input type="text" class="form-control" value="${value}" readonly>
            </div>
        `);
    }

    function appendTextarea(label, value) {
        if (!value) return;

        $('#viewContainer').append(`
            <div class="col-md-12">
                <label class="form-label">${label}</label>
                <textarea class="form-control" rows="3" readonly>${value}</textarea>
            </div>
        `);
    }

    function appendHtmlField(label, html, col = 12) {
        if (!html) return;

        $('#viewContainer').append(`
            <div class="col-md-${col}">
                <label class="form-label">${label}</label>
                <div class="form-control" style="background:#f8f9fa" readonly>
                    ${html}
                </div>
            </div>
        `);
    }


    function appendImage(label, image) {
        if (!image) return;

        $('#viewContainer').append(`
            <div class="col-md-6 text-center">
                <label class="form-label d-block">${label}</label>
                <img src="/uploads/image/${image}" 
                    class="img-thumbnail"
                    style="max-height:50px;"
                    alt="${label}">
            </div>
        `);
    }

    function appendSwitch(label, checked, col = 3) {
        if (checked === null || checked === undefined) return;

        $('#viewContainer').append(`
            <div class="col-md-${col}">
                <label class="form-label d-block">${label}</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" ${checked ? 'checked' : ''} disabled>
                    <span class="ms-2">${checked ? 'Yes' : 'No'}</span>
                </div>
            </div>
        `);
    }

</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection