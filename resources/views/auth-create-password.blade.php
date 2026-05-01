@extends('partials.layouts.master-auth')

@section('title', 'Create Password | RMS Bronze')

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
                        <img src="{{ asset('assets/images/Signin_image.jpg') }}" class="img-fluid auth-banner w-100 h-100 object-fit-cover" alt="create password banner">
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(8, 15, 35, 0.12) 0%, rgba(8, 15, 35, 0.78) 100%);"></div>
                        <div class="position-absolute bottom-0 start-0 end-0 p-5 text-center text-white">
                            <h3 class="text-white mb-3">Set New Password</h3>
                            <p class="mb-0 text-white">Use a strong password to protect your tenant workspace.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="auth-box card card-body m-0 h-100 border-0 justify-content-center">
                        <div class="mb-5 text-center">
                            <h4 class="fw-medium">Create New Password</h4>
                            <p class="text-muted mb-0">Use at least 8 characters with letters and numbers.</p>
                        </div>

                        <form class="form-custom mt-10" method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">
                            <input type="hidden" name="email" value="{{ old('email', $email ?? request('email')) }}">

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                            @endif

                            <div class="mb-5">
                                <label class="form-label" for="LoginPassword">Password<span class="text-danger ms-1">*</span></label>
                                <input type="password" id="LoginPassword" class="form-control" name="password" placeholder="Enter your password" required>
                                <span class="form-text">Use 8 or more characters with letters and numbers.</span>
                            </div>

                            <div class="mb-5">
                                <label class="form-label" for="confirmPassword">Confirm Password<span class="text-danger ms-1">*</span></label>
                                <input type="password" id="confirmPassword" class="form-control" name="password_confirmation" placeholder="Confirm your password" required>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-2 w-100">
                                Reset Password
                            </button>

                            <p class="mb-0 mt-10 text-muted text-center">
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
