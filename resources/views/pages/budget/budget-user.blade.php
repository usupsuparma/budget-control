@extends('layouts.master')

@section('title', 'Budget User | Budget Control')
@section('title-sub', 'Budget User')
@section('pagetitle', 'Budget User - Manage Budget Items')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <style>
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .workplan-selection {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px dashed #dee2e6;
            text-align: center;
        }

        .workplan-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .category-tabs .nav-link {
            font-size: 13px;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s;
            border: 1px solid transparent;
        }
        
        .category-tabs .nav-link:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        
        .category-tabs .nav-link.active {
            background-color: #0d6efd;
            color: white;
            font-weight: 600;
        }
        
        .child-tabs .nav-link {
            font-size: 12px;
            padding: 8px 12px;
            border-radius: 6px;
            margin-right: 5px;
            margin-bottom: 5px;
            border: 1px solid #dee2e6;
        }
        
        .child-tabs .nav-link.active {
            background-color: #495057;
            color: white;
            border-color: #495057;
        }
        
        .items-table {
            font-size: 11px;
            width: 100%;
        }
        
        .items-table th {
            background-color: #495057;
            color: white;
            padding: 10px 8px;
            text-align: center;
            font-weight: 600;
            white-space: nowrap;
            border: 1px solid #dee2e6;
        }
        
        .items-table td {
            padding: 8px 5px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .items-table input[type="text"],
        .items-table input[type="number"],
        .items-table select {
            width: 100%;
            padding: 4px 6px;
            font-size: 11px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        
        .items-table input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        
        .month-header {
            background-color: #6c757d !important;
            font-size: 10px;
        }
        
        .btn-action-item {
            padding: 6px 10px;
            font-size: 12px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            min-height: 32px;
        }
        
        .btn-action-item i {
            font-size: 14px;
        }
        
        .btn-action-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .action-column {
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
            min-width: 100px;
        }
        
        .status-badge {
            font-size: 10px;
            padding: 4px 8px;
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .loading-overlay.show {
            display: flex;
        }
        
        tr.new-row {
            background-color: #fff3cd;
        }
        
        tr.table-success {
            background-color: #d1e7dd;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .modal-xl {
            max-width: 1200px;
        }
        
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .workplan-card {
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
            margin-bottom: 15px;
        }

        .workplan-card:hover {
            border-color: #0d6efd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .workplan-card.selected {
            border-color: #198754;
            background-color: #d1e7dd;
        }

        .btn-save-item {
            background-color: #ff6b35;
            color: white;
            border: none;
        }

        .btn-save-item:hover {
            background-color: #ff5722;
            color: white;
        }
    </style>
@endsection

@section('content')
<div id="layout-wrapper">
    {{-- Filter Section --}}
    <div class="row">
        <div class="col-12">
            <div class="card filter-section">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Division</label>
                            <select class="form-select" id="divisionFilter">
                                <option value="">Select Division</option>
                                @foreach($divisions ?? [] as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Year</label>
                            <select class="form-select" id="yearFilter">
                                <option value="">Select Year</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary" id="loadBudgetBtn" disabled>
                                <i class="bi bi-table me-2"></i>Load Budget Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Info Section --}}
    <div class="row" id="dataInfoSection" style="display: none;">
        <div class="col-12">
            <div class="workplan-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Budget Items - <span id="selectedDivisionName"></span> (<span id="selectedYear"></span>)</h5>
                        <p class="mb-0">
                            <small><span id="totalWorkplans">0</span> Work Plans | <span id="totalItems">0</span> Budget Items</small>
                        </p>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-success" id="addDataBtn">
                            <i class="bi bi-plus-circle me-2"></i>Add Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Budget Items Section (Hidden by default) --}}
    <div class="row" id="budgetItemsSection" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered items-table" id="budgetItemsTable">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="text-center">Action</th>
                                    <th rowspan="2">Category</th>
                                    <th rowspan="2">Description</th>
                                    <th rowspan="2">Program ID</th>
                                    <th rowspan="2">Stock Code</th>
                                    <th rowspan="2">Budget Code</th>
                                    <th rowspan="2">Product Line</th>
                                    <th rowspan="2">Cost Center</th>
                                    <th rowspan="2">Beg Balance</th>
                                    <th rowspan="2">Supplier</th>
                                    <th rowspan="2">Cons Rate</th>
                                    <th rowspan="2">Unit</th>
                                    <th colspan="12" class="month-header text-center">Qty</th>
                                    <th rowspan="2">Total Des</th>
                                    <th rowspan="2">Price Estimation</th>
                                    <th rowspan="2">Price Estimation Description</th>
                                </tr>
                                <tr>
                                    <th class="month-header">Jan</th>
                                    <th class="month-header">Feb</th>
                                    <th class="month-header">Mar</th>
                                    <th class="month-header">Apr</th>
                                    <th class="month-header">Mei</th>
                                    <th class="month-header">Jun</th>
                                    <th class="month-header">Jul</th>
                                    <th class="month-header">Agu</th>
                                    <th class="month-header">Sep</th>
                                    <th class="month-header">Okt</th>
                                    <th class="month-header">Nov</th>
                                    <th class="month-header">Des</th>
                                </tr>
                            </thead>
                            <tbody id="budgetItemsTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Add/Edit Item --}}
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="itemModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Add Budget Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="itemForm">
                    <input type="hidden" id="itemId" name="item_id">
                    <input type="hidden" id="budgetCategoryId" name="budget_category_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            {{-- Left Column --}}
                            <div class="mb-3">
                                <label for="categoryType" class="form-label fw-bold">Type (Budget Category) <span class="text-danger">*</span></label>
                                <select class="form-select" id="categoryType" name="budget_category_id" required>
                                    <option value="">Select Budget Category...</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="programId" class="form-label fw-bold">Program ID <span class="text-danger">*</span></label>
                                <select class="form-select" id="programId" name="program_id" required>
                                    <option value="">Select Work Plan...</option>
                                </select>
                                <small class="text-muted">Choose a department or section work plan</small>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="description" name="description" required>
                            </div>

                            <div class="mb-3">
                                <label for="stockCode" class="form-label fw-bold">Stock Code</label>
                                <input type="text" class="form-control" id="stockCode" name="stock_code">
                            </div>

                            <div class="mb-3">
                                <label for="budgetCode" class="form-label fw-bold">Budget Code</label>
                                <select class="form-select" id="budgetCode" name="budget_code">
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="productLine" class="form-label fw-bold">Product Line</label>
                                <input type="text" class="form-control" id="productLine" name="product_line">
                            </div>

                            <div class="mb-3">
                                <label for="costCenter" class="form-label fw-bold">Cost Center</label>
                                <select class="form-select" id="costCenter" name="cost_center">
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="begBalance" class="form-label fw-bold">Beg Balance</label>
                                <input type="text" class="form-control" id="begBalance" name="beg_balance">
                            </div>

                            <div class="mb-3">
                                <label for="supplier" class="form-label fw-bold">Supplier</label>
                                <select class="form-select" id="supplier" name="supplier_name">
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="consRate" class="form-label fw-bold">Const Rate</label>
                                <input type="text" class="form-control" id="consRate" name="cons_rate">
                            </div>

                            <div class="mb-3">
                                <label for="unit" class="form-label fw-bold">Unit</label>
                                <select class="form-select" id="unit" name="unit">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            {{-- Right Column - Monthly Activities --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">January</label>
                                <input type="number" class="form-control monthly-activity" name="activity_jan" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">February</label>
                                <input type="number" class="form-control monthly-activity" name="activity_feb" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">March</label>
                                <input type="number" class="form-control monthly-activity" name="activity_mar" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">April</label>
                                <input type="number" class="form-control monthly-activity" name="activity_apr" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">May</label>
                                <input type="number" class="form-control monthly-activity" name="activity_may" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">June</label>
                                <input type="number" class="form-control monthly-activity" name="activity_jun" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">July</label>
                                <input type="number" class="form-control monthly-activity" name="activity_jul" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">August</label>
                                <input type="number" class="form-control monthly-activity" name="activity_aug" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">September</label>
                                <input type="number" class="form-control monthly-activity" name="activity_sep" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">October</label>
                                <input type="number" class="form-control monthly-activity" name="activity_oct" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">November</label>
                                <input type="number" class="form-control monthly-activity" name="activity_nov" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">December</label>
                                <input type="number" class="form-control monthly-activity" name="activity_dec" value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label for="total" class="form-label fw-bold">Total</label>
                                <input type="number" class="form-control bg-light" id="total" name="total" readonly value="0">
                            </div>

                            <div class="mb-3">
                                <label for="priceEstimation" class="form-label fw-bold">Price Estimation</label>
                                <input type="number" class="form-control" id="priceEstimation" name="price_estimation" step="0.01">
                            </div>

                            <div class="mb-3">
                                <label for="priceEstimationDescription" class="form-label fw-bold">Price Estimation Description</label>
                                <textarea class="form-control" id="priceEstimationDescription" name="price_estimation_description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-save-item" id="saveItemBtn">
                    Simpan
                </button>
                <button type="button" class="btn btn-secondary" id="resetItemBtn">
                    Reset
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div class="loading-overlay" id="loadingOverlay">
    <div class="text-center text-white">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading...</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('js')
<script type="module" src="{{ asset('assets/js/app.js') }}"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script>
    const CSRF_TOKEN = '{{ csrf_token() }}';
    
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const paramDivisionId = urlParams.get('division_id');
    const paramYear = urlParams.get('year');
    const paramWorkplanId = urlParams.get('workplan_id');
</script>
<script src="{{ asset('assets/js/budget-user.js') }}"></script>
@endsection
