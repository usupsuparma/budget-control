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
        padding: 4px 8px;
        font-size: 11px;
        margin: 2px;
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
                            <i class="fas fa-arrow-left me-1"></i> Back to Work Plan
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
                            <!-- Child Category Tabs -->
                            <div class="mb-3">
                                <ul class="nav nav-pills child-tabs" id="childCategoryTabs" role="tablist">
                                    <!-- Dynamic child categories will be loaded here -->
                                </ul>
                            </div>

                            <!-- Items Content -->
                            <div class="tab-content" id="itemsContent">
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                    <p>Please select a category to view budget items</p>
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