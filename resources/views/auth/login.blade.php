@extends('layouts.master-without-nav')

@section('title')
@lang('translation.Login')
@endsection

@section('css')
    <script src="https://alcdn.msauth.net/browser/2.38.0/js/msal-browser.min.js"></script>

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
                                        <p class="text-muted">Sign in to continue to {{ config('app.name') }}.</p>
                                    </div>

                                    <div class="mt-4">
                                        <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Email</label>
                                                <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" id="username" placeholder="Enter Email" autocomplete="email" autofocus>

                                                @if(session()->has('email'))


                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ session()->get('email') }}</strong>
                                                </span>

                                                @endif
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Password</label>
                                                <div class="input-group auth-pass-inputgroup @error('password') is-invalid @enderror">
                                                    <input type="password" name="password" class="form-control  @error('password') is-invalid @enderror" id="userpassword"  placeholder="Enter password" aria-label="Password" aria-describedby="password-addon">
                                                    <button class="btn btn-light " type="button" id="password-addon"><i class="mdi mdi-eye-outline"></i></button>
                                                    @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                                <div class="float-end mt-1">
                                                    @if (Route::has('password.request'))
                                                    <a href="{{ route('password.request') }}" class="text-muted">Forgot password?</a>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="remember" {{
                                                    old('remember') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="remember">
                                                    Remember me
                                                </label>
                                            </div>

                                            <div class="mt-3 d-grid">
                                                <button class="btn btn-primary waves-effect waves-light" type="submit">Log In</button>
                                                <button type="button" class="btn btn-primary waves-effect waves-light mt-2" onclick="login()">Login with Microsoft</button>

                                                    {{-- <a href="{{ url('user-rights-form') }}" class="text-muted mt-2">Request for user rights</a> --}}
                                            </div>

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

        const msalConfig = {
            auth: {
                clientId: "{{ config('services.azure.client_id') }}",
                authority: "https://login.microsoftonline.com/organizations",
                redirectUri: "{{ url('/auth/callback') }}"
            }
        };

        const msalInstance = new msal.PublicClientApplication(msalConfig);

        function login() {
            msalInstance.loginRedirect({
                scopes: ["openid", "profile", "email"]
            });
        }
        
    </script>
    @endsection