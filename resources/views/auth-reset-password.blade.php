@extends('partials.layouts.master-auth')

@section('title', 'Reset Password | RMS Bronze')

@section('css')
    @include('partials.head-css', ['auth' => 'layout-auth'])
@endsection

@section('content')
    <div class="account-pages">
        <img src="{{ asset('assets/images/auth/auth_bg.jpeg') }}" alt="auth_bg" class="auth-bg light">
        <img src="{{ asset('assets/images/auth/auth_bg_dark.jpg') }}" alt="auth_bg_dark" class="auth-bg dark">
        <div class="container">
            <div class="justify-content-center row gy-0">
                <div class="col-lg-6 auth-banners">
                    <div class="bg-login card card-body m-0 h-100 border-0 p-0 overflow-hidden position-relative">
                        <img src="{{ asset('assets/images/Signin_image.jpg') }}" class="img-fluid auth-banner w-100 h-100 object-fit-cover" alt="reset password banner">
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(8, 15, 35, 0.12) 0%, rgba(8, 15, 35, 0.78) 100%);"></div>
                        <div class="position-absolute bottom-0 start-0 end-0 p-5 text-center text-white">
                            <h3 class="text-white mb-3">Recover Access</h3>
                            <p class="mb-0 text-white">Send a secure reset link to a staff account email.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="auth-box card card-body m-0 h-100 border-0 justify-content-center">
                        <div class="mb-5 text-center">
                            <h4 class="fw-medium">Forgot Password?</h4>
                            <p class="text-muted mb-0">Enter your email address and we will send you a secure reset link.</p>
                        </div>

                        <form class="form-custom mt-10" method="POST" action="{{ route('password.email') }}">
                            @csrf

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                            @endif

                            <div class="mb-5">
                                <label class="form-label" for="login-email">Email<span class="text-danger ms-1">*</span></label>
                                <input type="email" class="form-control" id="login-email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-2 w-100">
                                Send Reset Link
                            </button>

                            <p class="mb-0 mt-5 text-muted text-center">
                                I remember my password.
                                <a href="{{ route('login') }}" class="text-primary fw-medium text-decoraton-underline ms-1">Sign In</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
