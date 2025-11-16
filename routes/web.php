<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SasaranStrategisController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\CompanyPolicyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\JobLevelController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\SubmissionController;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', Login::class)->name('login');
Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'executive'])->name('dashboard');      // Tampilkan dashboard

    Route::prefix('sasaran-strategis')->group(function () {
        Route::get('/', [SasaranStrategisController::class, 'index'])->name('sasaran-strategis.index');      // Tampilkan semua produk
        Route::get('/create', [SasaranStrategisController::class, 'create'])->name('sasaran-strategis.create'); // Form tambah produk
        Route::post('/', [SasaranStrategisController::class, 'store'])->name('sasaran-strategis.store');     // Simpan produk baru
        Route::get('/{id}', [SasaranStrategisController::class, 'show'])->name('sasaran-strategis.show');    // Detail produk
        Route::get('/{id}/edit', [SasaranStrategisController::class, 'edit'])->name('sasaran-strategis.edit'); // Form edit produk
        Route::put('/{id}', [SasaranStrategisController::class, 'update'])->name('sasaran-strategis.update'); // Update produk
        Route::delete('/{id}', [SasaranStrategisController::class, 'destroy'])->name('sasaran-strategis.destroy'); // Hapus produk
    });

    Route::prefix('kpi')->group(function () {
        Route::get('/', [KpiController::class, 'index'])->name('kpi.index');      // Tampilkan semua produk
        Route::get('/create', [KpiController::class, 'create'])->name('kpi.create'); // Form tambah produk
        Route::post('/', [KpiController::class, 'store'])->name('kpi.store');     // Simpan produk baru
        Route::get('/{id}', [KpiController::class, 'show'])->name('kpi.show');    // Detail produk
        Route::get('/{id}/edit', [KpiController::class, 'edit'])->name('kpi.edit'); // Form edit produk
        Route::put('/{id}', [KpiController::class, 'update'])->name('kpi.update'); // Update produk
        Route::delete('/{id}', [KpiController::class, 'destroy'])->name('kpi.destroy'); // Hapus produk
    });

    Route::prefix('anggaran')->group(function () {
        Route::get('/', [AnggaranController::class, 'index'])->name('anggaran.index');      // Tampilkan semua produk
        Route::get('/create', [AnggaranController::class, 'create'])->name('anggaran.create'); // Form tambah produk
        Route::post('/', [AnggaranController::class, 'store'])->name('anggaran.store');     // Simpan produk baru
        Route::get('/{id}', [AnggaranController::class, 'show'])->name('anggaran.show');    // Detail produk
        Route::get('/{id}/edit', [AnggaranController::class, 'edit'])->name('anggaran.edit'); // Form edit produk
        Route::put('/{id}', [AnggaranController::class, 'update'])->name('anggaran.update'); // Update produk
        Route::delete('/{id}', [AnggaranController::class, 'destroy'])->name('anggaran.destroy'); // Hapus produk
    });

    Route::prefix('resume-anggaran')->group(function () {
        Route::get('/', [AnggaranController::class, 'resume'])->name('resume-anggaran.index');      // Tampilkan semua produk
    });

    Route::prefix('admission')->group(function () {
        Route::get('/user', [SubmissionController::class, 'user'])->name('userSubmission.index');
        Route::get('/admin', [SubmissionController::class, 'admin'])->name('adminSubmission.index');
    });

    Route::prefix('realisasi')->group(function () {
        Route::get('/', [RealisasiController::class, 'index'])->name('realisasi.index');      // Tampilkan semua produk
        Route::get('/realisasiunitkerja', [RealisasiController::class, 'index_unitkerja'])->name('realisasiunitkerja.index');      // Tampilkan semua produk
        Route::get('/create', [RealisasiController::class, 'create'])->name('realisasi.create'); // Form tambah produk
        Route::post('/', [RealisasiController::class, 'store'])->name('realisasi.store');     // Simpan produk baru
        Route::get('/{id}', [RealisasiController::class, 'show'])->name('realisasi.show');    // Detail produk
        Route::get('/{id}/edit', [RealisasiController::class, 'edit'])->name('realisasi.edit'); // Form edit produk
        Route::put('/{id}', [RealisasiController::class, 'update'])->name('realisasi.update'); // Update produk
        Route::delete('/{id}', [RealisasiController::class, 'destroy'])->name('realisasi.destroy'); // Hapus produk
    });

    Route::prefix('company-policy')->group(function () {
        Route::get('/', [CompanyPolicyController::class, 'index'])->name('company-policy.index');      // Tampilkan semua produk
        Route::get('/create', [CompanyPolicyController::class, 'create'])->name('company-policy.create'); // Form tambah produk
        Route::get('/{id}/edit', [CompanyPolicyController::class, 'edit'])->name('company-policy.edit'); // Form edit produk
    });

    Route::get('/master', [MasterController::class, 'index'])->name('master');
    Route::get('/user', [MasterController::class, 'user'])->name('user');
    Route::get('/history', [MasterController::class, 'history'])->name('history');

    Route::prefix('employee')->group(function () {
        Route::get('/datatables', [EmployeeController::class, 'getData'])->name('employee.data');
        Route::get('/', [EmployeeController::class, 'store'])->name('employee.create');
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('employee.edit');
    });

    Route::prefix('organization')->group(function () {
        Route::get('/datatables', [OrganizationController::class, 'getData'])->name('organization.data');
        Route::get('/', [OrganizationController::class, 'store'])->name('organization.create');
        Route::get('/{id}/edit', [OrganizationController::class, 'edit'])->name('organization.edit');
    });

    Route::prefix('jobPosition')->group(function () {
        Route::get('/datatables', [JobPositionController::class, 'getData'])->name('jobPosition.data');
        Route::get('/', [JobPositionController::class, 'store'])->name('jobPosition.create');
        Route::get('/{id}/edit', [JobPositionController::class, 'edit'])->name('jobPosition.edit');
    });

    Route::prefix('jobLevel')->group(function () {
        Route::get('/datatables', [JobLevelController::class, 'getData'])->name('jobLevel.data');
        Route::get('/', [JobLevelController::class, 'store'])->name('jobLevel.create');
        Route::get('/{id}/edit', [JobLevelController::class, 'edit'])->name('jobLevel.edit');
    });

    Route::prefix('dashboard')->group(function () {
        Route::get('/dash', [DashboardController::class, 'executive'])->name('dash.executive');
    });

    Route::prefix('authorization')->group(function () {

        // Role
        Route::get('/roles', [AuthorizationController::class, 'roles'])->name('auth.roles');
        Route::post('/roles/store', [AuthorizationController::class, 'roleStore'])->name('auth.roles.store');
        Route::post('/roles/update/{id}', [AuthorizationController::class, 'roleUpdate'])->name('auth.roles.update');
        Route::delete('/roles/delete/{id}', [AuthorizationController::class, 'roleDelete'])->name('auth.roles.delete');

        // Permission
        Route::get('/permissions', [AuthorizationController::class, 'permissions'])->name('auth.permissions');
        Route::post('/permissions/store', [AuthorizationController::class, 'permissionStore'])->name('auth.permissions.store');
        Route::delete('/permissions/delete/{id}', [AuthorizationController::class, 'permissionDelete'])->name('auth.permissions.delete');

        // Assign Permission to Role
        Route::get('/roles/{id}/permissions', [AuthorizationController::class, 'rolePermissions'])->name('auth.roles.permissions');
        Route::post('/roles/{id}/permissions/update', [AuthorizationController::class, 'rolePermissionsUpdate'])->name('auth.roles.permissions.update');

        // Assign Role to User
        Route::post('/assign-role', [AuthorizationController::class, 'assignRole'])->name('auth.assign.role');
    });
});
