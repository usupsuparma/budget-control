<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\WorkplanBudgetItem;
use App\Observers\EmployeeObserver;
use App\Observers\WorkplanBudgetItemObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        // Register Observers
        Employee::observe(EmployeeObserver::class);
        WorkplanBudgetItem::observe(WorkplanBudgetItemObserver::class);
    }
}
