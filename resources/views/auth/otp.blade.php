@extends('layouts.master-without-nav')

@section('title')
    @lang('translation.Two_step_verification')
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
                                            <img src="{{ URL::asset('/build/images/logo-dark.png') }}" alt=""
                                                height="50" class="auth-logo-dark">
                                            <img src="{{ URL::asset('/build/images/logo-light.png') }}" alt=""
                                                height="50" class="auth-logo-light">
                                        </a>
                                    </div>
                                    <div class="my-auto">
                                        <div class="text-center">

                                            <div class="avatar-md mx-auto">
                                                <div class="avatar-title rounded-circle bg-light">
                                                    <i class="bx bxs-envelope h1 mb-0 text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="p-2 mt-4">

                                                <h4>Verify your email</h4>
                                                <p class="mb-5">Please enter the 4 digit code sent to <span
                                                        class="fw-semibold">{{ Auth::user()->email }}</span></p>
                                                @if (session()->has('message'))
                                                    <div class="alert alert-danger">
                                                        {{ session()->get('message') }}
                                                    </div>
                                                @endif

                                                <form method="POST" action="{{ route('otp.verify') }}" id="otpverify">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="mb-3">
                                                                <label for="digit1" class="visually-hidden">OTP
                                                                </label>
                                                                <input type="text"
                                                                    class="form-control form-control-lg text-center  "
                                                                    name="otp" placeholder="****">
                                                                <div class="float-end mt-1">
                                                                    <a href="{{ route('otp.resend') }}"
                                                                        class="text-muted">Resend OTP</a>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="mt-3 d-grid">
                                                        <button type="submit" id="confirm"
                                                            class="btn btn-primary waves-effect waves-light"
                                                      >Confirm</button>

                                                    </div>
                                                </form>


                                            </div>

                                        </div>
                                    </div>

                                    <div class="mt-4 mt-md-5 text-center">
                                        <p class="mb-0">©
                                            <script>
                                                document.write(new Date().getFullYear())
                                            </script> TREX <br> Designed & Developed by TREX
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
        <script>
            document.getElementById('otpverify').addEventListener('submit', function(e) {
                const submitButton = document.getElementById('confirm');

                // Disable the button to prevent multiple submissions
                submitButton.disabled = true;

                // Optionally, you can add a loading spinner or change button text
                submitButton.textContent = 'Submitting...';

                // Allow form submission to proceed
            });
        </script>
        <!-- owl.carousel js -->
        <script src="{{ URL::asset('/build/libs/owl.carousel/owl.carousel.min.js') }}"></script>
        <!-- auth-2-carousel init -->
        <script src="{{ URL::asset('/build/js/pages/auth-2-carousel.init.js') }}"></script>
        <!-- two-step-verification.init -->
        <script src="{{ URL::asset('/build/js/pages/two-step-verification.init.js') }}"></script>
    @endsection
