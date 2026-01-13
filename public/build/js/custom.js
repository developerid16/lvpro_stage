var filterDefaultsStatus = {
    Active: 'Active',
    Disabled: 'Disabled',

};
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    document.addEventListener('focusin', function (e) {
        if (e.target.closest('.tox-tinymce-aux, .moxman-window, .tam-assetmanager-root') !== null) {
            e.stopImmediatePropagation();
        }
    });


    $('body').on('click', '.delete-btn', function () {
        const btn = $(this)
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire(
                    'Deleted!',
                    'Your file has been deleted.',
                    'success'
                )
                const fd = new FormData()
                fd.append("_method", "delete")
                $.ajax({
                    url: btn.attr('data-url'),
                    type: "delete",
                    data: fd,
                    processData: false, //add this
                    contentType: false, //and this

                    beforeSend: function (xhr) {
                    },
                    success: function (response) {

                        if (response.status == true) {
                            Swal.fire({
                                position: 'top-end',

                                title: response.msg,
                                showConfirmButton: false,
                                timer: 1500
                            })
                            if (btn.attr('data-reload') == 'true') {
                                location.reload();
                            }

                        }
                    },
                    error: function (error) {

                        console.log(error);
                    }
                });

            }

        })

    });

    $(document).on('change', '.start_date', function () {
        let d = $(this);
        $('.end_date').val('');
        $('.end_date').attr('min', d.val());
    });
    $(document).on('click', '*[data-bs-toggle="modal"]', function () {
        var modal = $(this).data('bs-target');
        if ($(modal + " .select2").length) {
            $(modal + " .select2").select2({
                dropdownParent: $(modal)
            });
        }
        if ($(modal + " .select2-tags").length) {
            var input =   document.querySelector('.select2-tags')
            var tagify = new Tagify(input, {
                dropdown: {
                    enabled: 0
                },
            })
        }

    });

    // lightbox.prototype.enable = function () {
    //     var _this = this;
    //     return $(document).on('click', 'img[data-lightbox^=lightbox]', function (e) {
    //         _this.start($(e.currentTarget));
    //         return false;
    //     });
    // };

});

setTimeout(function () {
    hide_loader();
}, 300)

$(".select2").select2();
$(".select-multiple").chosen({
    // dropdownParent: $("#EditModal .modal-content")
});

function refresh_datatable(table) {
    let showTable = $(table);
    showTable.bootstrapTable('refresh');
}
function show_loader() {
    $("#custom_preloader").slideDown(900);
}

function hide_loader() {
    $("#custom_preloader").slideUp(900);
}

function show_errors(error, form_id = null) {
    $(".validation-error").remove();
    $(".error, .custom-error").html('');
    if (typeof error !== "undefined" && error !== "") {
        if (form_id === null) {
            $.each(error, function (index, key) {
                if ($("#" + index + '_error').length) {
                    $("#" + index + '_error').html(key);
                } else {
                    $('[name=' + index + ']').parent().append("<p class='validation-error'>" + key + "</p>");
                }
            });
        } else {
            $.each(error, function (index, key) {
                if ($(form_id + " #" + index + '_error').length) {
                    $(form_id + " #" + index + '_error').html(key);
                } else {
                    $(form_id + ' [name=' + index + ']').parent().append("<p class='validation-error'>" + key + "</p>");
                }
            });
        }
    }
    hide_loader()
}

function remove_errors(form_id = null) {
    if (!form_id) {
        $(".validation-error").remove();
        $(".error, .custom-error").html('');
    } else {
        $(form_id).find(".validation-error").remove();
        $(form_id).find(".error, .custom-error").html('');

    }
    hide_loader();
}



function show_message(status, text) {
    if (status == 'fail') {
        status = 'error';
    }
    Snackbar.show({ text, });

}
