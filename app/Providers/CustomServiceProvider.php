<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TransactionService\TransactionService;
use App\Services\TransactionService\TransactionServiceImpl;

use App\Services\ApprovalService\ApprovalService;
use App\Services\ApprovalService\ApprovalServiceImpl;
class CustomServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
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