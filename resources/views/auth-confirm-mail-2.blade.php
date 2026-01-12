@extends('layouts.master-without-nav')

@section('title')
    @lang('translation.Confirm_Mail') 2
@endsection

@section('css')
    <!-- owl.carousel css -->
    <link rel="stylesheet" href="{{ URL::asset('/build/libs/owl.carousel/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('/build/libs/owl.carousel/assets/owl.theme.default.min.css') }}">
@endsection

@section('body')

    <body class="auth-body-bg">
    @endsection

    @section('content')

        <div>
            <div class="container-fluid p-0">
                <div class="row g-0">

                    <div class="col-xl-9">
                        <div class="auth-full-bg pt-lg-5 p-4">
                            <div class="w-100">
                                <div class="bg-overlay"></div>
                                <div class="d-flex h-100 flex-column">

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end col -->

                    <div class="col-xl-3">
                        <div class="auth-full-page-content p-md-5 p-4">
                            <div class="w-100">

                                <div class="d-flex flex-column h-100">
                                    <div class="mb-4 mb-md-5">
                                        <a href="index" class="d-block auth-logo">
                                            <img src="{{ URL::asset('/build/images/logo-dark.png') }}?q={{ time() }}" alt="" height="18"
                                                class="auth-logo-dark">
                                            <img src="{{ URL::asset('/build/images/logo-light.png') }}?q={{ time() }}" alt="" height="18"
                                                class="auth-logo-light">
                                        </a>
                                    </div>
                                    <div class="my-auto">

                                        <div class="text-center">

                                            <div class="avatar-md mx-auto">
                                                <div class="avatar-title rounded-circle bg-light">
                                                    <i class="bx bx-mail-send h1 mb-0 text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="p-2 mt-4">
                                                <h4>Success !</h4>
                                                <p class="text-muted">At vero eos et accusamus et iusto odio dignissimos
                                                    ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti
                                                    quos dolores et</p>
                                                <div class="mt-4">
                                                    <a href="index" class="btn btn-success">Back to Home</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 mt-md-5 text-center">
                                        <p class="mb-0">© <script>
                                                document.write(new Date().getFullYear())

                                            </script> Safra. Crafted with <i class="mdi mdi-heart text-danger"></i> by
                                            Safra</p>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container-fluid -->
        </div>

    @endsection
    @section('script')
        <!-- owl.carousel js -->
        <script src="{{ URL::asset('/build/libs/owl.carousel/owl.carousel.min.js') }}"></script>
        <!-- auth-2-carousel init -->
        <script src="{{ URL::asset('/build/js/pages/auth-2-carousel.init.js') }}"></script>
    @endsection