$(document).ready(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');
   
    $(document).on('click', '.submit-btn', function (e) {
        const action = $(this).val();
        const form = $(this).closest('form');

        // DRAFT → submit directly
        if (action == 'draft') {
            form.find('.action-field').val(action);
            tinymce.triggerSave();
            form.trigger('submit');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to submit this form.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.find('.action-field').val(action);
                tinymce.triggerSave();
                form.trigger('submit');
            }
        });
    });
    
    $(document).off("submit", "#add_frm").on("submit", "#add_frm", function (e) {
        e.preventDefault();

        let $form = $(this);
        if ($form.data('submitting')) return;
        $form.data('submitting', true);

        let form_data = new FormData(this);

        $.ajax({
            url: ModuleBaseUrl.slice(0, -1),
            headers: { 'X-CSRF-Token': csrf },
            type: "POST",
            data: form_data,
            processData: false,
            contentType: false,

            success: function (response) {
                $form.data('submitting', false);
                if (response.status === 'success' || response.status === true) {
                    $("#AddModal").modal('hide');
                    show_message(response.status, response.message);
                    $form[0].reset();
                    refresh_datatable("#bstable");
                    $("#add_frm .select2").val('').trigger('change');
                } else {
                    show_message(response.status, response.message);
                }
                remove_errors();
            },

            error: function (response) {

                $form.data('submitting', false);
                $('.participating-location-error').text('');

                let errors = response.responseJSON?.errors || {};

                // 🔥 Handle club locations error in Birthday voucher
                if (errors.locations) {
                    $('.club-location-error').text(errors.locations[0]);
                    delete errors.locations;
                }

                // 🔥 Handle participating merchant locations error
                if (errors.participating_merchant_locations) {
                    $('.participating-location-error').text(errors.participating_merchant_locations[0]);
                    delete errors.participating_merchant_locations;
                }

                //Birthday month
                Object.keys(errors).forEach(function(key) {

                    if (key.startsWith("month")) {

                        $("#month").addClass("is-invalid");

                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: errors[key][0] // 🔥 show actual backend message
                        });

                        return false;
                    }

                });

               // Club Location Validation msg
                $('.location-error-msg').remove();

                Object.keys(errors).forEach(function(key) {

                    if (key.startsWith("locations.")) {

                        let match = key.match(/locations\.(\d+)\.inventory_qty/);

                        if (match) {

                            let locationId = match[1];

                            let locationBox = $(`input[name="locations[${locationId}][inventory_qty]"]`)
                                                .closest('.location-box');

                            locationBox.after(`
                                <div class="text-danger location-error-msg mt-1">
                                    ${errors[key][0]}
                                </div>
                            `);
                        }

                        delete errors[key];
                    }

                });
                                

                // Show remaining normal field errors
                show_errors(errors);
            }

        });
    });

    $(document).on("click", ".edit", function (e) {

        $("#EditModal").modal('hide').remove();
    
        var id = $(this).data('id');
        $.ajax({
            url: ModuleBaseUrl + id + "/edit",
            type: 'GET',
            data: '',
            headers: { 'X-CSRF-Token': csrf },
            success: function (response) {
                // $("#EditModal").remove();
                $("body").append(response.html);

                let modal = $("#EditModal");

                selectedOutletMapMerchant = {};
                selectedOutlets = response.selectedOutlets || {};

                if (response.participatingLocations && response.participatingLocations.length > 0) {

                    response.participatingLocations.forEach(function (loc) {
                        selectedOutletMapMerchant[loc.id] = loc.name;
                    });
                }
                
                
                selectedOutletMap = {};
                selectedOutlets = response.selectedOutlets || {};

                Object.keys(selectedOutlets).forEach(function (clubId) {

                    selectedOutletMap[clubId] = {};

                    selectedOutlets[clubId].forEach(function (loc) {
                        selectedOutletMap[clubId][loc.id] = loc.name;
                    });
                });

                
                if (response.clubInventory) {

                    Object.keys(response.clubInventory).forEach(function (clubId) {

                        let qty = response.clubInventory[clubId];

                        $('#EditModal')
                            .find('input[name="locations[' + clubId + '][inventory_qty]"]')
                            .val(qty);
                    });
                }
              
                window.savedLocations = response.savedLocations || {};
                
                if (modal.find(".select2").length) {
                    modal.find(".select2").select2({
                        dropdownParent: modal.find(".modal-content")
                    });
                }

                if (modal.find(".select-multiple").length) {
                    modal.find(".select-multiple").chosen();
                }

                // tagify.addTags(["a", "b"])
                let input = modal.find('.select2-tags')[0];
                if (input) {
                    new Tagify(input, {
                        dropdown: {
                            enabled: 0,
                            delimiters: ","
                        }
                    });
                }

                if (modal.find(".datetimepicker").length) {
                    modal.find(".datetimepicker").flatpickr({
                        enableTime: true,
                        minDate: "today",
                        dateFormat: "Y-m-d H:i",
                    });
                }

                if ($("textarea.elm1").length > 0) {

                    tinymce.remove(); // Remove previous instances
                    tinymce.init({
                        selector: "textarea.elm1",
                        height: 300,
                        relative_urls: false,
                        remove_script_host: false,
                        convert_urls: true,
                        images_upload_url: BaseURL + 'admin/image-upload-editor',
                        images_upload_base_path: BaseURLImageAsset,
                        plugins: [
                            "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                            "save table contextmenu directionality emoticons template paste textcolor"
                        ],
                        toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons",
                        style_formats: [
                            { title: 'Bold text', inline: 'b' },
                            { title: 'Red text', inline: 'span', styles: { color: '#ff0000' } },
                            { title: 'Red header', block: 'h1', styles: { color: '#ff0000' } },
                            { title: 'Example 1', inline: 'span', classes: 'example1' },
                            { title: 'Example 2', inline: 'span', classes: 'example2' },
                            { title: 'Table styles' },
                            { title: 'Table row 1', selector: 'tr', classes: 'tablerow1' }
                        ]
                    });
                }

                $("#EditModal").modal('show');
            }
        });
    });

    $(document).off("submit", "#edit_frm").on("submit", "#edit_frm", function (e) {

        e.preventDefault();

        var id = $(this).data('id');

        var form_data = new FormData($(this)[0]);
        $.ajax({
            url: ModuleBaseUrl + id,
            headers: {
                'X-CSRF-Token': csrf,
            },
            type: "POST",
            data: form_data,
            processData: false,
            contentType: false,
            success: function (response) {

                if (response.status == 'success') {
                    show_message(response.status, response.message);
                    $("#EditModal").modal('hide').remove();
                    refresh_datatable("#bstable");
                } else {
                    show_message(response.status, response.message);
                }
            },
            error: function (response) {

                $('.club-location-error').text('');
                $('.participating-location-error').text('');
                let errors = response.responseJSON?.errors || {};

                let locationMessages = [];

                Object.keys(errors).forEach(function(key) {

                    // 🔥 Catch dynamic location keys
                    if (key.startsWith("locations.")) {
                        locationMessages.push(errors[key][0]);
                        delete errors[key];
                    }

                });

                if (locationMessages.length > 0) {
                    $('.club-location-error').text(locationMessages.join(', '));
                }

                  // 🔥 Handle participating merchant locations error
                if (errors.participating_merchant_locations) {
                    $('.participating-location-error').text(errors.participating_merchant_locations[0]);
                    delete errors.participating_merchant_locations;
                }

                // Show other errors normally
                show_errors(errors, "#edit_frm");
            }

        });
    });   

    $(document).on("click", ".delete_btn", function (e) {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "you want to delete this record.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ModuleBaseUrl + id,
                    type: 'DELETE',
                    data: '',
                    headers: { 'X-CSRF-Token': csrf },
                    success: function (response) {
                        show_message(response.status, response.message);
                        refresh_datatable("#bstable");
                    }
                });
            }
        });
    }); 

   // Add Birthday Voucher Validation Start

    $(document).on("click", "#submitBtnBV", function () {

        const formId = $(this).closest('.modal').find('form').attr('id');
        const formSelector = "#" + formId;

        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to submit this form.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then(result => {

            if (!result.isConfirmed) return;

            tinymce.triggerSave();

            // ✅ If EDIT → submit directly
            if (formId === "edit_frm") {
                $(formSelector).submit();
                return;
            }

            // ✅ If ADD → open month modal
            if (formId === "add_frm") {

                // 🔥 Store active form
                $("#monthSelectModal").data("activeForm", formSelector);

                generateYears();
                generateMonths();

                let monthModalEl = document.getElementById('monthSelectModal');
                let monthModal = new bootstrap.Modal(monthModalEl);

                monthModal.show();
            }

        });

    });
    
    //generate month list based on current month and year, disable past months, and pre-check already selected months
    function generateMonths() {

        let today = new Date();
        let currentYear = today.getFullYear();
        let currentMonthIndex = today.getMonth();
        let selectedYear = parseInt($("#yearSelect").val()) || new Date().getFullYear();

        let months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

        let html = "";

        months.forEach((month, index) => {

            let monthValue = selectedYear + "-" + String(index + 1).padStart(2, '0');

            // Disable only if current year AND past month
            let isPast = (selectedYear === currentYear) && (index < currentMonthIndex);

            html += `
                <div class="col-4 mb-3">
                    <div class="month-card ${isPast ? 'disabled-card' : ''}">
                        
                        <input type="checkbox"
                            class="month-checkbox"
                            value="${monthValue}"
                            id="month_${selectedYear}_${index}"
                            ${isPast ? 'disabled' : ''}>

                        <label for="month_${selectedYear}_${index}" class="month-label">
                            ${month} ${selectedYear}
                        </label>

                    </div>
                </div>
            `;
        });

        $("#monthList").html(html);
        // Pre-check already selected months
        $('input[name="month[]"]').each(function () {
            let existingMonth = $(this).val();
            $(`.month-checkbox[value="${existingMonth}"]`).prop("checked", true).closest(".month-card").addClass("active");
        });
    }

    $(document).on("change", ".month-checkbox", function () {

        let selectedMonth = $(this).val();
        let form = $("#edit_frm").length ? "#edit_frm" : "#add_frm";

        $(this).closest(".month-card").toggleClass("active", $(this).is(":checked"));

        if ($(this).is(":checked")) {

            if ($(`input[name="month[]"][value="${selectedMonth}"]`).length === 0) {
                $('<input>').attr('type', 'hidden').attr('name', 'month[]').val(selectedMonth).appendTo(form);
            }

        } else {

            $(`input[name="month[]"][value="${selectedMonth}"]`).remove();
        }
    });

    function generateYears() {

        let currentYear = new Date().getFullYear();
        let html = "";

        for (let i = 0; i <= 5; i++) {
            let year = currentYear + i;
            html += `<option value="${year}">${year}</option>`;
        }

        $("#yearSelect").html(html);

        // Set default to current year
        $("#yearSelect").val(currentYear);
    }

    $(document).on("change", "#yearSelect", function () {
        generateMonths();
    });

    $(document).on("click", "#confirmMonths", function () {

        let monthModalEl = document.getElementById('monthSelectModal');
        let monthModal = bootstrap.Modal.getInstance(monthModalEl);

        $("#monthInput").removeClass("is-invalid");
        $("#monthError").hide();

        monthModal.hide();

        let activeForm = $("#monthSelectModal").data("activeForm");

        if (activeForm) {
            $(activeForm).submit();
        }
    });
   
});
