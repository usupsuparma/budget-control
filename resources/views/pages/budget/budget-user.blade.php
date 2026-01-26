@extends('layouts.master')

@section('title', 'Budget User | Budget Control')
@section('title-sub', 'Budget User')
@section('pagetitle', 'Budget User - Manage Budget Items')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <style>
        /* Horizontal Tab Styles for Budget Management */
        .nav-tabs-custom {
            border-bottom: 2px solid #dee2e6;
        }
        
        .nav-tabs-custom .nav-item {
            margin-bottom: -2px;
        }
        
        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.3s ease;
            background: transparent;
        }
        
        .nav-tabs-custom .nav-link:hover {
            border-color: transparent;
            border-bottom-color: #dee2e6;
            color: #495057;
            background: #f8f9fa;
        }
        
        .nav-tabs-custom .nav-link.active {
            border-color: transparent;
            border-bottom-color: #f97316;
            color: #f97316;
            background: transparent;
            font-weight: 600;
        }
        
        .nav-tabs-custom .nav-link i {
            font-size: 16px;
        }

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

        .workplan-info .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }

        .workplan-info .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: white;
            color: white;
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

        /* Tab Navigation Styles */
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }

        .nav-pills .nav-link {
            color: #495057;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .nav-pills .nav-link:hover:not(.active) {
            background-color: #e9ecef;
        }

        /* Verification Styles */
        .verification-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 1rem;
            transition: box-shadow 0.2s;
        }
        .verification-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .verification-card.pending {
            border-left-color: #ffc107;
        }
        .price-input-group {
            max-width: 300px;
        }
        .price-input-group .form-control {
            font-size: 1.1rem;
            font-weight: 600;
        }
        .item-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }
        .item-details dt {
            font-weight: 500;
            color: #6c757d;
        }
        .item-details dd {
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.5rem;
        }
        .action-buttons-verify {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .badge-estimation {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
    
<style>
    .timeline-container {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
        border-left: 2px solid #dee2e6;
        padding-left: 20px;
        margin-left: 10px;
    }
    .timeline-item:last-child {
        border-left: none;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -12px;
        top: 0;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #dee2e6;
    }
    .timeline-item.completed::before {
        background: #198754;
        border-color: #198754;
    }
    .timeline-item.pending::before {
        background: #ffc107;
        border-color: #ffc107;
    }
    .timeline-item.rejected::before {
        background: #dc3545;
        border-color: #dc3545;
    }
    .timeline-item.skipped::before {
        background: #6c757d;
        border-color: #6c757d;
    }
    .timeline-item.current::before {
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.5); }
        50% { box-shadow: 0 0 0 8px rgba(13, 110, 253, 0); }
    }
    .timeline-content {
        background: #f8f9fa;
        padding: 12px 15px;
        border-radius: 8px;
    }
    .timeline-content.current {
        background: #e7f1ff;
        border: 1px solid #0d6efd;
    }
</style>
@endsection

@section('content')
<div id="layout-wrapper">
    <!-- CARD PEMBUNGKUS UTAMA -->
    <div class="card card-h-100 shadow-sm border">
        <div class="card-body">
            <!-- HORIZONTAL TAB NAVIGATION -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted text-uppercase small mb-0">Budget Management</h6>
            </div>
            <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#budgetItems" role="tab">
                        <i class="ri-file-list-3-line me-2"></i> Budget Items
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#verification" role="tab">
                        <i class="ri-checkbox-circle-line me-2"></i>Price Verification
                        <span class="badge bg-warning ms-2" id="verificationBadge" style="display: none;">0</span>
                    </a>
                </li>
            </ul>

            <!-- TAB CONTENT -->
            <div class="tab-content">
                        <!-- TAB 1: Budget Items -->
                        <div class="tab-pane fade show active" id="budgetItems">
                            {{-- Filter Section --}}
                            <div class="card filter-section mb-3">
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

                            {{-- Data Info Section --}}
                            <div id="dataInfoSection" style="display: none;">
                                <div class="workplan-info mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">Budget Items - <span id="selectedDivisionName"></span> (<span id="selectedYear"></span>)</h5>
                                            <p class="mb-0">
                                                <small><span id="totalWorkplans">0</span> Work Plans | <span id="totalItems">0</span> Budget Items</small>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-outline-light me-2" id="refreshBudgetItemsBtn" onclick="refreshBudgetItems()">
                                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                            </button>
                                            <button type="button" class="btn btn-success" id="addDataBtn">
                                                <i class="bi bi-plus-circle me-2"></i>Add Data
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Budget Items Table Section --}}
                            <div id="budgetItemsSection" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered items-table" id="budgetItemsTable">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2" class="text-center">Action</th>
                                                        <th rowspan="2">Status</th>
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
                                                        <th rowspan="2">Price Estimation</th>
                                                        <th rowspan="2">Price Estimation Description</th>
                                                        <th rowspan="2">Total</th>
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
                        <!-- END TAB 1: Budget Items -->

                        <!-- TAB 2: Verification -->
                        <div class="tab-pane fade" id="verification">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-clipboard-check me-2"></i>Budget Items Pending Verification
                                    </h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadPendingVerificationItems()">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="pendingItemsContainer">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Loading pending items...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- END TAB 2: Verification -->
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
                    
                    <div class="row">
                        <div class="col-md-6">
                            {{-- Left Column --}}
                            <div class="mb-3">
                                <label for="budgetCategoryId" class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="budgetCategoryId" name="budget_category_id" required>
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

                            {{-- Radio Button Category Type --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="category_type" id="categoryRoutine" value="Routine" required>
                                            <label class="form-check-label" for="categoryRoutine">
                                                Routine
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="category_type" id="categoryCarryOver" value="Carry Over">
                                            <label class="form-check-label" for="categoryCarryOver">
                                                Carry Over
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="category_type" id="categoryTurnAround" value="Turn Around">
                                            <label class="form-check-label" for="categoryTurnAround">
                                                Turn Around
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="category_type" id="categoryMultiYear" value="Multi Year">
                                            <label class="form-check-label" for="categoryMultiYear">
                                                Multi Year
                                            </label>
                                        </div>
                                    </div>
                                </div>
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
                                <label for="priceEstimation" class="form-label fw-bold">Price Estimation</label>
                                <input type="text" class="form-control" id="priceEstimation" name="price_estimation" placeholder="0" inputmode="decimal">
                                <small class="text-muted">Format: 1.000.000 atau 1000000</small>
                            </div>

                            <div class="mb-3">
                                <label for="priceEstimationDescription" class="form-label fw-bold">Price Estimation Description</label>
                                <textarea class="form-control" id="priceEstimationDescription" name="price_estimation_description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="total" class="form-label fw-bold">Total</label>
                                <input type="text" class="form-control bg-light" id="total" name="total" readonly value="0">
                                <small class="text-muted">Otomatis: Jumlah bulan × Price Estimation</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-save-item" id="saveItemBtn">
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

{{-- Modal: Approval Timeline --}}
<div class="modal fade" id="approvalTimelineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history me-2"></i>Approval Timeline
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="text-muted mb-2">Item Details</h6>
                    <div id="approvalItemDetails" class="p-3 bg-light rounded"></div>
                </div>
                <div>
                    <h6 class="text-muted mb-3">Approval Progress</h6>
                    <div id="approvalTimelineContent" class="timeline-container"></div>
                </div>
            </div>
            <div class="modal-footer" id="approvalTimelineFooter">
                {{-- Approve/Reject buttons will be added here if authorized --}}
            </div>
        </div>
    </div>
</div>

{{-- Modal: Rejection Comments --}}
<div class="modal fade" id="rejectCommentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectDetailId">
                <div class="mb-3">
                    <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejectComments" rows="4" required placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="bi bi-x-circle me-1"></i>Tolak
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Verify Item --}}
<div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="verifyModalLabel">
                    <i class="bi bi-check-circle me-2"></i>Verify Budget Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="verifyItemDetails" class="item-details mb-4"></div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">
                            <strong>Price Estimation (User Input)</strong>
                        </label>
                        <div id="verifyPriceEstimation" class="badge bg-secondary badge-estimation">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="verifyFixPrice">
                            <strong>Verified Price (Fix Price) <span class="text-danger">*</span></strong>
                        </label>
                        <div class="input-group price-input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="verifyFixPrice" 
                                   placeholder="Enter verified price" required>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <label class="form-label" for="verifyNotes">Notes (Optional)</label>
                    <textarea class="form-control" id="verifyNotes" rows="3" 
                              placeholder="Add notes about the price verification..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmVerifyBudget()">
                    <i class="bi bi-check-lg me-1"></i>Verify & Submit for Approval
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Reject Verification --}}
<div class="modal fade" id="rejectVerificationModal" tabindex="-1" aria-labelledby="rejectVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectVerificationModalLabel">
                    <i class="bi bi-x-circle me-2"></i>Reject Verification
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="rejectVerificationItemDetails" class="item-details mb-4"></div>
                
                <div class="mt-3">
                    <label class="form-label" for="rejectVerificationNotes">
                        <strong>Rejection Reason <span class="text-danger">*</span></strong>
                    </label>
                    <textarea class="form-control" id="rejectVerificationNotes" rows="4" 
                              placeholder="Enter the reason for rejection..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRejectVerification()">
                    <i class="bi bi-x-lg me-1"></i>Reject
                </button>
            </div>
        </div>
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

    // ==================== VERIFICATION TAB FUNCTIONS ====================
    let currentVerifyItemId = null;
    let currentVerifyItemData = null;
    let pendingVerificationItems = [];

    // Handle tab changes
    $(document).ready(function() {
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            const targetTab = $(e.target).attr('href');
            if (targetTab === '#verification') {
                loadPendingVerificationItems();
            }
        });

        // Load pending count on page load
        loadVerificationBadgeCount();

        // Format price input
        $('#verifyFixPrice').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '');
            if (value) {
                $(this).val(formatNumberVerify(parseInt(value)));
            }
        });

        // Check if URL has tab=verification parameter
        const tabParam = urlParams.get('tab');
        if (tabParam === 'verification') {
            // Activate verification tab
            $('a[href="#verification"]').tab('show');
        }
    });

    /**
     * Load verification badge count
     */
    function loadVerificationBadgeCount() {
        $.ajax({
            url: '/budget-verification/pending',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    $('#verificationBadge').text(response.data.length).show();
                } else {
                    $('#verificationBadge').hide();
                }
            }
        });
    }

    /**
     * Load pending verification items for current user
     */
    function loadPendingVerificationItems() {
        $('#pendingItemsContainer').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading pending items...</p>
            </div>
        `);

        $.ajax({
            url: '/budget-verification/pending',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    pendingVerificationItems = response.data;
                    renderPendingVerificationItems(response.data);
                    $('#verificationBadge').text(response.data.length).show();
                } else {
                    renderEmptyVerificationState();
                    $('#verificationBadge').hide();
                }
            },
            error: function(xhr) {
                console.error('Error loading pending items:', xhr);
                $('#pendingItemsContainer').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Failed to load pending items. Please try again.
                    </div>
                `);
            }
        });
    }

    /**
     * Render pending verification items
     */
    function renderPendingVerificationItems(items) {
        let html = '';
        
        items.forEach(item => {
            html += `
                <div class="card verification-card pending" data-item-id="${item.id}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="card-title mb-3">
                                    <span class="badge bg-warning text-dark me-2">Pending</span>
                                    ${item.description || 'No description'}
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">Cost Center</small>
                                        <div class="fw-semibold">${item.cost_center || '-'}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Category</small>
                                        <div class="fw-semibold">${item.category?.name || item.category_type || '-'}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Workplan</small>
                                        <div class="fw-semibold">${item.workplan?.activity || '-'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <small class="text-muted d-block">Price Estimation</small>
                                <h4 class="text-primary mb-3">${formatCurrency(item.price_estimation || 0)}</h4>
                                <div class="action-buttons-verify justify-content-md-end">
                                    <button type="button" class="btn btn-success btn-sm" onclick="openVerifyBudgetModal(${item.id})">
                                        <i class="bi bi-check-lg me-1"></i>Verify
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="openRejectVerificationModal(${item.id})">
                                        <i class="bi bi-x-lg me-1"></i>Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#pendingItemsContainer').html(html);
    }

    /**
     * Render empty verification state
     */
    function renderEmptyVerificationState() {
        $('#pendingItemsContainer').html(`
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No Pending Verifications</h5>
                <p class="text-muted">You don't have any budget items pending verification.</p>
            </div>
        `);
    }

    /**
     * Open verify modal
     */
    function openVerifyBudgetModal(itemId) {
        currentVerifyItemId = itemId;
        currentVerifyItemData = pendingVerificationItems.find(i => i.id === itemId);
        
        if (!currentVerifyItemData) {
            showToast('Item not found', 'error');
            return;
        }

        // Populate item details
        $('#verifyItemDetails').html(`
            <dl class="row mb-0">
                <dt class="col-sm-4">Description</dt>
                <dd class="col-sm-8">${currentVerifyItemData.description || '-'}</dd>
                
                <dt class="col-sm-4">Cost Center</dt>
                <dd class="col-sm-8">${currentVerifyItemData.cost_center || '-'}</dd>
                
                <dt class="col-sm-4">Category</dt>
                <dd class="col-sm-8">${currentVerifyItemData.category?.name || currentVerifyItemData.category_type || '-'}</dd>
                
                <dt class="col-sm-4">Workplan</dt>
                <dd class="col-sm-8">${currentVerifyItemData.workplan?.activity || '-'}</dd>
            </dl>
        `);

        $('#verifyPriceEstimation').text(formatCurrency(currentVerifyItemData.price_estimation || 0));
        $('#verifyFixPrice').val(formatNumberVerify(currentVerifyItemData.price_estimation || 0));
        $('#verifyNotes').val('');

        $('#verifyModal').modal('show');
    }

    /**
     * Open reject verification modal
     */
    function openRejectVerificationModal(itemId) {
        currentVerifyItemId = itemId;
        currentVerifyItemData = pendingVerificationItems.find(i => i.id === itemId);
        
        if (!currentVerifyItemData) {
            showToast('Item not found', 'error');
            return;
        }

        // Populate item details
        $('#rejectVerificationItemDetails').html(`
            <dl class="row mb-0">
                <dt class="col-sm-4">Description</dt>
                <dd class="col-sm-8">${currentVerifyItemData.description || '-'}</dd>
                
                <dt class="col-sm-4">Cost Center</dt>
                <dd class="col-sm-8">${currentVerifyItemData.cost_center || '-'}</dd>
                
                <dt class="col-sm-4">Price Estimation</dt>
                <dd class="col-sm-8">${formatCurrency(currentVerifyItemData.price_estimation || 0)}</dd>
            </dl>
        `);

        $('#rejectVerificationNotes').val('');

        $('#rejectVerificationModal').modal('show');
    }

    /**
     * Confirm verify budget
     */
    function confirmVerifyBudget() {
        const fixPriceStr = $('#verifyFixPrice').val().replace(/[^0-9]/g, '');
        const fixPrice = parseFloat(fixPriceStr);
        const notes = $('#verifyNotes').val();

        if (!fixPrice || fixPrice <= 0) {
            showToast('Please enter a valid verified price', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirm Verification',
            html: `
                <p>You are about to verify this budget item with:</p>
                <h4 class="text-success">${formatCurrency(fixPrice)}</h4>
                <p class="text-muted">The item will be automatically submitted for approval.</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Yes, Verify & Submit',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                processVerificationBudget(fixPrice, notes);
            }
        });
    }

    /**
     * Process verification
     */
    function processVerificationBudget(fixPrice, notes) {
        showLoading();

        $.ajax({
            url: `/budget-verification/${currentVerifyItemId}/verify`,
            method: 'POST',
            data: {
                _token: CSRF_TOKEN,
                fix_price: fixPrice,
                notes: notes
            },
            success: function(response) {
                hideLoading();
                $('#verifyModal').modal('hide');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Verified!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadPendingVerificationItems();
                    // Also refresh budget items if on that tab
                    if (selectedDivisionId && selectedYear) {
                        loadAllBudgetItems();
                    }
                } else {
                    showToast(response.message || 'Failed to verify', 'error');
                }
            },
            error: function(xhr) {
                hideLoading();
                const msg = xhr.responseJSON?.message || 'Error processing verification';
                showToast(msg, 'error');
            }
        });
    }

    /**
     * Confirm reject verification
     */
    function confirmRejectVerification() {
        const notes = $('#rejectVerificationNotes').val().trim();

        if (!notes) {
            showToast('Please enter a rejection reason', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirm Rejection',
            text: 'Are you sure you want to reject this budget item verification?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                processRejectionBudget(notes);
            }
        });
    }

    /**
     * Process rejection
     */
    function processRejectionBudget(notes) {
        showLoading();

        $.ajax({
            url: `/budget-verification/${currentVerifyItemId}/reject`,
            method: 'POST',
            data: {
                _token: CSRF_TOKEN,
                notes: notes
            },
            success: function(response) {
                hideLoading();
                $('#rejectVerificationModal').modal('hide');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Rejected!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadPendingVerificationItems();
                    // Also refresh budget items if on that tab
                    if (selectedDivisionId && selectedYear) {
                        loadAllBudgetItems();
                    }
                } else {
                    showToast(response.message || 'Failed to reject', 'error');
                }
            },
            error: function(xhr) {
                hideLoading();
                const msg = xhr.responseJSON?.message || 'Error processing rejection';
                showToast(msg, 'error');
            }
        });
    }

    /**
     * Format number with thousand separator for verification
     */
    function formatNumberVerify(value) {
        return new Intl.NumberFormat('id-ID').format(value);
    }
    
</script>
<script src="{{ asset('assets/js/budget-user.js') }}"></script>
@endsection
