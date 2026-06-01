<?php

use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthorizationAddBudgetController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\AuthorizationTransactionController;
use App\Http\Controllers\BudgetAdminController;
use App\Http\Controllers\BudgetCategoryController;
use App\Http\Controllers\BudgetCodeController;
use App\Http\Controllers\BudgetResumeController;
use App\Http\Controllers\BudgetSubmissionController;
use App\Http\Controllers\BudgetUserController;
use App\Http\Controllers\CompanyPolicyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JobLevelController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\KPIDepartmentCompanyPolicyController;
use App\Http\Controllers\KPIDepartmentController;
use App\Http\Controllers\KPIDivisionCompanyPolicyController;
use App\Http\Controllers\KPIDivisionController;
use App\Http\Controllers\KPISectionCompanyPolicyController;
use App\Http\Controllers\KPISectionController;
use App\Http\Controllers\KPIWorkPlanController;
use App\Http\Controllers\LpjApprovalMasterController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\MasterApprovalController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PengajuanAnggaranController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\SasaranStrategisController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SegmenController;
use App\Http\Controllers\SettingCodeController;
use App\Http\Controllers\SettingPriceController;
use App\Http\Controllers\SettingProductionController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VerificationBudgetController;
use App\Http\Controllers\WorkPlanItemController;
use App\Http\Controllers\WorkplanBudgetItemMasterApprovalController;
use App\Http\Controllers\PipController;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// routes/web.php
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthorizationController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthorizationController::class, 'login'])->name('login.post');

// Route::get('/', Login::class)->name('login');

Route::middleware('auth')->group(function () {

    /* LOGOUT */
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    })->name('logout');

    /* PROFILE */
    Route::prefix('profile')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
        Route::post('/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
        Route::post('/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
    });

    /* ========================
        DASHBOARD
    ======================== */

    // DASHBOARD EXECUTIVE
    Route::middleware(['auth', 'permission:dashboard.view'])->group(function () {
        Route::get('/dashboard/executive', [DashboardController::class, 'executive'])
            ->name('dash.executive');
    });

    // DASHBOARD USER
    Route::middleware(['auth', 'permission:dashboard.view'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');   // <- WAJIB ADA
    });

    Route::get('/dash-executive/policies', [DashboardController::class, 'executivePoliciesByYear'])
        ->name('dash.executive.policies');
    Route::get('/budget/summary', [DashboardController::class, 'budgetSummaryByYear'])
        ->name('budget.summary.year');

    // DASHBOARD AJAX ENDPOINTS
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getDashboardStats'])
            ->name('dashboard.stats');
        Route::get('/division-realization', [DashboardController::class, 'getDivisionRealizationData'])
            ->name('dashboard.division.realization');
        Route::get('/monthly-chart', [DashboardController::class, 'getMonthlyChartData'])
            ->name('dashboard.monthly.chart');
    });

    // DASH EXECUTIVE AJAX ENDPOINTS
    Route::prefix('dash-executive')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getDashboardStats'])
            ->name('dash.executive.stats');
        Route::get('/division-realization', [DashboardController::class, 'getDivisionRealizationData'])
            ->name('dash.executive.division.realization');
        Route::get('/monthly-chart', [DashboardController::class, 'getMonthlyChartData'])
            ->name('dash.executive.monthly.chart');
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
    Route::prefix('KPIDepartment')
        // ->middleware('permission:kpi.KPIDepartment.view')
        ->group(function () {

            Route::get('/', [KPIDepartmentController::class, 'index'])
            ->name('KPIDepartment.index');

            Route::get('/datatable', [KPIDepartmentController::class, 'dataTable'])
            // ->middleware('permission:kpi.KPIDepartment.datatable')
            ->name('KPIDepartment.datatable');

        Route::get('/kpi-divisions', [KPIDepartmentController::class, 'getKpiDivisionsForForm'])
            ->name('KPIDepartment.kpiDivisions');

        Route::get('/create', [KPIDepartmentController::class, 'create'])
            // ->middleware('permission:kpi.KPIDepartment.create')
            ->name('KPIDepartment.create');

            Route::post('/', [KPIDepartmentController::class, 'store'])
            // ->middleware('permission:kpi.KPIDepartment.create')
            ->name('KPIDepartment.store');

            Route::get('/{id}/edit', [KPIDepartmentController::class, 'edit'])
            // ->middleware('permission:kpi.KPIDepartment.edit')
            ->name('KPIDepartment.edit');

            Route::get('/{id}/show', [KPIDepartmentController::class, 'show'])
            // ->middleware('permission:kpi.KPIDepartment.edit')
            ->name('KPIDepartment.show');

            Route::put('/{id}/update', [KPIDepartmentController::class, 'update'])
            // ->middleware('permission:kpi.KPIDepartment.edit')
            ->name('KPIDepartment.update');

        Route::delete('/{KPIDepartment}/destroy', [KPIDepartmentController::class, 'destroy'])
            // ->middleware('permission:kpi.KPIDepartment.delete')
            ->name('KPIDepartment.destroy');

        Route::patch('/{KPIDepartment}/inline', [KPIDepartmentController::class, 'inlineUpdate'])
            // ->middleware('permission:kpi.KPIDepartment.inline')
            ->name('KPIDepartment.inline');
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

        Route::get('/kpi-departments', [KPISectionController::class, 'getKpiDepartmentsForForm'])
            ->name('kpisection.kpiDepartments');

        Route::get('/sections', [KPISectionController::class, 'getSectionsForForm'])
            ->name('kpisection.sections');

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

        Route::get('/data', [BudgetSubmissionController::class, 'getData'])
            ->name('budget.submission.data');

        Route::get('/budget-codes-all', [BudgetSubmissionController::class, 'getAllBudgetCodes'])
            ->name('budget.submission.budgetCodesAll');

        Route::get('/budget-codes', [BudgetSubmissionController::class, 'getBudgetCodes'])
            ->name('budget.submission.budgetCodes');

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
        // ->middleware('permission:pengajuan.anggaran.view')
        ->group(function () {

            Route::get('/', [PengajuanAnggaranController::class, 'index'])
                ->name('pengajuan.anggaran.index');

            Route::get('/create', [PengajuanAnggaranController::class, 'create'])
                ->middleware('permission:pengajuan.anggaran.create')
                ->name('pengajuan.anggaran.create');

            Route::get('/import', [ImportController::class, 'showImportForm'])
                // ->middleware('permission:pengajuan.anggaran.create')
                ->name('import.workplan-budget.form');

            Route::post('/import', [ImportController::class, 'import'])
                // ->middleware('permission:pengajuan.anggaran.create')
                ->name('import.workplan-budget');

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
                // ->middleware('permission:production.store')
                ->name('production.store');

            Route::get('/{id}/edit', [ProductionController::class, 'edit'])
                ->middleware('permission:production.edit')
                ->name('production.edit');

            Route::put('/{id}', [ProductionController::class, 'update'])
                ->middleware('permission:production.update')
                ->name('production.update');

            Route::delete('/{id}', [ProductionController::class, 'destroy'])
                ->middleware('permission:production.destroy')
                ->name('production.destroy');

            // Import
            Route::post('/import', [ProductionController::class, 'import'])
                // ->middleware('permission:production.import')
                ->name('production.import');

            // Download template
            Route::get('/template', [ProductionController::class, 'template'])
                // ->middleware('permission:production.template')
                ->name('production.template');

            Route::get('{production}/json', [ProductionController::class, 'json'])
                // ->middleware('permission:production.json')
                ->name('production.json');

            Route::get('/datatable', [ProductionController::class, 'dataTable'])
                // ->middleware('permission:production.datatable')
                ->name('production.datatable');
        });

    // Route::prefix('marketing')
    //     ->middleware('permission:marketing.view')
    //     ->group(function () {

    //         Route::get('/', [MarketingController::class, 'index'])
    //             ->name('marketing.index');

    //         Route::get('/data', [MarketingController::class, 'getData'])
    //             ->name('marketing.data');

    //         Route::post('/upload-excel', [MarketingController::class, 'uploadExcel'])
    //             ->middleware('permission:marketing.create')
    //             ->name('marketing.upload_excel');

    //         Route::get('/download-template', [MarketingController::class, 'downloadTemplate'])
    //             ->name('marketing.downloadTemplate');
    //     });
    Route::prefix('marketing')->group(function () {

        Route::get('/', [MarketingController::class, 'index'])
            ->middleware('permission:marketing.view')
            ->name('marketing.index');

        Route::get('/data', [MarketingController::class, 'getData'])
            ->name('marketing.data');
        Route::post('/upload-excel', [MarketingController::class, 'uploadExcel'])
            ->middleware('permission:marketing.create') // ⬅️ INI PENTING
            ->name('marketing.upload_excel');

        Route::get('/download-template', [MarketingController::class, 'downloadTemplate'])
            ->middleware('permission:marketing.view')
            ->name('marketing.downloadTemplate');
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
    Route::prefix('transactions')->group(function () {

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

                Route::get('/template', [SubmissionController::class, 'downloadTemplate'])
                    ->name('userSubmission.template');

                Route::post('/import', [SubmissionController::class, 'import'])
                    ->name('userSubmission.import');

                // MacframeGA Two-Phase Import
                Route::post('/import-macframe-preview', [SubmissionController::class, 'previewMacframeImport'])
                    ->name('userSubmission.importMacframePreview');

                Route::post('/import-macframe-commit', [SubmissionController::class, 'commitMacframeImport'])
                    ->name('userSubmission.importMacframeCommit');

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

                // admission/user/due-date
                Route::get('/due-date', [SubmissionController::class, 'dueDate'])
                    ->name('userSubmission.dueDate');
                Route::get('/due-date-data', [SubmissionController::class, 'getDueDateData'])
                    ->name('userSubmission.dueDateData');

                Route::prefix('{id}')->group(function () {
                    Route::post('/approve', [SubmissionController::class, 'approve'])
                        ->name('userSubmission.approve');

                    Route::post('/reject', [SubmissionController::class, 'reject'])
                        ->name('userSubmission.reject');
                });

                Route::get('/badgeinfo/{id}', [SubmissionController::class, 'getBadgeInfo'])
                    ->name('userSubmission.badgeinfo');
                Route::get('/viewpdf/{id}', [SubmissionController::class, 'viewPdf'])
                    ->name('userSubmission.viewPdf');

                // Dynamic approval system routes
                Route::get('/approval-status/{id}', [SubmissionController::class, 'getApprovalStatus'])
                    ->name('userSubmission.approvalStatus');
                Route::get('/pending-approvals', [SubmissionController::class, 'getPendingApprovals'])
                    ->name('userSubmission.pendingApprovals');
                Route::post('/cancel-approval/{id}', [SubmissionController::class, 'cancelApproval'])
                    ->name('userSubmission.cancelApproval');
                Route::post('/resubmit/{id}', [SubmissionController::class, 'resubmitForApproval'])
                    ->name('userSubmission.resubmit');

                // Cascading dropdown routes
                Route::get('/job-positions/{jobLevelId}', [SubmissionController::class, 'getJobPositions'])
                    ->name('userSubmission.jobPositions');
                Route::get('/programs/{jobLevelId}', [SubmissionController::class, 'getPrograms'])
                    ->name('userSubmission.programs');
                Route::get('/budget-items/{programId}', [SubmissionController::class, 'getBudgetItems'])
                    ->name('userSubmission.budgetItems');

                // LPJ (Laporan Pertanggungjawaban) routes
                Route::prefix('lpj')->group(function () {
                    Route::get('/form/{transactionId}', [SubmissionController::class, 'getLpjFormData'])
                        ->name('userSubmission.lpj.form');
                    Route::post('/submit/{transactionId}', [SubmissionController::class, 'submitLpj'])
                        ->name('userSubmission.lpj.submit');
                    Route::get('/transaction/{transactionId}', [SubmissionController::class, 'getLpjByTransaction'])
                        ->name('userSubmission.lpj.byTransaction');
                Route::get('/pending', [SubmissionController::class, 'getPendingLpjApprovals'])
                    ->name('userSubmission.lpj.pending');
                Route::get('/counts', [SubmissionController::class, 'getLpjApprovalCounts'])
                    ->name('userSubmission.lpj.counts');
                    Route::get('/{lpjId}/proof', [SubmissionController::class, 'viewLpjProof'])
                        ->name('userSubmission.lpj.proof');
                    Route::post('/{lpjId}/approve', [SubmissionController::class, 'approveLpj'])
                        ->name('userSubmission.lpj.approve');
                Route::post('/{lpjId}/approve-with-fis', [SubmissionController::class, 'approveLpjWithFis'])
                    ->name('userSubmission.lpj.approveWithFis');
                    Route::post('/{lpjId}/reject', [SubmissionController::class, 'rejectLpj'])
                    ->name('userSubmission.lpj.reject');
                });
            });

        // Approval Submission Routes
        Route::prefix('approval')
            ->middleware('permission:transaction.approval.view')
            ->group(function () {
                Route::get('/', [SubmissionController::class, 'approval'])
                    ->name('approvalSubmission.index');

                // Get badge counts for all tabs (pending, approved, rejected)
                Route::get('/counts', [SubmissionController::class, 'getApprovalCounts'])
                    ->name('userSubmission.approval.counts');

                // Get approval data for specific tab (pending, approved, rejected)
                Route::get('/data', [SubmissionController::class, 'getApprovalData'])
                    ->name('userSubmission.approval.data');

                // Pending approvals route (kept for backward compatibility)
                Route::get('/pending-approvals', [SubmissionController::class, 'getPendingApprovals'])
                    ->name('approvalSubmission.pendingApprovals');

                // Show transaction detail
                Route::get('/{id}', [SubmissionController::class, 'show'])
                    ->name('approvalSubmission.show');

                // Approval actions - Authorization is handled inside controller
                // (checks if user is the next approver in the approval chain)
                Route::post('/{id}/approve', [SubmissionController::class, 'approve'])
                    ->name('approvalSubmission.approve');

                Route::post('/{id}/reject', [SubmissionController::class, 'reject'])
                    ->name('approvalSubmission.reject');
            });
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
            Route::get('/budget-codes', [BudgetUserController::class, 'getBudgetCodes'])
                ->name('budget-user.budget-codes');
            Route::get('/budget-codes/search', [BudgetUserController::class, 'searchBudgetCodes'])
                ->name('budget-user.budget-codes.search');
            Route::get('/budget-codes/by-code', [BudgetUserController::class, 'getBudgetCodeByCode'])
                ->name('budget-user.budget-codes.by-code');
            Route::get('/cost-centers', [BudgetUserController::class, 'getCostCenters'])
                ->name('budget-user.cost-centers');
            Route::get('/suppliers', [BudgetUserController::class, 'getSuppliers'])
                ->name('budget-user.suppliers');
            Route::get('/units', [BudgetUserController::class, 'getUnits'])
                ->name('budget-user.units');
            Route::get('/stock-codes', [BudgetUserController::class, 'getStockCodes'])
                ->name('budget-user.stock-codes');
            Route::get('/stock-codes/search', [BudgetUserController::class, 'searchStockCodes'])
                ->name('budget-user.stock-codes.search');
            Route::get('/stock-codes/by-code', [BudgetUserController::class, 'getStockCodeByCode'])
                ->name('budget-user.stock-codes.by-code');

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
    Route::prefix('workplans')
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
        NOTIFICATIONS
    ======================== */
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/user-notifications', [\App\Http\Controllers\NotificationController::class, 'getUserNotifications'])->name('notifications.user');
        Route::post('/mark-as-read/{id}', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

        Route::middleware('permission:setting.notification.view')->group(function () {
            Route::get('/monitoring', [\App\Http\Controllers\NotificationController::class, 'monitoring'])->name('notifications.monitoring');
            Route::get('/monitoring/data', [\App\Http\Controllers\NotificationController::class, 'data'])->name('notifications.monitoring.data');
            Route::delete('/monitoring/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.monitoring.destroy');
        });

        Route::middleware('permission:setting.notification.category.view')->group(function () {
            Route::get('/categories', [\App\Http\Controllers\NotificationCategoryController::class, 'index'])->name('notifications.categories');
            Route::get('/categories/data', [\App\Http\Controllers\NotificationCategoryController::class, 'data'])->name('notifications.categories.data');
            Route::post('/categories', [\App\Http\Controllers\NotificationCategoryController::class, 'store'])->name('notifications.categories.store');
            Route::delete('/categories/{id}', [\App\Http\Controllers\NotificationCategoryController::class, 'destroy'])->name('notifications.categories.destroy');
        });
    });

    /* ========================
        MASTER
    ======================== */

    /* ========================
        PIP / FIS INTEGRATION PROXY
    ======================== */
    Route::prefix('pip')->group(function () {
        Route::get('/jenis-kas', [PipController::class, 'getJenisKas'])->name('pip.jenis-kas');
        Route::get('/jenis-transaksi', [PipController::class, 'getJenisTransaksi'])->name('pip.jenis-transaksi');
        Route::get('/cost-center', [PipController::class, 'getCostCenter'])->name('pip.cost-center');
        Route::get('/vendor', [PipController::class, 'getVendor'])->name('pip.vendor');
        Route::get('/ppn', [PipController::class, 'getPpn'])->name('pip.ppn');
        Route::get('/tax', [PipController::class, 'getTax'])->name('pip.tax');
        Route::get('/pph', [PipController::class, 'getPph'])->name('pip.pph');
    });

    Route::prefix('employee')
        ->middleware('permission:employee.view')
        ->group(function () {
            Route::get('/data', [EmployeeController::class, 'getData'])
                ->name('employee.data');
        Route::get('/resolve-uppline/{jobPositionId}', [EmployeeController::class, 'resolveUppline'])
            ->name('employee.resolve-uppline');
            Route::post('/create', [EmployeeController::class, 'store'])
                ->name('employee.store')
                ->middleware('permission:employee.create');
            Route::delete('/delete/{id}', [EmployeeController::class, 'destroy'])
                ->middleware('permission:employee.delete');
            Route::post('/update/{id}', [EmployeeController::class, 'update'])
                ->name('employee.update');
            Route::post('/{id}/reset-password', [EmployeeController::class, 'resetPassword'])
                ->middleware('permission:employee.edit');
            Route::get('/{id}/edit', [EmployeeController::class, 'edit'])
                ->name('employee.edit')
                ->middleware('permission:employee.edit');
            Route::get('/{id}', [EmployeeController::class, 'show'])
                ->name('employee.show');
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
            // Main Approval Page with Tabs (Dashboard, Authorizer, Threshold)
            Route::get('/', [MasterApprovalController::class, 'index'])
                ->name('approval');

            // Threshold Management API (Accessed via Tab)
            Route::put('/threshold/update/{id}', [MasterApprovalController::class, 'updateThreshold'])
                ->middleware('permission:approval.edit')
                ->name('approval.threshold.update');
            Route::delete('/threshold/delete/{id}', [MasterApprovalController::class, 'deleteThreshold'])
                ->middleware('permission:approval.delete')
                ->name('approval.threshold.delete');

            // Authorizer Management API (Accessed via Tab)
            Route::get('/authorizer/data', [MasterApprovalController::class, 'getAuthorizers'])
                ->name('approval.authorizer.data');
            Route::post('/authorizer/store', [MasterApprovalController::class, 'storeAuthorizer'])
                ->middleware('permission:approval.create')
                ->name('approval.authorizer.store');
            Route::put('/authorizer/update/{id}', [MasterApprovalController::class, 'updateAuthorizer'])
                ->middleware('permission:approval.edit')
                ->name('approval.authorizer.update');
            Route::delete('/authorizer/delete/{id}', [MasterApprovalController::class, 'deleteAuthorizer'])
                ->middleware('permission:approval.delete')
                ->name('approval.authorizer.delete');

            // ========== NEW: Modules Management API ==========
            Route::get('/modules/data', [MasterApprovalController::class, 'getModules'])
                ->name('approval.modules.data');
            Route::get('/modules/available-tables', [MasterApprovalController::class, 'getAvailableTables'])
                ->name('approval.modules.tables');
            Route::post('/modules/store', [MasterApprovalController::class, 'storeModule'])
                ->middleware('permission:approval.create')
                ->name('approval.modules.store');
            Route::put('/modules/update/{id}', [MasterApprovalController::class, 'updateModule'])
                ->middleware('permission:approval.edit')
                ->name('approval.modules.update');
            Route::delete('/modules/delete/{id}', [MasterApprovalController::class, 'deleteModule'])
                ->middleware('permission:approval.delete')
                ->name('approval.modules.delete');

            // ========== NEW: Templates Management API ==========
            Route::get('/templates/data', [MasterApprovalController::class, 'getTemplates'])
                ->name('approval.templates.data');
            Route::get('/templates/modules-dropdown', [MasterApprovalController::class, 'getModulesForDropdown'])
                ->name('approval.templates.modules');
            Route::post('/templates/store', [MasterApprovalController::class, 'storeTemplate'])
                ->middleware('permission:approval.create')
                ->name('approval.templates.store');
            Route::put('/templates/update/{id}', [MasterApprovalController::class, 'updateTemplate'])
                ->middleware('permission:approval.edit')
                ->name('approval.templates.update');
            Route::delete('/templates/delete/{id}', [MasterApprovalController::class, 'deleteTemplate'])
                ->middleware('permission:approval.delete')
                ->name('approval.templates.delete');

            // ========== NEW: Flow Details Management API ==========
            Route::get('/flow-details/data/{templateId}', [MasterApprovalController::class, 'getFlowDetails'])
                ->name('approval.flowdetails.data');
            Route::post('/flow-details/store', [MasterApprovalController::class, 'storeFlowDetail'])
                ->middleware('permission:approval.create')
                ->name('approval.flowdetails.store');
            Route::put('/flow-details/update/{id}', [MasterApprovalController::class, 'updateFlowDetail'])
                ->middleware('permission:approval.edit')
                ->name('approval.flowdetails.update');
            Route::delete('/flow-details/delete/{id}', [MasterApprovalController::class, 'deleteFlowDetail'])
                ->middleware('permission:approval.delete')
                ->name('approval.flowdetails.delete');

            // ========== NEW: Helper - Employments ==========
            Route::get('/employments/data', [MasterApprovalController::class, 'getEmployments'])
                ->name('approval.employments.data');

            // ========== NEW: Uppline Configs Management API ==========
            Route::get('/uppline-configs/data/{templateId}', [MasterApprovalController::class, 'getUpplineConfigs'])
                ->name('approval.upplineconfigs.data');
            Route::post('/uppline-configs/store', [MasterApprovalController::class, 'storeUpplineConfig'])
                ->middleware('permission:approval.create')
                ->name('approval.upplineconfigs.store');
            Route::put('/uppline-configs/update/{id}', [MasterApprovalController::class, 'updateUpplineConfig'])
                ->middleware('permission:approval.edit')
                ->name('approval.upplineconfigs.update');
            Route::delete('/uppline-configs/delete/{id}', [MasterApprovalController::class, 'deleteUpplineConfig'])
                ->middleware('permission:approval.delete')
                ->name('approval.upplineconfigs.delete');

            // ========== NEW: Helper - Divisions & Job Levels ==========
            Route::get('/divisions/data', [MasterApprovalController::class, 'getDivisions'])
                ->name('approval.divisions.data');
            Route::get('/joblevels/data', [MasterApprovalController::class, 'getJobLevels'])
                ->name('approval.joblevels.data');
        });

    /* ========================
        WORKPLAN BUDGET ITEM APPROVAL
    ======================== */
    Route::prefix('workplan-budget-item-approval')
        ->middleware('auth')
        ->group(function () {
            Route::post('/{id}/submit', [WorkplanBudgetItemMasterApprovalController::class, 'submitForApproval'])
                ->name('wbi.approval.submit');
            Route::post('/detail/{detailId}/approve', [WorkplanBudgetItemMasterApprovalController::class, 'approve'])
                ->name('wbi.approval.approve');
            Route::post('/detail/{detailId}/reject', [WorkplanBudgetItemMasterApprovalController::class, 'reject'])
                ->name('wbi.approval.reject');
            Route::get('/{id}/status', [WorkplanBudgetItemMasterApprovalController::class, 'getApprovalStatus'])
                ->name('wbi.approval.status');
            Route::get('/pending', [WorkplanBudgetItemMasterApprovalController::class, 'myPendingApprovals'])
                ->name('wbi.approval.pending');
            Route::post('/bulk-process', [WorkplanBudgetItemMasterApprovalController::class, 'bulkProcess'])
                ->name('wbi.approval.bulkProcess');
            Route::post('/{id}/cancel', [WorkplanBudgetItemMasterApprovalController::class, 'cancel'])
                ->name('wbi.approval.cancel');
        });

    /* ========================
        BUDGET VERIFICATION
    ======================== */
    Route::prefix('budget-verification')
        ->middleware('auth')
        ->group(function () {
            // Dashboard for verifiers
            Route::get('/', [VerificationBudgetController::class, 'index'])
                ->name('verification.budget.index');

            // Get pending verifications for current user
            Route::get('/pending', [VerificationBudgetController::class, 'myPendingVerifications'])
                ->name('verification.budget.pending');

            // Submit item for verification (from budget-user)
            Route::post('/{itemId}/submit', [VerificationBudgetController::class, 'submitForVerification'])
                ->name('verification.budget.submit');

            // Verify item (approve and set fix price)
            Route::post('/{itemId}/verify', [VerificationBudgetController::class, 'verify'])
                ->name('verification.budget.verify');

            // Reject verification
            Route::post('/{itemId}/reject', [VerificationBudgetController::class, 'reject'])
                ->name('verification.budget.reject');

            // Bulk verification actions
            Route::post('/bulk-verify', [VerificationBudgetController::class, 'bulkVerify'])
                ->name('verification.budget.bulkVerify');
            Route::post('/bulk-reject', [VerificationBudgetController::class, 'bulkReject'])
                ->name('verification.budget.bulkReject');
            Route::post('/import-csv', [VerificationBudgetController::class, 'importCsv'])
                ->name('verification.budget.importCsv');

            // Get verification status for an item
            Route::get('/{itemId}/status', [VerificationBudgetController::class, 'getStatus'])
                ->name('verification.budget.status');

            // Check if current user can verify
            Route::get('/{itemId}/can-verify', [VerificationBudgetController::class, 'canVerify'])
                ->name('verification.budget.canVerify');
        });

    /* ========================
        SETTINGS
    ======================== */
    Route::prefix('master-data')
        ->middleware('permission:setting.master.view')
        ->group(function () {
            Route::get('/options', [MasterController::class, 'options'])
                ->name('master.options');
            Route::get('/organization', [MasterController::class, 'organization'])
                ->name('master.organization');
            Route::get('/', [MasterController::class, 'index'])
                ->name('master');
        });

    Route::prefix('users')
        ->middleware('permission:setting.users.view')
        ->group(function () {
            Route::get('/', [UsersController::class, 'index'])
                ->name('users.index');
        });

    Route::middleware('permission:setting.history.view')
        ->get('/history', [MasterController::class, 'history'])
        ->name('history');
    Route::middleware('permission:setting.code.view')
        ->get('/code', [SettingCodeController::class, 'index'])
        ->name('code.index');

    Route::prefix('stock-code')->middleware('permission:setting.code.view')->group(function () {
        Route::get('/data', [SettingCodeController::class, 'getStockCodeData'])->name('stock-code.data');
        Route::post('/', [SettingCodeController::class, 'storeStockCode'])->name('stock-code.store');
        Route::get('/{id}/edit', [SettingCodeController::class, 'editStockCode'])->name('stock-code.edit');
        Route::put('/{id}', [SettingCodeController::class, 'updateStockCode'])->name('stock-code.update');
        Route::delete('/{id}', [SettingCodeController::class, 'destroyStockCode'])->name('stock-code.destroy');
    });

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
        LPJ APPROVER MASTER
    ======================== */
    Route::prefix('lpj-approver')
        ->middleware('permission:setting.master.view')
        ->group(function () {
            Route::get('/', [LpjApprovalMasterController::class, 'index'])
                ->name('lpjApprovalMaster.index');
            Route::get('/data', [LpjApprovalMasterController::class, 'getData'])
                ->name('lpjApprovalMaster.data');
            Route::post('/', [LpjApprovalMasterController::class, 'store'])
                ->name('lpjApprovalMaster.store');
            Route::put('/{id}', [LpjApprovalMasterController::class, 'update'])
                ->name('lpjApprovalMaster.update');
            Route::post('/{id}/toggle', [LpjApprovalMasterController::class, 'toggleActive'])
                ->name('lpjApprovalMaster.toggleActive');
            Route::delete('/{id}', [LpjApprovalMasterController::class, 'destroy'])
                ->name('lpjApprovalMaster.destroy');
            Route::get('/available-employees', [LpjApprovalMasterController::class, 'getAvailableEmployees'])
                ->name('lpjApprovalMaster.availableEmployees');
        });

    Route::prefix('setting-price-verificator')
        ->middleware('permission:setting.price.view')
        ->group(function () {

            Route::get('/', [SettingPriceController::class, 'index'])
                ->name('settingPriceVerificator.index');

            // Verificator CRUD
            Route::post('/store-verificator', [SettingPriceController::class, 'storeVerificator'])
                ->name('settingPriceVerificator.storeVerificator');
            Route::put('/verificator/{id}', [SettingPriceController::class, 'updateVerificator'])
                ->name('settingPriceVerificator.updateVerificator');
            Route::delete('/verificator/{id}', [SettingPriceController::class, 'deleteVerificator'])
                ->name('settingPriceVerificator.deleteVerificator');

            // Code CRUD
            Route::post('/assign-code', [SettingPriceController::class, 'assignCode'])
                ->name('settingPriceVerificator.assignCode');
            Route::put('/code/{id}', [SettingPriceController::class, 'updateCode'])
                ->name('settingPriceVerificator.updateCode');
            Route::delete('/code/{id}', [SettingPriceController::class, 'deleteCode'])
                ->name('settingPriceVerificator.deleteCode');

            // User CRUD
            Route::post('/assign-user', [SettingPriceController::class, 'assignUser'])
                ->name('settingPriceVerificator.assignUser');
            Route::delete('/user/{id}', [SettingPriceController::class, 'deleteUser'])
                ->name('settingPriceVerificator.deleteUser');
        });

    /* ========================
        AUTHORIZATION
    ======================== */
    Route::prefix('authorization')
        ->middleware('permission:authorization.view|setting.users.view')
        ->group(function () {

            Route::get('/roles', [AuthorizationController::class, 'roles'])->name('auth.roles');
            Route::post('/roles/store', [AuthorizationController::class, 'roleStore'])->name('auth.roles.store');
            Route::post('/roles/update/{id}', [AuthorizationController::class, 'roleUpdate'])->name('auth.roles.update');
            Route::delete('/roles/delete/{id}', [AuthorizationController::class, 'roleDelete'])->name('auth.roles.delete');

            Route::get('/permissions', [AuthorizationController::class, 'permissions'])->name('auth.permissions');
            Route::post('/permissions/store', [AuthorizationController::class, 'permissionStore'])->name('authorization.permissions.create');
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

    /* ========================
        MASTER APPROVAL
    ======================== */
    Route::prefix('approval')->group(function () {
        // Main page
        Route::get('/', [MasterApprovalController::class, 'index'])->name('approval.index');

        // Modules
        Route::get('/modules/data', [MasterApprovalController::class, 'getModules'])->name('approval.modules.data');
        Route::get('/modules/tables', [MasterApprovalController::class, 'getAvailableTables'])->name('approval.modules.tables');
        Route::post('/modules/store', [MasterApprovalController::class, 'storeModule'])->name('approval.modules.store');
        Route::post('/modules/update/{id}', [MasterApprovalController::class, 'updateModule'])->name('approval.modules.update');
        Route::post('/modules/delete/{id}', [MasterApprovalController::class, 'deleteModule'])->name('approval.modules.delete');

        // Templates
        Route::get('/templates/data', [MasterApprovalController::class, 'getTemplates'])->name('approval.templates.data');
        Route::get('/templates/modules', [MasterApprovalController::class, 'getModulesForDropdown'])->name('approval.templates.modules');
        Route::get('/templates/with-flow-details', [MasterApprovalController::class, 'getAllTemplatesWithFlowDetails'])->name('approval.templates.withflowdetails');
        Route::post('/templates/store', [MasterApprovalController::class, 'storeTemplate'])->name('approval.templates.store');
        Route::post('/templates/update/{id}', [MasterApprovalController::class, 'updateTemplate'])->name('approval.templates.update');
        Route::post('/templates/delete/{id}', [MasterApprovalController::class, 'deleteTemplate'])->name('approval.templates.delete');

        // Flow Details
        Route::get('/flow-details/data/{templateId}', [MasterApprovalController::class, 'getFlowDetails'])->name('approval.flowdetails.data');
        Route::post('/flow-details/store', [MasterApprovalController::class, 'storeFlowDetail'])->name('approval.flowdetails.store');
        Route::post('/flow-details/update/{id}', [MasterApprovalController::class, 'updateFlowDetail'])->name('approval.flowdetails.update');
        Route::post('/flow-details/delete/{id}', [MasterApprovalController::class, 'deleteFlowDetail'])->name('approval.flowdetails.delete');

        // Uppline Configs
        Route::get('/uppline-configs/data/{templateId}', [MasterApprovalController::class, 'getUpplineConfigs'])->name('approval.upplineconfigs.data');
        Route::post('/uppline-configs/store', [MasterApprovalController::class, 'storeUpplineConfig'])->name('approval.upplineconfigs.store');
        Route::post('/uppline-configs/update/{id}', [MasterApprovalController::class, 'updateUpplineConfig'])->name('approval.upplineconfigs.update');
        Route::post('/uppline-configs/delete/{id}', [MasterApprovalController::class, 'deleteUpplineConfig'])->name('approval.upplineconfigs.delete');

        // Helpers (Employment, Divisions, Job Levels)
        Route::get('/employments/data', [MasterApprovalController::class, 'getEmployments'])->name('approval.employments.data');
        Route::get('/divisions/data', [MasterApprovalController::class, 'getDivisions'])->name('approval.divisions.data');
        Route::get('/job-levels/data', [MasterApprovalController::class, 'getJobLevels'])->name('approval.joblevels.data');
    });
});
