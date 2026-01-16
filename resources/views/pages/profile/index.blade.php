@extends('layouts.master')

@section('title', 'My Profile')

@section('content')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">My Profile</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="card overflow-hidden">
                <!-- Profile Header with Gradient Background -->
                <div class="bg-primary position-relative" style="height: 100px;">
                    <div class="position-absolute w-100" style="bottom: -50px;">
                        <div class="text-center">
                            <img src="{{ asset('assets/images/avatar/dummy-avatar.jpg') }}" 
                                 alt="Profile" 
                                 class="rounded-circle border border-4 border-white shadow"
                                 style="width: 100px; height: 100px; object-fit: cover; background: #fff;">
                        </div>
                    </div>
                </div>

                <div class="card-body text-center" style="padding-top: 60px;">
                    <h5 class="fs-18 mb-1">{{ $user->first_name }} {{ $user->last_name }}</h5>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    <span class="badge bg-primary-subtle text-primary px-3 py-2">
                        {{ $user->employment->job_position_name ?? 'Employee' }}
                    </span>
                </div>


                <!-- Profile Information -->
                <div class="card-body border-top">
                    <div class="row">
                        <div class="col-12">
                            <h5 class="fs-15 mb-3"><i class="bi bi-person-badge me-2"></i>Information</h5>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-medium text-muted" style="width: 40%;">Employee ID</td>
                                    <td>{{ $user->employee_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Full Name</td>
                                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Email</td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Phone</td>
                                    <td>{{ $user->phone ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Employment Information -->
                <div class="card-body border-top">
                    <div class="row">
                        <div class="col-12">
                            <h5 class="fs-15 mb-3"><i class="bi bi-briefcase me-2"></i>Employment</h5>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-medium text-muted" style="width: 40%;">Job Position</td>
                                    <td>{{ $user->jobPosition->job_position_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Job Level</td>
                                    <td>{{ $user->employment->job_level_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Organization</td>
                                    <td>{{ $user->jobPosition->structure_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Role</td>
                                    <td>
                                        @if($user->roles->count() > 0)
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary-subtle text-primary">{{ $role->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No Role</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Uppline</td>
                                    <td>{{ $user->employment->uppline_id_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Status</td>
                                    <td>
                                        @if($user->status === 'Active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile & Change Password -->
        <div class="col-xl-8 col-lg-7">
            <!-- Edit Profile Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form id="profileUpdateForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="{{ $user->first_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="{{ $user->last_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="{{ $user->email }}" disabled readonly>
                                <small class="text-muted">Email tidak dapat diubah</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="{{ $user->phone }}" placeholder="Masukkan nomor telepon">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form id="passwordUpdateForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key me-1"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Update Profile Form
    $('#profileUpdateForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "{{ route('profile.update') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    for (let field in errors) {
                        errorMessages += '• ' + errors[field][0] + '<br>';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: errorMessages
                    });
                } else {
                    Swal.fire('Error', 'Terjadi kesalahan saat memperbarui profil.', 'error');
                }
            }
        });
    });

    // Change Password Form
    $('#passwordUpdateForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "{{ route('profile.password') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#passwordUpdateForm')[0].reset();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let response = xhr.responseJSON;
                    
                    // Check if it's a validation error with 'errors' object or direct message
                    if (response.errors) {
                        let errorMessages = '';
                        for (let field in response.errors) {
                            errorMessages += '• ' + response.errors[field][0] + '<br>';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal',
                            html: errorMessages
                        });
                    } else if (response.message) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                    }
                } else {
                    Swal.fire('Error', 'Terjadi kesalahan saat mengubah password.', 'error');
                }
            }
        });
    });
</script>
@endpush
