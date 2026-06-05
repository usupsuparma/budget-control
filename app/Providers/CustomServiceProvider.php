<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\VerificationBudgetService\VerificationBudgetService;
use App\Services\VerificationBudgetService\VerificationBudgetServiceImpl;
use App\Services\ApprovalTransactionService\ApprovalTransactionService;
use App\Services\ApprovalTransactionService\ApprovalTransactionServiceImpl;
use App\Services\LpjService\LpjService;
use App\Services\LpjService\LpjServiceImpl;
use App\Services\BudgetLedgerService\BudgetLedgerService;
use App\Services\BudgetLedgerService\BudgetLedgerServiceImpl;
use App\Services\StockCodeService\StockCodeService;
use App\Services\StockCodeService\StockCodeServiceImpl;
use App\Services\BudgetUserService\BudgetUserService;
use App\Services\BudgetUserService\BudgetUserServiceImpl;
use App\Services\BudgetSubmissionApprovalService\BudgetSubmissionApprovalService;
use App\Services\BudgetSubmissionApprovalService\BudgetSubmissionApprovalServiceImpl;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\NotificationServiceImpl;
use App\Services\DashboardService\DashboardService;
use App\Services\DashboardService\DashboardServiceImpl;
use App\Services\MasterDataService\MasterDataService;
use App\Services\MasterDataService\MasterDataServiceImpl;
use App\Services\WorkplanImportService\WorkplanImportService;
use App\Services\WorkplanImportService\WorkplanImportServiceImpl;
use App\Services\PipIntegrationService\PipIntegrationService;
use App\Services\PipIntegrationService\PipIntegrationServiceImpl;
use App\Services\KPIDivisionService\KPIDivisionService;
use App\Services\KPIDivisionService\KPIDivisionServiceImpl;
use App\Services\KPIDepartmentService\KPIDepartmentService;
use App\Services\KPIDepartmentService\KPIDepartmentServiceImpl;
use App\Services\KPISectionService\KPISectionService;
use App\Services\KPISectionService\KPISectionServiceImpl;
use App\Services\UserRoleService\UserRoleService;
use App\Services\UserRoleService\UserRoleServiceImpl;

use App\Services\LogService\LogService;
use App\Services\LogService\LogServiceImpl;
use App\Services\SubmissionService\SubmissionService;
use App\Services\SubmissionService\SubmissionServiceImpl;

class CustomServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LogService::class, LogServiceImpl::class);
        $this->app->bind(SubmissionService::class, SubmissionServiceImpl::class);
        $this->app->bind(LpjService::class, LpjServiceImpl::class);
        $this->app->bind(ApprovalTransactionService::class, ApprovalTransactionServiceImpl::class);
        $this->app->bind(VerificationBudgetService::class, VerificationBudgetServiceImpl::class);
        $this->app->bind(BudgetLedgerService::class, BudgetLedgerServiceImpl::class);
        $this->app->bind(StockCodeService::class, StockCodeServiceImpl::class);
        $this->app->bind(BudgetUserService::class, BudgetUserServiceImpl::class);
        $this->app->bind(NotificationService::class, NotificationServiceImpl::class);
        $this->app->bind(DashboardService::class, DashboardServiceImpl::class);
        $this->app->bind(MasterDataService::class, MasterDataServiceImpl::class);
        $this->app->bind(WorkplanImportService::class, WorkplanImportServiceImpl::class);
        $this->app->bind(PipIntegrationService::class, PipIntegrationServiceImpl::class);
        $this->app->bind(KPIDivisionService::class, KPIDivisionServiceImpl::class);
        $this->app->bind(KPIDepartmentService::class, KPIDepartmentServiceImpl::class);
        $this->app->bind(KPISectionService::class, KPISectionServiceImpl::class);
        $this->app->bind(UserRoleService::class, UserRoleServiceImpl::class);
        $this->app->bind(\App\Services\EmployeeService\EmployeeService::class, \App\Services\EmployeeService\EmployeeServiceImpl::class);
        $this->app->bind(\App\Services\BudgetSubmissionService\BudgetSubmissionService::class, \App\Services\BudgetSubmissionService\BudgetSubmissionServiceImpl::class);
        $this->app->bind(
            BudgetSubmissionApprovalService::class,
            BudgetSubmissionApprovalServiceImpl::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
