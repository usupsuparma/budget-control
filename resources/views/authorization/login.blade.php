<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Budget Control</title>

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">

    <style>
        /* Background fullscreen */
        .auth-bg-img {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
            filter: brightness(0.45);
        }

        /* Card transparan */
        .login-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
    </style>
</head>

<body>

    <!-- BACKGROUND IMAGE -->
    <img src="{{ asset('assets/images/auth/login_back.jpg') }}" alt="background" class="auth-bg-img">

    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <div class="col-md-4">
            <div class="login-card">

                <!-- Logo -->
                <div class="text-center mb-4">
                    <img src="{{ asset('assets/images/logo-dark.png') }}" height="90">
                </div>

                @if (session('error'))
                <div class="alert alert-danger text-center">{{ session('error') }}</div>
                @endif

                <!-- FORM LOGIN -->
                <form method="POST" action="/login">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required placeholder="Enter your password">
                    </div>

                    <div class="mb-3 form-check">

                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        Sign In
                    </button>
                </form>

                <p class="text-center text-muted mt-3 mb-0 fs-12">
                    © {{ date('Y') }} BudgetControl. All Rights Reserved
                </p>

            </div>
        </div>

    </div>

</body>

</html>