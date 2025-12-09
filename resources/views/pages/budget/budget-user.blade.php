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
                            <button type="button" class="btn btn-primary" id="selectWorkplanBtn" disabled>
                                <i class="bi bi-search me-2"></i>Select Work Plan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Workplan Selection Placeholder --}}
    <div class="row" id="workplanPlaceholder">
        <div class="col-12">
            <div class="workplan-selection">
                <i class="bi bi-info-circle fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">Please select Division and Year, then click "Select Work Plan"</h5>
                <p class="text-muted mb-0">Choose a work plan to manage budget items</p>
            </div>
        </div>
    </div>

    {{-- Workplan Info (Hidden by default) --}}
    <div class="row" id="workplanInfoSection" style="display: none;">
        <div class="col-12">
            <div class="workplan-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-2"><i class="bi bi-clipboard-check me-2"></i>Selected Work Plan</h5>
                        <h4 class="mb-1" id="selectedWorkplanActivity">-</h4>
                        <p class="mb-0 opacity-75">
                            <span id="selectedWorkplanDetails">-</span>
                        </p>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-light btn-sm" id="changeWorkplanBtn">
                            <i class="bi bi-arrow-repeat me-1"></i>Change Work Plan
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
                    <h5 class="card-title mb-4">Budget Items</h5>
                    
                    {{-- Category Tabs --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="nav flex-column nav-pills category-tabs" id="parentCategoryTabs" role="tablist">
                                <!-- Parent categories will be loaded here -->
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div id="categoryContent">
                                <!-- Child categories and items will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Select Workplan --}}
<div class="modal fade" id="workplanModal" tabindex="-1" aria-labelledby="workplanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="workplanModalLabel">
                    <i class="bi bi-list-check me-2"></i>Select Work Plan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="workplanList">
                    <!-- Workplan cards will be loaded here -->
                </div>
                <div class="no-data" id="noWorkplanData" style="display: none;">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="mt-3">No work plans found for selected Division and Year</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmWorkplanBtn" disabled>
                    <i class="bi bi-check-circle me-2"></i>Confirm Selection
                </button>
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
                            <div class="mb-3">
                                <label class="form-label">Category Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="categoryType" name="category_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Routine">Routine</option>
                                    <option value="Carry Over">Carry Over</option>
                                    <option value="Turn Around">Turn Around</option>
                                    <option value="Multi Year">Multi Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Budget Code</label>
                                <select class="form-select" id="budgetCode" name="budget_code">
                                    <option value="">Select Budget Code</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Stock Code</label>
                                <input type="text" class="form-control" id="stockCode" name="stock_code">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Product Line</label>
                                <input type="text" class="form-control" id="productLine" name="product_line">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Cost Center</label>
                                <input type="text" class="form-control" id="costCenter" name="cost_center">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Beginning Balance</label>
                                <input type="text" class="form-control" id="begBalance" name="beg_balance">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Consumption Rate</label>
                                <input type="text" class="form-control" id="consRate" name="cons_rate">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Total</label>
                                <input type="number" class="form-control" id="total" name="total" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Price Estimation</label>
                                <input type="number" class="form-control" id="priceEstimation" name="price_estimation" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Price Estimation Description</label>
                                <input type="text" class="form-control" id="priceEstimationDescription" name="price_estimation_description">
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-3 mt-4">Monthly Activities</h6>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">January</label>
                                <input type="number" class="form-control form-control-sm" name="activity_jan" min="0" max="1000">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">February</label>
                                <input type="number" class="form-control form-control-sm" name="activity_feb" min="0" max="1000">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">March</label>
                                <input type="number" class="form-control form-control-sm" name="activity_mar" min="0" max="1000">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">April</label>
                                <input type="number" class="form-control form-control-sm" name="activity_apr" min="0" max="1000">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">May</label>
                                <input type="number" class="form-control form-control-sm" name="activity_may" min="0" max="1000">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">June</label>
                                <input type="number" class="form-control form-control-sm" name="activity_jun" min="0" max="1000">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">July</label>
                                <input type="number" class="form-control form-control-sm" name="activity_jul" min="0" max="1000">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">August</label>
                                <input type="number" class="form-control form-control-sm" name="activity_aug" min="0" max="1000">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">September</label>
                                <input type="number" class="form-control form-control-sm" name="activity_sep" min="0" max="1000">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">October</label>
                                <input type="number" class="form-control form-control-sm" name="activity_oct" min="0" max="1000">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">November</label>
                                <input type="number" class="form-control form-control-sm" name="activity_nov" min="0" max="1000">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">December</label>
                                <input type="number" class="form-control form-control-sm" name="activity_dec" min="0" max="1000">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveItemBtn">
                    <i class="bi bi-save me-2"></i>Save Item
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
</script>
<script src="{{ asset('assets/js/budget-user.js') }}"></script>
@endsection
