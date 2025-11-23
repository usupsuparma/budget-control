<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SasaranStrategisController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\CompanyPolicyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\JobLevelController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SubmissionController;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', Login::class)->name('login');

Route::middleware('auth')->group(function () {

    /* LOGOUT */
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');


    /* ========================
        DASHBOARD
    ======================== */

    Route::middleware(['auth', 'permission:dashboard.view'])->group(function () {
        Route::get('/dashboard/dash', [DashboardController::class, 'executive'])
            ->name('dash.executive');
    });
    Route::middleware(['auth', 'permission:dashboard.view'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'executive'])
            ->name('dashboard');   // <- WAJIB ADA
    });



    /* ========================
        SASARAN STRATEGIS
    ======================== */
    Route::prefix('sasaran-strategis')
        ->middleware('permission:kpi.sasaranstrategis.view')
        ->group(function () {

            Route::get('/', [SasaranStrategisController::class, 'index'])
                ->name('sasaran-strategis.index');

            Route::get('/create', [SasaranStrategisController::class, 'create'])
                ->middleware('permission:kpi.sasaranstrategis.create')
                ->name('sasaran-strategis.create');

            Route::post('/', [SasaranStrategisController::class, 'store'])
                ->middleware('permission:kpi.sasaranstrategis.create')
                ->name('sasaran-strategis.store');

            Route::get('/{id}/edit', [SasaranStrategisController::class, 'edit'])
                ->middleware('permission:kpi.sasaranstrategis.edit')
                ->name('sasaran-strategis.edit');

            Route::put('/{id}', [SasaranStrategisController::class, 'update'])
                ->middleware('permission:kpi.sasaranstrategis.edit')
                ->name('sasaran-strategis.update');

            Route::delete('/{id}', [SasaranStrategisController::class, 'destroy'])
                ->middleware('permission:kpi.sasaranstrategis.delete')
                ->name('sasaran-strategis.destroy');
        });


    /* ========================
        KPI
    ======================== */
    Route::prefix('kpi')
        ->middleware('permission:kpi.view')
        ->group(function () {

            Route::get('/', [KpiController::class, 'index'])->name('kpi.index');

            Route::get('/create', [KpiController::class, 'create'])
                ->middleware('permission:kpi.create')
                ->name('kpi.create');

            Route::post('/', [KpiController::class, 'store'])
                ->middleware('permission:kpi.create')
                ->name('kpi.store');

            Route::get('/{id}/edit', [KpiController::class, 'edit'])
                ->middleware('permission:kpi.edit')
                ->name('kpi.edit');

            Route::put('/{id}', [KpiController::class, 'update'])
                ->middleware('permission:kpi.edit')
                ->name('kpi.update');

            Route::delete('/{id}', [KpiController::class, 'destroy'])
                ->middleware('permission:kpi.delete')
                ->name('kpi.destroy');
        });


    /* ========================
        COMPANY POLICY
    ======================== */
    Route::prefix('company-policy')
        ->middleware('permission:companypolicy.view')
        ->group(function () {

            Route::get('/', [CompanyPolicyController::class, 'index'])
                ->name('company-policy.index');

            Route::get('/create', [CompanyPolicyController::class, 'create'])
                ->middleware('permission:companypolicy.create')
                ->name('company-policy.create');

            Route::post('/', [CompanyPolicyController::class, 'store'])
                ->middleware('permission:companypolicy.create')
                ->name('company-policy.store');

            Route::get('/{id}/edit', [CompanyPolicyController::class, 'edit'])
                ->middleware('permission:companypolicy.edit')
                ->name('company-policy.edit');

            Route::delete('/{dokumen}', [CompanyPolicyController::class, 'destroy'])
                ->middleware('permission:companypolicy.delete')
                ->name('company-policy.destroy');
        });


    /* ========================
        ANGGARAN
    ======================== */
    Route::prefix('anggaran')
        ->middleware('permission:budget.view')
        ->group(function () {

            Route::get('/', [AnggaranController::class, 'index'])
                ->name('anggaran.index');

            Route::get('/create', [AnggaranController::class, 'create'])
                ->middleware('permission:budget.create')
                ->name('anggaran.create');

            Route::post('/', [AnggaranController::class, 'store'])
                ->middleware('permission:budget.create')
                ->name('anggaran.store');

            Route::get('/{id}/edit', [AnggaranController::class, 'edit'])
                ->middleware('permission:budget.edit')
                ->name('anggaran.edit');

            Route::put('/{id}', [AnggaranController::class, 'update'])
                ->middleware('permission:budget.edit')
                ->name('anggaran.update');

            Route::delete('/{id}', [AnggaranController::class, 'destroy'])
                ->middleware('permission:budget.delete')
                ->name('anggaran.destroy');
        });


    Route::prefix('resume-anggaran')
        ->middleware('permission:budget.view')
        ->group(function () {

            Route::get('/', [AnggaranController::class, 'resume'])
                ->name('resume-anggaran.index');
        });


    /* ========================
        ADMISSION
    ======================== */
    Route::prefix('admission')->group(function () {

        Route::get('/user', [SubmissionController::class, 'user'])
            ->middleware('permission:transaction.user.view')
            ->name('userSubmission.index');

        Route::get('/admin', [SubmissionController::class, 'admin'])
            ->middleware('permission:transaction.admin.view')
            ->name('adminSubmission.index');
    });


    /* ========================
        REALISASI
    ======================== */
    Route::prefix('realisasi')
        ->middleware('permission:realisasi.view')
        ->group(function () {

            Route::get('/', [RealisasiController::class, 'index'])
                ->name('realisasi.index');

            Route::get('/create', [RealisasiController::class, 'create'])
                ->middleware('permission:realisasi.create')
                ->name('realisasi.create');

            Route::post('/', [RealisasiController::class, 'store'])
                ->middleware('permission:realisasi.create')
                ->name('realisasi.store');

            Route::get('/{id}/edit', [RealisasiController::class, 'edit'])
                ->middleware('permission:realisasi.edit')
                ->name('realisasi.edit');

            Route::put('/{id}', [RealisasiController::class, 'update'])
                ->middleware('permission:realisasi.edit')
                ->name('realisasi.update');

            Route::delete('/{id}', [RealisasiController::class, 'destroy'])
                ->middleware('permission:realisasi.delete')
                ->name('realisasi.destroy');
        });


    /* ========================
        MASTER
    ======================== */

    Route::prefix('employee')
        ->middleware('permission:employee.view')
        ->group(function () {

            Route::get('/datatables', [EmployeeController::class, 'getData'])
                ->name('employee.data');

            Route::get('/create', [EmployeeController::class, 'create'])
                ->middleware('permission:employee.create')
                ->name('employee.create');

            Route::get('/{id}/edit', [EmployeeController::class, 'edit'])
                ->middleware('permission:employee.edit')
                ->name('employee.edit');
        });



    Route::prefix('jobPosition')
        ->middleware('permission:jobposition.view')
        ->group(function () {

            Route::get('/datatables', [JobPositionController::class, 'getData'])
                ->name('jobPosition.data');
            Route::get(
                '/organization/by-level/{level_id}',
                [JobPositionController::class, 'getOrganizationByLevel']
            )
                ->name('jobPosition.orgByLevel');
            Route::post('/create', [JobPositionController::class, 'store'])
                ->middleware('permission:jobposition.create')
                ->name('jobPosition.store');
            Route::get('/{id}/edit', [JobPositionController::class, 'edit'])
                ->middleware('permission:jobposition.edit')
                ->name('jobPosition.edit');
            Route::post('/update/{id}', [JobPositionController::class, 'update'])
                ->middleware('permission:jobposition.edit')
                ->name('jobPosition.update');
            Route::delete('delete/{id}', [JobPositionController::class, 'destroy'])
                ->middleware('permission:jobposition.delete')
                ->name('jobPosition.delete');
        });

    Route::prefix('jobLevel')
        ->middleware('permission:joblevel.view')
        ->group(function () {
            Route::get('/datatables', [JobLevelController::class, 'getData'])
                ->name('jobLevel.data');
            Route::post('/create', [JobLevelController::class, 'store'])
                ->middleware('permission:joblevel.create')
                ->name('jobLevel.store');
            Route::get('/{id}/edit', [JobLevelController::class, 'edit'])
                ->middleware('permission:joblevel.edit')
                ->name('jobLevel.edit');
            Route::post('/update/{id}', [JobLevelController::class, 'update'])
                ->middleware('permission:joblevel.edit')
                ->name('jobLevel.update');
            Route::delete('delete/{id}', [JobLevelController::class, 'destroy'])
                ->middleware('permission:joblevel.delete')
                ->name('jobLevel.delete');
        });

    Route::prefix('section')
        ->middleware('permission:section.view')
        ->group(function () {
            Route::get('/datatables', [SectionController::class, 'getData'])
                ->name('section.data');
            Route::post('/create', [SectionController::class, 'store'])
                ->middleware('permission:section.create')
                ->name('section.store');
            Route::get('/{id}/edit', [SectionController::class, 'edit'])
                ->middleware('permission:section.edit')
                ->name('section.edit');
            Route::post('/update/{id}', [SectionController::class, 'update'])
                ->middleware('permission:section.edit')
                ->name('section.update');
            Route::delete('/delete/{id}', [SectionController::class, 'destroy'])
                ->middleware('permission:section.delete')
                ->name('section.delete');
        });

    Route::prefix('department')
        ->middleware('permission:department.view')
        ->group(function () {
            Route::get('/datatables', [DepartmentController::class, 'getData'])
                ->name('department.data');
            Route::post('/create', [DepartmentController::class, 'store'])
                ->middleware('permission:department.create')
                ->name('department.store');
            Route::get('/{id}/edit', [DepartmentController::class, 'edit'])
                ->middleware('permission:department.edit')
                ->name('department.edit');
            Route::post('/update/{id}', [DepartmentController::class, 'update'])
                ->middleware('permission:department.edit')
                ->name('department.update');
            Route::delete('/delete/{id}', [DepartmentController::class, 'destroy'])
                ->middleware('permission:department.delete')
                ->name('department.delete');
        });

    Route::prefix('division')
        ->middleware('permission:division.view')
        ->group(function () {
            Route::get('/datatables', [DivisionController::class, 'getData'])
                ->name('division.data');
            Route::post('/create', [DivisionController::class, 'store'])
                ->middleware('permission:division.create')
                ->name('division.store');
            Route::get('/{id}/edit', [DivisionController::class, 'edit'])
                ->middleware('permission:division.edit')
                ->name('division.edit');
            Route::post('/update/{id}', [DivisionController::class, 'update'])
                ->middleware('permission:division.edit')
                ->name('division.update');
            Route::delete('delete/{id}', [DivisionController::class, 'destroy'])
                ->middleware('permission:division.delete')
                ->name('division.delete');
        });

    Route::prefix('director')
        ->middleware('permission:director.view')
        ->group(function () {
            Route::get('/datatables', [DirectorController::class, 'getData'])
                ->name('director.data');
            Route::post('/create', [DirectorController::class, 'store'])
                ->middleware('permission:director.create')
                ->name('director.store');
            Route::get('/{id}/edit', [DirectorController::class, 'edit'])
                ->middleware('permission:director.edit')
                ->name('director.edit');
            Route::post('/update/{id}', [DirectorController::class, 'update'])
                ->middleware('permission:director.edit')
                ->name('director.update');
            Route::delete('delete/{id}', [DirectorController::class, 'destroy'])
                ->middleware('permission:director.delete')
                ->name('director.delete');
        });


    /* ========================
        SETTINGS
    ======================== */
    Route::middleware('permission:setting.master.view')->get('/master', [MasterController::class, 'index'])->name('master');
    Route::middleware('permission:setting.users.view')->get('/user', [MasterController::class, 'user'])->name('user');
    Route::middleware('permission:setting.history.view')->get('/history', [MasterController::class, 'history'])->name('history');
    Route::middleware('permission:setting.coa.view')->get('/coa', [MasterController::class, 'coa'])->name('coa');



    /* ========================
        AUTHORIZATION
    ======================== */
    Route::prefix('authorization')
        ->middleware('permission:authorization.view')
        ->group(function () {

            Route::get('/roles', [AuthorizationController::class, 'roles'])->name('auth.roles');
            Route::post('/roles/store', [AuthorizationController::class, 'roleStore'])->name('auth.roles.store');
            Route::post('/roles/update/{id}', [AuthorizationController::class, 'roleUpdate'])->name('auth.roles.update');
            Route::delete('/roles/delete/{id}', [AuthorizationController::class, 'roleDelete'])->name('auth.roles.delete');

            Route::get('/permissions', [AuthorizationController::class, 'permissions'])->name('auth.permissions');
            Route::post('/permissions/store', [AuthorizationController::class, 'permissionStore'])->name('auth.permissions.store');
            Route::post('/permissions/update/{id}', [AuthorizationController::class, 'permissionUpdate'])->name('auth.permissions.update');
            Route::delete('/permissions/delete/{id}', [AuthorizationController::class, 'permissionDelete'])->name('auth.permissions.delete');

            Route::get('/roles/{id}/permissions', [AuthorizationController::class, 'rolePermissions'])->name('auth.roles.permissions');
            Route::post('/roles/{id}/permissions/update', [AuthorizationController::class, 'rolePermissionsUpdate'])->name('auth.roles.permissions.update');

            Route::get('/assign-role/{id}', [AuthorizationController::class, 'assignRoleView'])->name('auth.assign.view');
            Route::post('/assign-role', [AuthorizationController::class, 'assignRole'])->name('auth.assign.role');
        });
});
