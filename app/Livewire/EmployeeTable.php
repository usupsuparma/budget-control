<?php

namespace App\Livewire;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\{PowerGridComponent, Column};
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;

final class EmployeeTable extends PowerGridComponent
{
    public string $tableName = 'employee_table';

    public function boot(): void
    {
        // 🩵 Paksa PowerGrid pakai Bootstrap 5
        config(['livewire-powergrid.framework' => 'bootstrap5']);
    }

    public function datasource(): Builder
    {
        return Employee::query();
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id'),
            Column::make('Email', 'email'),
            Column::make('First Name', 'first_name'),
            Column::make('Last Name', 'last_name'),
            Column::make('Status', 'status'),
        ];
    }

    public function setUp(): array
    {
        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()->showPerPage()->showRecordCount(),
        ];
    }
}
