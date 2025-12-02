<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100 py-10">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">

            <div class="card mx-xxl-8 shadow">
                <div class="card-body py-12 px-8">

                    <!-- Logo -->
                    <img src="{{ asset('assets/images/logo-dark.png') }}"
                        alt="logo"
                        height="100"
                        class="mb-4 mx-auto d-block">

                    <h6 class="mb-3 fw-medium text-center text-muted">
                        Login to Your Account
                    </h6>

                    @if (session('error'))
                    <div class="alert alert-danger text-center py-2">
                        {{ session('error') }}
                    </div>
                    @endif

                    <form wire:submit.prevent="login">
                        <div class="row g-4">

                            <div class="col-12">
                                <label class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" wire:model="email" class="form-control"
                                    placeholder="Enter your email">
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <input type="password" wire:model="password" class="form-control"
                                    placeholder="Enter your password">
                                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    Sign In
                                    <i class="bi bi-box-arrow-in-right ms-1 fs-16"></i>
                                </button>
                            </div>

                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="text-center fs-12 mb-0 text-muted">
                            © {{ date('Y') }} BudgetControl. All Rights Reserved
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>