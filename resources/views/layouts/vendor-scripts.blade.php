<!-- JAVASCRIPT -->
<script src="{{ URL::asset('build/libs/jquery/jquery.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/metismenu/metisMenu.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/node-waves/waves.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/lightbox/js/lightbox.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/chosen/chosen.jquery.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script>
    var BaseURL = "{{url('')}}" + '/';
    var BaseURLImageAsset = "{{ asset('images')}}" + '/';

    $('#change-password').on('submit',function(event){
        event.preventDefault();
        var Id = $('#data_id').val();
        var current_password = $('#current-password').val();
        var password = $('#password').val();
        var password_confirm = $('#password-confirm').val();
        $('#current_passwordError').text('');
        $('#passwordError').text('');
        $('#password_confirmError').text('');
        $.ajax({
            url: "{{ url('update-password') }}" + "/" + Id,
            type:"POST",
            data:{
                "current_password": current_password,
                "password": password,
                "password_confirmation": password_confirm,
                "_token": "{{ csrf_token() }}",
            },
            success:function(response){
                $('#current_passwordError').text('');
                $('#passwordError').text('');
                $('#password_confirmError').text('');
                if(response.isSuccess == false){ 
                    $('#current_passwordError').text(response.Message);
                }else if(response.isSuccess == true){
                    setTimeout(function () {   
                        window.location.href = "{{ route('root') }}"; 
                    }, 1000);
                }
            },
            error: function(response) {
                $('#current_passwordError').text(response.responseJSON.errors.current_password);
                $('#passwordError').text(response.responseJSON.errors.password);
                $('#password_confirmError').text(response.responseJSON.errors.password_confirmation);
            }
        });
    });
   
    function bindStartEndFlatpickr(startSelector, endSelector) {

        const startEl = document.querySelector(startSelector);
        const endEl   = document.querySelector(endSelector);

        if (!startEl || !endEl) return;

        let endPickerInstance;

        const startPickerInstance = flatpickr(startEl, {
            enableTime: true,
            enableSeconds: true,      // ✅ REQUIRED
            time_24hr: true,          // HH format (recommended)
            dateFormat: 'Y-m-d H:i:S',
            altInput: true,
            altFormat: 'Y-m-d H:i:S',

            onReady: function (selectedDates, dateStr, instance) {
                instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';
            },

            onChange: function (selectedDates) {
                if (!endPickerInstance) return;

                if (selectedDates.length) {
                    endPickerInstance.set('minDate', selectedDates[0]);
                    endPickerInstance.input.removeAttribute('disabled');
                    endPickerInstance.set('clickOpens', true);
                } else {
                    endPickerInstance.clear();
                    endPickerInstance.input.setAttribute('disabled', true);
                    endPickerInstance.set('clickOpens', false);
                }
            }
        });

        endPickerInstance = flatpickr(endEl, {
            enableTime: true,
            enableSeconds: true,      // ✅ REQUIRED
            time_24hr: true,
            dateFormat: 'Y-m-d H:i:S',
            altInput: true,
            altFormat: 'Y-m-d H:i:S',
            clickOpens: false,

            onReady: function (selectedDates, dateStr, instance) {
                instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';
            }
        });

        // Disable end initially
        endPickerInstance.input.setAttribute('disabled', true);
    }

    function bindStartEndFlatpickrEdit(modal, startSelector, endSelector) {
        const startEl = modal.querySelector(startSelector);
        const endEl   = modal.querySelector(endSelector);

        if (!startEl || !endEl) return;

        if (startEl._flatpickr) startEl._flatpickr.destroy();
        if (endEl._flatpickr) endEl._flatpickr.destroy();

        let endPickerInstance;

        const startPickerInstance = flatpickr(startEl, {
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i:S',
            altInput: true,
            altFormat: 'Y-m-d H:i:S',
            defaultDate: startEl.value || null,

            onReady(_, __, instance) {
                instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';
            },

            onChange(selectedDates) {
                if (!endPickerInstance) return;

                if (selectedDates.length) {
                    endPickerInstance.set('minDate', selectedDates[0]);
                    endPickerInstance.input.removeAttribute('disabled');
                    endPickerInstance.set('clickOpens', true);
                } else {
                    endPickerInstance.clear();
                    endPickerInstance.input.setAttribute('disabled', true);
                    endPickerInstance.set('clickOpens', false);
                }
            }
        });

        endPickerInstance = flatpickr(endEl, {
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i:S',
            altInput: true,
            altFormat: 'Y-m-d H:i:S',
            defaultDate: endEl.value || null,
            clickOpens: false,

            onReady(_, __, instance) {
                instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';
            }
        });

        // ✅ EDIT MODE HANDLING
        if (startEl.value) {

            endPickerInstance.set('minDate', startPickerInstance.selectedDates[0]);
            endPickerInstance.input.removeAttribute('disabled');
            endPickerInstance.set('clickOpens', true);

        } else {

            // start empty → end must be disabled
            endPickerInstance.clear();
            endPickerInstance.input.setAttribute('disabled', true);
            endPickerInstance.set('clickOpens', false);
        }

    }
  
    function bindMonthFlatpickr(startSelector, endSelector) {

        const startEl = document.querySelector(startSelector);
        const endEl   = document.querySelector(endSelector);

        if (!startEl || !endEl) return;

        let endPicker;

        const startPicker = flatpickr(startEl, {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",   // value sent to backend
                    altFormat: "F Y",    // UI display (Jan 2025)
                    theme: "light"
                })
            ],
            allowInput: false,
            onChange(selectedDates) {
                if (!selectedDates.length) return;

                const selected = selectedDates[0];

                // enable TO month
                endPicker.set("minDate", selected);
                endPicker.open();
                endPicker.input.removeAttribute("disabled");
            }
        });

        endPicker = flatpickr(endEl, {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y",
                    theme: "light"
                })
            ],
            allowInput: false
        });

        // disable TO initially
        endPicker.input.setAttribute("disabled", true);
    }


    function initFlatpickrDate(context = document) {

        context.querySelectorAll('.js-flat-date').forEach(function (el) {

            // prevent double init (important for edit modal)
            if (el._flatpickr) {
                el._flatpickr.destroy();
            }

            flatpickr(el, {
                dateFormat: 'Y-m-d',   // backend format
                altInput: true,
                altFormat: 'Y-m-d',
                allowInput: true,

                onReady(_, __, instance) {
                    instance.altInput.placeholder = 'yyyy-MM-dd';
                }
            });
        });
    }

    function loadParticipatingMerchantLocations(merchantIds) {
        // normalize → always array
        if (!merchantIds) {
            $("#participating_merchant_location").html('');
            $("#selected_locations_summary").html('');
            $("#selected_locations_wrapper").hide();   // ✅ hide
            $("#participating_section").hide(); 
            return;
        }

        if (!Array.isArray(merchantIds)) {
            merchantIds = [merchantIds];
        }

        $.ajax({
            url: "{{ url('admin/reward/get-participating-merchant-locations') }}",
            type: "GET",
            data: {
                merchant_ids: merchantIds // ✅ send array
            },
            success: function (res) {

                if (res.status !== 'success') return;

                let html = '';

                html += `
                    <label class="sh_dec">
                        <b>Participating Merchant Outlets</b>
                        <span style="color:#f46a6a;">*</span>
                    </label>
                    <div id="participating_location_wrapper" class="row gx-3 gy-3">
                `;

                res.locations.forEach(loc => {
                    html += `
                        <div class="col-md-4 col-12">
                            <div class="location-box d-flex align-items-center p-2"
                                style="border:1px solid #e9e9e9; border-radius:6px;">

                                <div class="d-flex align-items-center me-auto">
                                    <input type="checkbox"
                                        name="participating_merchant_locations[${loc.id}][selected]"
                                        value="1"
                                        class="form-check-input me-2">

                                    <label class="mb-0 font-12 fw-bold">
                                        ${loc.name}
                                    </label>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `
                    </div>
                    <div id="participating_merchant_locations_error"
                        class="text-danger mt-1"></div>
                `;

                $("#participating_merchant_location").html(html);
                $("#participating_section").show();        // ✅ added
                // updateSelectedLocationsSummary(document); 
            }
        });
    }

    function editParticipatingMerchantLocations(modal, merchantIds) {

        if (!merchantIds) {
            modal.find('#participating_section').hide();
            return;
        }

        if (!Array.isArray(merchantIds)) {
            merchantIds = [merchantIds];
        }

        $.ajax({
            url: "{{ url('admin/reward/get-participating-merchant-locations') }}",
            type: "GET",
            data: { merchant_ids: merchantIds },
            success: function (res) {

                if (res.status !== 'success') return;

                let html = `
                    <label class="sh_dec">
                        <b>Participating Merchant Outlets</b>
                        <span style="color:#f46a6a;">*</span>
                    </label>
                    <div class="row gx-3 gy-3">
                `;

                res.locations.forEach(loc => {

                    let checked = Array.isArray(participatingLocations)
                        && participatingLocations.includes(loc.id)
                        ? 'checked'
                        : '';

                    html += `
                        <div class="col-md-6 col-12">
                            <div class="location-box d-flex align-items-center p-2 border rounded">

                                <input type="checkbox"
                                    name="participating_merchant_locations[${loc.id}][selected]"
                                    value="1"
                                    class="form-check-input me-2"
                                    ${checked}>

                                <label class="mb-0 fw-bold">${loc.name}</label>

                            </div>
                        </div>
                    `;
                });

                html += `</div>`;

                modal.find("#participating_merchant_location").html(html);
                modal.find("#participating_section").show();

                updateSelectedLocationsSummary(modal);
            }
        });
    }

    function updateSelectedLocationsSummary(context) {

        const selected = [];

        $(context)
            .find('input[name^="participating_merchant_locations"]:checked')
            .each(function () {

                const name = $(this)
                    .closest('.location-box')
                    .find('label')
                    .text()
                    .trim();

                if (name) selected.push(name);
            });

        const wrapper = $(context).find('#selected_locations_wrapper');
        const summary = $(context).find('#selected_locations_summary');

        if (selected.length) {
            summary.html(
                selected.map(n => `<div>• ${n}</div>`).join('')
            );
            wrapper.show();
        } else {
            summary.html('');
            wrapper.hide();
        }
    }

    $(document).on('change','input[name^="participating_merchant_locations"]', function () {
            const modal = $(this).closest('.modal');

            updateSelectedLocationsSummary(modal);
        }
    );

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

    function editToggleClearingFields(modal) {
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
            $("#EditModal #participating_merchant_location").show();
            let participatingMerchantId = $('#EditModal #participating_merchant_id').val();
            editParticipatingMerchantLocations(modal, participatingMerchantId);
             setTimeout(() => {
                updateSelectedLocationsSummary(modal);
            }, 300); // will use savedLocations variable inside modal
            // External Code OR Merchant Code → show PARTICIPATING MERCHANT
            merchantField.show();
            locationField.hide();
        }
    }

    function editToggleInventoryFields(modal) {
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

</script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/tableExport.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.21.4/dist/bootstrap-table.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.21.4/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/export/bootstrap-table-export.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ URL::asset('build/libs/select2/js/select2.min.js') }}"></script>
<script src="{{ URL::asset('/build/libs/snackbar/snackbar.min.js') }}"></script>
@yield('script')
<!-- App js -->
<script src="{{ URL::asset('build/js/app.js')}}"></script>
<script src="{{ URL::asset('build/js/custom.js')}}"></script>
@yield('script-bottom')