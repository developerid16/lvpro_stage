@extends('layouts.master-layouts')

@section('title')
    Broadcast Message
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
            Broadcast Message
        @endslot
    @endcomponent



    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
            {{-- <h4 class="card-title mb-0">Broadcast Message</h4> --}}
            <div class="ml_auto">

                @can("$permission_prefix-create")
                    <button class="sh_btn  btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"
                        onclick="hideshowdata('edm')"><i class="mdi mdi-plus"></i>
                        Specific User</button>
                    <button class="sh_btn ml_auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddModal"
                        onclick="hideshowdata('broadcast')"><i class="mdi mdi-plus"></i>
                        All User</button>
                @endcan
            </div>

        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="sh_table table table-bordered" id="bstable" data-toggle="table"
                    data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest"
                    data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count"
                    data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false"
                    data-filter-control="true" data-show-columns-toggle-all="false">
                    <thead>
                        <tr>
                            <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75"
                                data-width-unit="px" data-searchable="false">Sr. No.</th>
                            <th data-field="title" data-filter-control="input" data-sortable="true">Broadcast Message</th>
                            <th data-field="date" data-sortable="true">Broadcast Message Date & Time</th>
                            <th data-field="type">Type</th>
                            <th data-field="status">Status</th>
                            <th data-field="created_at">Created At</th>
                            <th class="text-center" data-field="action" data-searchable="false">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Create -->
    @can("$permission_prefix-create")
        @include('admin.broadcast.add-edit-modal')
    @endcan
    <!-- end modal -->
@endsection

@section('script')
    <script src='{{ URL::asset('build/libs/tinymce/tinymce.min.js') }}'></script>
    <script src="{{ URL::asset('/build/libs/flatpicker/flatpickr.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('/build/libs/flatpicker/flatpickr.min.css') }}">

    <script>
        var csvrows = 0
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && Swal.isVisible()) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        });
        document.getElementById("email_csv").addEventListener("change", function(event) {
            const file = event.target.files[0]; // Get the selected file

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const content = e.target.result; // Read the file content
                    const rows = content.split("\n"); // Split by newline to get rows

                    // Filter out empty lines (if any)
                    const rowCount = rows.filter(row => row.trim() !== "").length;
                    csvrows = rowCount - 1
                    console.log(`Total rows: ${rowCount}`);

                    // document.getElementById("rowCount").textContent = `Total rows: ${rowCount}`;
                };

                reader.onerror = function() {
                    console.error("An error occurred while reading the file.");
                };

                reader.readAsText(file); // Read the file as text
            } else {
                console.log("No file selected.");
            }
        });


        $(document).ready(function() {

        });
        var ModuleBaseUrl = "{{ $module_base_url }}/";
        var totalUser = "{{ $totaluser }}";
        var DataTableUrl = ModuleBaseUrl + "datatable";
        var typeofbrodcast = ""

        function ajaxRequest(params) {
            $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
                $('.fixed-table-body .fixed-table-loading').removeClass('open');
                params.success(res)
            })
        }

        function hideshowdata(type) {
            typeofbrodcast = type
            if (type == 'edm') {
                $("#is_type").val("edm")
                $('.hide-edm').hide();
                $('.hide-broadcast').show();
                $('#email-broadcast').show();

            } else {
                $("#is_type").val("broadcast")
                $('.hide-edm').show();
                $('.hide-broadcast').hide();
                $("#SMS,#EmailCheck,#PushNotification").prop("checked", false);


                // hide default checkboc option
                $('#sms-broadcast,#email-broadcast,#push-broadcast').hide();
            }
        }

        function handleCheckboxChange(checkbox, type) {
            const id = checkbox.dataset.divid; // Get the value of data-divid
            if (checkbox.checked) {
                $(`#${type} #${id}`).show();
            } else {
                $(`#${type} #${id}`).hide();
                if (id === 'sms-broadcast') {
                    $(`#${type} [name="sms_content"]`).val(''); // Clears input

                }
                if (id === 'email-broadcast') {

                    $(`#${type}  [name="email_subject"]`).val(''); // Clears textarea
                    $(`#${type}  [name="email_content"]`).val(''); // Clears textarea
                }
                if (id === 'push-broadcast') {
                    $(`#${type}  [name="inapp_title"]`).val(''); // Clears input
                    $(`#${type}  [name="inapp_content"]`).val(''); // Clears input
                    $(`#${type}  [name="push_title"]`).val(''); // Clears input
                    $(`#${type}  [name="push_subtitle"]`).val(''); // Clears input
                }
                // reset input value and textarea
            }
        }

        function disableNow(e) {

            $('input[name="date_of_publish"').attr('disabled', e.target.checked);


        }

        function askforconfirmation(type) {

            tinymce.triggerSave(); // Updates the textarea value with TinyMCE content
            if (type === 'edit_frm') {
                typeofbrodcast = 'broadcast'
            }

            if (typeofbrodcast != 'broadcast' || $(`#${type}  #SMS`).is(':checked') || $(`#${type} #EmailCheck`).is(
                    ':checked') || $(
                    `#${type} #PushNotification`)
                .is(':checked')) {



            } else {

                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: 'Please select at least one of broadcast',
                    showConfirmButton: false,
                    timer: 1500
                })
                return;
            }
            if (typeofbrodcast === "broadcast") {

                console.log("${type} .brodcastform", `${type} .brodcastform`);

                $(`#${type}`).submit();


                // Swal.fire({
                //     title: 'Are you sure?',
                //     text: `This action will blast to ${totalUser}, Do you wish to proceed?`,
                //     icon: 'warning',
                //     showCancelButton: true,
                //     confirmButtonText: 'Yes',
                //     cancelButtonText: 'No',
                //     reverseButtons: true
                // }).then((result) => {
                //     if (result.isConfirmed) {

                //         // $.ajax({
                //         //     url: ModuleBaseUrl + id,
                //         //     type: 'DELETE',
                //         //     data: '',
                //         //     headers: { 'X-CSRF-Token': csrf },
                //         //     success: function (response) {
                //         //         show_message(response.status, response.message);
                //         //         refresh_datatable("#bstable");
                //         //     }
                //         // });
                //     }
                // });
            } else {
                var emailuser = csvrows + ' Emails'

                Swal.fire({
                    title: 'Are you sure?',
                    text: `This action will blast to ${emailuser}, Do you wish to proceed?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(`#${type}`).submit();

                        // $.ajax({
                        //     url: ModuleBaseUrl + id,
                        //     type: 'DELETE',
                        //     data: '',
                        //     headers: { 'X-CSRF-Token': csrf },
                        //     success: function (response) {
                        //         show_message(response.status, response.message);
                        //         refresh_datatable("#bstable");
                        //     }
                        // });
                    }
                });

            }

        }

        function sendTestTemplate(id) {
            console.log("id", id, `#${id}-email_subject`);

            const emails = $(`#${id}-emails`).val().split(',').filter(email => email.trim()).length;
            console.log(emails, "emails");
            var emailuser = emails + ' Emails'
            Swal.fire({
                title: 'Are you sure?',
                text: `This action will blast to ${emailuser}, Do you wish to proceed?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#send-test-email').attr("disabled", true);
                    show_message("success", "Please wait...");


                    var form_data = new FormData();
                    var content = tinymce.get(`${id}-email_content`).getContent();

                    form_data.append('email_content', content);
                    form_data.append('subject', $(`#${id}-email_subject`).val());
                    form_data.append('emails', $(`#${id}-emails`).val());
                    var files = document.getElementById('attachments').files;
                    for (var x = 0; x < files.length; x++) {
                        form_data.append("attachments[]", files[x]);
                    }
                    const csrf = $('meta[name="csrf-token"]').attr('content')


                    $.ajax({
                        url: ModuleBaseUrl.slice(0, -1) + '/testing-template',
                        headers: {
                            'X-CSRF-Token': csrf,
                        },
                        type: "POST",
                        data: form_data,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.status === 'success') {
                                show_message(response.status, response.message);
                                $('#send-test-email').attr("disabled", false);

                                // $("#AddModal").modal('hide');
                                // $("#add_frm").trigger("reset");
                                // refresh_datatable("#bstable");
                                // $("#add_frm .select2").val('').trigger('change');
                            } else {
                                show_message(response.status, response.message);
                                $('#send-test-email').attr("disabled", false);

                            }
                            // remove_errors();
                        },
                        error: function(response) {
                            show_errors(response.responseJSON.errors);
                        }
                    });
                }
            });



        }
        $(function() {
            tinymce.init({
                selector: "textarea.elm1",
                height: 300,
                relative_urls: false,
                remove_script_host: false,
                convert_urls: true,
                images_upload_url: '{{ url('admin/image-upload-editor') }}',
                images_upload_base_path: "{{ asset('images') }}/",
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

            $(".datetimepicker").flatpickr({
                enableTime: true,
                minDate: "today",
                dateFormat: "Y-m-d H:i",
            });

        });
    </script>
    <script src="{{ URL::asset('build/js/crud.js') }}"></script>
@endsection
