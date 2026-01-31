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
class CustomServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
                        $this->app->bind(ApprovalTransactionService::class, ApprovalTransactionServiceImpl::class);
$this->app->bind(VerificationBudgetService::class, VerificationBudgetServiceImpl::class);
$this->app->bind(ApprovalService::class, ApprovalServiceImpl::class);
        $this->app->bind(TransactionService::class, TransactionServiceImpl::class);
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