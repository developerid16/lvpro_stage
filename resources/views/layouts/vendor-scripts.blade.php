<!-- JAVASCRIPT -->
<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/metismenu/metisMenu.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/node-waves/waves.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/lightbox/js/lightbox.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/chosen/chosen.jquery.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script>
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
   
    
    function bindStartEndFlatpickr(startSelector, endSelector) {
        
        const startEl = document.querySelector(startSelector);
        const endEl   = document.querySelector(endSelector);
        if (startEl._flatpickr) return;

        if (!startEl || !endEl) return;

        let startPicker, endPicker;

        startPicker = flatpickr(startEl, {
            minDate: "today",
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i:S',
            altInput: true,
            altFormat: 'Y-m-d H:i:S',

            onReady(_, __, instance) {
                instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';
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
            enableSeconds: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i:S',
            altInput: true,
            altFormat: 'Y-m-d H:i:S',

            onReady(_, __, instance) {
                instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';
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


    function bindStartEndFlatpickrEdit(modal, startSelector, endSelector) {

        const startEl = modal.querySelector(startSelector);.0
        const endEl   = modal.querySelector(endSelector);

        if (!startEl || !endEl) return;

        // prevent double init
        if (startEl._flatpickr) return;

        // normalize bad value like: 2026-01-23 00:00:00 01:00:00
        startEl.value = normalizeDateTime(startEl.value);
        endEl.value   = normalizeDateTime(endEl.value);

        let endPickerInstance;

        const startPickerInstance = flatpickr(startEl, {
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i:s',
            altInput: true,
            altFormat: 'Y-m-d H:i:s',
            defaultDate: startEl.value
                ? new Date(startEl.value.replace(' ', 'T'))
                : null,

            onReady(_, __, instance) {
                instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';

                if (instance.selectedDates.length && endPickerInstance) {
                    const startDate = instance.selectedDates[0];
                    endPickerInstance.set('minDate', startDate);
                    endPickerInstance.set('clickOpens', true);
                    endPickerInstance.altInput.removeAttribute('disabled');
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
                    endPickerInstance.altInput.setAttribute('disabled', true);
                }
            }
        });

        endPickerInstance = flatpickr(endEl, {
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i:s',
            altInput: true,
            altFormat: 'Y-m-d H:i:s',
            defaultDate: endEl.value
                ? new Date(endEl.value.replace(' ', 'T'))
                : null,
            clickOpens: false,

            onReady(_, __, instance) {
                instance.altInput.placeholder = 'yyyy-MM-dd HH:mm:ss';
                instance.jumpToDate(instance.selectedDates[0] || new Date());
            }
        });

        if (startPickerInstance.selectedDates.length) {
            safeEnableEndPicker(endPickerInstance, startPickerInstance.selectedDates[0]);
        } else {
            endPickerInstance.altInput.setAttribute('disabled', true);
        }
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

        if (method === "2") {
            merchantField.show();
            outletSection.show();
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

        // RESET UI
        locationField.hide();
        merchantField.hide();
        outletWrapper.hide();
        outletList.empty();
        outletSection.hide();

        // External link / text based
        if ([0, 1, 3,4].includes(method)) {
            locationField.show();
            return;
        }

        // Merchant based
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

        // Everything else → reset merchant data
        merchantSelect.val(null).trigger('change');
        selectedOutletMapMerchant = {};
        modal.find('#selected_locations_summary').empty();
        modal.find('#selected_locations_hidden').empty();
    }

    function editToggleInventoryFields(modal) {

        let type = modal.find('.inventory_type').val();
        let fileField = modal.find('.file');
        let qtyField  = modal.find('.inventory_qty');
        modal.find('#inventory_qty').prop('readonly', false);

        let clearing = modal.find('#clearing_method');
        clearing.find('option[value="3"], option[value="4"]').show();        
        if (type === "1") {
            fileField.show();
            qtyField.show();
            
        } else if (type === "0") {
            modal.find('#inventory_qty').prop('readonly', false);
            qtyField.show();
            fileField.hide();
            fileField.find("input").val(""); // clear
            clearing.find('option[value="3"], option[value="4"]').hide();
            if (clearing.val() === "3" || clearing.val() === "4") {
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
            qtyField.find("input").val(""); // clear
        } else if (type === "0") {
            modal.find('#inventory_qty').val('');
            qtyField.show();
            fileField.hide();
            fileField.find("input").val(""); // clear
            clearing.find('option[value="3"], option[value="4"]').hide();
            if (clearing.val() === "3" || clearing.val() === "4") {
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

 
    function initTinyMCE(context = document) {
        if (typeof tinymce === 'undefined') return;
    
        // remove existing editors (important for edit modal)
        tinymce.remove(context.querySelectorAll('textarea.wysiwyg'));
    
        tinymce.init({
            selector: "textarea.wysiwyg",
            height: 200,
            valid_elements: '*[*]',
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
            forced_root_block: false,
            entity_encoding: 'raw',
    
            setup: function (editor) {
                editor.on('keydown', function (e) {
                    const text = editor.getContent({ format: 'text' });
    
                    if (text.length >= 180 && e.keyCode !== 8 && e.keyCode !== 46) {
                        e.preventDefault();
                    }
                });
            },
    
            images_upload_url: '{{ url("admin/image-upload-editor") }}',
            images_upload_base_path: "{{ asset('images') }}/",
    
            plugins: [
                "advlist autolink link image lists charmap preview hr anchor",
                "searchreplace wordcount visualblocks code fullscreen",
                "table emoticons"
            ],
    
            toolbar:
                "undo redo | styleselect | bold italic | " +
                "alignleft aligncenter alignright alignjustify | " +
                "bullist numlist outdent indent | link image | code"
        });
    }    
    
    function initEditor() {
        if (typeof tinymce === 'undefined') return;
                
        tinymce.init({
            selector: "textarea.wysiwyg",
            height: 200,
            valid_elements: '*[*]',
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
            forced_root_block: false,
            entity_encoding: 'raw',
    
            setup: function (editor) {
                editor.on('keydown', function (e) {
                    const text = editor.getContent({ format: 'text' });
    
                    if (text.length >= 180 && e.keyCode !== 8 && e.keyCode !== 46) {
                        e.preventDefault();
                    }
                });
            },
    
            images_upload_url: '{{ url("admin/image-upload-editor") }}',
            images_upload_base_path: "{{ asset('images') }}/",
    
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media",
                "save table directionality emoticons template textcolor"
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
<script src="{{ URL::asset('build/js/crud.js') }}"></script>

@yield('script-bottom')