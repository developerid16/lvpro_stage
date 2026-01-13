@yield('css')

<!-- Bootstrap Css -->
<link href="{{ URL::asset('/build/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('/build/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('/build/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('/build/css/custom.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('/build/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('/build/libs/snackbar/snackbar.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/lightbox/css/lightbox.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/chosen/chosen.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ URL::asset('/build/libs/bdt/bootstrap-table.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
 <style>
    .chosen-container, .chosen-container-multi{
        width: 100% !important
    }
 </style>

<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
