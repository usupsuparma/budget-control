<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-layout="vertical">

<head>
    <meta charset="utf-8" />
    <title>Sign In | Budget Control</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Admin & Dashboards Template" name="description" />
    <meta content="BudgetControl" name="author" />

    <!-- layout setup -->
    <script type="module" src="{{ asset('assets/js/layout-setup.js') }}"></script>

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/k_favicon_32x.png') }}">

    <!-- CSS dependencies -->
    <link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.min.css') }}">
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css">

    @livewireStyles
</head>

<body>
    <!-- START -->
    <div>
        <!-- Background -->
        <img src="{{ asset('assets/images/auth/login_bg.jpg') }}" alt="" class="auth-bg light w-full h-full opacity-60 position-absolute top-0">
        <img src="{{ asset('assets/images/auth/auth_bg_dark.jpg') }}" alt="" class="auth-bg d-none dark">

        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100 py-10">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card mx-xxl-8 shadow">
                        <div class="card-body py-12 px-8">
                            <!-- Logo -->
                            <img src="{{ asset('assets/images/logo-dark.png') }}" alt="" height="100" class="mb-4 mx-auto d-block">

                            <h6 class="mb-3 fw-medium text-center text-muted">Login to Your Account</h6>

                            {{-- ✅ ALERT ERROR --}}
                            @if (session('error'))
                            <div class="alert alert-danger text-center py-2">{{ session('error') }}</div>
                            @endif

                            <!-- Livewire Login Form -->
                            <form wire:submit.prevent="login">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" wire:model="email" class="form-control" id="email" placeholder="Enter your email">
                                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" wire:model="password" class="form-control" id="password" placeholder="Enter your password">
                                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input type="checkbox" wire:model="remember" class="form-check-input" id="rememberMe">
                                                <label class="form-check-label" for="rememberMe">Remember me</label>
                                            </div>
                                            <div class="form-text">
                                                <a href="#" class="link link-primary text-muted text-decoration-underline">Forgot password?</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary w-100">
                                            Sign In
                                            <i class="bi bi-box-arrow-in-right ms-1 fs-16"></i>
                                        </button>
                                    </div>
                                </div>

                                <p class="mb-0 fw-semibold position-relative text-center fs-12 mt-5">
                                    Don’t have an account?
                                    <a href="#" class="text-decoration-underline text-primary">Sign up here</a>
                                </p>
                            </form>

                            <div class="text-center mt-3">
                                <p class="position-relative text-center fs-12 mb-0 text-muted">
                                    © {{ date('Y') }} BudgetControl. Copyright. All Rights Reserved
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/scroll-top.init.js') }}"></script>
    @livewireScripts
</body>

</html>