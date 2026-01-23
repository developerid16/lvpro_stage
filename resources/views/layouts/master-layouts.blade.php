<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title> @yield('title') | {{config('app.name')}} </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- App favicon -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        if (Cache::has('CMScolor')) {

            $color = Cache::get('CMScolor', '#FFC0CB');
        } else {
            $color = App\Models\ContentManagement::where('name', 'CMScolor')->value('value') ?? '#FFC0CB';
            Cache::put('CMScolor', $color);
        }

    @endphp
    <style>
        :root {
            --cms-color:
                {{ $color }}
            ;
        }
    </style>
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.png') }}">
    @include('layouts.head-css')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
<script src="{{ URL::asset('build/libs/jquery/jquery.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

</head>

@section('body')

<body data-topbar="light" data-layout="horizontal">
    <!-- Loader -->
    <div id="preloader">
        <div id="status">
            <div class="spinner-chase">
                <div class="chase-dot"></div>
                <div class="chase-dot"></div>
                <div class="chase-dot"></div>
                <div class="chase-dot"></div>
                <div class="chase-dot"></div>
                <div class="chase-dot"></div>
            </div>
        </div>
    </div>
    @show

    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.horizontal')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <!-- Start content -->
                <div class="container-fluid">
                    @yield('content')
                </div> <!-- content -->
            </div>
            @include('layouts.footer')
        </div>
        <!-- ============================================================== -->
        <!-- End Right content here -->
        <!-- ============================================================== -->
    </div>
    <!-- END wrapper -->

    <!-- Right Sidebar -->
    <!-- END Right Sidebar -->

    @include('layouts.vendor-scripts')
</body>

</html>