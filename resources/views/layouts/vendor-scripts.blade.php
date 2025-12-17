<!-- JAVASCRIPT -->
<script src="{{ URL::asset('build/libs/jquery/jquery.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/metismenu/metisMenu.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/node-waves/waves.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/lightbox/js/lightbox.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/chosen/chosen.jquery.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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