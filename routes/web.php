<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SasaranStrategisController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthorizationAddBudgetController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\AuthorizationTransactionController;
use App\Http\Controllers\BudgetAdminController;
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
use App\Http\Controllers\KPIDivisionController;
use App\Http\Controllers\KPIDivisionCompanyPolicyController;
use App\Http\Controllers\KPIDepartmentController;
use App\Http\Controllers\KPIDepartmentCompanyPolicyController;
use App\Http\Controllers\KPISectionController;
use App\Http\Controllers\KPISectionCompanyPolicyController;
use App\Http\Controllers\KPIWorkPlanController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PengajuanAnggaranController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\SettingCodeController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WorkPlanItemController;
use App\Http\Controllers\BudgetCategoryController;
use App\Http\Controllers\BudgetCodeController;
use App\Http\Controllers\BudgetResumeController;
use App\Http\Controllers\BudgetSubmissionController;
use App\Http\Controllers\SettingProductionController;
use App\Http\Controllers\BudgetUserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SegmenController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Livewire\Auth\Login;
use App\Models\WorkplanBudgetItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// routes/web.php
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthorizationController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthorizationController::class, 'login']);

// Route::get('/', Login::class)->name('login');

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
    Route::get('/dash-executive/policies', [DashboardController::class, 'executivePoliciesByYear'])
        ->name('dash.executive.policies');
    Route::get('/budget/summary', [DashboardController::class, 'budgetSummaryByYear'])
        ->name('budget.summary.year');


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

            Route::get('/{id}/json', [CompanyPolicyController::class, 'json'])
                // ->middleware('permission:companypolicy.edit')
                ->name('company-policy.json');

            Route::put('/{id}', [CompanyPolicyController::class, 'update'])
                // ->middleware('permission:companypolicy.update')
                ->name('company-policy.update');

            Route::delete('/{dokumen}', [CompanyPolicyController::class, 'destroy'])
                ->middleware('permission:companypolicy.delete')
                ->name('company-policy.destroy');

            Route::get('/{id}/pdf', [CompanyPolicyController::class, 'downloadPdf'])
                // ->middleware('permission:companypolicy.delete')
                ->name('company-policy.pdf');
        });

    /* ========================
        KPI Division
    ======================== */
    Route::prefix('kpidivision')
        // ->middleware('permission:kpi.kpidivision.view')
        ->group(function () {

            Route::get('/', [KPIDivisionController::class, 'index'])
                // ->middleware('permission:kpi.kpidivision.index')
                ->name('kpidivision.index');

            Route::get('/datatable', [KPIDivisionController::class, 'dataTable'])
                // ->middleware('permission:kpi.kpidivision.datatable')
                ->name('kpidivision.datatable');

            Route::get('/create', [KPIDivisionController::class, 'create'])
                // ->middleware('permission:kpi.kpidivision.create')
                ->name('kpidivision.create');

            Route::post('/', [KPIDivisionController::class, 'store'])
                // ->middleware('permission:kpi.kpidivision.create')
                ->name('kpidivision.store');

            Route::get('/{id}/edit', [KPIDivisionController::class, 'edit'])
                // ->middleware('permission:kpi.kpidivision.edit')
                ->name('kpidivision.edit');

            Route::get('/{id}/show', [KPIDivisionController::class, 'show'])
                // ->middleware('permission:kpi.kpidivision.edit')
                ->name('kpidivision.show');

            Route::put('/{id}/update', [KPIDivisionController::class, 'update'])
                // ->middleware('permission:kpi.kpidivision.edit')
                ->name('kpidivision.update');

            Route::delete('/{id}', [KPIDivisionController::class, 'destroy'])
                // ->middleware('permission:kpi.kpidivision.delete')
                ->name('kpidivision.destroy');

            Route::patch('/{id}/inline', [KpiDivisionController::class, 'inlineUpdate'])
                // ->middleware('permission:kpi.kpidivision.inline')
                ->name('kpidivision.inline');
        });

    Route::prefix('kpidivisioncompanypolicy')
        // ->middleware('permission:kpi.kpidivisioncp.view')
        ->group(function () {
            Route::get('/datatable', [KPIDivisionCompanyPolicyController::class, 'dataTable'])
                // ->middleware('permission:kpi.kpidivisioncompanypolicy.dataTable')
                ->name('kpidivisioncompanypolicy.datatable');

            Route::post('/', [KPIDivisionCompanyPolicyController::class, 'store'])
                // ->middleware('permission:kpi.kpidivisioncompanypolicy.store')
                ->name('kpidivisioncompanypolicy.store');

            Route::get('/{id}/show', [KPIDivisionCompanyPolicyController::class, 'show'])
                // ->middleware('permission:kpi.kpidivisioncompanypolicy.show')
                ->name('kpidivisioncompanypolicy.show');

            Route::put('/{id}/update', [KPIDivisionCompanyPolicyController::class, 'update'])
                // ->middleware('permission:kpi.kpidivisioncompanypolicy.update')
                ->name('kpidivisioncompanypolicy.update');

            Route::delete('/{id}', [KPIDivisionCompanyPolicyController::class, 'destroy'])
                // ->middleware('permission:kpi.kpidivisioncompanypolicy.destroy')
                ->name('kpidivisioncompanypolicy.destroy');

            Route::get('/{id}/pdf', [KPIDivisionCompanyPolicyController::class, 'downloadPdf'])
                // ->middleware('permission:kpidivisioncompanypolicy.pdf')
                ->name('kpidivisioncompanypolicy.pdf');
        });

    /* ========================
        KPI Department
    ======================== */
    Route::prefix('kpidepartment')
        // ->middleware('permission:kpi.kpidepartment.view')
        ->group(function () {

            Route::get('/', [KPIDepartmentController::class, 'index'])
                ->name('kpidepartment.index');

            Route::get('/datatable', [KPIDepartmentController::class, 'dataTable'])
                // ->middleware('permission:kpi.kpidepartment.datatable')
                ->name('kpidepartment.datatable');

            Route::get('/create', [KPIDepartmentController::class, 'create'])
                // ->middleware('permission:kpi.kpidepartment.create')
                ->name('kpidepartment.create');

            Route::post('/', [KPIDepartmentController::class, 'store'])
                // ->middleware('permission:kpi.kpidepartment.create')
                ->name('kpidepartment.store');

            Route::get('/{id}/edit', [KPIDepartmentController::class, 'edit'])
                // ->middleware('permission:kpi.kpidepartment.edit')
                ->name('kpidepartment.edit');

            Route::get('/{id}/show', [KPIDepartmentController::class, 'show'])
                // ->middleware('permission:kpi.kpidepartment.edit')
                ->name('kpidepartment.show');

            Route::put('/{id}/update', [KPIDepartmentController::class, 'update'])
                // ->middleware('permission:kpi.kpidepartment.edit')
                ->name('kpidepartment.update');

            Route::delete('/{kpiDepartment}/destroy', [KPIDepartmentController::class, 'destroy'])
                // ->middleware('permission:kpi.kpidepartment.delete')
                ->name('kpidepartment.destroy');

            Route::patch('/{kpiDepartment}/inline', [KPIDepartmentController::class, 'inlineUpdate'])
                // ->middleware('permission:kpi.kpidepartment.inline')
                ->name('kpidepartment.inline');
        });

    Route::prefix('kpidepartmentcompanypolicy')
        // ->middleware('permission:kpi.kpidepartmentcp.view')
        ->group(function () {
            Route::get('/datatable', [KPIDepartmentCompanyPolicyController::class, 'dataTable'])
                // ->middleware('permission:kpi.kpidepartmentcompanypolicy.dataTable')
                ->name('kpidepartmentcompanypolicy.datatable');

            Route::post('/', [KPIDepartmentCompanyPolicyController::class, 'store'])
                // ->middleware('permission:kpi.kpidepartmentcompanypolicy.store')
                ->name('kpidepartmentcompanypolicy.store');

            Route::get('/{id}/show', [KPIDepartmentCompanyPolicyController::class, 'show'])
                // ->middleware('permission:kpi.kpidepartmentcompanypolicy.show')
                ->name('kpidepartmentcompanypolicy.show');

            Route::put('/{id}/update', [KPIDepartmentCompanyPolicyController::class, 'update'])
                // ->middleware('permission:kpi.kpidepartmentcompanypolicy.update')
                ->name('kpidepartmentcompanypolicy.update');

            Route::delete('/{id}', [KPIDepartmentCompanyPolicyController::class, 'destroy'])
                // ->middleware('permission:kpi.kpidepartmentcompanypolicy.destroy')
                ->name('kpidepartmentcompanypolicy.destroy');

            Route::get('/{id}/pdf', [KPIDepartmentCompanyPolicyController::class, 'downloadPdf'])
                // ->middleware('permission:kpidepartmentcompanypolicy.pdf')
                ->name('kpidepartmentcompanypolicy.pdf');
        });

    /* ========================
        KPI Section
    ======================== */
    Route::prefix('kpisection')
        // ->middleware('permission:kpi.kpisection.view')
        ->group(function () {

            Route::get('/', [KPISectionController::class, 'index'])
                ->name('kpisection.index');

            Route::get('/datatable', [KPISectionController::class, 'dataTable'])
                // ->middleware('permission:kpi.kpisection.datatable')
                ->name('kpisection.datatable');

            Route::get('/create', [KPISectionController::class, 'create'])
                // ->middleware('permission:kpi.kpisection.create')
                ->name('kpisection.create');

            Route::post('/', [KPISectionController::class, 'store'])
                // ->middleware('permission:kpi.kpisection.create')
                ->name('kpisection.store');

            Route::get('/{id}/edit', [KPISectionController::class, 'edit'])
                // ->middleware('permission:kpi.kpisection.edit')
                ->name('kpisection.edit');

            Route::get('/{id}/show', [KPISectionController::class, 'show'])
                // ->middleware('permission:kpi.kpisection.edit')
                ->name('kpisection.show');

            Route::put('/{id}/update', [KPISectionController::class, 'update'])
                // ->middleware('permission:kpi.kpisection.edit')
                ->name('kpisection.update');

            Route::delete('/{kpiSection}/destroy', [KPISectionController::class, 'destroy'])
                // ->middleware('permission:kpi.kpisection.delete')
                ->name('kpisection.destroy');

            Route::patch('/{kpiSection}/inline', [KPISectionController::class, 'inlineUpdate'])
                // ->middleware('permission:kpi.kpisection.inline')
                ->name('kpisection.inline');
        });

    Route::prefix('kpisectioncompanypolicy')
        // ->middleware('permission:kpi.kpisectioncp.view')
        ->group(function () {
            Route::get('/datatable', [KPISectionCompanyPolicyController::class, 'dataTable'])
                // ->middleware('permission:kpi.kpisectioncompanypolicy.dataTable')
                ->name('kpisectioncompanypolicy.datatable');

            Route::post('/', [KPISectionCompanyPolicyController::class, 'store'])
                // ->middleware('permission:kpi.kpisectioncompanypolicy.store')
                ->name('kpisectioncompanypolicy.store');

            Route::get('/{id}/show', [KPISectionCompanyPolicyController::class, 'show'])
                // ->middleware('permission:kpi.kpisectioncompanypolicy.show')
                ->name('kpisectioncompanypolicy.show');

            Route::put('/{id}/update', [KPISectionCompanyPolicyController::class, 'update'])
                // ->middleware('permission:kpi.kpisectioncompanypolicy.update')
                ->name('kpisectioncompanypolicy.update');

            Route::delete('/{id}', [KPISectionCompanyPolicyController::class, 'destroy'])
                // ->middleware('permission:kpi.kpisectioncompanypolicy.destroy')
                ->name('kpisectioncompanypolicy.destroy');

            Route::get('/{id}/pdf', [KPISectionCompanyPolicyController::class, 'downloadPdf'])
                // ->middleware('permission:kpisectioncompanypolicy.pdf')
                ->name('kpisectioncompanypolicy.pdf');
        });

    /* ========================
        BUDGET SUBMISSION
    ======================== */
    Route::prefix('budget-submission')->group(function () {
        Route::get('/', [BudgetSubmissionController::class, 'index'])
            ->name('budget.submission.index');

        Route::post('/', [BudgetSubmissionController::class, 'store'])
            ->name('budget.submission.store');

        Route::get('/{id}/edit', [BudgetSubmissionController::class, 'edit'])
            ->name('budget.submission.edit');

        Route::put('/{id}', [BudgetSubmissionController::class, 'update'])
            ->name('budget.submission.update');

        Route::delete('/{id}', [BudgetSubmissionController::class, 'destroy'])
            ->name('budget.submission.destroy');

        Route::post('/{id}/approve', [BudgetSubmissionController::class, 'approve'])
            ->name('budget.submission.approve');

        Route::post('/{id}/reject', [BudgetSubmissionController::class, 'reject'])
            ->name('budget.submission.reject');
    });


    Route::prefix('supplier')->group(function () {

        Route::get('/data', [SupplierController::class, 'data'])
            ->name('supplier.data');

        Route::post('/', [SupplierController::class, 'store'])
            ->name('supplier.store');

        Route::get('/{id}/edit', [SupplierController::class, 'edit'])
            ->name('supplier.edit');

        Route::put('/{id}', [SupplierController::class, 'update'])
            ->name('supplier.update');

        Route::delete('/{id}', [SupplierController::class, 'destroy'])
            ->name('supplier.destroy');
    });



    Route::prefix('customer')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customer.index');
        Route::get('/data', [CustomerController::class, 'data'])->name('customer.data');
        Route::post('/', [CustomerController::class, 'store'])->name('customer.store');
        Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('customer.edit');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('customer.update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('customer.destroy');
    });


    Route::prefix('unit')->group(function () {

        Route::get('/data', [UnitController::class, 'data'])
            ->name('unit.data');

        Route::post('/', [UnitController::class, 'store'])
            ->name('unit.store');

        Route::get('/{id}/edit', [UnitController::class, 'edit'])
            ->name('unit.edit');

        Route::put('/{id}', [UnitController::class, 'update'])
            ->name('unit.update');

        Route::delete('/{id}', [UnitController::class, 'destroy'])
            ->name('unit.destroy');
    });

    Route::prefix('segmen')->group(function () {

        Route::get('/data', [SegmenController::class, 'data'])
            ->name('segmen.data');

        Route::post('/', [SegmenController::class, 'store'])
            ->name('segmen.store');

        Route::get('/{id}/edit', [SegmenController::class, 'edit'])
            ->name('segmen.edit');

        Route::put('/{id}', [SegmenController::class, 'update'])
            ->name('segmen.update');

        Route::delete('/{id}', [SegmenController::class, 'destroy'])
            ->name('segmen.destroy');
    });

    Route::prefix('area')->group(function () {

        Route::get('/data', [AreaController::class, 'data'])
            ->name('area.data');

        Route::post('/', [AreaController::class, 'store'])
            ->name('area.store');

        Route::get('/{id}/edit', [AreaController::class, 'edit'])
            ->name('area.edit');

        Route::put('/{id}', [AreaController::class, 'update'])
            ->name('area.update');

        Route::delete('/{id}', [AreaController::class, 'destroy'])
            ->name('area.destroy');
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

    Route::prefix('pengajuan.anggaran')
        ->middleware('permission:pengajuan.anggaran.view')
        ->group(function () {

            Route::get('/', [PengajuanAnggaranController::class, 'index'])
                ->name('pengajuan.anggaran.index');

            Route::get('/create', [PengajuanAnggaranController::class, 'create'])
                ->middleware('permission:pengajuan.anggaran.create')
                ->name('pengajuan.anggaran.create');

            Route::post('/', [PengajuanAnggaranController::class, 'store'])
                ->middleware('permission:pengajuan.anggaran.create')
                ->name('pengajuan.anggaran.store');

            Route::get('/{id}/edit', [PengajuanAnggaranController::class, 'edit'])
                ->middleware('permission:pengajuan.anggaran.edit')
                ->name('pengajuan.anggaran.edit');

            Route::put('/{id}', [PengajuanAnggaranController::class, 'update'])
                ->middleware('permission:budget.edit')
                ->name('anggaran.update');

            Route::delete('/{id}', [PengajuanAnggaranController::class, 'destroy'])
                ->middleware('permission:pengajuan.anggaran.delete')
                ->name('pengajuan.anggaran.destroy');
        });

    Route::prefix('production')
        ->middleware('permission:production.view')
        ->group(function () {

            Route::get('/', [ProductionController::class, 'index'])
                ->name('production.index');

            Route::get('/create', [ProductionController::class, 'create'])
                ->middleware('permission:production.create')
                ->name('production.create');

            Route::post('/', [ProductionController::class, 'store'])
                ->middleware('permission:production.create')
                ->name('production.store');

            Route::get('/{id}/edit', [ProductionController::class, 'edit'])
                ->middleware('permission:production.edit')
                ->name('production.edit');

            Route::put('/{id}', [ProductionController::class, 'update'])
                ->middleware('permission:production.edit')
                ->name('production.update');

            Route::delete('/{id}', [ProductionController::class, 'destroy'])
                ->middleware('permission:production.delete')
                ->name('production.destroy');

            // Import
            Route::post('/import', [ProductionController::class, 'import'])
            // ->middleware('permission:production.import')
                ->name('production.import');

            // Download template
            Route::get('/template', [ProductionController::class, 'template'])
                // ->middleware('permission:production.template')
                ->name('production.template');

            Route::get('production/{production}/json', [ProductionController::class, 'json'])
                // ->middleware('permission:production.json')
                ->name('production.json');
        });

    Route::prefix('marketing')
        ->middleware('permission:marketing.view')
        ->group(function () {

            Route::get('/', [MarketingController::class, 'index'])
                ->name('marketing.index');

            Route::get('/create', [MarketingController::class, 'create'])
                ->middleware('permission:marketing.create')
                ->name('marketing.create');

            Route::post('/', [MarketingController::class, 'store'])
                ->middleware('permission:marketing.create')
                ->name('marketing.store');

            Route::get('/data', [MarketingController::class, 'getData'])
                ->name('marketing.data');

            Route::get('/{id}/edit', [MarketingController::class, 'edit'])
                ->middleware('permission:marketing.edit')
                ->name('marketing.edit');

            Route::put('/{id}', [MarketingController::class, 'update'])
                ->middleware('permission:marketing.edit')
                ->name('marketing.update');

            Route::delete('/{id}', [MarketingController::class, 'destroy'])
                ->middleware('permission:marketing.delete')
                ->name('marketing.destroy');
        });
    Route::post('/marketing-plan/upload-excel', [MarketingController::class, 'uploadExcel'])
        ->name('marketing.upload_excel');
    Route::get('/marketing/download-template', [MarketingController::class, 'downloadTemplate'])
        ->name('marketing.downloadTemplate');



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

        // User Submission Routes
        Route::prefix('user')
            ->middleware('permission:transaction.user.view')
            ->group(function () {

                Route::get('/', [SubmissionController::class, 'user'])
                    ->name('userSubmission.index');

                Route::get('/create', [SubmissionController::class, 'user_create'])
                    ->name('userSubmission.create');

                Route::get('/data', [SubmissionController::class, 'getData'])
                    ->name('userSubmission.data');

                Route::get('/summary', [SubmissionController::class, 'getSummary'])
                    ->name('userSubmission.summary');

                Route::post('/store', [SubmissionController::class, 'store'])
                    ->name('userSubmission.store');

                Route::get('/show/{id}', [SubmissionController::class, 'show'])
                    ->name('userSubmission.show');

                Route::put('/update/{id}', [SubmissionController::class, 'update'])
                    ->name('userSubmission.update');

                Route::delete('/delete/{id}', [SubmissionController::class, 'destroy'])
                    ->name('userSubmission.destroy');

                Route::get('/budget/{id}', [SubmissionController::class, 'getBudgetInfo'])
                    ->name('userSubmission.budget.info');

                Route::prefix('{id}')->group(function () {
                    Route::post('/approve', [SubmissionController::class, 'approve'])
                        ->name('userSubmission.approve');

                    Route::post('/reject', [SubmissionController::class, 'reject'])
                        ->name('userSubmission.reject');
                });

                // Cascading dropdown routes
                Route::get('/job-positions/{jobLevelId}', [SubmissionController::class, 'getJobPositions'])
                    ->name('userSubmission.jobPositions');
                Route::get('/programs/{jobLevelId}', [SubmissionController::class, 'getPrograms'])
                    ->name('userSubmission.programs');
                Route::get('/budget-items/{programId}', [SubmissionController::class, 'getBudgetItems'])
                    ->name('userSubmission.budgetItems');
            });

        // Admin Submission Routes
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
        BUDGET ADMIN
    ======================== */
    Route::prefix('budget-admin')
        ->middleware('permission:budget.view')
        ->group(function () {
            Route::get('/', [BudgetAdminController::class, 'index'])
                ->name('budget.admin');

            Route::get('/data', [BudgetAdminController::class, 'getBudgetData'])
                ->name('budget.admin.data');
        });

    // BUDGET USER
    Route::prefix('budget-user')
        ->middleware('permission:budget.view')
        ->group(function () {
            Route::get('/', [BudgetUserController::class, 'index'])
                ->name('budget-user.index');

            // New endpoints for all items
            Route::get('/items/all', [BudgetUserController::class, 'getAllItems'])
                ->name('budget-user.items.all');
            Route::post('/items', [BudgetUserController::class, 'storeItem'])
                ->name('budget-user.items.store');
            Route::put('/items/{itemId}', [BudgetUserController::class, 'updateItem'])
                ->name('budget-user.items.update');
            Route::delete('/items/{itemId}', [BudgetUserController::class, 'destroyItem'])
                ->name('budget-user.items.destroy');

            // Dropdown data endpoints
            Route::get('/budget-categories', [BudgetUserController::class, 'getBudgetCategories'])
                ->name('budget-user.budget-categories');
            Route::get('/cost-centers', [BudgetUserController::class, 'getCostCenters'])
                ->name('budget-user.cost-centers');
            Route::get('/suppliers', [BudgetUserController::class, 'getSuppliers'])
                ->name('budget-user.suppliers');
            Route::get('/units', [BudgetUserController::class, 'getUnits'])
                ->name('budget-user.units');

            // Workplans dropdown for department and section
            Route::get('/workplans/dropdown', [BudgetUserController::class, 'getWorkplansDropdown'])
                ->name('budget-user.workplans.dropdown');

            // Old endpoints (kept for compatibility)
            Route::get('/divisions', [BudgetUserController::class, 'getDivisions'])
                ->name('budget-user.divisions');
            Route::get('/workplans', [BudgetUserController::class, 'getWorkplans'])
                ->name('budget-user.workplans');
            Route::get('/{workplanId}/categories', [BudgetUserController::class, 'getCategories'])
                ->name('budget-user.categories');
            Route::get('/{workplanId}/items', [BudgetUserController::class, 'getItems'])
                ->name('budget-user.items');
            Route::post('/{workplanId}/items', [BudgetUserController::class, 'store'])
                ->name('budget-user.store');
            Route::put('/{workplanId}/items/{itemId}', [BudgetUserController::class, 'update'])
                ->name('budget-user.update');
            Route::delete('/{workplanId}/items/{itemId}', [BudgetUserController::class, 'destroy'])
                ->name('budget-user.destroy');
        });

    /* ========================
        WORK PLAN (Program Kerja)
    ======================== */
    Route::prefix('workplan')
        ->middleware('permission:budget.view')
        ->group(function () {

            Route::get('/', [KPIWorkPlanController::class, 'index'])
                ->name('workplan.index');

            // API endpoints for dynamic data
            Route::get('/get-kpi-data', [KPIWorkPlanController::class, 'getKpiData'])
                ->name('workplan.getKpiData');

            Route::post('/store', [KPIWorkPlanController::class, 'store'])
                ->name('workplan.store');

            Route::prefix('{id}')->group(function () {

                Route::prefix('item')->group(function () {
                    Route::get('/', [WorkPlanItemController::class, 'index'])
                        ->name('workplan.items');

                    // AJAX endpoints for budget items
                    Route::get('/categories', [WorkPlanItemController::class, 'getCategories'])
                        ->name('workplan.items.categories');

                    Route::get('/list', [WorkPlanItemController::class, 'getItems'])
                        ->name('workplan.items.list');

                    Route::post('/', [WorkPlanItemController::class, 'store'])
                        ->name('workplan.items.store');

                    Route::put('/{itemId}', [WorkPlanItemController::class, 'update'])
                        ->name('workplan.items.update');

                    Route::delete('/{itemId}', [WorkPlanItemController::class, 'destroy'])
                        ->name('workplan.items.destroy');
                });

                Route::put('/', [KPIWorkPlanController::class, 'update'])
                    ->name('workplan.update');

                Route::delete('/', [KPIWorkPlanController::class, 'destroy'])
                    ->name('workplan.destroy');

                Route::post('/approve', [KPIWorkPlanController::class, 'approve'])
                    ->name('workplan.approve');

                Route::patch('/update-realization', [KPIWorkPlanController::class, 'updateRealization'])
                    ->name('workplan.updateRealization');
            });
        });


    /*==========================
        BUDGET ADMIN
    ==========================*/
    Route::prefix('budget-admin')
        ->middleware('permission:budget.view')
        ->group(function () {

            Route::get('/', [BudgetAdminController::class, 'index'])
                ->name('budget-admin.index');
        });

    // Budget Resume
    Route::prefix('budget-resume')
        ->middleware('permission:budget.view')
        ->group(function () {

            Route::get('/', [BudgetResumeController::class, 'index'])
                ->name('budget-resume.index');
        });



    /* ========================
        MASTER
    ======================== */

    Route::prefix('employee')
        ->middleware('permission:employee.view')
        ->group(function () {
            Route::get('/data', [EmployeeController::class, 'getData'])
                ->name('employee.data');
            Route::post('/{id}/reset-password', [EmployeeController::class, 'resetPassword'])
                ->middleware('permission:employee.edit');
            Route::get('/{id}/edit', [EmployeeController::class, 'edit'])
                ->name('employee.edit')
                ->middleware('permission:employee.edit');
            Route::post('/create', [EmployeeController::class, 'store'])
                ->name('employee.store')
                ->middleware('permission:employee.create');
            Route::post('/delete/{id}', [EmployeeController::class, 'destroy'])
                ->middleware('permission:employee.delete');
            Route::get('/{id}', [EmployeeController::class, 'show'])
                ->name('employee.show');
            Route::post('/update/{id}', [EmployeeController::class, 'update'])
                ->name('employee.update');
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
        DYNAMIC APPROVAL SYSTEM
    ======================== */
    Route::prefix('approval')
        ->middleware('permission:approval.view')
        ->group(function () {
            // Dashboard
            Route::get('/', [ApprovalController::class, 'index'])
                ->name('approval');

            // Get pending approvals for current user (AJAX)
            Route::get('/pending', [ApprovalController::class, 'getPendingApprovals'])
                ->name('approval.pending');

            // Get all transactions for admin (AJAX)
            Route::get('/transactions', [ApprovalController::class, 'getAllTransactions'])
                ->name('approval.transactions');

            // Get statistics (AJAX)
            Route::get('/statistics', [ApprovalController::class, 'getStatistics'])
                ->name('approval.statistics');

            // Get approval detail
            Route::get('/detail/{id}', [ApprovalController::class, 'show'])
                ->name('approval.show');

            // Get transaction detail with approvals
            Route::get('/transaction/{transactionId}', [ApprovalController::class, 'getTransactionDetail'])
                ->name('approval.transaction.detail');

            // Get approval history
            Route::get('/history/{transactionId}', [ApprovalController::class, 'getHistory'])
                ->name('approval.history');

            // Process approval (approve)
            Route::post('/approve/{id}', [ApprovalController::class, 'approve'])
                ->middleware('permission:approval.create')
                ->name('approval.approve');

            // Process approval (reject)
            Route::post('/reject/{id}', [ApprovalController::class, 'reject'])
                ->middleware('permission:approval.create')
                ->name('approval.reject');

            // Cancel transaction
            Route::post('/cancel/{transactionId}', [ApprovalController::class, 'cancel'])
                ->name('approval.cancel');

            // Check threshold for amount
            Route::post('/check-threshold', [ApprovalController::class, 'checkThreshold'])
                ->name('approval.checkThreshold');

            // Threshold Management
            Route::prefix('threshold')->group(function () {
                Route::get('/', [ApprovalController::class, 'thresholdIndex'])
                    ->name('approval.threshold');
                Route::get('/data', [ApprovalController::class, 'getThresholds'])
                    ->name('approval.threshold.data');
                Route::post('/store', [ApprovalController::class, 'storeThreshold'])
                    ->middleware('permission:approval.create')
                    ->name('approval.threshold.store');
                Route::put('/update/{id}', [ApprovalController::class, 'updateThreshold'])
                    ->middleware('permission:approval.edit')
                    ->name('approval.threshold.update');
                Route::delete('/delete/{id}', [ApprovalController::class, 'deleteThreshold'])
                    ->middleware('permission:approval.delete')
                    ->name('approval.threshold.delete');
            });

            // Authorizer Management
            Route::prefix('authorizer')->group(function () {
                Route::get('/', [ApprovalController::class, 'authorizerIndex'])
                    ->name('approval.authorizer');
                Route::get('/data', [ApprovalController::class, 'getAuthorizers'])
                    ->name('approval.authorizer.data');
                Route::post('/store', [ApprovalController::class, 'storeAuthorizer'])
                    ->middleware('permission:approval.create')
                    ->name('approval.authorizer.store');
                Route::put('/update/{id}', [ApprovalController::class, 'updateAuthorizer'])
                    ->middleware('permission:approval.edit')
                    ->name('approval.authorizer.update');
                Route::delete('/delete/{id}', [ApprovalController::class, 'deleteAuthorizer'])
                    ->middleware('permission:approval.delete')
                    ->name('approval.authorizer.delete');
            });
        });

    /* ========================
        SETTINGS
    ======================== */
    Route::middleware('permission:setting.master.view')
        ->get('/master', [MasterController::class, 'index'])
        ->name('master');
    Route::middleware('permission:setting.users.view')
        ->get('/user', [UsersController::class, 'index'])
        ->name('users.index');
    Route::middleware('permission:setting.history.view')
        ->get('/history', [MasterController::class, 'history'])
        ->name('history');
    Route::middleware('permission:setting.code.view')
        ->get('/code', [SettingCodeController::class, 'index'])
        ->name('code.index');
    Route::middleware('permission:setting.production.view')
        ->get('/setting.production', [SettingProductionController::class, 'index'])
        ->name('setting.production.index');

    /* ========================
        BUDGET CATEGORY
    ======================== */
    Route::prefix('budgetCategory')
        ->middleware('permission:setting.master.view')
        ->group(function () {
            Route::get('/', [BudgetCategoryController::class, 'index'])
                ->name('budgetCategory.index');
            Route::get('/data', [BudgetCategoryController::class, 'data'])
                ->name('budgetCategory.data');
            Route::post('/', [BudgetCategoryController::class, 'store'])
                ->name('budgetCategory.store');
            Route::get('/{id}/edit', [BudgetCategoryController::class, 'edit'])
                ->name('budgetCategory.edit');
            Route::put('/{id}', [BudgetCategoryController::class, 'update'])
                ->name('budgetCategory.update');
            Route::delete('/{id}', [BudgetCategoryController::class, 'destroy'])
                ->name('budgetCategory.destroy');
            Route::get('/parents', [BudgetCategoryController::class, 'getParentCategories'])
                ->name('budgetCategory.parents');
        });

    /* ========================
        BUDGET CODE
    ======================== */


    Route::prefix('budgetCode')
        ->middleware('permission:setting.master.view')
        ->group(function () {
            Route::get('/', [BudgetCodeController::class, 'index'])
                ->name('budgetCode.index');
            Route::get('/data', [BudgetCodeController::class, 'data'])
                ->name('budgetCode.data');
            Route::post('/', [BudgetCodeController::class, 'store'])
                ->name('budgetCode.store');
            Route::get('/{id}/edit', [BudgetCodeController::class, 'edit'])
                ->name('budgetCode.edit');
            Route::put('/{id}', [BudgetCodeController::class, 'update'])
                ->name('budgetCode.update');
            Route::delete('/{id}', [BudgetCodeController::class, 'destroy'])
                ->name('budgetCode.destroy');
        });



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
            Route::post('/permissions/store', [AuthorizationController::class, 'permissionStore'])->name('authorization.permissions.store');
            Route::post('/permissions/update/{id}', [AuthorizationController::class, 'permissionUpdate'])->name('auth.permissions.update');
            Route::delete('/permissions/delete/{id}', [AuthorizationController::class, 'permissionDelete'])->name('auth.permissions.delete');


            Route::get('/roles/{id}/permissions', [AuthorizationController::class, 'rolePermissions'])->name('auth.roles.permissions');
            Route::post('/roles/{id}/permissions/update', [AuthorizationController::class, 'rolePermissionsUpdate'])->name('auth.roles.permissions.update');

            Route::get('/assign-role/{id}', [AuthorizationController::class, 'assignRoleView'])->name('auth.assign.view');
            Route::post('/assign-role', [AuthorizationController::class, 'assignRole'])->name('auth.assign.role');

            Route::post('/role/remove-user', [AuthorizationController::class, 'removeUserRole'])
                ->name('role.removeUser');
        });

    Route::prefix('authorizationTransaction')->group(function () {
        Route::get('/', [AuthorizationTransactionController::class, 'index'])->name('authorizationTransaction.index');
        Route::get('/data', [AuthorizationTransactionController::class, 'data'])->name('authorizationTransaction.data');
        Route::post('/', [AuthorizationTransactionController::class, 'store'])->name('authorizationTransaction.store');
        Route::get('/{id}/edit', [AuthorizationTransactionController::class, 'edit'])->name('authorizationTransaction.edit');
        Route::put('/{id}', [AuthorizationTransactionController::class, 'update'])->name('authorizationTransaction.update');
        Route::delete('/{id}', [AuthorizationTransactionController::class, 'destroy'])->name('authorizationTransaction.destroy');
    });


    Route::prefix('authorizationAddBudget')->group(function () {
        Route::get('/', [AuthorizationAddBudgetController::class, 'index'])->name('authorizationAddBudget.index');
        Route::get('/data', [AuthorizationAddBudgetController::class, 'data'])->name('authorizationAddBudget.data');
        Route::post('/', [AuthorizationAddBudgetController::class, 'store'])->name('authorizationAddBudget.store');
        Route::get('/{id}/edit', [AuthorizationAddBudgetController::class, 'edit'])->name('authorizationAddBudget.edit');
        Route::put('/{id}', [AuthorizationAddBudgetController::class, 'update'])->name('authorizationAddBudget.update');
        Route::delete('/{id}', [AuthorizationAddBudgetController::class, 'destroy'])->name('authorizationAddBudget.destroy');
    });
});
