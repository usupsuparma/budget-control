@extends('layouts.master')

@section('title', 'Budget Resume | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Budget Resume')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <style>
        .budget-table {
            font-size: 11px;
        }

        .budget-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            white-space: nowrap;
            vertical-align: middle;
            padding: 8px 4px;
        }

        .budget-table td {
            vertical-align: middle;
            padding: 6px 4px;
        }

        .category-header {
            background-color: #d1e7dd;
            font-weight: bold;
            font-size: 12px;
        }

        .division-header {
            background-color: #e9ecef;
            font-weight: 600;
            font-size: 11px;
        }

        .text-end {
            text-align: right;
        }

        .month-col {
            min-width: 180px;
        }

        .month-header {
            background-color: #cfe2ff;
        }

        .filter-card {
            background-color: #f8f9fa;
        }
    </style>
@endsection
@section('content')

    <!-- Begin page -->
    <div id="layout-wrapper">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ $title }}</h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <form method="GET" action="{{ route('budget-resume.index') }}" id="filterForm">
                            <div class="row g-3 mb-3 filter-card p-3 rounded">
                                <div class="col-md-2">
                                    <label class="form-label">Year</label>

                                    <select name="year" class="form-select form-select-sm" id="year-filter">
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                                {{ $year }}</option>
                                        @endforeach
                                        <option value="2025" {{ $year == 2025 ? 'selected' : '' }}>2025</option>
                                        <option value="2024" {{ $year == 2024 ? 'selected' : '' }}>2024</option>
                                        <option value="2023" {{ $year == 2023 ? 'selected' : '' }}>2023</option>
                                        <option value="2022" {{ $year == 2022 ? 'selected' : '' }}>2022</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select form-select-sm" id="category-filter">
                                        <option value="all">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ $categoryId == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Division</label>
                                    <select name="division_id" class="form-select form-select-sm" id="division-filter">
                                        <option value="all">All Divisions</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}"
                                                {{ $divisionId == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Budget Code</label>
                                    <select name="budget_code" class="form-select form-select-sm" id="budget-code-filter">
                                        <option value="all">All Budget Codes</option>
                                        @foreach ($budgetCodes as $code)
                                            <option value="{{ $code->stock_code }}"
                                                {{ $budgetCode == $code->stock_code ? 'selected' : '' }}>
                                                {{ $code->stock_code }} - {{ $code->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-funnel me-1"></i>Filter
                                    </button>
                                    <a href="{{ route('budget-resume.index') }}" class="btn btn-secondary btn-sm">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm" onclick="exportToExcel()">
                                        <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Table Section -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm budget-table mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="text-center" style="min-width: 120px;">DIVISION</th>
                                        <th rowspan="2" class="text-center" style="min-width: 120px;">CATEGORY</th>
                                        <th rowspan="2" class="text-center" style="min-width: 100px;">BUDGET CODE</th>
                                        <th rowspan="2" class="text-center" style="min-width: 200px;">BUDGET NAME</th>
                                        <th colspan="3" class="text-center">BUDGET TOTAL</th>
                                        <th colspan="3" class="text-center month-header">JAN</th>
                                        <th colspan="3" class="text-center month-header">FEB</th>
                                        <th colspan="3" class="text-center month-header">MAR</th>
                                        <th colspan="3" class="text-center month-header">APR</th>
                                        <th colspan="3" class="text-center month-header">MAY</th>
                                        <th colspan="3" class="text-center month-header">JUN</th>
                                        <th colspan="3" class="text-center month-header">JUL</th>
                                        <th colspan="3" class="text-center month-header">AUG</th>
                                        <th colspan="3" class="text-center month-header">SEP</th>
                                        <th colspan="3" class="text-center month-header">OCT</th>
                                        <th colspan="3" class="text-center month-header">NOV</th>
                                        <th colspan="3" class="text-center month-header">DEC</th>
                                    </tr>
                                    <tr>
                                        <!-- BUDGET TOTAL -->
                                        <th class="text-center">AMOUNT</th>
                                        <th class="text-center">REALIZATION</th>
                                        <th class="text-center">BALANCE</th>

                                        <!-- Repeat for each month -->
                                        @foreach (['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $month)
                                            <th class="text-center">BUDGET</th>
                                            <th class="text-center">REALIZATION</th>
                                            <th class="text-center">BALANCE</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($budgetData as $divisionName => $items)
                                        @php
                                            $divisionTotalBudget = collect($items)->sum('total');
                                        @endphp

                                        <!-- Division Header Row -->
                                        <tr class="division-header">
                                            <td rowspan="{{ count($items) + 1 }}"><strong>{{ $divisionName }}</strong>
                                            </td>
                                            <td colspan="3"><strong>TOTAL {{ strtoupper($divisionName) }}</strong></td>
                                            <td class="text-end">
                                                <strong>{{ number_format($divisionTotalBudget, 2) }}</strong></td>
                                            <td class="text-end"><strong>0.00</strong></td>
                                            <td class="text-end">
                                                <strong>{{ number_format($divisionTotalBudget, 2) }}</strong></td>
                                            @foreach (['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $month)
                                                @php
                                                    $monthBudget = collect($items)->sum("months.$month");
                                                @endphp
                                                <td class="text-end">{{ number_format($monthBudget, 0) }}</td>
                                                <td class="text-end">0</td>
                                                <td class="text-end">{{ number_format($monthBudget, 0) }}</td>
                                            @endforeach
                                        </tr>

                                        <!-- Budget Items -->
                                        @foreach ($items as $item)
                                            <tr>
                                                <td>{{ $item['category_name'] }}</td>
                                                <td>{{ $item['budget_code'] }}</td>
                                                <td>{{ $item['budget_name'] }}</td>
                                                <td class="text-end">{{ number_format($item['total'], 2) }}</td>
                                                <td class="text-end">0.00</td>
                                                <td class="text-end">{{ number_format($item['total'], 2) }}</td>

                                                @foreach (['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $month)
                                                    <td class="text-end">{{ number_format($item['months'][$month], 0) }}
                                                    </td>
                                                    <td class="text-end">0</td>
                                                    <td class="text-end">{{ number_format($item['months'][$month], 0) }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="43" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                                    <p class="mt-2">No budget data available for the selected filters.
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <script>
        // Initialize Choices.js for all select elements
        document.addEventListener('DOMContentLoaded', function() {
            const selects = ['#year-filter', '#category-filter', '#division-filter', '#budget-code-filter'];
            selects.forEach(selector => {
                const element = document.querySelector(selector);
                if (element) {
                    new Choices(element, {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false
                    });
                }
            });
        });

        function exportToExcel() {
            // Get current filter values
            const year = document.querySelector('[name="year"]').value;
            const categoryId = document.querySelector('[name="category_id"]').value;
            const divisionId = document.querySelector('[name="division_id"]').value;
            const budgetCode = document.querySelector('[name="budget_code"]').value;

            // Build export URL with filters
            let exportUrl = '{{ route('budget-resume.index') }}?export=excel';
            exportUrl += '&year=' + year;
            if (categoryId) exportUrl += '&category_id=' + categoryId;
            if (divisionId) exportUrl += '&division_id=' + divisionId;
            if (budgetCode) exportUrl += '&budget_code=' + budgetCode;

            // For now, show alert. You can implement actual Excel export later
            alert('Export to Excel functionality will be implemented. URL: ' + exportUrl);

            // TODO: Implement actual Excel export
            // window.location.href = exportUrl;
        }
    </script>
@endsection
