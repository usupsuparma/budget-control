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
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\NotificationServiceImpl;

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
