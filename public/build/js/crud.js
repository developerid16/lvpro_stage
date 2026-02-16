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
                if (response.status === 'success') {
                    show_message(response.status, response.message);
                    $("#AddModal").modal('hide');
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

                // 🔥 Handle club locations error
                if (errors.locations) {
                    $('.club-location-error').text(errors.locations[0]);
                    delete errors.locations;
                }

                // 🔥 Handle participating merchant locations error
                if (errors.participating_merchant_locations) {
                    $('.participating-location-error').text(errors.participating_merchant_locations[0]);
                    delete errors.participating_merchant_locations;
                }

                if (errors.month) {

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: errors.month[0]
                    });

                    // OR show below input
                    $("#month").addClass("is-invalid");
                }

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

                selectedOutletMap = {};
                selectedOutlets = response.selectedOutlets || {};

                if (response.participatingLocations && response.participatingLocations.length > 0) {

                    response.participatingLocations.forEach(function (loc) {
                        selectedOutletMap[loc.id] = loc.name;
                    });
                }
                
                
                if (response.selectedOutlets) {
                    Object.keys(response.selectedOutlets).forEach(function (clubId) {

                        selectedOutletMap[clubId] = {};

                        response.selectedOutlets[clubId].forEach(function (loc) {
                            selectedOutletMap[clubId][loc.id] = loc.name;
                        });

                    });
                }

                
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
                show_errors(response.responseJSON.errors);
                show_errors(response.responseJSON.errors, "#edit_frm");
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
        function openClub(club) {
            bootstrap.Collapse
                .getOrCreateInstance(club.find(".accordion-collapse")[0])
                .show();
        }

        const req = (el, cond = true) => el.toggleClass("is-invalid", cond && !el.val());

        $(document).on("click", "#submitBtnBV", function () {
            const formId = $(this).closest('.modal').find('form').attr('id'); 

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

                let isValid = true;
                let anyLocationUsed = false;

                // ---- Basic Required Fields ----
                if (formId === "add_frm") {

                    req($('input[name="from_month"]'));
                    req($('input[name="to_month"]'), $('input[name="from_month"]').val());
                    req($('input[name="voucher_image"]'));
                    req($('input[name="voucher_detail_img"]'));
                    req($('input[name="name"]'));
                    req($('textarea[name="description"]'));
                    req($('textarea[name="how_to_use"]'));
                    req($('textarea[name="term_of_use"]'));
                    req($('select[name="merchant_id"]'));
                    req($('input[name="voucher_validity"]'));
                    req($('select[name="inventory_type"]'));
                    req($('input[name="voucher_value"]'));
                    req($('input[name="voucher_set"]'));
                    req($('input[name="set_qty"]'));
                    req($('select[name="clearing_method"]'));

                    const inventoryType = $('select[name="inventory_type"]').val();

                    if (inventoryType === "0")
                        req($('input[name="inventory_qty"]'));

                    if (inventoryType === "1")
                        req($('input[name="csvFile"]'));

                    if($('input[name="voucher_validity"]').val() === ""){
                    $('.voucher_validity').addClass("is-invalid");
                        $('.voucher_validity').next('.invalid-feedback').add();
                    }else{
                        $('.voucher_validity').removeClass("is-invalid");
                        $('.voucher_validity').next('.invalid-feedback').remove();
                    }
                }

                // ---- Accordion Locations ----
                $("#location_with_outlet .accordion-item").each(function () {

                    let club = $(this);
                    let inventory = club.find("input[name*='[inventory_qty]']").val();
                    let merchant = club.find(".merchant-dropdown").val();
                    let outlets = club.find(".outlet-checkbox-bday:checked").length;

                    if ((inventory > 0) || outlets) anyLocationUsed = true;

                   

                });

                if (!anyLocationUsed) {
                    $(".club-location-error")
                        .text("Please fill at least one location");
                    isValid = false;
                }
                tinymce.triggerSave();

               if (isValid) {

                    generateMonths();
                    generateYears();

                    let monthModalEl = document.getElementById('monthSelectModal');
                    let monthModal = new bootstrap.Modal(monthModalEl);

                    monthModal.show();

                    // If modal is closed without selecting month → submit normally
                    monthModalEl.addEventListener('hidden.bs.modal', function handler() {

                        monthModalEl.removeEventListener('hidden.bs.modal', handler);

                        // If no month selected → submit form normally
                        if (!$("#selected_month").val()) {
                            submitMainForm();
                        }
                    });
                }


            });
        });


    
    //generate month list based on current month and year, disable past months, and pre-check already selected months
    function generateMonths() {

        let today = new Date();
        let currentYear = today.getFullYear();
        let currentMonthIndex = today.getMonth();

        let selectedYear = parseInt($("#yearSelect").val());

        let months = [
            "Jan","Feb","Mar","Apr","May","Jun",
            "Jul","Aug","Sep","Oct","Nov","Dec"
        ];

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

            $(`.month-checkbox[value="${existingMonth}"]`)
                .prop("checked", true)
                .closest(".month-card")
                .addClass("active");
        });
    }


    $(document).on("change", ".month-checkbox", function () {

        let selectedMonth = $(this).val();
        let form = $("#edit_frm").length ? "#edit_frm" : "#add_frm";

        $(this).closest(".month-card").toggleClass("active", $(this).is(":checked"));

        if ($(this).is(":checked")) {

            if ($(`input[name="month[]"][value="${selectedMonth}"]`).length === 0) {
                $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'month[]')
                    .val(selectedMonth)
                    .appendTo(form);
            }

        } else {

            $(`input[name="month[]"][value="${selectedMonth}"]`).remove();
        }
    });

    function submitMainForm() {

        if ($("#edit_frm").length) {
            $("#edit_frm").submit();
        } else {
            $("#add_frm").submit();
        }
    }

    function generateYears() {

        let currentYear = new Date().getFullYear();
        let html = "";

        for (let i = 0; i <= 5; i++) {
            let year = currentYear + i;
            html += `<option value="${year}">${year}</option>`;
        }

        $("#yearSelect").html(html);
    }

    $(document).on("change", "#yearSelect", function () {
        generateMonths();
    });

    $(document).on("click", "#confirmMonths", function () {

        $("#monthSelectModal").modal("hide");
        submitMainForm();
    });
   
});
