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
            background: #495057;
            color: white;
            padding: 8px 5px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 10px;
        }

        .workplan-table td {
            border: 1px solid #dee2e6;
            padding: 5px;
            vertical-align: middle;
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
            padding: 4px 8px;
            font-size: 11px;
            margin: 2px;
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .no-data-message {
            text-align: center;
            padding: 30px;
            color: #6c757d;
            font-style: italic;
        }

        .action-column {
            width: 100px;
            text-align: center;
        }

        .target-satuan-cell {
            background: #fff3cd;
            font-weight: 500;
        }

        .expand-btn {
            cursor: pointer;
            padding: 5px 10px;
            background: #28a745;
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
            background: rgba(0,0,0,0.5);
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
