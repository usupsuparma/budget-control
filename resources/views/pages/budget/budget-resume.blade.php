@extends('layouts.master')

@section('title', 'Budget Control | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Budget Control')
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

        .summary-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 14px;
            background: #ffffff;
            min-height: 92px;
        }

        .summary-card .label {
            color: #6c757d;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 20px;
            font-weight: 700;
            margin-top: 6px;
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
                                        @foreach ($years as $availableYear)
                                            <option value="{{ $availableYear }}" {{ (int) $availableYear === (int) $year ? 'selected' : '' }}>
                                                {{ $availableYear }}</option>
                                        @endforeach
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
                                    @php
                                        $selectedBudgetCodeValue = $budgetCode && $budgetCode !== 'all' ? (string) $budgetCode : '';
                                        $selectedBudgetCodeLabel =
                                            $selectedBudgetCode['text'] ?? ($selectedBudgetCodeValue ?: 'All Budget Codes');
                                    @endphp
                                    <select name="budget_code" class="form-select form-select-sm" id="budget-code-filter">
                                        <option value="all" {{ $selectedBudgetCodeValue === '' ? 'selected' : '' }}>All Budget Codes</option>
                                        @if ($selectedBudgetCodeValue !== '')
                                            <option value="{{ $selectedBudgetCodeValue }}" selected>
                                                {{ $selectedBudgetCodeLabel }}
                                            </option>
                                        @endif
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

                        <!-- Summary Section -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <div class="label">Total Anggaran</div>
                                    <div class="value text-primary">{{ number_format($summary['total_budget'] ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <div class="label">Sudah Digunakan</div>
                                    <div class="value text-danger">{{ number_format($summary['total_realization'] ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <div class="label">Proses Pengajuan</div>
                                    <div class="value text-warning">{{ number_format($summary['total_submission'] ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <div class="label">Sisa Saldo</div>
                                    <div class="value text-success">{{ number_format($summary['total_balance'] ?? 0, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Table Section -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm budget-table mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="text-center" style="min-width: 120px;">DIVISION</th>
                                        <th rowspan="2" class="text-center" style="min-width: 120px;">CATEGORY</th>
                                        <th rowspan="2" class="text-center" style="min-width: 100px;">BUDGET CODE</th>
                                        <th rowspan="2" class="text-center" style="min-width: 200px;">BUDGET NAME</th>
                                        <th colspan="4" class="text-center">BUDGET TOTAL</th>
                                        <th colspan="4" class="text-center month-header">JAN</th>
                                        <th colspan="4" class="text-center month-header">FEB</th>
                                        <th colspan="4" class="text-center month-header">MAR</th>
                                        <th colspan="4" class="text-center month-header">APR</th>
                                        <th colspan="4" class="text-center month-header">MAY</th>
                                        <th colspan="4" class="text-center month-header">JUN</th>
                                        <th colspan="4" class="text-center month-header">JUL</th>
                                        <th colspan="4" class="text-center month-header">AUG</th>
                                        <th colspan="4" class="text-center month-header">SEP</th>
                                        <th colspan="4" class="text-center month-header">OCT</th>
                                        <th colspan="4" class="text-center month-header">NOV</th>
                                        <th colspan="4" class="text-center month-header">DEC</th>
                                    </tr>
                                    <tr>
                                        <!-- BUDGET TOTAL -->
                                        <th class="text-center">AMOUNT</th>
                                        <th class="text-center">REALIZATION</th>
                                        <th class="text-center">SUBMISSION</th>
                                        <th class="text-center">BALANCE</th>

                                        <!-- Repeat for each month -->
                                        @foreach (['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $month)
                                            <th class="text-center">BUDGET</th>
                                            <th class="text-center">REALIZATION</th>
                                            <th class="text-center">SUBMISSION</th>
                                            <th class="text-center">BALANCE</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($budgetData as $divisionName => $items)
                                        @php
                                            $divisionTotalBudget = collect($items)->sum('total');
                                            $divisionTotalRealization = collect($items)->sum('realization');
                                            $divisionTotalSubmission = collect($items)->sum('total_submission');
                                            $divisionTotalBalance = collect($items)->sum('balance');
                                        @endphp

                                        <!-- Division Header Row -->
                                        <tr class="division-header">
                                            <td rowspan="{{ count($items) + 1 }}"><strong>{{ $divisionName }}</strong>
                                            </td>
                                            <td colspan="3"><strong>TOTAL {{ strtoupper($divisionName) }}</strong></td>
                                            <td class="text-end">
                                                <strong>{{ number_format($divisionTotalBudget, 2) }}</strong>
                                            </td>
                                            <td class="text-end"><strong>{{ number_format($divisionTotalRealization, 2) }}</strong></td>
                                            <td class="text-end">
                                                <strong>{{ number_format($divisionTotalSubmission, 2) }}</strong>
                                            </td>
                                            <td class="text-end">
                                                <strong>{{ number_format($divisionTotalBalance, 2) }}</strong>
                                            </td>
                                            @foreach (['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $month)
                                                @php
                                                    $monthBudget = collect($items)->sum("months.$month.budget");
                                                    $monthRealization = collect($items)->sum("months.$month.realization");
                                                    $monthSubmission = collect($items)->sum("months.$month.submission");
                                                    $monthBalance = collect($items)->sum("months.$month.balance");
                                                @endphp
                                                <td class="text-end">{{ number_format($monthBudget, 2) }}</td>
                                                <td class="text-end">{{ number_format($monthRealization, 2) }}</td>
                                                <td class="text-end">{{ number_format($monthSubmission, 2) }}</td>
                                                <td class="text-end">{{ number_format($monthBalance, 2) }}</td>
                                            @endforeach
                                        </tr>

                                        <!-- Budget Items -->
                                        @foreach ($items as $item)
                                            <tr>
                                                <td>{{ $item['category_name'] }}</td>
                                                <td>{{ $item['budget_code'] }}</td>
                                                <td>{{ $item['budget_name'] }}</td>
                                                <td class="text-end">{{ number_format($item['total'], 2) }}</td>
                                                <td class="text-end">{{ number_format($item['realization'], 2) }}</td>
                                                <td class="text-end">{{ number_format($item['total_submission'], 2) }}</td>
                                                <td class="text-end">{{ number_format($item['balance'], 2) }}</td>

                                                @foreach (['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $month)
                                                    <td class="text-end">{{ number_format($item['months'][$month]['budget'], 2) }}
                                                    </td>
                                                    <td class="text-end">{{ number_format($item['months'][$month]['realization'], 2) }}</td>
                                                    <td class="text-end">{{ number_format($item['months'][$month]['submission'], 2) }}
                                                    </td>
                                                    <td class="text-end">{{ number_format($item['months'][$month]['balance'], 2) }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="56" class="text-center py-4">
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
        window.budgetResumeRoutes = {
            budgetCodeSearch: "{{ route('budget-resume.budget-codes.search') }}",
            budgetCodeByCode: "{{ route('budget-resume.budget-codes.by-code') }}"
        };

        document.addEventListener('DOMContentLoaded', function() {
            const selects = ['#year-filter', '#category-filter', '#division-filter'];
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

            initializeBudgetCodeFilter();
        });

        function initializeBudgetCodeFilter() {
            const select = document.querySelector('#budget-code-filter');

            if (!select) {
                return;
            }

            const choices = new Choices(select, {
                searchEnabled: true,
                searchChoices: false,
                searchFloor: 0,
                searchResultLimit: 10,
                searchPlaceholderValue: 'Search budget code...',
                noResultsText: 'No budget codes found.',
                noChoicesText: 'Open or type to load budget codes.',
                itemSelectText: '',
                shouldSort: false
            });

            const pageSize = 10;
            let currentQuery = '';
            let currentPage = 1;
            let isLoading = false;
            let hasMore = true;
            let scrollBound = false;
            let searchTimer = null;
            let loadedValues = new Set();

            function getSelectedChoice() {
                const selectedOption = Array.from(select.options).find(option => option.selected);

                if (!selectedOption || selectedOption.value === '' || selectedOption.value === 'all') {
                    return null;
                }

                return {
                    value: selectedOption.value,
                    label: selectedOption.textContent.trim(),
                    selected: true
                };
            }

            function makeBudgetCodeChoice(item) {
                const code = item.budget_code || item.value || '';
                const label = item.text || [code, item.name].filter(Boolean).join(' - ');

                return {
                    value: code,
                    label: label || code
                };
            }

            function addUniqueChoice(collection, choice) {
                if (!choice.value || loadedValues.has(choice.value)) {
                    return;
                }

                loadedValues.add(choice.value);
                collection.push(choice);
            }

            function setBudgetCodeChoices(results, replace) {
                const options = [];
                const selectedChoice = getSelectedChoice();

                if (replace) {
                    loadedValues = new Set();
                    addUniqueChoice(options, {
                        value: 'all',
                        label: 'All Budget Codes',
                        selected: !selectedChoice
                    });

                    if (selectedChoice) {
                        addUniqueChoice(options, selectedChoice);
                    }
                }

                results.forEach(item => addUniqueChoice(options, makeBudgetCodeChoice(item)));
                choices.setChoices(options, 'value', 'label', replace);

                if (selectedChoice) {
                    choices.setChoiceByValue(selectedChoice.value);
                } else {
                    choices.setChoiceByValue('all');
                }
            }

            function fetchBudgetCodes(query, page, replace) {
                if (isLoading || (!replace && !hasMore)) {
                    return;
                }

                isLoading = true;

                $.ajax({
                    url: window.budgetResumeRoutes.budgetCodeSearch,
                    method: 'GET',
                    data: {
                        q: query,
                        limit: pageSize,
                        page: page
                    },
                    success: function(response) {
                        isLoading = false;

                        if (!response.success) {
                            hasMore = false;
                            return;
                        }

                        currentPage = response.page || page;
                        hasMore = Boolean(response.has_more);
                        setBudgetCodeChoices(response.data || [], replace);
                    },
                    error: function() {
                        isLoading = false;
                        hasMore = false;
                    }
                });
            }

            function bindInfiniteScroll() {
                if (scrollBound) {
                    return;
                }

                const listElement = choices.choiceList && choices.choiceList.element;

                if (!listElement) {
                    return;
                }

                scrollBound = true;
                listElement.addEventListener('scroll', function() {
                    const threshold = 60;
                    const reachedBottom = listElement.scrollTop + listElement.clientHeight >=
                        listElement.scrollHeight - threshold;

                    if (reachedBottom && !isLoading && hasMore) {
                        fetchBudgetCodes(currentQuery, currentPage + 1, false);
                    }
                });
            }

            function loadFirstPage() {
                currentPage = 1;
                hasMore = true;
                fetchBudgetCodes(currentQuery, currentPage, true);
                bindInfiniteScroll();
            }

            select.addEventListener('showDropdown', loadFirstPage);
            select.addEventListener('search', function(event) {
                clearTimeout(searchTimer);

                searchTimer = setTimeout(function() {
                    currentQuery = event.detail.value || '';
                    currentPage = 1;
                    hasMore = true;
                    fetchBudgetCodes(currentQuery, currentPage, true);
                }, 300);
            });

            setTimeout(function() {
                const inputElement = choices.input && choices.input.element;
                const containerElement = choices.containerOuter && choices.containerOuter.element;

                if (containerElement) {
                    containerElement.addEventListener('click', function() {
                        if (choices.isOpen) {
                            loadFirstPage();
                        }
                    });
                }

                if (inputElement) {
                    inputElement.addEventListener('input', function() {
                        if (this.value === '') {
                            clearTimeout(searchTimer);
                            currentQuery = '';
                            currentPage = 1;
                            hasMore = true;
                            fetchBudgetCodes('', currentPage, true);
                        }
                    });
                }
            }, 0);
        }

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
