@extends('layouts.master-without-nav')

@section('title')
    @lang('translation.Register') 2
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
                                            <img src="{{ URL::asset('/build/images/logo-dark.png') }}" alt="" height="18"
                                                class="auth-logo-dark">
                                            <img src="{{ URL::asset('/build/images/logo-light.png') }}" alt="" height="18"
                                                class="auth-logo-light">
                                        </a>
                                    </div>
                                    <div class="my-auto">

                                        <div>
                                            <h5 class="text-primary">Register account</h5>
                                            <p class="text-muted">Get your free Shilla account now.</p>
                                        </div>

                                        <div class="mt-4">
                                            <form class="needs-validation" novalidate action="index">

                                                <div class="mb-3">
                                                    <label for="useremail" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="useremail"
                                                        placeholder="Enter email" required>
                                                    <div class="invalid-feedback">
                                                        Please Enter Email
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input type="text" class="form-control" id="username"
                                                        placeholder="Enter username" required>
                                                    <div class="invalid-feedback">
                                                        Please Enter Username
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="userpassword" class="form-label">Password</label>
                                                    <input type="password" class="form-control" id="userpassword"
                                                        placeholder="Enter password" required>
                                                    <div class="invalid-feedback">
                                                        Please Enter Password
                                                    </div>
                                                </div>

                                                <div>
                                                    <p class="mb-0">By registering you agree to the Shilla <a href="#"
                                                            class="text-primary">Terms of Use</a></p>
                                                </div>

                                                <div class="mt-4 d-grid">
                                                    <button class="btn btn-primary waves-effect waves-light"
                                                        type="submit">Register</button>
                                                </div>

                                                <div class="mt-4 text-center">
                                                    <h5 class="font-size-14 mb-3">Sign up using</h5>

                                                    <ul class="list-inline">
                                                        <li class="list-inline-item">
                                                            <a href="javascript::void()"
                                                                class="social-list-item bg-primary text-white border-primary">
                                                                <i class="mdi mdi-facebook"></i>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="javascript::void()"
                                                                class="social-list-item bg-info text-white border-info">
                                                                <i class="mdi mdi-twitter"></i>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="javascript::void()"
                                                                class="social-list-item bg-danger text-white border-danger">
                                                                <i class="mdi mdi-google"></i>
                                                            </a>
                                                        </li>
                                                    </ul>

                                                </div>

                                            </form>

                                            <div class="mt-5 text-center">
                                                <p>Already have an account ? <a href="auth-login-2"
                                                        class="fw-medium text-primary"> Login</a> </p>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="mt-4 mt-md-5 text-center">
                                        <p class="mb-0">© <script>
                                                document.write(new Date().getFullYear())

                                            </script> Shilla. Crafted with <i class="mdi mdi-heart text-danger"></i> by
                                            shilla</p>
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
