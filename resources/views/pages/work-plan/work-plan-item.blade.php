@extends('layouts.master')

@section('title', 'Budget Items | Work Plan')
@section('title-sub', 'Budget Items')
@section('pagetitle', 'Work Plan Budget Items - ' . $workplan->activity)

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<style>
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
    
    .btn-action-item.btn-primary {
        background-color: #0d6efd;
        color: white;
    }
    
    .btn-action-item.btn-primary:hover {
        background-color: #0b5ed7;
    }
    
    .btn-action-item.btn-success {
        background-color: #198754;
        color: white;
    }
    
    .btn-action-item.btn-success:hover {
        background-color: #157347;
    }
    
    .btn-action-item.btn-danger {
        background-color: #dc3545;
        color: white;
    }
    
    .btn-action-item.btn-danger:hover {
        background-color: #bb2d3b;
    }
    
    .btn-action-item.btn-secondary {
        background-color: #6c757d;
        color: white;
    }
    
    .btn-action-item.btn-secondary:hover {
        background-color: #5c636a;
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
    
    .workplan-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
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
    
    .child-category-header {
        background: #e9ecef;
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .expand-btn-item {
        cursor: pointer;
        padding: 5px 12px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 11px;
        transition: all 0.3s;
    }
    
    .expand-btn-item:hover {
        background: #218838;
    }
    
    .expand-btn-item i {
        transition: transform 0.3s;
    }
    
    .expand-btn-item.collapsed i {
        transform: rotate(0deg);
    }
    
    .expand-btn-item:not(.collapsed) i {
        transform: rotate(90deg);
    }
    
    .collapse-section-item {
        display: none;
        animation: slideDown 0.3s ease-out;
    }
    
    .collapse-section-item.show {
        display: block;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Modal Styles */
    .modal-xl {
        max-width: 1200px;
    }
    
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    #itemForm .form-label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    #itemForm .form-control,
    #itemForm .form-select {
        font-size: 13px;
    }
    
    #itemForm .form-control-sm {
        font-size: 12px;
        padding: 6px 10px;
    }
    
    .modal-header.bg-primary {
        background-color: #0d6efd !important;
    }
</style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <!-- Workplan Info -->
            <div class="workplan-info">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Activity:</strong><br>
                        {{ $workplan->activity }}
                    </div>
                    <div class="col-md-2">
                        <strong>Year:</strong><br>
                        {{ $workplan->year }}
                    </div>
                    <div class="col-md-2">
                        <strong>Budget:</strong><br>
                        Rp {{ number_format($workplan->budget, 0, ',', '.') }}
                    </div>
                    <div class="col-md-2">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $workplan->status == 'approved' ? 'success' : ($workplan->status == 'pending' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($workplan->status) }}
                        </span>
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="{{ route('workplan.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Back to Work Plan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <!-- LEFT SIDEBAR (Parent Category Tabs) -->
                        <div class="col-md-2 border-end">
                            <ul class="nav nav-pills flex-column category-tabs" id="parentCategoryTabs" role="tablist">
                                <!-- Dynamic parent categories will be loaded here -->
                            </ul>
                        </div>

                        <!-- RIGHT CONTENT -->
                        <div class="col-md-10">
                            <!-- Items Container -->
                            <div id="itemsContainer">
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-info-circle fa-2x mb-2"></i>
                                    <p>Please select a category to view items</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="text-center text-white">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading...</p>
    </div>
</div>

<!-- Modal for Add/Edit Item -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="itemModalLabel">Add Budget Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="itemForm">
                <div class="modal-body">
                    <input type="hidden" id="itemId" name="item_id">
                    <input type="hidden" id="categoryId" name="budget_category_id">
                    
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoryType" class="form-label">Category Type <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="category_type" id="categoryRoutine" value="Routine" checked>
                                        <label class="form-check-label" for="categoryRoutine">
                                            Routine
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="category_type" id="categoryCarryOver" value="Carry Over">
                                        <label class="form-check-label" for="categoryCarryOver">
                                            Carry Over
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="category_type" id="categoryTurnAround" value="Turn Around">
                                        <label class="form-check-label" for="categoryTurnAround">
                                            Turn Around
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="category_type" id="categoryMultiYear" value="Multi Year">
                                        <label class="form-check-label" for="categoryMultiYear">
                                            Multi Year
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="stockCode" class="form-label">Stock Code</label>
                                <input type="text" class="form-control" id="stockCode" name="stock_code" maxlength="50">
                            </div>
                            
                            <div class="mb-3">
                                <label for="budgetCode" class="form-label">Budget Code</label>
                                <select class="form-select" id="budgetCode" name="budget_code">
                                    <option value="">Select Budget Code...</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="productLine" class="form-label">Product Line</label>
                                <input type="text" class="form-control" id="productLine" name="product_line" maxlength="100">
                            </div>
                            
                            <div class="mb-3">
                                <label for="costCenter" class="form-label">Cost Center</label>
                                <input type="text" class="form-control" id="costCenter" name="cost_center" maxlength="50">
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="begBalance" class="form-label">Beginning Balance</label>
                                <input type="text" class="form-control" id="begBalance" name="beg_balance" maxlength="100">
                            </div>
                            
                            <div class="mb-3">
                                <label for="consRate" class="form-label">Consumption Rate</label>
                                <input type="text" class="form-control" id="consRate" name="cons_rate" maxlength="100">
                            </div>
                            
                            <div class="mb-3">
                                <label for="unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" maxlength="50">
                            </div>
                            
                            <div class="mb-3">
                                <label for="total" class="form-label">Total</label>
                                <input type="number" class="form-control" id="total" name="total" step="0.01" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="priceEstimation" class="form-label">Price Estimation</label>
                                <input type="number" class="form-control" id="priceEstimation" name="price_estimation" step="0.01">
                            </div>
                            
                            <div class="mb-3">
                                <label for="priceEstimationDescription" class="form-label">Price Estimation Description</label>
                                <textarea class="form-control" id="priceEstimationDescription" name="price_estimation_description" rows="2"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Monthly Activity Quantities -->
                    <div class="mt-4">
                        <h6 class="mb-3 fw-bold">Monthly Activity Quantities (0 - 1000)</h6>
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="activityJan" class="form-label">January</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityJan" name="activity_jan" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activityFeb" class="form-label">February</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityFeb" name="activity_feb" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activityMar" class="form-label">March</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityMar" name="activity_mar" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activityApr" class="form-label">April</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityApr" name="activity_apr" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activityMay" class="form-label">May</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityMay" name="activity_may" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activityJun" class="form-label">June</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityJun" name="activity_jun" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activityJul" class="form-label">July</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityJul" name="activity_jul" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activityAug" class="form-label">August</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityAug" name="activity_aug" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activitySep" class="form-label">September</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activitySep" name="activity_sep" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activityOct" class="form-label">October</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityOct" name="activity_oct" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activityNov" class="form-label">November</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityNov" name="activity_nov" min="0" max="1000" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="activityDec" class="form-label">December</label>
                                <input type="number" class="form-control form-control-sm month-input" id="activityDec" name="activity_dec" min="0" max="1000" value="0">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="bi bi-calculator me-2"></i>
                                    <strong>Total Activity:</strong>
                                    <span class="ms-2" id="totalActivity">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveModal">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('js')
<script type="module" src="{{ asset('assets/js/app.js') }}"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script>
    const WORKPLAN_ID = {{ $workplan->id }};
    const CSRF_TOKEN = '{{ csrf_token() }}';
</script>
<script src="{{ asset('assets/js/work-plan-item.js') }}"></script>
@endsection