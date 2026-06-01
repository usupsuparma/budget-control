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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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
            background: rgba(0, 0, 0, 0.5);
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

        /* Price verification indicators */
        .price-verified {
            color: #198754;
            font-weight: 600;
        }

        .price-estimated {
            color: #6c757d;
            font-style: italic;
        }

        .price-diff-positive {
            color: #dc3545;
        }

        .price-diff-negative {
            color: #198754;
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

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.5);
            }

            50% {
                box-shadow: 0 0 0 8px rgba(13, 110, 253, 0);
            }
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
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#approvalTab" role="tab" id="approvalTabLink">
                            <i class="ri-check-double-line me-2"></i>Approval
                            <span class="badge bg-danger ms-2" id="approvalBadge" style="display: none;">0</span>
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
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold mb-1">
                                            Division
                                            @if (!($isAdmin ?? true))
                                                <span class="badge bg-light text-secondary border ms-1" style="font-size:10px;font-weight:500;">
                                                    <i class="ri-lock-line me-1"></i>Sesuai Akun
                                                </span>
                                            @endif
                                        </label>
                                        <select class="form-select" id="divisionFilter"
                                            {{ !($isAdmin ?? true) ? 'disabled' : '' }}
                                            style="{{ !($isAdmin ?? true) ? 'background-color:#f8f9fa;cursor:not-allowed;' : '' }}">
                                            @if ($isAdmin ?? true)
                                                <option value="">Select Division</option>
                                            @endif
                                            @foreach ($divisions ?? [] as $division)
                                                <option value="{{ $division->id }}"
                                                    {{ in_array($division->id, $userDivisionIds ?? []) ? 'selected' : '' }}>
                                                    {{ $division->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold mb-1">Year</label>
                                        <select class="form-select" id="yearFilter">
                                            <option value="">Select Year</option>
                                            @foreach ($years as $year)
                                                <option value="{{ $year }}"
                                                    {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5 d-flex align-items-end gap-2">
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
                                        <h5 class="mb-1">Budget Items - <span id="selectedDivisionName"></span> (<span
                                                id="selectedYear"></span>)</h5>
                                        <p class="mb-0">
                                            <small><span id="totalWorkplans">0</span> Work Plans | <span
                                                    id="totalItems">0</span> Budget Items</small>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-outline-light me-2" id="refreshBudgetItemsBtn"
                                            onclick="refreshBudgetItems()">
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
                                                    <th rowspan="2" style="display:none;">Cons Rate</th>
                                                    <th rowspan="2">Unit</th>
                                                    <th colspan="12" class="month-header text-center">Qty</th>
                                                    <th rowspan="2">Unit Price</th>
                                                    <th rowspan="2">Price Status</th>
                                                    <th rowspan="2">Total Budget</th>
                                                </tr>
                                                <tr>
                                                    <th class="month-header">Jan</th>
                                                    <th class="month-header">Feb</th>
                                                    <th class="month-header">Mar</th>
                                                    <th class="month-header">Apr</th>
                                                    <th class="month-header">May</th>
                                                    <th class="month-header">Jun</th>
                                                    <th class="month-header">Jul</th>
                                                    <th class="month-header">Aug</th>
                                                    <th class="month-header">Sep</th>
                                                    <th class="month-header">Oct</th>
                                                    <th class="month-header">Nov</th>
                                                    <th class="month-header">Dec</th>
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
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clipboard-check me-2"></i>Budget Items Pending Verification
                                </h5>
                                <div class="d-flex gap-2">
                                    <div id="bulkActions" style="display: none;">
                                        <button type="button" class="btn btn-success btn-sm" onclick="openBulkVerifyModal()">
                                            <i class="bi bi-check-all me-1"></i>Bulk Verify (<span id="selectedCount">0</span>)
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="openBulkRejectModal()">
                                            <i class="bi bi-x-all me-1"></i>Bulk Reject
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                                        <i class="bi bi-file-earmark-arrow-up me-1"></i>Import CSV
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="loadPendingVerificationItems()">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" id="pendingItemsContainer">
                                    <table class="table table-bordered table-hover align-middle" id="pendingVerificationTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input" id="selectAllVerification">
                                                </th>
                                                <th>Description</th>
                                                <th>Cost Center</th>
                                                <th>Category</th>
                                                <th>Workplan</th>
                                                <th class="text-end">Estimation</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pendingVerificationTableBody">
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <p class="mt-2 text-muted">Loading pending items...</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END TAB 2: Verification -->

                    <!-- TAB 3: Approval -->
                    <div class="tab-pane fade" id="approvalTab">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-check-double me-2"></i>Pending Approvals
                                    <span class="badge bg-danger ms-2" id="approvalCountHeader">0</span>
                                </h5>
                                <div class="d-flex gap-2">
                                    <div id="bulkApprovalActions" style="display: none;">
                                        <button type="button" class="btn btn-success btn-sm" onclick="handleBulkApprove()">
                                            <i class="bi bi-check-all me-1"></i>Bulk Approve (<span id="selectedApprovalCount">0</span>)
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="openBulkApprovalRejectModal()">
                                            <i class="bi bi-x-all me-1"></i>Bulk Reject
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="loadPendingApprovalItems()">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="pendingApprovalContainer">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading pending approvals...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END TAB 3: Approval -->
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="itemForm">
                        <input type="hidden" id="itemId" name="item_id">

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Left Column --}}
                                <div class="mb-3">
                                    <label for="budgetCategoryId" class="form-label fw-bold">Type <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="budgetCategoryId" name="budget_category_id" required>
                                        <option value="">Select Budget Category...</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="programId" class="form-label fw-bold">Program ID <span
                                            class="text-danger">*</span></label>
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
                                                <input class="form-check-input" type="radio" name="category_type"
                                                    id="categoryRoutine" value="Routine" required>
                                                <label class="form-check-label" for="categoryRoutine">
                                                    Routine
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="category_type"
                                                    id="categoryCarryOver" value="Carry Over">
                                                <label class="form-check-label" for="categoryCarryOver">
                                                    Carry Over
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="category_type"
                                                    id="categoryTurnAround" value="Turn Around">
                                                <label class="form-check-label" for="categoryTurnAround">
                                                    Turn Around
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="category_type"
                                                    id="categoryMultiYear" value="Multi Year">
                                                <label class="form-check-label" for="categoryMultiYear">
                                                    Multi Year
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">Description <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="description" name="description"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label for="stockCode" class="form-label fw-bold">Stock Code</label>
                                    <select class="form-select" id="stockCode" name="stock_code">
                                        <option value="">Select Stock Code</option>
                                    </select>
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
                                    <input type="number" class="form-control" id="begBalance" name="beg_balance">
                                </div>

                                <div class="mb-3">
                                    <label for="supplier" class="form-label fw-bold">Supplier</label>
                                    <select class="form-select" id="supplier" name="supplier_name">
                                        <option value="">Select</option>
                                    </select>
                                </div>

                                <div class="mb-3" style="display:none;">
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
                                    <input type="number" class="form-control monthly-activity" name="activity_jan"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">February</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_feb"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">March</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_mar"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">April</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_apr"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">May</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_may"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">June</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_jun"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">July</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_jul"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">August</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_aug"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">September</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_sep"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">October</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_oct"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">November</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_nov"
                                        value="0" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">December</label>
                                    <input type="number" class="form-control monthly-activity" name="activity_dec"
                                        value="0" min="0">
                                </div>


                                <div class="mb-3">
                                    <label for="priceEstimation" class="form-label fw-bold">Price Estimation</label>
                                    <input type="text" class="form-control" id="priceEstimation"
                                        name="price_estimation" placeholder="0" inputmode="decimal">
                                    <small class="text-muted">Format: 1,000,000 or 1000000</small>
                                </div>

                                <div class="mb-3">
                                    <label for="priceEstimationDescription" class="form-label fw-bold">Price Estimation
                                        Description</label>
                                    <textarea class="form-control" id="priceEstimationDescription" name="price_estimation_description" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="total" class="form-label fw-bold">Total</label>
                                    <input type="text" class="form-control bg-light" id="total" name="total"
                                        readonly value="0">
                                    <small class="text-muted">Auto: Total months × Price Estimation</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-save-item" id="saveItemBtn">
                        Save
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
                        <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectComments" rows="4" required
                            placeholder="Enter rejection reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmReject()">
                        <i class="bi bi-x-circle me-1"></i>Reject
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="verifyItemDetails" class="item-details mb-4"></div>

                    <hr>

                    <!-- Qty Information -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="bi bi-calculator me-1"></i>Total Quantity:</strong>
                                <span id="verifyTotalQty" class="ms-2 fs-5">0</span> units
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">Sum of all monthly activities</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">
                                <strong>Price Estimation per Unit (User Input)</strong>
                            </label>
                            <div id="verifyPriceEstimation" class="badge bg-secondary badge-estimation">-</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="verifyFixPrice">
                                <strong>Verified Price per Unit <span class="text-danger">*</span></strong>
                            </label>
                            <div class="input-group price-input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="verifyFixPrice"
                                    placeholder="Enter verified price per unit" required>
                            </div>
                            <small class="text-muted">Enter price per unit (not total)</small>
                        </div>
                    </div>

                    <!-- Total Calculation -->
                    <div class="alert alert-success mt-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <strong><i class="bi bi-cash-stack me-1"></i>Estimated Total:</strong>
                                <div id="verifyEstimatedTotal" class="fs-4 fw-bold text-success">Rp 0</div>
                                <small class="text-muted">(Total Qty × Price Estimation per Unit)</small>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="bi bi-check-circle me-1"></i>Verified Total:</strong>
                                <div id="verifyVerifiedTotal" class="fs-4 fw-bold text-primary">Rp 0</div>
                                <small class="text-muted">(Total Qty × Verified Price per Unit)</small>
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
    <div class="modal fade" id="rejectVerificationModal" tabindex="-1" aria-labelledby="rejectVerificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectVerificationModalLabel">
                        <i class="bi bi-x-circle me-2"></i>Reject Verification
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
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

    {{-- Modal: Bulk Verify --}}
    <div class="modal fade" id="bulkVerifyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-all me-2"></i>Bulk Verify Budget Items</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        You are about to verify <strong id="bulkVerifyCount">0</strong> items.
                        <br><small>Prices will be set based on their current estimation.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="bulkVerifyNotes" rows="3" placeholder="Add notes for all selected items..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="confirmBulkVerify()">
                        <i class="bi bi-check-lg me-1"></i>Verify All Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Bulk Reject --}}
    <div class="modal fade" id="bulkRejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-all me-2"></i>Bulk Reject Verification</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        You are about to reject <strong id="bulkRejectCount">0</strong> items.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="bulkRejectNotes" rows="3" required placeholder="Reason for rejecting these items..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmBulkReject()">
                        <i class="bi bi-x-lg me-1"></i>Reject All Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Import CSV --}}
    <div class="modal fade" id="importCsvModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Verification CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="importCsvForm">
                        <div class="alert alert-info">
                            <p class="mb-1"><strong>CSV Format:</strong></p>
                            <code>item_id,verified_price</code>
                            <br><small>Example: 123,550000</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Choose CSV File</label>
                            <input type="file" class="form-control" id="csvFile" accept=".csv" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" onclick="uploadCsv()">
                        <i class="bi bi-upload me-1"></i>Upload & Process
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
                if (targetTab === '#approvalTab') {
                    loadPendingApprovalItems();
                }
            });

            // Load pending count on page load
            loadVerificationBadgeCount();
            loadApprovalBadgeCount();

            // Format price input and recalculate verified total
            $('#verifyFixPrice').on('input', function() {
                let value = $(this).val().replace(/[^0-9]/g, '');
                if (value) {
                    $(this).val(formatNumberVerify(parseInt(value)));
                    // Recalculate verified total
                    updateVerifiedTotal();
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
            $('#pendingVerificationTableBody').html(`
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading pending items...</p>
                </td>
            </tr>
        `);

            // Reset checkboxes and bulk actions
            $('#selectAllVerification').prop('checked', false);
            $('#bulkActions').hide();

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
                    $('#pendingVerificationTableBody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Failed to load pending items. Please try again.
                            </div>
                        </td>
                    </tr>
                `);
                }
            });
        }

        /**
         * Render pending verification items into the table
         */
        function renderPendingVerificationItems(items) {
            let html = '';

            items.forEach(item => {
                html += `
                <tr data-item-id="${item.id}">
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input item-checkbox" value="${item.id}">
                    </td>
                    <td>
                        <div class="fw-semibold">${item.description || 'No description'}</div>
                        <small class="text-muted">ID: ${item.id}</small>
                    </td>
                    <td>${item.cost_center || '-'}</td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            ${item.category?.name || item.category_type || '-'}
                        </span>
                    </td>
                    <td>${item.workplan?.activity || '-'}</td>
                    <td class="text-end fw-bold">${formatCurrency(item.price_estimation || 0)}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-success" onclick="openVerifyBudgetModal(${item.id})" title="Verify">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="openRejectVerificationModal(${item.id})" title="Reject">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            });

            $('#pendingVerificationTableBody').html(html);

            // Re-initialize checkbox event listeners
            initCheckboxListeners();
        }

        /**
         * Initialize checkbox listeners
         */
        function initCheckboxListeners() {
            $('#selectAllVerification').on('change', function() {
                $('.item-checkbox').prop('checked', $(this).is(':checked'));
                updateBulkActionsVisibility();
            });

            $('.item-checkbox').on('change', function() {
                const total = $('.item-checkbox').length;
                const checked = $('.item-checkbox:checked').length;
                $('#selectAllVerification').prop('checked', total === checked);
                updateBulkActionsVisibility();
            });
        }

        /**
         * Update bulk actions visibility based on selection
         */
        function updateBulkActionsVisibility() {
            const checkedCount = $('.item-checkbox:checked').length;
            if (checkedCount > 0) {
                $('#selectedCount').text(checkedCount);
                $('#bulkActions').fadeIn();
            } else {
                $('#bulkActions').fadeOut();
            }
        }

        /**
         * Render empty verification state
         */
        function renderEmptyVerificationState() {
            $('#pendingVerificationTableBody').html(`
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="empty-state py-0">
                        <i class="bi bi-inbox fs-1 d-block mb-3 text-muted"></i>
                        <h5>No Pending Verifications</h5>
                        <p class="text-muted">You don't have any budget items pending verification.</p>
                    </div>
                </td>
            </tr>
        `);
        }

        /**
         * Bulk actions modals and confirmations
         */
        function openBulkVerifyModal() {
            const count = $('.item-checkbox:checked').length;
            $('#bulkVerifyCount').text(count);
            $('#bulkVerifyNotes').val('');
            $('#bulkVerifyModal').modal('show');
        }

        function confirmBulkVerify() {
            const itemIds = [];
            $('.item-checkbox:checked').each(function() {
                itemIds.push($(this).val());
            });

            const notes = $('#bulkVerifyNotes').val();

            Swal.fire({
                title: 'Confirm Bulk Verification',
                text: `Are you sure you want to verify all ${itemIds.length} selected items?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'Yes, Verify All',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    processBulkVerification(itemIds, notes);
                }
            });
        }

        function processBulkVerification(itemIds, notes) {
            showLoading();
            $.ajax({
                url: '/budget-verification/bulk-verify',
                method: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    item_ids: itemIds,
                    notes: notes
                },
                success: function(response) {
                    hideLoading();
                    $('#bulkVerifyModal').modal('hide');
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success');
                        loadPendingVerificationItems();
                        if (typeof loadAllBudgetItems === 'function' && selectedDivisionId) {
                            loadAllBudgetItems();
                        }
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    showToast(xhr.responseJSON?.message || 'Bulk verification failed', 'error');
                }
            });
        }

        function openBulkRejectModal() {
            const count = $('.item-checkbox:checked').length;
            $('#bulkRejectCount').text(count);
            $('#bulkRejectNotes').val('');
            $('#bulkRejectModal').modal('show');
        }

        function confirmBulkReject() {
            const itemIds = [];
            $('.item-checkbox:checked').each(function() {
                itemIds.push($(this).val());
            });

            const notes = $('#bulkRejectNotes').val();
            if (!notes) {
                showToast('Please provide a rejection reason', 'warning');
                return;
            }

            Swal.fire({
                title: 'Confirm Bulk Rejection',
                text: `Are you sure you want to reject all ${itemIds.length} selected items?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Reject All',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    processBulkRejection(itemIds, notes);
                }
            });
        }

        function processBulkRejection(itemIds, notes) {
            showLoading();
            $.ajax({
                url: '/budget-verification/bulk-reject',
                method: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    item_ids: itemIds,
                    notes: notes
                },
                success: function(response) {
                    hideLoading();
                    $('#bulkRejectModal').modal('hide');
                    if (response.success) {
                        Swal.fire('Rejected!', response.message, 'success');
                        loadPendingVerificationItems();
                        if (typeof loadAllBudgetItems === 'function' && selectedDivisionId) {
                            loadAllBudgetItems();
                        }
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    showToast(xhr.responseJSON?.message || 'Bulk rejection failed', 'error');
                }
            });
        }

        /**
         * CSV Upload
         */
        function uploadCsv() {
            const fileInput = document.getElementById('csvFile');
            if (fileInput.files.length === 0) {
                showToast('Please select a CSV file', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', CSRF_TOKEN);

            showLoading();
            $.ajax({
                url: '/budget-verification/import-csv',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoading();
                    $('#importCsvModal').modal('hide');
                    $('#importCsvForm')[0].reset();
                    if (response.success) {
                        Swal.fire('Import Complete!', response.message, 'success');
                        loadPendingVerificationItems();
                        if (typeof loadAllBudgetItems === 'function' && selectedDivisionId) {
                            loadAllBudgetItems();
                        }
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    showToast(xhr.responseJSON?.message || 'CSV upload failed', 'error');
                }
            });
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

            // Calculate total qty from all months
            const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
            const totalQty = months.reduce((sum, month) => {
                return sum + (parseInt(currentVerifyItemData[`activity_${month}`]) || 0);
            }, 0);

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

            // Display total qty
            $('#verifyTotalQty').text(totalQty);

            // Display price estimation and calculated total
            const priceEstimation = parseFloat(currentVerifyItemData.price_estimation) || 0;
            $('#verifyPriceEstimation').text(formatCurrency(priceEstimation));

            // Calculate and display estimated total
            const estimatedTotal = totalQty * priceEstimation;
            $('#verifyEstimatedTotal').text(formatCurrency(estimatedTotal));

            // Set initial verified price and calculate verified total
            $('#verifyFixPrice').val(formatNumberVerify(priceEstimation));
            const verifiedTotal = totalQty * priceEstimation;
            $('#verifyVerifiedTotal').text(formatCurrency(verifiedTotal));

            $('#verifyNotes').val('');

            $('#verifyModal').modal('show');
        }

        /**
         * Update verified total when verified price changes
         */
        function updateVerifiedTotal() {
            if (!currentVerifyItemData) return;

            // Calculate total qty
            const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
            const totalQty = months.reduce((sum, month) => {
                return sum + (parseInt(currentVerifyItemData[`activity_${month}`]) || 0);
            }, 0);

            // Get verified price
            const verifiedPriceStr = $('#verifyFixPrice').val().replace(/[^0-9]/g, '');
            const verifiedPrice = parseFloat(verifiedPriceStr) || 0;

            // Calculate verified total
            const verifiedTotal = totalQty * verifiedPrice;
            $('#verifyVerifiedTotal').text(formatCurrency(verifiedTotal));
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

        // ==================== APPROVAL TAB FUNCTIONS ====================
        let pendingApprovalItems = [];

        /**
         * Load approval badge count (called on page load)
         */
        function loadApprovalBadgeCount() {
            $.ajax({
                url: '/workplan-budget-item-approval/pending',
                method: 'GET',
                success: function(response) {
                    if (response.success && response.count > 0) {
                        $('#approvalBadge').text(response.count).show();
                        $('#approvalCountHeader').text(response.count);
                    } else {
                        $('#approvalBadge').hide();
                        $('#approvalCountHeader').text('0');
                    }
                },
                error: function() {
                    $('#approvalBadge').hide();
                }
            });
        }

        /**
         * Load all pending approval items for the current user
         */
        function loadPendingApprovalItems() {
            $('#pendingApprovalContainer').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading pending approvals...</p>
            </div>
        `);

            // Reset checkboxes and bulk actions
            $('#bulkApprovalActions').hide();

            $.ajax({
                url: '/workplan-budget-item-approval/pending',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        pendingApprovalItems = response.data || [];
                        const count = response.count || 0;

                        // Update badges
                        if (count > 0) {
                            $('#approvalBadge').text(count).show();
                        } else {
                            $('#approvalBadge').hide();
                        }
                        $('#approvalCountHeader').text(count);

                        renderPendingApprovalItems(pendingApprovalItems);
                    } else {
                        $('#pendingApprovalContainer').html(`
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>${response.message || 'Failed to load pending approvals.'}
                        </div>
                    `);
                    }
                },
                error: function(xhr) {
                    $('#pendingApprovalContainer').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>Error loading pending approvals. Please try again.
                    </div>
                `);
                }
            });
        }

        /**
         * Render the list of pending approval items
         */
        function renderPendingApprovalItems(items) {
            if (!items || items.length === 0) {
                $('#pendingApprovalContainer').html(`
                <div class="text-center py-5">
                    <i class="bi bi-check-circle fs-1 text-success"></i>
                    <h5 class="mt-3 text-muted">No Pending Approvals</h5>
                    <p class="text-muted">All budget items have been reviewed. Great job!</p>
                </div>
            `);
                return;
            }

            let html = `
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="approvalItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAllApproval">
                            </th>
                            <th class="text-center" style="width:50px;">#</th>
                            <th>Ref Number</th>
                            <th>Description</th>
                            <th>Program / Workplan</th>
                            <th>Division / Department</th>
                            <th>Category</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-center">Total Qty</th>
                            <th class="text-end">Total Budget</th>
                            <th class="text-center">Level</th>
                            <th>Requested At</th>
                            <th class="text-center" style="width:130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

            items.forEach((approval, index) => {
                const item = approval.item;
                if (!item) return;

                const categoryColors = {
                    'Routine': 'bg-secondary',
                    'Turn Around': 'bg-info',
                    'Carry Over': 'bg-warning',
                    'Multi Year': 'bg-primary',
                };
                const categoryColor = categoryColors[item.category_type] || 'bg-secondary';

                html += `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input approval-checkbox" value="${approval.detail_id}">
                    </td>
                    <td class="text-center">${index + 1}</td>
                    <td>
                        <span class="fw-semibold text-primary" style="font-size:12px;">${approval.reference_number || '-'}</span>
                    </td>
                    <td>
                        <div class="fw-semibold" style="font-size:13px;">${item.description || '-'}</div>
                        <small class="text-muted">${item.stock_code || ''} ${item.budget_code ? '| ' + item.budget_code : ''}</small>
                    </td>
                    <td>
                        <div style="font-size:12px;">${item.workplan_activity || '-'}</div>
                        <small class="text-muted">Year: ${item.workplan_year || '-'}</small>
                    </td>
                    <td>
                        <div style="font-size:12px;">${item.division_name || '-'}</div>
                        <small class="text-muted">${item.department_name || ''}</small>
                    </td>
                    <td><span class="badge ${categoryColor}" style="font-size:11px;">${item.category_type || '-'}</span></td>
                    <td class="text-end" style="font-size:12px;">${formatApprovalCurrency(item.unit_price || 0)}</td>
                    <td class="text-center" style="font-size:12px;">${item.total_qty || 0}</td>
                    <td class="text-end fw-bold" style="font-size:12px;">${formatApprovalCurrency(item.total_budget || 0)}</td>
                    <td class="text-center">
                        <span class="badge bg-info">${approval.level} / ${approval.total_levels}</span>
                    </td>
                    <td style="font-size:12px;">
                        <div>${formatApprovalDate(approval.requested_at)}</div>
                        <small class="text-muted">${getApprovalTimeAgo(approval.requested_at)}</small>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info" onclick="showApprovalDetail(${index})" title="Detail & Timeline">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-success" onclick="approveFromTab(${approval.detail_id}, ${item.id})" title="Approve">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="rejectFromTab(${approval.detail_id})" title="Reject">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            });

            html += `
                    </tbody>
                </table>
            </div>
        `;

            $('#pendingApprovalContainer').html(html);

            // Initialize checkbox listeners for approval tab
            initApprovalCheckboxListeners();
        }

        /**
         * Initialize checkbox listeners for approval tab
         */
        function initApprovalCheckboxListeners() {
            $('#selectAllApproval').on('change', function() {
                $('.approval-checkbox').prop('checked', $(this).is(':checked'));
                updateBulkApprovalActionsVisibility();
            });

            $('.approval-checkbox').on('change', function() {
                const total = $('.approval-checkbox').length;
                const checked = $('.approval-checkbox:checked').length;
                $('#selectAllApproval').prop('checked', total === checked);
                updateBulkApprovalActionsVisibility();
            });
        }

        /**
         * Update bulk actions visibility for approval tab
         */
        function updateBulkApprovalActionsVisibility() {
            const checkedCount = $('.approval-checkbox:checked').length;
            if (checkedCount > 0) {
                $('#selectedApprovalCount').text(checkedCount);
                $('#bulkApprovalActions').fadeIn();
            } else {
                $('#bulkApprovalActions').fadeOut();
            }
        }

        /**
         * Handle Bulk Approve
         */
        function handleBulkApprove() {
            const detailIds = [];
            $('.approval-checkbox:checked').each(function() {
                detailIds.push($(this).val());
            });

            Swal.fire({
                title: 'Confirm Bulk Approval',
                text: `Are you sure you want to approve all ${detailIds.length} selected items?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'Yes, Approve All',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    processBulkApproval(detailIds, 'approve');
                }
            });
        }

        /**
         * Open Bulk Approval Reject Modal
         */
        function openBulkApprovalRejectModal() {
            const count = $('.approval-checkbox:checked').length;
            $('#bulkRejectCount').text(count); // Reuse existing modal fields if suitable, or use standard Swal
            
            Swal.fire({
                title: 'Bulk Reject Reason',
                input: 'textarea',
                inputLabel: 'Please provide a reason for rejecting the selected items',
                inputPlaceholder: 'Enter reason here...',
                inputAttributes: {
                    'aria-label': 'Enter reason here'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Reject Selected',
                preConfirm: (value) => {
                    if (!value) {
                        Swal.showValidationMessage('Rejection reason is required');
                    }
                    return value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const detailIds = [];
                    $('.approval-checkbox:checked').each(function() {
                        detailIds.push($(this).val());
                    });
                    processBulkApproval(detailIds, 'reject', result.value);
                }
            });
        }

        /**
         * Process Bulk Approval/Reject via AJAX
         */
        function processBulkApproval(detailIds, action, comments = null) {
            showLoading();
            $.ajax({
                url: '/workplan-budget-item-approval/bulk-process',
                method: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    detail_ids: detailIds,
                    action: action,
                    comments: comments
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success');
                        loadPendingApprovalItems();
                        // Also refresh badge count
                        loadApprovalBadgeCount();
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    showToast(xhr.responseJSON?.message || 'Bulk process failed', 'error');
                }
            });
        }

        /**
         * Show detailed approval modal with timeline from the approval tab
         */
        function showApprovalDetail(index) {
            const approval = pendingApprovalItems[index];
            if (!approval || !approval.item) return;

            const item = approval.item;
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const monthKeys = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

            // Build monthly breakdown
            let monthlyHtml = '';
            monthKeys.forEach((m, i) => {
                const val = item.monthly ? (item.monthly[m] || 0) : 0;
                if (val > 0) {
                    monthlyHtml += `<span class="badge bg-light text-dark me-1 mb-1">${months[i]}: ${val}</span>`;
                }
            });

            // Build item details
            const detailsHtml = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" style="width:130px;">Ref Number</td><td class="fw-semibold">${approval.reference_number || '-'}</td></tr>
                        <tr><td class="text-muted">Description</td><td class="fw-semibold">${item.description || '-'}</td></tr>
                        <tr><td class="text-muted">Workplan</td><td>${item.workplan_activity || '-'} (${item.workplan_year || '-'})</td></tr>
                        <tr><td class="text-muted">Division</td><td>${item.division_name || '-'}</td></tr>
                        <tr><td class="text-muted">Department</td><td>${item.department_name || '-'}</td></tr>
                        <tr><td class="text-muted">Category</td><td><span class="badge bg-secondary">${item.category_type || '-'}</span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" style="width:130px;">Stock Code</td><td>${item.stock_code || '-'}</td></tr>
                        <tr><td class="text-muted">Budget Code</td><td>${item.budget_code || '-'}</td></tr>
                        <tr><td class="text-muted">Cost Center</td><td>${item.cost_center || '-'}</td></tr>
                        <tr><td class="text-muted">Supplier</td><td>${item.supplier_name || '-'}</td></tr>
                        <tr><td class="text-muted">Unit</td><td>${item.unit_name || '-'}</td></tr>
                        <tr style="display:none;"><td class="text-muted">Cons Rate</td><td>${item.cons_rate || '-'}</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card bg-light border-0">
                        <div class="card-body py-2 px-3 text-center">
                            <small class="text-muted">Unit Price</small>
                            <div class="fw-bold">${formatApprovalCurrency(item.unit_price || 0)}</div>
                            <small class="text-muted">${item.verification_status === 'verified' ? '<i class="bi bi-check-circle text-success"></i> Verified' : '<i class="bi bi-hourglass-split"></i> Estimated'}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light border-0">
                        <div class="card-body py-2 px-3 text-center">
                            <small class="text-muted">Total Qty</small>
                            <div class="fw-bold">${item.total_qty || 0}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary bg-opacity-10 border-0">
                        <div class="card-body py-2 px-3 text-center">
                            <small class="text-muted">Total Budget</small>
                            <div class="fw-bold text-primary">${formatApprovalCurrency(item.total_budget || 0)}</div>
                        </div>
                    </div>
                </div>
            </div>
            ${monthlyHtml ? `<div class="mb-2"><small class="text-muted">Monthly Activity:</small><br>${monthlyHtml}</div>` : ''}
        `;
            $('#approvalItemDetails').html(detailsHtml);

            // Build timeline
            let timelineHtml = '';
            const timeline = approval.timeline || [];
            if (timeline.length === 0) {
                timelineHtml =
                    `<div class="text-center text-muted py-3"><i class="bi bi-info-circle fs-3"></i><p class="mt-2">No timeline data available.</p></div>`;
            } else {
                const nextPending = timeline.find(d => d.status === 'pending');
                timeline.forEach(detail => {
                    const isCurrentPending = nextPending && nextPending.id === detail.id;
                    const statusClass = getTimelineStatusClassApproval(detail.status, isCurrentPending);
                    const contentClass = isCurrentPending ? 'current' : '';

                    timelineHtml += `
                    <div class="timeline-item ${statusClass} ${isCurrentPending ? 'current' : ''}">
                        <div class="timeline-content ${contentClass}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong>Level ${detail.level_sequence}</strong>
                                <span class="badge bg-${getStatusBadgeClassApproval(detail.status)}">${capitalizeFirstApproval(detail.status)}</span>
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-person me-1"></i>${detail.employment_name || 'Unknown Approver'}
                            </div>
                            ${detail.approved_at ? `<div class="text-muted small mt-1"><i class="bi bi-calendar me-1"></i>${formatApprovalDate(detail.approved_at)}</div>` : ''}
                        </div>
                    </div>
                `;
                });
            }
            $('#approvalTimelineContent').html(timelineHtml);

            // Footer buttons
            let footerHtml = `
            <button type="button" class="btn btn-danger" onclick="rejectFromTab(${approval.detail_id})">
                <i class="bi bi-x-lg me-1"></i>Reject
            </button>
            <button type="button" class="btn btn-success" onclick="approveFromTab(${approval.detail_id}, ${item.id})">
                <i class="bi bi-check-lg me-1"></i>Approve
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        `;
            $('#approvalTimelineFooter').html(footerHtml);

            $('#approvalTimelineModal').modal('show');
        }

        /**
         * Approve item from approval tab
         */
        function approveFromTab(detailId, itemId) {
            Swal.fire({
                title: 'Approve this item?',
                text: 'Are you sure you want to approve this budget item?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    $.ajax({
                        url: `/workplan-budget-item-approval/detail/${detailId}/approve`,
                        method: 'POST',
                        data: {
                            _token: CSRF_TOKEN
                        },
                        success: function(response) {
                            hideLoading();
                            if (response.success) {
                                showToast(response.message || 'Item approved successfully', 'success');
                                $('#approvalTimelineModal').modal('hide');
                                loadPendingApprovalItems(); // Refresh approval list
                            } else {
                                showToast(response.message || 'Failed to approve', 'error');
                            }
                        },
                        error: function(xhr) {
                            hideLoading();
                            const msg = xhr.responseJSON?.message || 'Error approving item';
                            showToast(msg, 'error');
                        }
                    });
                }
            });
        }

        /**
         * Reject item from approval tab - opens rejection modal
         */
        function rejectFromTab(detailId) {
            $('#rejectDetailId').val(detailId);
            $('#rejectComments').val('');
            $('#approvalTimelineModal').modal('hide');
            // Set flag so after reject we refresh approval tab
            window._rejectFromApprovalTab = true;
            $('#rejectCommentModal').modal('show');
        }

        // Override confirmReject to also refresh approval tab
        const _originalConfirmReject = (typeof confirmReject === 'function') ? confirmReject : null;

        // Note: confirmReject is defined in budget-user.js with approval tab support

        // ==================== APPROVAL TAB HELPER FUNCTIONS ====================

        function formatApprovalCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
        }

        function formatApprovalDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getApprovalTimeAgo(dateStr) {
            if (!dateStr) return '';
            const now = new Date();
            const date = new Date(dateStr);
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} min ago`;
            if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            if (diffDays < 30) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
            return `${Math.floor(diffDays / 30)} month${Math.floor(diffDays / 30) > 1 ? 's' : ''} ago`;
        }

        function getStatusBadgeClassApproval(status) {
            const classes = {
                'draft': 'secondary',
                'pending': 'warning',
                'in_progress': 'info',
                'approved': 'success',
                'rejected': 'danger',
                'skipped': 'secondary',
                'cancelled': 'secondary'
            };
            return classes[status] || 'secondary';
        }

        function getTimelineStatusClassApproval(status, isCurrentPending) {
            if (isCurrentPending) return 'current';
            if (status === 'approved') return 'completed';
            if (status === 'rejected') return 'rejected';
            if (status === 'skipped') return 'skipped';
            return 'pending';
        }

        function capitalizeFirstApproval(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    </script>
    <script src="{{ asset('assets/js/budget-user.js') }}"></script>
@endsection
