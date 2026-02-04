@extends('layouts.master-layouts')

@section('title')
    Treats & Deals
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
            {{ $type === 'campaign-voucher' ? 'Campaign Voucher Management' : 'Treats & Deals Management' }}
        @endslot
    @endcomponent


    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
            {{-- <h4 class="card-title mb-0">Rewards</h4> --}}
            @can("$permission_prefix-create")
                <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal" onclick="resetFormById()"><i
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
                          
                            <th data-field="name" data-filter-control="input" data-sortable="true" data-escape="true">Name</th>
                            <th data-field="reward_type" data-filter-control="input" data-sortable="true" data-escape="true">Reward Type</th>
                            <th data-field="amount" data-sortable="true">Amount</th>
                            <th data-field="quantity" data-sortable="true">Total</th>
                            <th data-field="balance">Balance</th>
                            <th data-field="purchased" data-sortable="true">Issuance</th>
                            <th data-field="redeemed">Redeemed</th>
                            <th data-field="duration">Publish Date Duration</th>
                            <th data-field="image">Image</th>
                            <th data-field="is_draft">Is Draft</th>
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
        @include('admin.reward.add-edit-modal')
    @endcan
    <!-- end modal -->
@endsection

@section('script')

    <script>
        function initTinyMCE() {
            if (typeof tinymce === 'undefined') return;

            tinymce.init({
                selector: "textarea.wysiwyg",
                height: 300,
                relative_urls: false,
                remove_script_host: false,
                convert_urls: true,
                setup: function (editor) {
                editor.on('keydown', function (e) {
                    var content = editor.getContent({ format: 'text' }); // Get plain text content
                if (content.length >= 180 && e.keyCode !== 8 && e.keyCode !== 46) { // Allow backspace and delete
                    e.preventDefault();
                }
                });
            },
            images_upload_url: '{{url("admin/image-upload-editor")}}',
            images_upload_base_path: "{{asset('images')}}/",
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "save table contextmenu directionality emoticons template textcolor"
            ],
            toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons ",
                style_formats: [{
                        title: 'Bold text',
                        inline: 'b'
                    },
                    {
                        title: 'Red text',
                        inline: 'span',
                        styles: {
                            color: '#ff0000'
                        }
                    },
                    {
                        title: 'Red header',
                        block: 'h1',
                        styles: {
                            color: '#ff0000'
                        }
                    },
                    {
                        title: 'Example 1',
                        inline: 'span',
                        classes: 'example1'
                    },
                    {
                        title: 'Example 2',
                        inline: 'span',
                        classes: 'example2'
                    },
                    {
                        title: 'Table styles'
                    },
                    {
                        title: 'Table row 1',
                        selector: 'tr',
                        classes: 'tablerow1'
                    }
                ]
            });


        }
    </script>


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
                $('#collection_reminder_title').text('Send Collection Reminder');
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
                $('#collection_reminder_title').text('Send Reminder');
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

                    if (res.status === 'success') {

                        let html = '';
                        let i = 1;

                        html += `<label class="sh_dec"><b>Locations </b><span style="color:#f46a6a;">*</span></label>`;

                        // Wrapper ONLY ONCE
                        html += `<div id="location_wrapper" class="row gx-3 gy-3">`;

                        res.locations.forEach(loc => {

                            html += `
                                <div class="col-md-6 col-12">
                                    <div class="location-box d-flex align-items-center p-2"
                                        style="border:1px solid #e9e9e9; border-radius:6px;">

                                        <div class="d-flex align-items-center me-auto">
                                            <label class="mb-0 me-2 font-12" style="margin-top: 4px;">
                                                <span class="fw-bold"></span> ${loc.name}
                                            </label>
                                            <input type="checkbox" 
                                                name="locations[${loc.id}][selected]" 
                                                value="1" 
                                                class="form-check-input">
                                        </div>

                                        <div class="d-flex align-items-center ms-3">
                                            <label class="mb-0 me-2 font-12">Inventory Qty</label>
                                            <input type="number" min="0"
                                                class="form-control"
                                                name="locations[${loc.id}][inventory_qty]"
                                                placeholder="Qty"
                                                style="max-width:100px">
                                        </div>

                                    </div>
                                </div>
                            `;

                            i++;
                        });

                        html += `</div><div id="locations_error" class="text-danger mt-1"></div>`; // close row wrapper

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
