<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TransactionService\TransactionService;
use App\Services\TransactionService\TransactionServiceImpl;

use App\Services\ApprovalService\ApprovalService;
use App\Services\ApprovalService\ApprovalServiceImpl;
use App\Services\VerificationBudgetService\VerificationBudgetService;
use App\Services\VerificationBudgetService\VerificationBudgetServiceImpl;
use App\Services\ApprovalTransactionService\ApprovalTransactionService;
use App\Services\ApprovalTransactionService\ApprovalTransactionServiceImpl;
use App\Services\LpjService\LpjService;
use App\Services\LpjService\LpjServiceImpl;
use App\Services\BudgetLedgerService\BudgetLedgerService;
use App\Services\BudgetLedgerService\BudgetLedgerServiceImpl;

use App\Services\LogService\LogService;
use App\Services\LogService\LogServiceImpl;
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
$this->app->bind(LpjService::class, LpjServiceImpl::class);
        $this->app->bind(ApprovalTransactionService::class, ApprovalTransactionServiceImpl::class);
        $this->app->bind(VerificationBudgetService::class, VerificationBudgetServiceImpl::class);
        $this->app->bind(ApprovalService::class, ApprovalServiceImpl::class);
        $this->app->bind(TransactionService::class, TransactionServiceImpl::class);
        $this->app->bind(BudgetLedgerService::class, BudgetLedgerServiceImpl::class);
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