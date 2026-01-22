@extends('layouts.master')

@section('title', 'Work Plan | Budget Control')

@section('title-sub', 'Work Plan')
@section('pagetitle', 'Program Kerja (Work Plan)')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<style>
    .workplan-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }

    .kpi-division-header {
        background: #6c757d;
        color: white;
        padding: 12px 20px;
        font-weight: 600;
        border-radius: 6px 6px 0 0;
        margin-top: 20px;
    }

    .kpi-department-header {
        background: #dee2e6;
        padding: 10px 20px;
        font-weight: 600;
        border-left: 4px solid #6c757d;
        margin-top: 15px;
    }

    .kpi-section-header {
        background: #e9ecef;
        padding: 8px 20px;
        font-weight: 500;
        border-left: 4px solid #adb5bd;
        margin-top: 10px;
        margin-left: 20px;
    }

    .workplan-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 11px;
    }

    .workplan-table th {
        background: #ffffffff;
        color: #4e4e4eff;
        padding: 8px 5px;
        text-align: center;
        border: 1px solid #dee2e6;
        font-weight: 600;
        font-size: 12px;
    }

    .workplan-table td {
        border: 1px solid #dee2e6;
        padding: 5px;
        vertical-align: middle;
        font-weight: 600;
        font-size: 12px;
    }

    .workplan-table input[type="text"],
    .workplan-table input[type="number"],
    .workplan-table input[type="date"] {
        width: 100%;
        border: 1px solid #ced4da;
        padding: 4px 6px;
        border-radius: 4px;
        font-size: 11px;
    }

    .workplan-table input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .month-cell {
        background: #cfe2ff;
        text-align: center;
        padding: 3px !important;
    }

    .realization-cell {
        background: #f8d7da;
        text-align: center;
        padding: 3px !important;
    }

    .btn-action {
        padding: 3px 6px;
        font-size: 10px;
        margin: 0 2px;
        display: inline-block;
        min-width: auto;
        position: relative;
    }

    /* Custom Tooltip for buttons */
    .btn-action:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 11px;
        white-space: nowrap;
        z-index: 1000;
        pointer-events: none;
        margin-bottom: 5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .btn-action:hover::before {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: rgba(0, 0, 0, 0.9);
        z-index: 1000;
        pointer-events: none;
    }

    .action-column {
        white-space: nowrap;
    }

    .btn-add-workplan {
        margin: 10px 0;
        font-size: 12px;
    }

    .budget-input {
        text-align: right;
    }

    .status-badge {
        font-size: 10px;
        padding: 3px 8px;
    }

    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .no-data-message {
        text-align: center;
        padding: 30px;
        color: #6c757d;
        font-style: italic;
    }

    .action-column {
        width: 90px;
        text-align: center;
        padding: 5px !important;
    }

    .target-satuan-cell {
        background: #fff3cd;
        font-weight: 500;
    }

    .expand-btn {
        cursor: pointer;
        padding: 5px 10px;
        background: #0099d6;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 12px;
    }

    .collapse-section {
        display: none;
    }

    .collapse-section.show {
        display: block;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-overlay.show {
        display: flex;
    }

    /* Approved row styling */
    tr.table-success {
        background-color: #d1e7dd !important;
    }

    tr.table-success input {
        background-color: #f0f8f3 !important;
        cursor: not-allowed;
    }

    /* Smooth animations */
    .workplan-table tbody tr {
        transition: background-color 0.3s ease;
    }

    /* Toast notification custom style */
    .swal2-toast {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    }

    /* Modal styling */
    #workplanModal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    #workplanModal .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    #workplanModal .form-check {
        padding: 8px 12px;
        background: white;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    #workplanModal .form-check:hover {
        background: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    #workplanModal .form-check-input {
        width: 20px;
        height: 20px;
        margin-top: 0;
        cursor: pointer;
        border: 2px solid #6c757d;
    }

    #workplanModal .form-check-input:checked {
        background-color: #ff6900;
        border-color: #ff6900;
    }

    #workplanModal .form-check-label {
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        margin-left: 8px;
        user-select: none;
    }

    #workplanModal .month-section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 15px;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0;
    }

    #workplanModal .month-container {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 0 0 8px 8px;
        padding: 15px;
    }

    #workplanModal .realization-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .btn-edit-workplan {
        cursor: pointer;
    }

    /* Budget Cell Styling */
    .budget-cell {
        text-align: center;
        vertical-align: middle;
        transition: all 0.3s ease;
    }

    .budget-cell:hover {
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
</style>
<style>
    /* ===== Workplan Action Buttons ===== */
    .btn-action {
        padding: 2px 6px;
        font-size: 12px;
        line-height: 1;
    }

    .btn-action i {
        font-size: 12px;
    }

    /* WAJIB pakai selector gabungan */
    .btn.btn-workplan {
        background-color: #00B0F0;
        border-color: #00B0F0;
        color: #fff;
    }

    .btn.btn-workplan:hover {
        background-color: #0099d6;
        border-color: #0099d6;
        color: #fff;
    }

    .btn.btn-workplan:focus,
    .btn.btn-workplan:active {
        background-color: #008cc2;
        border-color: #008cc2;
        color: #fff;
        box-shadow: 0 0 0 0.15rem rgba(0, 176, 240, 0.4);
    }
</style>


@endsection

@section('content')
<div id="layout-wrapper">
    {{-- Filter Section --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="filter-section">
                <h6 class="mb-3">Filter KPI</h6>
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Divisi</label>
                        <select id="filter_division" class="form-select">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $div)
                            <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select id="filter_year" class="form-select">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach($years as $year)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button id="btnLoadKpi" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Load KPI Data
                        </button>
                        <button id="btnReset" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Work Plan Container --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Work Plan (Program Kerja)</h6>
                </div>
                <div class="card-body">
                    <div id="workplan-container" class="workplan-container">
                        <div class="no-data-message">
                            <i class="bi bi-info-circle" style="font-size: 48px;"></i>
                            <p class="mt-3">Silakan pilih Divisi dan Tahun kemudian klik "Load KPI Data" untuk menampilkan data KPI dan Work Plan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Work Plan Modal --}}
<div class="modal fade" id="workplanModal" tabindex="-1" aria-labelledby="workplanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workplanModalLabel">Add Work Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="workplanForm">
                    <input type="hidden" id="workplan_id" name="workplan_id">
                    <input type="hidden" id="kpi_type" name="kpi_type">
                    <input type="hidden" id="kpi_id" name="kpi_id">

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Activity Name <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="activity" name="activity" rows="2" required></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Duration (Working Days) <small class="text-muted">Mon-Fri</small></label>
                            <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" placeholder="Enter working days">
                            <small class="form-text text-muted">Working days only (Monday to Friday)</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="schedule_start" name="schedule_start">
                            <small class="form-text text-muted">Will auto-calculate end date</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date <small class="text-muted">(Auto-calculated)</small></label>
                            <input type="date" class="form-control" id="schedule_end" name="schedule_end">
                            <small class="form-text text-muted">Calculated based on working days</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="month-section-header">
                                <i class="bi bi-calendar-check"></i> Planning Schedule (Select Months)
                            </div>
                            <div class="month-container">
                                <div class="row g-2">
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_jan" name="plan_jan">
                                            <label class="form-check-label" for="plan_jan">
                                                <i class="bi bi-calendar3"></i> January
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_feb" name="plan_feb">
                                            <label class="form-check-label" for="plan_feb">
                                                <i class="bi bi-calendar3"></i> February
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_mar" name="plan_mar">
                                            <label class="form-check-label" for="plan_mar">
                                                <i class="bi bi-calendar3"></i> March
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_apr" name="plan_apr">
                                            <label class="form-check-label" for="plan_apr">
                                                <i class="bi bi-calendar3"></i> April
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_may" name="plan_may">
                                            <label class="form-check-label" for="plan_may">
                                                <i class="bi bi-calendar3"></i> May
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_jun" name="plan_jun">
                                            <label class="form-check-label" for="plan_jun">
                                                <i class="bi bi-calendar3"></i> June
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_jul" name="plan_jul">
                                            <label class="form-check-label" for="plan_jul">
                                                <i class="bi bi-calendar3"></i> July
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_aug" name="plan_aug">
                                            <label class="form-check-label" for="plan_aug">
                                                <i class="bi bi-calendar3"></i> August
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_sep" name="plan_sep">
                                            <label class="form-check-label" for="plan_sep">
                                                <i class="bi bi-calendar3"></i> September
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_oct" name="plan_oct">
                                            <label class="form-check-label" for="plan_oct">
                                                <i class="bi bi-calendar3"></i> October
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_nov" name="plan_nov">
                                            <label class="form-check-label" for="plan_nov">
                                                <i class="bi bi-calendar3"></i> November
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="plan_dec" name="plan_dec">
                                            <label class="form-check-label" for="plan_dec">
                                                <i class="bi bi-calendar3"></i> December
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3" style="display: none;">
                        <div class="col-md-12">
                            <div class="month-section-header realization-header">
                                <i class="bi bi-check2-square"></i> Realization (Select Months)
                            </div>
                            <div class="month-container">
                                <div class="row g-2">
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_jan" name="real_jan">
                                            <label class="form-check-label" for="real_jan">
                                                <i class="bi bi-check-circle"></i> January
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_feb" name="real_feb">
                                            <label class="form-check-label" for="real_feb">
                                                <i class="bi bi-check-circle"></i> February
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_mar" name="real_mar">
                                            <label class="form-check-label" for="real_mar">
                                                <i class="bi bi-check-circle"></i> March
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_apr" name="real_apr">
                                            <label class="form-check-label" for="real_apr">
                                                <i class="bi bi-check-circle"></i> April
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_may" name="real_may">
                                            <label class="form-check-label" for="real_may">
                                                <i class="bi bi-check-circle"></i> May
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_jun" name="real_jun">
                                            <label class="form-check-label" for="real_jun">
                                                <i class="bi bi-check-circle"></i> June
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_jul" name="real_jul">
                                            <label class="form-check-label" for="real_jul">
                                                <i class="bi bi-check-circle"></i> July
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_aug" name="real_aug">
                                            <label class="form-check-label" for="real_aug">
                                                <i class="bi bi-check-circle"></i> August
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_sep" name="real_sep">
                                            <label class="form-check-label" for="real_sep">
                                                <i class="bi bi-check-circle"></i> September
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_oct" name="real_oct">
                                            <label class="form-check-label" for="real_oct">
                                                <i class="bi bi-check-circle"></i> October
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_nov" name="real_nov">
                                            <label class="form-check-label" for="real_nov">
                                                <i class="bi bi-check-circle"></i> November
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="real_dec" name="real_dec">
                                            <label class="form-check-label" for="real_dec">
                                                <i class="bi bi-check-circle"></i> December
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="btnSaveWorkplan">
                    <i class="bi bi-save"></i> Save Work Plan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div class="loading-overlay" id="loadingOverlay">
    <div class="text-center text-white">
        <div class="spinner-border" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Loading data...</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: "{{ session('error') }}",
    });
</script>
@endif
@endsection

@section('js')
<script type="module" src="{{ asset('assets/js/app.js') }}"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/workplan.js') }}"></script>
@endsection