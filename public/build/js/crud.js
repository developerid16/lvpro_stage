$(document).ready(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');

    // 🔥 CLICK HANDLER
    $(document).on('click', '.submit-btn', function () {
        const action = $(this).val();
        $('#action').val(action);
        tinymce.triggerSave();

        const editId = $('#edit_frm').data('id');

        if (editId) {
            $('#edit_frm').trigger('submit');
        } else {
            $('#add_frm').trigger('submit');
        }
    });
  
$(document)
  .off("submit", "#add_frm")
  .on("submit", "#add_frm", function (e) {
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
              show_errors(response.responseJSON?.errors || {});
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
                // if (response && response.savedLocations && response.savedLocations.length > 0) {
                      window.savedLocations = response.savedLocations || {};

                // }
                if (response && response.participatingLocations && response.participatingLocations.length > 0){
                    participatingLocations = response.participatingLocations;
                }
                
                $("body").append(response.html);
                $("#EditModal .select2").select2({
                    dropdownParent: $("#EditModal .modal-content")
                });
                $("#EditModal .select-multiple").chosen({
                });
              

                var input = document.querySelector('#EditModal .select2-tags')
                var tagify = new Tagify(input, {
                    dropdown: {
                        enabled: 0,
                          delimiters: ","
                    },
                 })

                // tagify.addTags(["a", "b"])

                if ($(".datetimepicker").length > 0) {
                    $(".datetimepicker").flatpickr({
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
                    console.log('tinymce', tinymce);
                }

                $("#EditModal").modal('show');

            }
        });
    });

    $(document).on("submit", "#edit_frm", function (e) {
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
                console.log(response,'response');
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

});
