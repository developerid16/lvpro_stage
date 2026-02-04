@extends('layouts.master-without-nav')

@section('title')
@lang('translation.Login')
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
                                    <a href="{{url('/')}}"class="d-block auth-logo">
                                        <img src="{{ URL::asset('/build/images/logo-dark.png') }}?q={{ time() }}" alt="" height="50" class="auth-logo-dark">
                                        <img src="{{ URL::asset('/build/images/logo-light.png') }}?q={{ time() }}" alt="" height="50" class="auth-logo-light">
                                    </a>
                                </div>
                                <div class="my-auto">

                                    <div>
                                        <h5 class="text-primary">Welcome Back !</h5>
                                        <p class="text-muted">Send Request For User Rights</p>
                                    </div>

                                    <div class="mt-4">
                                        <form method="POST" action="{{ url('user-rights-form') }}">
                                            @csrf

                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="4"
                                                        placeholder="Why do you need admin access?"></textarea>
                                            </div>

                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary">
                                                    Submit Request
                                                </button>

                                                <a href="{{ route('login') }}" class="text-muted mt-2 text-center">
                                                    Back to Login
                                                </a>
                                            </div>

                                            @if(session('success'))
                                                <div class="alert alert-success mt-3">
                                                    {{ session('success') }}
                                                </div>
                                            @endif
                                        </form>


                                    </div>
                                </div>

                                <div class="mt-4 mt-md-5 text-center">
                                    <p class="mb-0">© <script>
                                            document.write(new Date().getFullYear())
                                        </script> {{ config('app.name') }} <br> Designed & Developed by {{ config('app.name') }}
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
    <script>
        $(function() {

            $("#password-addon").on('click', function() {
                if ($(this).siblings('input').length > 0) {
                    $(this).siblings('input').attr('type') == "password" ? $(this).siblings('input').attr('type', 'input') : $(this).siblings('input').attr('type', 'password');
                }

            })
        });
    </script>
    @endsection