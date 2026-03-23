<!-- JAVASCRIPT -->
<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/metismenu/metisMenu.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/node-waves/waves.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/lightbox/js/lightbox.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/chosen/chosen.jquery.min.js')}}"></script>
<script src="{{ URL::asset('build/js/flatpickr.min.js')}}"></script>
<script src="{{ URL::asset('build/js/monthSelect.js')}}"></script>

<script>
    $.ajaxSetup({
        xhrFields: {
            withCredentials: true   // 🔥 forces Firefox to send cookies
        }
    });
    let selectedOutletMap = {};    
    let selectedOutletMapMerchant = {};    

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
   
    // Prevent Enter from submitting form inside modal
    $(document).on('keydown', '.modal form', function (e) {
        if (e.key === "Enter" && !$(e.target).is('textarea')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });
    
    // Completely block Enter from submitting modal forms
    document.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {

            const modal = e.target.closest(".modal");
            if (modal) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            }
        }
    }, true); // 👈 IMPORTANT: use capture phase

    $(document).on('keydown', '.common-datetime, .common-date, .month-picker, .js-flat-date', function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            $(this).blur(); // close picker properly
        }
    });    

    function bindStartEndFlatpickrEdit(modal, startSelector, endSelector) {

        const startEl = modal.querySelector(startSelector);
        const endEl   = modal.querySelector(endSelector);

        if (!startEl || !endEl) return;

        // prevent double init
        if (startEl._flatpickr) return;

        // normalize bad value like: 2026-01-23 00:00:00 01:00:00
        startEl.value = normalizeDateTime(startEl.value);
        endEl.value   = normalizeDateTime(endEl.value);

        let endPickerInstance;

        const startPickerInstance = flatpickr(startEl, {
            allowInput: true,
            enableTime: true,
            enableSeconds: false,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'Y-m-d H:i',
            defaultDate: startEl.value
                ? new Date(startEl.value.replace(' ', 'T'))
                : null,

            // plugins: [
            //     new confirmDatePlugin({
            //         confirmIcon: "<i class='fa fa-check'></i>",
            //         confirmText: "OK",
            //         showAlways: false,
            //         theme: "light"
            //     })
            // ],
            onKeyDown: function(selectedDates, dateStr, instance, event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    event.stopPropagation();
                    instance.close();
                }
            },


            onReady(_, __, instance) {
                // instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';

                if (instance.selectedDates.length && endPickerInstance) {
                    const startDate = instance.selectedDates[0];
                    endPickerInstance.set('minDate', startDate);
                    endPickerInstance.set('clickOpens', true);
                    // endPickerInstance.altInput.removeAttribute('disabled');
                    endPickerInstance.altInput.readOnly = true;
                    endPickerInstance.jumpToDate(startDate);
                }
            },

            onChange(selectedDates) {
                if (!endPickerInstance) return;

                if (selectedDates.length) {
                    safeEnableEndPicker(endPickerInstance, selectedDates[0]);
                } else {
                    endPickerInstance.clear();
                    endPickerInstance.set('clickOpens', false);
                    // endPickerInstance.altInput.setAttribute('disabled', true);
                    endPickerInstance.altInput.readOnly = true;
                }
            }
        });

        endPickerInstance = flatpickr(endEl, {
            allowInput: true,
            enableTime: true,
            enableSeconds: false,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'Y-m-d H:i',
            defaultDate: endEl.value
                ? new Date(endEl.value.replace(' ', 'T'))
                : null,
            clickOpens: false,
            // plugins: [
            //     new confirmDatePlugin({
            //         confirmIcon: "<i class='fa fa-check'></i>",
            //         confirmText: "OK",
            //         showAlways: false,
            //         theme: "light"
            //     })
            // ],
            onKeyDown: function(selectedDates, dateStr, instance, event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    event.stopPropagation();
                    instance.close();
                }
            },

            onReady(_, __, instance) {
                // instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';
                instance.jumpToDate(instance.selectedDates[0] || new Date());
            }
        });

        if (startPickerInstance.selectedDates.length) {
            safeEnableEndPicker(endPickerInstance, startPickerInstance.selectedDates[0]);
        } else {
            // endPickerInstance.altInput.setAttribute('disabled', true);
            endPickerInstance.altInput.readOnly = true;
        }
    }
       
    function bindStartEndFlatpickr(startSelector, endSelector) {
        
        const startEl = document.querySelector(startSelector);
        const endEl   = document.querySelector(endSelector);
        if (startEl._flatpickr) return;

        if (!startEl || !endEl) return;

        let startPicker, endPicker;

        startPicker = flatpickr(startEl, {
            minDate: "today",
            enableTime: true,
            time_24hr: true,
            minuteIncrement: 1,      // 👈 change here
            secondIncrement: 1,      // 👈 change here
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'Y-m-d H:i',
            enableSeconds: false,
            confirmIcon: "<i class='fa fa-check'></i>",
            confirmText: "OK ",
            // plugins: [
            //     new confirmDatePlugin({
            //         confirmIcon: "<i class='fa fa-check'></i>",
            //         confirmText: "OK",
            //         showAlways: false,
            //         theme: "light"
            //     })
            // ],
            onKeyDown: function(selectedDates, dateStr, instance, event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    event.stopPropagation();
                    instance.close();
                }
            },

            onReady(_, __, instance) {
                // instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';
            },

            onChange(selectedDates) {

                if (!selectedDates.length) {
                    endPicker.clear();
                    endPicker.input.disabled = true;
                    return;
                }

                const start = selectedDates[0];

                endPicker.input.disabled = false;

                // ✅ ONLY block same exact datetime
                endPicker.set('minDate', new Date(start.getTime() + 1000));

                // Clear invalid already-selected end
                if (
                    endPicker.selectedDates.length &&
                    endPicker.selectedDates[0] <= start
                ) {
                    endPicker.clear();
                }
            }
        });

        endPicker = flatpickr(endEl, {
            enableTime: true,
            time_24hr: true,
            minuteIncrement: 1,      // 👈 change here
            secondIncrement: 1,      // 👈 change here
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'Y-m-d H:i',
            enableSeconds: false,

            // plugins: [
            //     new confirmDatePlugin({
            //         confirmIcon: "<i class='fa fa-check'></i>",
            //         confirmText: "OK",
            //         showAlways: false,
            //         theme: "light"
            //     })
            // ],
            onKeyDown: function(selectedDates, dateStr, instance, event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    event.stopPropagation();
                    instance.close();
                }
            },

            onReady(_, __, instance) {
                // instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';
                instance.input.disabled = true;
            },

            // 🔒 HARD GUARD (manual typing)
            onChange(selectedDates, _, instance) {

                if (!selectedDates.length || !startPicker.selectedDates.length) return;

                const start = startPicker.selectedDates[0];
                const end   = selectedDates[0];

                if (end.getTime() <= start.getTime()) {
                    instance.clear();
                }
            }
        });
    }
   
    function normalizeDateTime(val) {
        if (!val) return val;

        const parts = val.trim().split(' ');

        // format: [date, 00:00:00, real_time]
        if (parts.length == 3) {
            return parts[0] + ' ' + parts[2];
        }

        // normal case: [date, time]
        return parts[0] + ' ' + parts[1];
    }

    // function initFlatpickrDate(context = document) {

    //     context.querySelectorAll('.js-flat-date').forEach(function (el) {

    //         // prevent double init (important for edit modal)
    //         if (el._flatpickr) {
    //             el._flatpickr.destroy();
    //         }

    //         flatpickr(el, {
    //             dateFormat: 'Y-m-d',   // backend format
    //             altInput: true,
    //             altFormat: 'Y-m-d',
    //             allowInput: true,

    //             onReady(_, __, instance) {
    //                 // instance.altInput.placeholder = 'yyyy-MM-dd';
    //             }
    //         });
    //     });
    // }

    function initFlatpickrDate(context = document) {

    // Init .js-flat-date pickers
    context.querySelectorAll('.js-flat-date').forEach(function (el) {
        if (el._flatpickr) {
            el._flatpickr.destroy();
        }
        flatpickr(el, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'Y-m-d',
            allowInput: true,
        });
    });

    // Init start/end date pairs
    bindStartEndFlatpickrEdit(
        context,
        'input[name="publish_start"]',
        'input[name="publish_end"]'
    );
    bindStartEndFlatpickrEdit(
        context,
        'input[name="sales_start"]',
        'input[name="sales_end"]'
    );

    // ✅ STEP 2: Intercept Reset Button
    const $modal = $(context).closest('.modal').length
        ? $(context).closest('.modal')
        : $(context);

    $modal.find('button[type="reset"]').off('click.flatpickrReset').on('click.flatpickrReset', function (e) {
        e.preventDefault();

        const $form = $modal.find('form');

        // ✅ Restore all normal inputs/selects
        $form.find('input, select, textarea').each(function () {
            if (this.type === 'hidden') return;

            // Skip flatpickr's auto-generated altInput (no name attr)
            if ($(this).hasClass('flatpickr-input') && !$(this).attr('name')) return;

            const original = $(this).data('originalValue');
            if (original !== undefined) {
                $(this).val(original);
            }
        });

        // ✅ Restore multi-select (days[]) to original server selected options
        $form.find('select').each(function () {
            $(this).find('option').each(function () {
                this.selected = this.defaultSelected;
            });
        });

        // ✅ Restore all Flatpickr instances
        $form.find('input[name]').each(function () {
            if (this._flatpickr) {
                const original = $(this).data('originalValue');
                if (original) {
                    this._flatpickr.setDate(original, true);
                } else {
                    this._flatpickr.clear();
                }
            }
        });
    });
}

    function safeEnableEndPicker(instance, anchorDate) {
        instance.set('minDate', anchorDate);
        instance.jumpToDate(anchorDate);   // 🔑 REQUIRED
        instance.set('clickOpens', true);
        instance.altInput.removeAttribute('disabled');
    }

    function bindMonthFlatpickr(modal) {

        modal = modal instanceof HTMLElement ? modal : modal[0];

        const el = modal.querySelector('#monthInput');
        if (!el) return;

        if (el._flatpickr) {
            el._flatpickr.destroy();
        }

        const now = new Date();
        const firstDayOfCurrentMonth =
            new Date(now.getFullYear(), now.getMonth(), 1);

        flatpickr(el, {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y",
                    theme: "light"
                })
            ],
            minDate: firstDayOfCurrentMonth,
            allowInput: false
        });
    }

    function bindMonthFlatpickrEdit(modal, startSelector, endSelector) {

        // make sure modal is DOM element, not jQuery
        modal = modal instanceof HTMLElement ? modal : modal[0];

        const startEl = modal.querySelector(startSelector);
        const endEl   = modal.querySelector(endSelector);

        if (!startEl || !endEl) return;

        // 💣 DESTROY old instances (CRITICAL for edit modal)
        if (startEl._flatpickr) startEl._flatpickr.destroy();
        if (endEl._flatpickr) endEl._flatpickr.destroy();

        let endPicker;

        const startPicker = flatpickr(startEl, {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y",
                    theme: "light"
                })
            ],
            allowInput: false,
            onChange(selectedDates) {
                if (!selectedDates.length) return;

                const selected = selectedDates[0];

                endPicker.set("minDate", selected);
                endPicker.input.removeAttribute("disabled");
                endPicker.open();
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

        // 🧠 EDIT MODE: if FROM already has value, enable TO
        if (startEl.value) {
            endPicker.input.removeAttribute("disabled");
            endPicker.set("minDate", startEl.value);
        }
    }
   
    function loadParticipatingMerchantLocations(modal, merchantIds) {
        if (!merchantIds || merchantIds.length === 0) {
            modal.find("#participating_merchant_location").empty();
            modal.find("#participating_section").hide();
            return;
        }

        if (!Array.isArray(merchantIds)) {
            merchantIds = [merchantIds];
        }

        modal.find("#participating_merchant_location").empty();

        $.ajax({
            url: "{{ url('admin/reward/get-participating-merchant-locations') }}",
            type: "GET",
            data: { merchant_ids: merchantIds },
            success: function (res) {

                // keep your original guard
                if (!res || res.status !== 'success') {
                    showNoOutlets(modal);
                    return;
                }

                // ✅ FIX: handle empty / null locations
                if (!res.locations || res.locations.length === 0) {
                    showNoOutlets(modal);
                    return;
                }

                let html = `
                    <label class="sh_dec fw-bold">
                        Participating Merchant Outlets <span class="text-danger">*</span>
                    </label>
                    <div id="participating_location_wrapper" class="row gx-3 gy-3">
                `;

                res.locations.forEach(function (loc) {

                    const checked = selectedOutletMapMerchant && selectedOutletMapMerchant[loc.id]
                        ? 'checked'
                        : '';

                    html += `
                        <div class="col-md-4 col-12">
                            <div class="location-box d-flex align-items-center p-2 border rounded">
                                <input type="checkbox"
                                    class="form-check-input me-2 outlet-checkbox"
                                    data-id="${loc.id}"
                                    data-name="${loc.name}"
                                    ${checked}>
                                <label class="mb-0 fw-bold">${loc.name}</label>
                            </div>
                        </div>
                    `;
                });

                html += `</div>`;

                modal.find("#participating_merchant_location").html(html);
                modal.find("#participating_section").show();
            },
            error: function () {
                showNoOutlets(modal);
            }
        });
    }
    

    function showNoOutlets(modal) {

        modal.find("#participating_merchant_location").html(`
            <div class="alert alert-danger mb-2" style="width:170px;">
                Outlets not found
            </div>
        `);

        modal.find("#participating_section").show();
    }

    function syncHiddenSelectedLocations(modal) {

        const container = modal.find("#selected_locations_hidden");
        container.empty();

        Object.keys(selectedOutletMapMerchant).forEach(locId => {
            container.append(`
                <input type="hidden"
                    name="participating_merchant_locations[${locId}][selected]"
                    value="1">
            `);
        });
    }

    function editParticipatingMerchantLocations(modal) {

        if (!selectedOutletMapMerchant || Object.keys(selectedOutletMapMerchant).length === 0) {
            return;
        }

        let locationBox = modal.find("#participating_merchant_location");

        locationBox.empty();

        Object.keys(selectedOutletMapMerchant).forEach(function (id) {

            let name = selectedOutletMapMerchant[id];
           
        });

        modal.find("#participating_section").show();

        updateSelectedLocationsSummary(modal);
        syncHiddenSelectedLocations(modal);
    }


    function updateSelectedLocationsSummary(modal) {

        const wrapper = modal.find("#selected_locations_wrapper");
        const summary = modal.find("#selected_locations_summary");

        const names = Object.values(selectedOutletMapMerchant);

        if (!names.length) {
            summary.empty();
            wrapper.hide();
            return;
        }

        summary.html(names.map(n => `<div>• ${n}</div>`).join(""));
        wrapper.show();
    }
    
    $(document).on("change", ".outlet-checkbox", function () {

        const modal = $(this).closest(".modal");

        const id   = $(this).data("id");
        const name = $(this).data("name");

        if (this.checked) {
            selectedOutletMapMerchant[id] = name;
        } else {
            delete selectedOutletMapMerchant[id];
        }

        updateSelectedLocationsSummary(modal);
        syncHiddenSelectedLocations(modal);
    });

    function toggleClearingFields(modal) {

        const method = modal.find('.clearing_method').val();

        const locationField  = modal.find('.location_text');
        const merchantField  = modal.find('.participating_merchant');
        const outletWrapper  = modal.find('#selected_locations_wrapper');
        const outletList     = modal.find('#participating_merchant_location');
        const outletSection  = modal.find('#participating_section');
        const merchantSelect = modal.find('#participating_merchant_id');

        // 🔥 ALWAYS RESET SELECTED OUTLETS ON METHOD CHANGE
        selectedOutletMapMerchant = [];
        modal.find('#selected_locations_summary').empty();
        modal.find('#selected_locations_hidden').empty();
        // outletWrapper.hide();

        // 🔴 HARD RESET UI
        locationField.hide();
        merchantField.hide();
        outletList.empty();
        outletSection.hide();

        // 🔥 Reset merchant selection unless method = 2
        if (method !== "2") {
            merchantSelect.val(null).trigger('change');
        }

        // ✅ SHOW BASED ON METHOD
        if (["0", "1", "3","4"].includes(method)) {
            locationField.show();
        }

        // ❌ If clearing_method = 2 → force inventory_type = 0
        if (method === "2") {
            merchantField.show();
            outletSection.show();
            if (modal.find('.inventory_type').val() == "1") {
                modal.find('.inventory_type').val("0").trigger('change');
            }            
            // Hide inventory type 1 option
            // modal.find('.inventory_type option[value="1"]').hide();
        } else {
            // Show inventory type 1 back
            // modal.find('.inventory_type option[value="1"]').show();
        }
        
    }

    function editToggleClearingFields(modal) {

        const method = Number(modal.find('.clearing_method').val());

        const locationField  = modal.find('.location_text');
        const merchantField  = modal.find('.participating_merchant');
        const outletWrapper  = modal.find('#selected_locations_wrapper');
        const outletList     = modal.find('#participating_merchant_location');
        const outletSection  = modal.find('#participating_section');
        const merchantSelect = modal.find('#participating_merchant_id');

        locationField.hide();
        merchantField.hide();
        outletWrapper.hide();
        outletList.empty();
        outletSection.hide();

        // 🔒 If clearing = 2 → inventory 1 not allowed
        if (method === 2) {
            // modal.find('.inventory_type option[value="1"]').hide();
            if (modal.find('.inventory_type').val() == "1") {
                modal.find('.inventory_type').val("0").trigger('change');
            } 
        } else {
            // modal.find('.inventory_type option[value="1"]').show();
        }

        if ([0,1,3,4].includes(method)) {
            locationField.show();
            return;
        }

        if (method === 2) {
            merchantField.show();
            outletSection.show();

            const merchantId = merchantSelect.val();
            if (merchantId) {
                editParticipatingMerchantLocations(modal, merchantId);
                setTimeout(() => {
                    updateSelectedLocationsSummary(modal);
                }, 300);
            }
            return;
        }

        merchantSelect.val(null).trigger('change');
        selectedOutletMapMerchant = {};
        modal.find('#selected_locations_summary').empty();
        modal.find('#selected_locations_hidden').empty();
    }

    function editToggleInventoryFields(modal) {

        let type = modal.find('.inventory_type').val();
        let fileField = modal.find('.file');
        let qtyField  = modal.find('.inventory_qty');
        let clearing  = modal.find('#clearing_method');

        modal.find('#inventory_qty').prop('readonly', false);
        clearing.find('option[value="3"], option[value="4"]').show();

        // 🔒 If inventory = 1 → clearing 2 not allowed
        if (type === "1") {

            let clearingSelect = modal.find('#clearing_method');

            if (clearingSelect.val() === "2") {
                clearingSelect.val('').trigger('change');
            }

            clearingSelect.find('option[value="2"]').hide();

            fileField.show();
            qtyField.show();
        } else if (type === "0") {
             modal.find('#inventory_qty').css('background-color', 'rgb(255, 255, 255)');

            clearing.find('option[value="2"]').show();

            qtyField.show();
            fileField.hide();
            fileField.find("input").val("");

            clearing.find('option[value="3"]').hide();

            if (clearing.val() === "3") {
                clearing.val('');
            }

        } else {
            fileField.hide();
            qtyField.hide();
        }
    }
    
    function toggleInventoryFields(modal) {
        let type = modal.find('.inventory_type').val();

        let fileField = modal.find('.file');
        let qtyField  = modal.find('.inventory_qty');
        modal.find('#inventory_qty').prop('readonly', false);

        let clearing = modal.find('#clearing_method');
        clearing.find('option[value="3"], option[value="4"]').show();        
        
        if (type === "1") {

            fileField.show();
            qtyField.hide();
            qtyField.find("input").val("");

            let clearingSelect = modal.find('#clearing_method');

            // 🔥 If clearing = 2 → reset it
            if (clearingSelect.val() === "2") {
                clearingSelect.val('').trigger('change');
            }

            // 🔥 Hide option 2
            clearingSelect.find('option[value="2"]').hide();
        } else if (type === "0") {
            modal.find('#inventory_qty').css('background-color', 'rgb(255, 255, 255)');

            modal.find('#inventory_qty').val('');
            qtyField.show();
            fileField.hide();
            fileField.find("input").val(""); // clear
            modal.find('#clearing_method option[value="2"]').show();
            clearing.find('option[value="3"]').hide();
            if (clearing.val() === "3") {
                clearing.val('');
            }
        } else {
            // nothing selected → hide both
            fileField.hide();
            qtyField.hide();
        }
    }

    function forceInventoryReadonly(modal) {
        let type = modal.find('.inventory_type').val();
        let qtyInput = modal.find('#inventory_qty');

        if (type === "0") {
            qtyInput.prop('readonly', false); // manual entry
        } else if (type === "1") {
            qtyInput.prop('readonly', true);  // excel-controlled
        }
    }
  
    // Show preview when selecting a new image
    document.getElementById('voucher_image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const preview = document.getElementById('voucher_image_preview');
        
        const clearBtn = document.getElementById('clear_voucher_image');

        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            clearBtn.style.display = 'inline-block';
        }
    });
   
    // Clear image
    document.getElementById('clear_voucher_image').addEventListener('click', function () {
        const input = document.getElementById('voucher_image');
        const preview = document.getElementById('voucher_image_preview');

        input.value = '';
        preview.src = '';
        preview.style.display = 'none';
        this.style.display = 'none';
    });

    // Show preview when selecting a new image
    document.getElementById('voucher_detail_img').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const preview = document.getElementById('voucher_detail_img_preview');
        
        const clearBtn = document.getElementById('clear_voucher_detail_img');

        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            clearBtn.style.display = 'inline-block';
        }
    });
   
    // Clear image
    document.getElementById('clear_voucher_detail_img').addEventListener('click', function () {
        const input = document.getElementById('voucher_detail_img');
        const preview = document.getElementById('voucher_detail_img_preview');

        input.value = '';
        preview.src = '';
        preview.style.display = 'none';
        this.style.display = 'none';
    });

    //count set quantitry
    function editCalculateSetQty() {
        let inventoryQty = parseFloat($('#EditModal #inventory_qty').val());
        let voucherSet   = parseFloat($('#EditModal #voucher_set').val());
        if (!isNaN(inventoryQty) && !isNaN(voucherSet) && voucherSet > 0) {
            $('#EditModal #set_qty').val(Math.floor(inventoryQty / voucherSet));
        } else {
            $('#EditModal #set_qty').val('');
        }
    }

 
    function initTinyMCE() {

        if (typeof tinymce === 'undefined') return;

        // Remove old instances (important for modal edit)
        tinymce.remove('textarea.wysiwyg');

        tinymce.init({
            selector: "textarea.wysiwyg",
            paste_enable_default_filters: true,

            height: 200,
            sticky: true, // ✅ ADD THIS
               // OPTIONAL (better control)
            toolbar_sticky: true,
            toolbar_sticky_offset: 60, // adjust based on your header
            // IMPORTANT → prevents multiple block stacking
            forced_root_block: 'p',

            // Allow all elements
            valid_elements: '*[*]',
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
            entity_encoding: 'raw',

            // Character limit 180
            setup: function (editor) {
                editor.on('keydown', function (e) {
                    const text = editor.getContent({ format: 'text' });

                    if (text.length >= 180 && e.keyCode !== 8 && e.keyCode !== 46) {
                        e.preventDefault();
                    }
                });
            },

            // Image upload
            images_upload_url: '{{ url("admin/image-upload-editor") }}',
            images_upload_base_path: "{{ asset('images') }}/",

            plugins: [
                "advlist autolink link image lists charmap preview hr anchor",
                "searchreplace wordcount visualblocks code fullscreen",
                "table emoticons"
            ],

            // 🔥 Use formatselect (NOT styleselect)
            toolbar:
                "undo redo | formatselect | bold italic | " +
                "alignleft aligncenter alignright alignjustify | " +
                "bullist numlist outdent indent | link image ",

            // Allow only one block selection
            block_formats:
                "Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3",

            // Custom styles (NO header here)
            style_formats: [
                {
                    title: 'Red text',
                    inline: 'span',
                    styles: { color: '#ff0000' }
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
                    title: 'Table row 1',
                    selector: 'tr',
                    classes: 'tablerow1'
                }
            ],

            // Make H1 automatically red
            content_style: `
                h1 { color: #ff0000; }
            `
        });
    }

   
    $(document).on('scroll wheel touchmove', function () {
        // $('.tox-tinymce-aux').hide();
        $('.tox-tiered-menu').css('display', 'none');

    });

    function initEditor() {

        if (typeof tinymce === 'undefined') return;

        // Remove old instances (important for modal edit)
        tinymce.remove('textarea.wysiwyg');

        tinymce.init({
            selector: "textarea.wysiwyg",
              paste_enable_default_filters: true,

            height: 200,
             sticky: true, // ✅ ADD THIS
               // OPTIONAL (better control)
            toolbar_sticky: true,
            toolbar_sticky_offset: 60, // adjust based on your header

            // IMPORTANT → prevents multiple block stacking
            forced_root_block: 'p',

            // Allow all elements
            valid_elements: '*[*]',
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
            entity_encoding: 'raw',

            // Character limit 180 (21-03-2026) bug fixed
            // setup: function (editor) {
            //     editor.on('keydown', function (e) {
            //         const text = editor.getContent({ format: 'text' });

            //         if (text.length >= 180 && e.keyCode !== 8 && e.keyCode !== 46) {
            //             e.preventDefault();
            //         }
            //     });
            // },

            // Image upload
            images_upload_url: '{{ url("admin/image-upload-editor") }}',
            images_upload_base_path: "{{ asset('images') }}/",

            plugins: [
                "advlist autolink link image lists charmap preview hr anchor",
                "searchreplace wordcount visualblocks code fullscreen",
                "table emoticons"
            ],

            // 🔥 Use formatselect (NOT styleselect)
            toolbar:
                "undo redo | formatselect | bold italic | " +
                "alignleft aligncenter alignright alignjustify | " +
                "bullist numlist outdent indent | link image ",

            // ✅ Right click menu disabled due to UX issues and conflicts with our custom context menu
            contextmenu: "All",
            // Allow only one block selection
            block_formats:
                "Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3",

            // Custom styles (NO header here)
            style_formats: [
                {
                    title: 'Red text',
                    inline: 'span',
                    styles: { color: '#ff0000' }
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
                    title: 'Table row 1',
                    selector: 'tr',
                    classes: 'tablerow1'
                }
            ],

            // Make H1 automatically red
            content_style: `
                h1 { color: #ff0000; }
            `
        });
    }

    $(document).on('change', '.merchant-dropdown', function () {

        let clubId     = $(this).data('club');
        let merchantId = $(this).val();
        let modal      = $(this).closest('.modal');

        let clubContainer = modal.find("#outlet_container_" + clubId);
        modal.find(".outlet-container").show();
        // clubContainer.find(".selected-locations-summary").empty();
        // clubContainer.find(".selected-locations-hidden").empty();
        // clubContainer.find(".outlet-list").hide();

        if (!merchantId) {
            selectedOutletMap[clubId] = {}; // only clear if merchant removed
            clubContainer.find(".participating-merchant-location").empty();
            return;
        }

        loadParticipatingMerchantLocationsBday(modal, clubId, merchantId);
    });


    function loadParticipatingMerchantLocationsBday(modal, clubId, merchantId) {

        $.ajax({
            url: "{{ url('admin/birthday-voucher/get-club-locations-with-outlets') }}",
            type: "GET",
            data: { merchant_ids: merchantId },
            success: function (res) {

                let clubContainer = modal.find("#outlet_container_" + clubId);
                let locationBox   = clubContainer.find(".participating-merchant-location");

                locationBox.empty();

                if (!res || res.status !== 'success' || !res.locations || res.locations.length === 0) {
                    locationBox.html(`
                        <div class="alert alert-danger py-1 px-2 mb-2">
                            Outlets not found
                        </div>
                    `);
                    return;
                }

                res.locations.forEach(function (loc) {

                    let checked = selectedOutletMap[clubId] && selectedOutletMap[clubId][loc.id]
                        ? 'checked'
                        : '';

                    locationBox.append(`
                        <div class="form-check mb-1">
                            <input type="checkbox"
                                class="form-check-input outlet-checkbox-bday"
                                data-club="${clubId}"
                                data-id="${loc.id}"
                                data-name="${loc.name}"
                                id="outlet_${clubId}_${loc.id}"
                                ${checked}>
                            <label class="form-check-label"
                                for="outlet_${clubId}_${loc.id}">
                                ${loc.name}
                            </label>
                        </div>
                    `);
                });
            }
        });
    }

    $(document).on("change", ".outlet-checkbox-bday", function () {

        let clubId = $(this).data("club");
        let id     = $(this).data("id");
        let name   = $(this).data("name");
        let modal  = $(this).closest(".modal");

        if (!selectedOutletMap[clubId]) {
            selectedOutletMap[clubId] = {};
        }

        if (this.checked) {
            selectedOutletMap[clubId][id] = name;
        } else {
            delete selectedOutletMap[clubId][id];
        }

        updateSelectedLocationsSummaryBirthDayVoucher(modal, clubId);
        syncHiddenSelectedLocationsBday(modal, clubId);
    });

    function syncHiddenSelectedLocationsBday(modal, clubId) {

        let clubContainer = modal.find("#outlet_container_" + clubId);
        let hiddenDiv     = clubContainer.find(".selected-locations-hidden");

        hiddenDiv.empty();

        if (!selectedOutletMap[clubId]) return;

        Object.keys(selectedOutletMap[clubId]).forEach(function (locId) {

            hiddenDiv.append(`
                <input type="hidden"
                    name="selected_outlets[${clubId}][]"
                    value="${locId}">
            `);
        });
    }

    function updateSelectedLocationsSummaryBirthDayVoucher(modal, clubId) {
        modal.find(".outlet-container").show();

        let clubContainer = modal.find("#outlet_container_" + clubId);
        let wrapper = clubContainer.find(".outlet-list");
        let summary = clubContainer.find(".selected-locations-summary");

        summary.empty();
        
        if (!selectedOutletMap[clubId] ||
            Object.keys(selectedOutletMap[clubId]).length === 0) {

            wrapper.hide();
            return;
        }

        let html = Object.values(selectedOutletMap[clubId])
            .map(name => `<div>• ${name}</div>`)
            .join("");
            

        summary.html(html);
        wrapper.show();
    }

    function loadEditSelectedOutlets(modal, clubId, outlets = []) {
        if (!selectedOutletMap[clubId]) {
            selectedOutletMap[clubId] = {};
        }

        outlets.forEach(function (loc) {
            selectedOutletMap[clubId][loc.id] = loc.name;
        });
        
        updateSelectedLocationsSummaryBirthDayVoucher(modal, clubId);
        syncHiddenSelectedLocationsBday(modal, clubId);
    }

    function generateMonths() {

        let year = new Date().getFullYear();
        let months = [
            "January","February","March","April","May","June",
            "July","August","September","October","November","December"
        ];

        let html = "";

        months.forEach((month, index) => {

            let monthValue = year + "-" + String(index + 1).padStart(2, '0');

            html += `
                <div class="col-4 mb-2">
                    <button type="button"
                        class="btn btn-outline-primary w-100 month-btn"
                        data-month="${monthValue}">
                        ${month}
                    </button>
                </div>
            `;
        });

        $("#monthList").html(html);
    }

    $(document).on('shown.bs.collapse', '.accordion-collapse', function () {

        let icon = $(this)
            .closest('.accordion-item')
            .find('.toggle-icon');

        icon.removeClass('mdi-chevron-down')
            .addClass('mdi-chevron-up');
    });

    $(document).on('hidden.bs.collapse', '.accordion-collapse', function () {

        let icon = $(this)
            .closest('.accordion-item')
            .find('.toggle-icon');

        icon.removeClass('mdi-chevron-up')
            .addClass('mdi-chevron-down');
    });

    $(document).on('input', 'input[type="number"]:not(.validity_month)', function () {

        let maxLength = 6; // change if needed

        if (this.value.length > maxLength) {
            this.value = this.value.slice(0, maxLength);
        }

    });
    $(document).on('keydown', 'input[type="number"]', function(e) {
        if (e.key === '-' || e.key === 'Minus') {
            e.preventDefault();
        }
    });

    $(document).on('keydown', '.stock-input', function(e) {

        // block decimal and minus
        if (e.key === '.' || e.key === 'Decimal' || e.key === '-' || e.key === 'Minus') {
            e.preventDefault();
        }

    });

    $(document).on('input', '.stock-input', function () {

        let value = this.value.replace(/[^0-9]/g, ''); // allow digits only
        let maxLength = 6;

        if (value.length > maxLength) {
            value = value.slice(0, maxLength);
        }

        this.value = value;

    });

    $(document).on('paste', '.stock-input', function(e) {

        let paste = (e.originalEvent || e).clipboardData.getData('text');

        if (!/^\d+$/.test(paste)) {
            e.preventDefault();
        }

});

    $('#AddModal').on('shown.bs.modal', function () {
        $('.validation-error').hide();
    });

    function handleExpiryType(modal){

        let type = $(modal).find('#expiry_type').val();

        $(modal).find('#fixed_expiry_div').addClass('d-none');
        $(modal).find('#validity_period_div').addClass('d-none');

        if(type === 'fixed'){
            $(modal).find('#fixed_expiry_div').removeClass('d-none');
        }

        if(type === 'validity'){
            $(modal).find('#validity_period_div').removeClass('d-none');
        }

    }

    function limitMonthInput(selector, max = 12) {
        $(document).on('input', selector, function () {

            let value = $(this).val();

            // allow only 2 digits
            if (value.length > 2) {
                value = value.slice(0, 2);
                $(this).val(value);
            }

            let num = parseInt(value);

            if (num > max) {
                $(this).val(max);
            }

            if (num < 1 || isNaN(num)) {
                $(this).val('');
            }
        });
    }

    $(document).on('input', '.validity_month', function () {

        let value = $(this).val();

        if (value.length > 2) {
            value = value.slice(0, 2);
        }

        let num = parseInt(value);

        if (num > 99) {
            value = 99;
        }

        $(this).val(value);

    });

    $(document).on('input', '#voucher_set, #inventory_qty, #set_qty, #voucher_value', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });


    $(document).on('click', '.notification-item', function () {
        let id = $(this).data('id');

        $.post("{{ url('admin/notification/read') }}/" + id, {
            _token: $('meta[name="csrf-token"]').attr('content')
        });

        $(this).find('span[style]').remove();
    });

    
    $('#csvFile').on('change', function () {

        let file = this.files[0];
        if (!file) return;

        let formData = new FormData();
        formData.append('csvFile', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: "{{ url('admin/upload-csv') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            xhrFields: {
                responseType: 'blob' // 🔥 REQUIRED for download
            },
            success: function (res, status, xhr) {

                let disposition = xhr.getResponseHeader('Content-Disposition');

                if (disposition && disposition.includes('attachment')) {

                    // 🔴 DUPLICATE FOUND → download file
                    let blob = new Blob([res]);
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "duplicate_codes.xlsx";
                    link.click();
                    // ❌ reset file input
                    $('#csvFile').val('');
                    $('#uploadedFileLink').text('');
                    $('#removeCsvFile').hide();

                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate codes found',
                        text: 'Please check downloaded file for details',
                        confirmButtonText: 'OK'
                    });
                    
                } else {

                    // ✅ NO duplicate
                    // show_message('success', 'All codes are valid');

                    // show UI (your existing code)
                    $('#uploadedFileLink').text(file.name);
                    $('#uploadedFile').removeClass('d-none').addClass('d-flex');
                    $('#removeCsvFile').show();

                }
            },
            error: function () {
                show_message('error','Error while checking file');
            }
        });

    });

</script>

<script src="{{ URL::asset('/build/js/tableexport.jquery.plugin.js') }}"></script>
<script src="{{ URL::asset('/build/js/bootstrap-table.min.js') }}"></script>
<script src="{{ URL::asset('/build/js/bootstrap-table-filter-control.min.js') }}"></script>
<script src="{{ URL::asset('/build/js/bootstrap-table-export.min.js') }}"></script>
<script src="{{ URL::asset('/build/js/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('/build/js/tagify.min.js') }}"></script>
<script src="{{ URL::asset('/build/libs/select2/js/select2.min.js') }}"></script>
<script src="{{ URL::asset('/build/libs/snackbar/snackbar.min.js') }}"></script>
@yield('script')
<!-- App js -->
<script src="{{ URL::asset('build/js/app.js')}}"></script>
<script src="{{ URL::asset('build/js/custom.js')}}"></script>
<script src="{{ URL::asset('build/js/crud.js') }}"></script>
<script src="{{ URL::asset('/build/libs/flatpicker/flatpickr.js') }}"></script>
<script>
(function () {
  function closeOpenFlatpickrs() {
    document.querySelectorAll('input.flatpickr-input').forEach(function (el) {
      if (el._flatpickr && el._flatpickr.isOpen) {
        el._flatpickr.close();
      }
    });
  }

  function onModalMove(e) {
    var t = e.target;
    if (t && t.closest && t.closest('.modal.show')) {
      closeOpenFlatpickrs();
    }
  }

  // scroll doesn't bubble reliably -> use capture
  document.addEventListener('scroll', onModalMove, true);
  document.addEventListener('wheel', onModalMove, { capture: true, passive: true });
  document.addEventListener('touchmove', onModalMove, { capture: true, passive: true });
})();
</script>


@yield('script-bottom')