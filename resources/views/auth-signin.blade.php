@extends('partials.layouts.master-auth')

@section('title', 'Sign In | RMS Bronze')

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
                        <img src="{{ asset('assets/images/Signin_image.jpg') }}" class="img-fluid auth-banner w-100 h-100 object-fit-cover"
                            alt="signin banner">
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(8, 15, 35, 0.12) 0%, rgba(8, 15, 35, 0.78) 100%);"></div>
                        <div class="position-absolute bottom-0 start-0 end-0 p-5 text-center text-white">
                            <h3 class="text-white mb-3">Welcome Back</h3>
                            <p class="mb-0 text-white">Sign in to access your tenant workspace, outlet activity, and daily operations.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="auth-box card card-body m-0 h-100 border-0 justify-content-center">
                        <div class="mb-5 text-center">
                            <h4 class="fw-normal">Welcome to <span class="fw-bold text-primary">Advistors RMS Bronze Plan</span></h4>
                            <p class="text-muted mb-0">Please enter your account details to continue.</p>
                        </div>
                        <form class="form-custom mt-10" method="POST" action="{{ route('login.attempt') }}">
                            @csrf

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div class="mb-5">
                                <label class="form-label" for="login-email">Email<span class="text-danger ms-1">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="login-email" name="email" value="{{ old('email') }}"
                                    placeholder="Enter your email" required>
                            </div>

                            <div class="mb-5">
                                <label class="form-label" for="LoginPassword">Password<span class="text-danger ms-1">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="LoginPassword"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        placeholder="Enter your password" data-visible="false" required>
                                    <a class="input-group-text bg-transparent toggle-password" href="javascript:;"
                                        data-target="password">
                                        <i class="ri-eye-off-line text-muted toggle-icon"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-sm-6">
                                    <div class="form-check form-check-sm d-flex align-items-center gap-2 mb-0">
                                        <input class="form-check-input" type="checkbox" value="1" name="remember"
                                            id="remember-me" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember-me">
                                            Remember me
                                        </label>
                                    </div>
                                </div>
                                <a href="{{ url('auth-reset-password') }}" class="col-sm-6 text-end">
                                    <span class="fs-14 text-muted">Forgot your password?</span>
                                </a>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-2 w-100">
                                <span class="indicator-label">Sign In</span>
                            </button>
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
