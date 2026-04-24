@extends('layouts.master')

@section('title', 'User Submission | Budget Control')

@section('title-sub', 'Transactions')
@section('pagetitle', 'User Submission')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
        }

        .table-responsive {
            min-height: 150px;
        }

        .badge-status {
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
        }

        /* Modal error alert styling */
        #modalErrorAlert {
            position: sticky;
            top: 0;
            z-index: 1050;
            margin-bottom: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        #modalErrorAlert ul {
            margin-left: 1rem;
        }

        .modal-body {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        /* Invalid feedback styling */
        .invalid-feedback {
            display: block;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-image: none;
        }

        /* View Modal Styling */
        .form-control-plaintext {
            padding-top: 0.375rem;
            padding-bottom: 0.375rem;
            margin-bottom: 0;
            font-size: inherit;
            line-height: 1.5;
            border-bottom: 1px solid #dee2e6;
        }

        #viewModal .form-label {
            margin-bottom: 0.25rem;
            font-weight: 600;
            color: #495057;
        }

        /* Budget Validation Styling */
        .validation-error {
            background-color: #fff3cd;
            border-left: 4px solid #dc3545;
        }

        .alert-sm {
            font-size: 0.875rem;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff5f5 !important;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid">
            {{-- === PAGE TITLE & BREADCRUMB === --}}
            {{-- <div class="d-flex align-items-center mt-2 mb-2">
                <h6 class="mb-0 flex-grow-1">List Pengajuan</h6>
                <div class="flex-shrink-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Submission</li>
        </ol>
        </nav>
    </div>
</div> --}}

            {{-- === SUMMARY CARD === --}}
            <style>
                .stat-card {
                    border-left: 4px solid transparent;
                    border-radius: 14px;
                    transition: .15s ease-in-out;
                    min-height: 96px;
                }

                .stat-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
                }

                /* ✅ lebih bold */
                .stat-title {
                    font-size: .85rem;
                    margin-bottom: .25rem;
                    font-weight: 700;
                    /* tambah bold */
                    letter-spacing: .2px;
                }

                .stat-value {
                    font-weight: 800;
                    /* angka lebih tebal */
                    letter-spacing: .2px;
                    line-height: 1.1;
                }

                .stat-icon {
                    font-size: 1.75rem;
                    opacity: .95;
                    font-weight: 700;
                    /* kalau icon font support */
                }

                .text-purple {
                    color: #6610f2 !important;
                }
            </style>

            {{-- === FILTER SECTION === --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <select id="filterYear" class="form-select">
                                <option value="all">Semua Tahun</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select id="filterStatus" class="form-select">
                                <option value="all">Semua Status</option>
                                <option value="request">Request</option>
                                <option value="3">Disbursed</option>
                                <option value="4">Completed</option>
                                <option value="5">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary" id="btnFilter">
                                <i class="ri-filter-line"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-end gap-2">
                            <a href="{{ route('userSubmission.template') }}" class="btn btn-outline-info">
                                <i class="ri-download-line"></i> Template
                            </a>
                            <button type="button" class="btn btn-outline-primary" id="btnImport">
                                <i class="ri-upload-line"></i> Import
                            </button>
                            @if (isset($employment->job_level_id) && in_array($employment->job_level_id, [3, 4]))
                                <button type="button" class="btn btn-success" id="btnAddData">
                                    <i class="ri-add-line"></i> Add Data
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- === MAIN TABLE === --}}
            <div class="col-12">
                <div class="card card-h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="ri-file-list-3-line me-2"></i>Daftar Pengajuan</h5>
                            <div class="d-flex gap-2">
                                <span class="badge bg-light text-dark border">
                                    <i class="ri-file-add-line me-1"></i>Request: <strong id="newSubmissionCount">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                    </strong>
                                </span>
                                <span class="badge bg-light text-dark border">
                                    <i class="ri-time-line me-1 text-warning"></i>Disbursed: <strong id="progressCount">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                    </strong>
                                </span>
                                <span class="badge bg-light text-dark border">
                                    <i class="ri-checkbox-circle-line me-1 text-success"></i>Completed: <strong
                                        id="completionCount">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                    </strong>
                                </span>
                                <span class="badge bg-light text-dark border">
                                    <i class="ri-file-forbid-line me-1 text-danger"></i>Rejected: <strong
                                        id="rejectedCount">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                    </strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-box table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Date</th>
                                        <th>User Submitter</th>
                                        <th>Purpose</th>
                                        <th>Estimated Value</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            Loading data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex flex-wrap gap-4 align-items-center m-4">
                            <div class="flex-grow-1">
                                <p class="mb-0" id="paginationInfo">Showing 0 to 0 of 0 entries</p>
                            </div>
                            <div id="paginationLinks"></div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-semibold">Daftar Pengajuan Anggaran</h6>
                </div>

                <div class="card-body p-0">
                    
                </div>
            </div> --}}

        </div>
    </div>

    {{-- === VIEW DETAIL MODAL === --}}
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Submission Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Transaction Date</label>
                            <p class="form-control-plaintext" id="view_transaction_date">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Planned Usage Date</label>
                            <p class="form-control-plaintext" id="view_planned_usage_date">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">User</label>
                            <p class="form-control-plaintext" id="view_user_name">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Job Level</label>
                            <p class="form-control-plaintext" id="view_job_level">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Job Position</label>
                            <p class="form-control-plaintext" id="view_job_position">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Program</label>
                            <p class="form-control-plaintext" id="view_program">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Unit</label>
                            <p class="form-control-plaintext" id="view_unit">-</p>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Purpose</label>
                            <p class="form-control-plaintext" id="view_purpose">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estimated Amount</label>
                            <p class="form-control-plaintext" id="view_estimated_amount">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext" id="view_status">-</p>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Urgency</label>
                            <p class="form-control-plaintext" id="view_urgency">-</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Budget Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">Description of Goods/Service</th>
                                    <th width="15%">Budget Code</th>
                                    <th width="15%">Budget Value</th>
                                    <th width="10%">Unit</th>
                                    <th width="10%">Qty</th>
                                    <th width="12%">Price</th>
                                    <th width="13%">Total</th>
                                </tr>
                            </thead>
                            <tbody id="view_items_body">
                                <!-- Dynamic rows will be added here -->
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3" id="view_lpj_section" style="display: none;">
                        <div class="col-md-12">
                            <hr class="my-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">LPJ Detail</h6>
                                <button type="button" class="btn btn-sm btn-outline-info" id="view_lpj_open_detail_btn">
                                    <i class="ri-file-text-line me-1"></i>Open LPJ Detail
                                </button>
                            </div>

                            <div class="alert alert-info" id="view_lpj_status_alert">
                                <strong>Status LPJ:</strong> <span id="view_lpj_status_text">-</span>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Submission Date</label>
                                    <p class="form-control-plaintext" id="view_lpj_submission_date">-</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Realization Date</label>
                                    <p class="form-control-plaintext" id="view_lpj_realization_date">-</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Estimated Amount</label>
                                    <p class="form-control-plaintext" id="view_lpj_estimated_amount">-</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Realization Amount</label>
                                    <p class="form-control-plaintext" id="view_lpj_actual_amount">-</p>
                                </div>
                            </div>

                            <div class="card border mb-3">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="ri-attachment-2 me-2"></i>Proof of Payment</h6>
                                    <a href="#" class="btn btn-sm btn-outline-primary d-none" id="view_lpj_proof_open_link"
                                        target="_blank" rel="noopener">
                                        <i class="ri-external-link-line me-1"></i>Open File
                                    </a>
                                </div>
                                <div class="card-body" id="view_lpj_proof_preview_body">
                                    <p class="text-muted mb-0">No proof file uploaded.</p>
                                </div>
                            </div>

                            <h6 class="mb-3">LPJ Realization Items</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="25%">Description</th>
                                            <th class="text-center" width="10%">Est. Qty</th>
                                            <th class="text-end" width="15%">Est. Price</th>
                                            <th class="text-end" width="15%">Est. Total</th>
                                            <th class="text-center" width="10%">Real. Qty</th>
                                            <th class="text-end" width="10%">Real. Price</th>
                                            <th class="text-end" width="10%">Real. Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view_lpj_items_body">
                                        <!-- Dynamic LPJ rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3" id="view_approval_section" style="display: none;">
                        <div class="col-md-12">
                            <hr class="my-4">
                            <h6 class="mb-3">Approval History</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Level</th>
                                            <th>Approver</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view_approval_body">
                                        <!-- Dynamic approval rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- === IMPORT MODAL (Template / MacframeGA) === --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="ri-upload-cloud-2-line me-2"></i>Import Submissions
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- === Import Type Selector === --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Pilih Jenis Import</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="d-block border rounded-3 p-3 cursor-pointer import-type-card" id="cardTemplate" for="importTypeTemplate" style="cursor:pointer;">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input mt-0" type="radio" name="import_type" id="importTypeTemplate" value="template" checked>
                                        <div>
                                            <div class="fw-semibold"><i class="ri-file-excel-line text-success me-1"></i>Template Sistem</div>
                                            <small class="text-muted">File template yang diunduh dari sistem ini</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="d-block border rounded-3 p-3 cursor-pointer import-type-card" id="cardMacframe" for="importTypeMacframe" style="cursor:pointer;">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input mt-0" type="radio" name="import_type" id="importTypeMacframe" value="macframe">
                                        <div>
                                            <div class="fw-semibold"><i class="ri-file-transfer-line text-primary me-1"></i>MacframeGA</div>
                                            <small class="text-muted">File export dari aplikasi MacframeGA</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- === Template Import Section === --}}
                    <div id="sectionTemplate">
                        <form id="importForm" enctype="multipart/form-data">
                            <div class="alert alert-info mb-3">
                                <i class="ri-information-line me-2"></i>
                                Gunakan template resmi sistem.
                                <a href="{{ route('userSubmission.template') }}" class="fw-bold">Download Template</a>.
                            </div>
                            <div class="mb-3">
                                <label for="importFile" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                                <input type="file" class="form-control" id="importFile" name="file" accept=".xlsx,.xls,.csv">
                            </div>
                            <div id="importResults" class="d-none">
                                <hr>
                                <h6>Hasil Import:</h6>
                                <div id="importMessage" class="alert mb-2"></div>
                                <ul id="importErrors" class="text-danger small"></ul>
                            </div>
                        </form>
                    </div>

                    {{-- === MacframeGA Import Section === --}}
                    <div id="sectionMacframe" class="d-none">
                        <div class="alert alert-primary mb-3">
                            <i class="ri-information-line me-2"></i>
                            Upload file Excel dari <strong>MacframeGA</strong>. Setelah diproses, Anda akan diminta memilih <strong>Program ID</strong> sebelum data disimpan.
                        </div>
                        <form id="macframeUploadForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="macframeFile" class="form-label">File MacframeGA (.xlsx, .xls)</label>
                                <input type="file" class="form-control" id="macframeFile" name="file" accept=".xlsx,.xls">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    {{-- Shown when type = template --}}
                    <button type="submit" class="btn btn-primary" id="btnDoImport" form="importForm">
                        <i class="ri-upload-line me-1"></i> Mulai Import
                    </button>
                    {{-- Shown when type = macframe --}}
                    <button type="button" class="btn btn-primary d-none" id="btnProcessMacframe">
                        <i class="ri-search-line me-1"></i> Proses & Preview
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- === MACFRAME PREVIEW MODAL === --}}
    <div class="modal fade" id="macframePreviewModal" tabindex="-1" aria-labelledby="macframePreviewModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="macframePreviewModalLabel">
                        <i class="ri-file-transfer-line me-2"></i>Preview Data MacframeGA — Pilih Program ID
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Program & Metadata form --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="mf_transaction_date">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Program ID (KPI Workplan) <span class="text-danger">*</span></label>
                            <select class="form-select" id="mf_program_id" name="mf_program_id">
                                <option value="">-- Pilih Program --</option>
                                @foreach ($workplans as $workplan)
                                    <option value="{{ $workplan->id }}">
                                        {{ $workplan->activity }} ({{ $workplan->year }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted">Semua baris data dari file ini akan dikaitkan dengan program yang dipilih.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Purpose <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mf_purpose" placeholder="Tujuan pengajuan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Urgency <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mf_urgency" placeholder="Urgensi pengajuan">
                        </div>
                    </div>

                    {{-- Alert for unresolved units --}}
                    <div id="mf_unit_warning" class="alert alert-warning d-none">
                        <i class="ri-error-warning-line me-2"></i>
                        <strong>Perhatian:</strong> Beberapa unit tidak ditemukan di master data (ditandai <span class="badge bg-warning text-dark">?</span>). Data tetap bisa disimpan namun unit akan kosong. Silakan tambahkan unit terlebih dahulu jika diperlukan.
                    </div>

                    {{-- Preview table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="22%">Nama Barang/Jasa</th>
                                    <th width="12%">Purpose</th>
                                    <th width="12%">Urgency</th>
                                    <th width="15%">Budget Item <span class="text-danger">*</span></th>
                                    <th width="8%">Unit</th>
                                    <th width="7%" class="text-center">Qty</th>
                                    <th width="10%" class="text-end">Harga</th>
                                    <th width="10%" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="mf_preview_body">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada data</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="6" class="text-end">Grand Total:</td>
                                    <td colspan="2" class="text-end" id="mf_grand_total">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-arrow-left-line me-1"></i> Kembali
                    </button>
                    <button type="button" class="btn btn-success" id="btnConfirmMacframe">
                        <i class="ri-save-line me-1"></i> Konfirmasi & Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- === ADD/EDIT MODAL === --}}
    <div class="modal fade" id="submissionModal" tabindex="-1" aria-labelledby="submissionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="submissionModalLabel">Add Submission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="submissionForm">
                    <input type="hidden" id="submissionId" name="id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">User</label>
                                <input type="text" class="form-control" id="userName"
                                    value="{{ Auth::user()->name }}" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Job Level <span class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    @foreach ($jobLevels as $level)
                                        <option value="{{ $level->id }}"
                                            {{ optional($employment ?? null)->job_level_id == $level->id ? 'selected' : '' }}>
                                            {{ $level->job_level_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <input type="hidden" id="jobLevel" name="job_level_id"
                                    value="{{ optional($employment ?? null)->job_level_id }}">

                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Job Position <span class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    <option value="">Select Job Position</option>
                                    @foreach ($jobPositions as $position)
                                        <option value="{{ $position->id }}"
                                            {{ optional($employment ?? null)->job_position_id == $position->id ? 'selected' : '' }}>
                                            {{ $position->job_position_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <input type="hidden" id="jobPosition" name="job_position_id"
                                    value="{{ optional($employment ?? null)->job_position_id }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Request Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="transactionDate" name="transaction_date"
                                    value="{{ date('Y-m-d') }}" required readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Usage Date</label>
                                <input type="date" class="form-control" id="plannedUsageDate"
                                    name="planned_usage_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Program ID <span class="text-danger">*</span></label>
                                <select class="form-select" id="programId" name="program_id" required>
                                    <option value="">Select Program</option>
                                    @foreach ($workplans as $workplan)
                                        @if ($workplan->year == date('Y'))
                                            <option value="{{ $workplan->id }}">{{ $workplan->activity }} -
                                                {{ $workplan->year }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Purpose <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="purpose" name="purpose" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Estimated Value (Total)</label>
                                <input type="text" class="form-control bg-light" id="estimatedValue" readonly>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Budget Items</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="btnAddItem">
                                <i class="ri-add-line"></i> Add Item
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="20%">Description of Goods/Service <span class="text-danger">*</span>
                                        </th>
                                        <th width="15%">Budget ID <span class="text-danger">*</span></th>
                                        <th width="15%">Budget Value</th>
                                        <th width="10%">Unit <span class="text-danger">*</span></th>
                                        <th width="10%">Qty <span class="text-danger">*</span></th>
                                        <th width="15%">Price <span class="text-danger">*</span></th>
                                        <th width="12%">Total</th>
                                        <th width="3%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Dynamic rows will be added here -->
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Urgency <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="urgency" name="urgency" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnSave">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Timeline wrapper */
        .tracking-timeline {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 18px;
            padding: 6px 4px;
        }

        /* Each item */
        .tt-item {
            position: relative;
            display: grid;
            grid-template-columns: 44px 1fr;
            gap: 12px;
            align-items: start;
        }

        /* Vertical line */
        .tt-item::before {
            content: "";
            position: absolute;
            left: 22px;
            /* center of icon col */
            top: 44px;
            /* below icon */
            bottom: -18px;
            /* extend to next item */
            width: 2px;
            background: rgba(0, 0, 0, .12);
        }

        .tt-item:last-child::before {
            display: none;
        }

        /* Icon circle */
        .tt-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: #fff;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
            position: relative;
        }

        /* Dot inside icon (simple) */
        .tt-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .92);
            display: inline-block;
        }

        /* Content card feel */
        .tt-content {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 12px;
            padding: 12px 12px;
        }

        .sts {
            cursor: pointer;
        }
    </style>
    <div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trackingModalLabel">Status Tracking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <!-- Timeline -->
                    <div class="tracking-timeline" id="timeline"></div>
                    <!-- /Timeline -->

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- === APPROVAL REJECTION MODAL === --}}
    <div class="modal fade" id="rejectApprovalModal" tabindex="-1" aria-labelledby="rejectApprovalModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectApprovalModalLabel">
                        <i class="ri-close-circle-line me-2"></i>Reject Transaction
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rejectTransactionId">
                    <div class="alert alert-warning">
                        <i class="ri-error-warning-line me-2"></i>
                        You are about to reject this transaction. Please provide a reason for rejection.
                    </div>
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label fw-semibold">Rejection Reason <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" rows="4" placeholder="Enter your rejection reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmRejectTransaction()">
                        <i class="ri-close-circle-line me-1"></i>Confirm Reject
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- === APPROVAL DETAIL MODAL === --}}
    <div class="modal fade" id="approvalDetailModal" tabindex="-1" aria-labelledby="approvalDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="approvalDetailModalLabel">
                        <i class="ri-file-list-3-line me-2"></i>Transaction Detail for Approval
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="approvalTransactionId">

                    {{-- Transaction Info --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Basic Information</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="fw-semibold" style="width: 130px;">Date</td>
                                            <td id="approval_transaction_date">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Submitter</td>
                                            <td id="approval_user_name">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Purpose</td>
                                            <td id="approval_purpose">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Urgency</td>
                                            <td id="approval_urgency">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Amount Information</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="fw-semibold" style="width: 130px;">Estimated</td>
                                            <td class="text-success fw-bold" id="approval_estimated_amount">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Approval Level</td>
                                            <td id="approval_level_info">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="card border">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="ri-list-check me-2"></i>Transaction Items</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Item Description</th>
                                            <th>Budget Code</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="approval_items_body">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No items</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" onclick="openRejectModal()">
                        <i class="ri-close-circle-line me-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-success" onclick="approveFromDetailModal()">
                        <i class="ri-check-line me-1"></i>Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- === LPJ (Laporan Pertanggungjawaban) MODAL === --}}
    <div class="modal fade" id="lpjModal" tabindex="-1" aria-labelledby="lpjModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="lpjModalLabel">
                        <i class="ri-file-text-line me-2"></i>Laporan Pertanggungjawaban (LPJ)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="lpjForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="lpj_transaction_id" name="transaction_id">

                        {{-- Alert for errors --}}
                        <div id="lpjErrorAlert" class="alert alert-danger d-none" role="alert"></div>

                        {{-- Transaction Info (Read-only) --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Transaction Information</h6>
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <td class="fw-semibold" style="width: 140px;">User</td>
                                                <td id="lpj_user_name">-</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">Submitter</td>
                                                <td id="lpj_job_level">-</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">Program ID</td>
                                                <td id="lpj_program_id">-</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">Purpose</td>
                                                <td id="lpj_purpose">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Date & Value</h6>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">Submission Date</label>
                                                <input type="date" class="form-control" id="lpj_submission_date"
                                                    name="submission_date" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">Submission Value</label>
                                                <input type="text" class="form-control-plaintext fw-bold text-success"
                                                    id="lpj_submission_value" readonly>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">Realization Date</label>
                                                <input type="date" class="form-control" id="lpj_realization_date"
                                                    name="realization_date" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">Realization Value</label>
                                                <input type="text" class="form-control-plaintext fw-bold"
                                                    id="lpj_realization_value" readonly value="Rp 0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Items Table --}}
                        <div class="card border mb-3">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="ri-list-check me-2"></i>Realization Report</h6>
                                <span class="badge bg-info" id="lpj_variance_badge"></span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered mb-0" id="lpjItemsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th rowspan="2" class="align-middle text-center"
                                                    style="width: 200px;">Description of Goods/Service</th>
                                                <th colspan="5" class="text-center bg-secondary bg-opacity-10">
                                                    Submission</th>
                                                <th colspan="4" class="text-center bg-success bg-opacity-10">
                                                    Realization Report</th>
                                            </tr>
                                            <tr>
                                                <th class="bg-secondary bg-opacity-10">Budget ID</th>
                                                <th class="bg-secondary bg-opacity-10 text-center">Unit</th>
                                                <th class="bg-secondary bg-opacity-10 text-center">Qty</th>
                                                <th class="bg-secondary bg-opacity-10 text-end">Price</th>
                                                <th class="bg-secondary bg-opacity-10 text-end">Total</th>
                                                <th class="bg-success bg-opacity-10 text-center">Unit</th>
                                                <th class="bg-success bg-opacity-10 text-center" style="width: 80px;">Qty
                                                </th>
                                                <th class="bg-success bg-opacity-10 text-end" style="width: 150px;">Price
                                                </th>
                                                <th class="bg-success bg-opacity-10 text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="lpj_items_body">
                                            <tr>
                                                <td colspan="10" class="text-center text-muted">Loading items...</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="5" class="text-end fw-bold">Submission Total:</td>
                                                <td class="text-end fw-bold" id="lpj_submission_total">Rp 0</td>
                                                <td colspan="3" class="text-end fw-bold">Realization Total:</td>
                                                <td class="text-end fw-bold" id="lpj_realization_total">Rp 0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Proof of Payment --}}
                        <div class="card border">
                            <div class="card-body">
                                <label class="form-label fw-semibold">
                                    <i class="ri-attachment-2 me-1"></i>Attach Proof of Payment
                                </label>
                                <input type="file" class="form-control" id="lpj_proof_of_payment"
                                    name="proof_of_payment" accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted">Accepted formats: JPG, PNG, PDF (Max: 5MB)</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="reset" class="btn btn-outline-warning" onclick="resetLpjForm()">
                            <i class="ri-refresh-line me-1"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-success" id="btnSubmitLpj">
                            <i class="ri-save-line me-1"></i>Submit LPJ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- === LPJ VIEW DETAIL MODAL === --}}
    <div class="modal fade" id="lpjViewModal" tabindex="-1" aria-labelledby="lpjViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="lpjViewModalLabel">
                        <i class="ri-file-text-line me-2"></i>LPJ Detail
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="lpj_view_id">

                    {{-- LPJ Status --}}
                    <div class="alert alert-info" id="lpj_view_status_alert">
                        <strong>Status:</strong> <span id="lpj_view_status_text">-</span>
                    </div>

                    {{-- LPJ Info --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-semibold" style="width: 140px;">Submission Date</td>
                                    <td id="lpj_view_submission_date">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Realization Date</td>
                                    <td id="lpj_view_realization_date">-</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-semibold" style="width: 140px;">Submission Value</td>
                                    <td id="lpj_view_submission_value" class="fw-bold text-success">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Realization Value</td>
                                    <td id="lpj_view_realization_value" class="fw-bold">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Proof of Payment Preview --}}
                    <div class="card border mb-3" id="lpj_proof_preview_card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="ri-attachment-2 me-2"></i>Proof of Payment</h6>
                            <a href="#" class="btn btn-sm btn-outline-primary d-none" id="lpj_proof_open_link"
                                target="_blank" rel="noopener">
                                <i class="ri-external-link-line me-1"></i>Open File
                            </a>
                        </div>
                        <div class="card-body" id="lpj_proof_preview_body">
                            <p class="text-muted mb-0">No proof file uploaded.</p>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="card border mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="ri-list-check me-2"></i>Items</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Description</th>
                                            <th class="text-center">Est. Qty</th>
                                            <th class="text-end">Est. Price</th>
                                            <th class="text-end">Est. Total</th>
                                            <th class="text-center">Real. Qty</th>
                                            <th class="text-end">Real. Price</th>
                                            <th class="text-end">Real. Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lpj_view_items_body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Approval Timeline --}}
                    <div class="card border">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="ri-time-line me-2"></i>Approval Timeline</h6>
                        </div>
                        <div class="card-body">
                            <div class="tracking-timeline" id="lpj_approval_timeline">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Global variables
        let currentPage = 1;
        let itemRowCounter = 0;
        const budgetCodes = @json($budgetCodes);
        const units = @json($units);
        let availableBudgetItems = [];

        $(document).ready(function() {
            // Load summary data on page load
            loadSummary();

            // Load data on page load
            loadData();

            // Load LPJ approval counts
            loadLpjApprovalCounts();

            // Filter button
            $('#btnFilter').on('click', function() {
                currentPage = 1;
                loadData();
                loadSummary(); // Reload summary when filter changes
            });

            // Add data button
            $('#btnAddData').on('click', function() {
                resetForm();
                $('#submissionModalLabel').text('Add Submission');
                $('#submissionModal').modal('show');
                addItemRow();
            });

            // Import button
            $('#btnImport').on('click', function() {
                $('#importForm')[0].reset();
                $('#importResults').addClass('d-none');
                $('#importModal').modal('show');
            });

            // Import form submit
            $('#importForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const btn = $('#btnDoImport');
                const originalHtml = btn.html();

                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Importing...'
                    );

                $.ajax({
                    url: `{{ route('userSubmission.import') }}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html(originalHtml);

                        $('#importResults').removeClass('d-none');
                        const msgDiv = $('#importMessage');
                        const errorList = $('#importErrors');

                        msgDiv.removeClass('alert-success alert-danger').addClass(response
                            .success ? 'alert-success' : 'alert-danger').text(response
                            .message);
                        errorList.empty();

                        if (response.errors && response.errors.length > 0) {
                            response.errors.forEach(err => {
                                errorList.append(`<li>${err}</li>`);
                            });
                        }

                        if (response.success) {
                            loadData();
                            loadSummary();
                            let htmlContent = response.message;
                            if (response.errors && response.errors.length > 0) {
                                htmlContent += '<br><br><div style="max-height: 150px; overflow-y: auto; text-align: left;"><ul style="color: #dc3545; font-size: 0.85em;">';
                                response.errors.forEach(err => {
                                    htmlContent += `<li>${err}</li>`;
                                });
                                htmlContent += '</ul></div>';
                            }
                            Swal.fire({
                                icon: response.errors && response.errors.length > 0 ? 'warning' : 'success',
                                title: 'Proses Import Selesai',
                                html: htmlContent,
                                confirmButtonColor: '#28a745'
                            }).then((result) => {
                                $('#importModal').modal('hide');
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(originalHtml);
                        let message = 'An error occurred during import.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        $('#importResults').removeClass('d-none');
                        $('#importMessage').removeClass('alert-success').addClass(
                            'alert-danger').text(message);
                        $('#importErrors').empty();
                    }
                });
            });

            // Add item row button
            $('#btnAddItem').on('click', function() {
                addItemRow();
            });

            // Form submit
            $('#submissionForm').on('submit', function(e) {
                e.preventDefault();
                saveSubmission();
            });

            // Modal hidden event
            $('#submissionModal').on('hidden.bs.modal', function() {
                resetForm();
            });

            // Cascading dropdown: Job Level -> Job Position
            $('#jobLevel').on('change', function() {
                const jobLevelId = $(this).val();
                $('#jobPosition').html('<option value="">Loading...</option>').prop('disabled', true);
                $('#programId').html('<option value="">Select Program</option>').prop('disabled', true);

                if (jobLevelId) {
                    loadJobPositions(jobLevelId);
                    loadPrograms(jobLevelId);
                } else {
                    $('#jobPosition').html('<option value="">Select Job Position</option>').prop('disabled',
                        false);
                    $('#programId').html('<option value="">Select Program</option>').prop('disabled',
                        false);
                }
            });

            // Cascading dropdown: Program ID -> Budget ID
            $('#programId').on('change', function() {
                const programId = $(this).val();

                if (programId) {
                    loadBudgetItems(programId);
                }
            });
        });

        // Load job positions based on job level
        function loadJobPositions(jobLevelId) {
            let urlJobPositions = `{{ route('userSubmission.jobPositions', ':jobLevelId') }}`.replace(':jobLevelId',
                jobLevelId);
            $.ajax({
                url: urlJobPositions,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">Select Job Position</option>';
                        response.data.forEach(function(position) {
                            options +=
                                `<option value="${position.id}">${position.job_position_name}</option>`;
                        });
                        $('#jobPosition').html(options).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    showAlert('Error loading job positions', 'danger');
                    $('#jobPosition').html('<option value="">Error loading positions</option>').prop('disabled',
                        false);
                }
            });
        }

        // Load programs based on job level
        function loadPrograms(jobLevelId) {
            let urlPrograms = `{{ route('userSubmission.programs', ':jobLevelId') }}`;
            urlPrograms = urlPrograms.replace(':jobLevelId', jobLevelId);
            $.ajax({
                url: urlPrograms,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">Select Program</option>';
                        response.data.forEach(function(program) {
                            options += `<option value="${program.id}">${program.label}</option>`;
                        });
                        $('#programId').html(options).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    showAlert('Error loading programs', 'danger');
                    $('#programId').html('<option value="">Error loading programs</option>').prop('disabled',
                        false);
                }
            });
        }

        // Load budget items based on program ID
        function loadBudgetItems(programId) {
            let urlBudgetItems = `{{ route('userSubmission.budgetItems', ':programId') }}`.replace(':programId',
                programId);
            $.ajax({
                url: urlBudgetItems,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        availableBudgetItems = response.data;
                        // Update all budget select dropdowns in item rows
                        updateAllBudgetSelects();
                    }
                },
                error: function(xhr) {
                    console.log(xhr);

                    showAlert('Error loading budget items', 'danger');
                    availableBudgetItems = [];
                    updateAllBudgetSelects();
                }
            });
        }

        // Load summary data function
        function loadSummary() {
            const year = $('#filterYear').val();
            $.ajax({
                url: `{{ route('userSubmission.summary') }}`,
                type: 'GET',
                data: {
                    year: year
                },
                success: function(response) {
                    if (response.success) {
                        // Request Tab (submission + progress + approved)
                        $('#newSubmissionCount').text(response.data.requestCount);
                        // Disbursed Tab (paid)
                        $('#progressCount').text(response.data.paid);

                        $('#completionCount').text(response.data.completion);
                        $("#rejectedCount").text(response.data.rejected);
                        $('#totalSubmissionCount').text(response.data.totalSubmission);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading summary:', xhr);
                    $('#newSubmissionCount').text('0');
                    $('#progressCount').text('0');
                    $('#paidCount').text('0');
                    $('#completionCount').text('0');
                    $('#rejectedCount').text('0');
                    $('#totalSubmissionCount').text('0');
                }
            });
        }

        // Update all budget select dropdowns
        function updateAllBudgetSelects() {
            let options = '<option value="">Select Budget</option>';
            availableBudgetItems.forEach(function(item) {
                options +=
                    `<option value="${item.id}" data-value="${item.total}" data-code="${item.budget_code}" data-unit-id="${item.unit_id || ''}" data-unit-name="${item.unit_name || ''}">${item.label}</option>`;
            });

            $('.budget-select').each(function() {
                const currentValue = $(this).val();
                $(this).html(options);
                if (currentValue) {
                    $(this).val(currentValue);
                }
            });
        }

        // Load data function
        function loadData() {
            const year = $('#filterYear').val();
            const status = $('#filterStatus').val();
            let url = `{{ route('userSubmission.data') }}`;

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    year: year,
                    status: status,
                    page: currentPage
                },
                success: function(response) {
                    console.log(response);

                    if (response.success) {
                        renderTable(response.data);
                        renderPagination(response.data);
                    }
                },
                error: function(xhr) {
                    showAlert('Error loading data', 'danger');
                }
            });
        }

        // Render table
        function renderTable(data) {
            let html = '';

            if (data.data.length === 0) {
                html = '<tr><td colspan="7" class="text-center">No data available</td></tr>';
            } else {
                data.data.forEach((item, index) => {
                    const rowNumber = (data.current_page - 1) * data.per_page + index + 1;
                    const hasLpj = item.lpj_submission != null;
                    const lpjStatus = hasLpj ? item.lpj_submission.status_approval : null;
                    const canSubmitLpj = item.can_submit_lpj || (!hasLpj && (item.status == 2 || item.status == 3));

                    html += `
                <tr>
                    <td>${rowNumber}</td>
                    <td>${formatDate(item.transaction_date)}</td>
                    <td>${item.user_name}</td>
                    <td>${item.purpose}</td>
                    <td>${formatCurrency(item.estimated_amount)}</td>
                    <td>${getStatusBadge(item.status, item.id)}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-info" onclick="viewSubmission(${item.id})" title="View Detail">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="viewPdf(${item.id})" title="View PDF">
                                <i class="ri-file-pdf-2-line"></i>
                            </button>
                            ${item.can_edit ? `
                                                <button type="button" class="btn btn-warning" onclick="editSubmission(${item.id})" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="deleteSubmission(${item.id})" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            ` : ''}
                            ${item.can_approve ? `
                                                <button type="button" class="btn btn-success" onclick="approveSubmission(${item.id})" title="Approve">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="rejectSubmission(${item.id})" title="Reject">
                                                    <i class="ri-close-line"></i>
                                                </button>
                                            ` : ''}
                            ${canSubmitLpj ? `
                                                <button type="button" class="btn btn-success" onclick="openLpjModal(${item.id})" title="Create LPJ">
                                                    <i class="ri-file-text-line"></i> LPJ
                                                </button>
                                            ` : ''}
                            ${hasLpj ? `
                                                <button type="button" class="btn btn-outline-${getLpjStatusColor(lpjStatus)}" onclick="viewLpjDetail(${item.id})" title="View LPJ">
                                                    <i class="ri-file-text-line"></i> ${getLpjStatusLabel(lpjStatus)}
                                                </button>
                                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
                });
            }

            $('#tableBody').html(html);
        }

        // Render pagination
        function renderPagination(data) {
            let paginationInfo = `Showing ${data.from || 0} to ${data.to || 0} of ${data.total || 0} entries`;
            $('#paginationInfo').text(paginationInfo);

            let paginationHtml = '<ul class="pagination mb-0">';

            // Previous button
            paginationHtml += `
        <li class="page-item ${!data.prev_page_url ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${data.current_page - 1}); return false;">«</a>
        </li>
    `;

            // Page numbers
            for (let i = 1; i <= data.last_page; i++) {
                paginationHtml += `
            <li class="page-item ${i === data.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
            </li>
        `;
            }

            // Next button
            paginationHtml += `
        <li class="page-item ${!data.next_page_url ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${data.current_page + 1}); return false;">»</a>
        </li>
    `;

            paginationHtml += '</ul>';
            $('#paginationLinks').html(paginationHtml);
        }

        // LPJ Status helpers
        function getLpjStatusColor(status) {
            const colors = {
                'pending': 'warning',
                'in_progress': 'info',
                'approved': 'success',
                'rejected': 'danger'
            };
            return colors[status] || 'secondary';
        }

        function getLpjStatusLabel(status) {
            const labels = {
                'pending': 'LPJ Pending',
                'in_progress': 'LPJ In Progress',
                'approved': 'LPJ Approved',
                'rejected': 'LPJ Rejected'
            };
            return labels[status] || 'View LPJ';
        }

        // Change page function
        function changePage(page) {
            currentPage = page;
            loadData();
        }

        // Add item row
        function addItemRow() {
            itemRowCounter++;

            // Prepare budget options from available budget items
            let budgetOptions = '<option value="">Select Budget</option>';
            availableBudgetItems.forEach(function(item) {
                budgetOptions +=
                    `<option value="${item.id}" data-value="${item.total}" data-code="${item.budget_code}" data-unit-id="${item.unit_id || ''}" data-unit-name="${item.unit_name || ''}">${item.label}</option>`;
            });

            // Prepare unit options
            let unitOptions = '<option value="">Select Unit</option>';
            units.forEach(function(unit) {
                unitOptions += `<option value="${unit.id}">${unit.unit || unit.unit_name}</option>`;
            });

            let html = `
        <tr data-row="${itemRowCounter}">
            <td>
                <input type="text" class="form-control form-control-sm goods-name-input" name="items[${itemRowCounter}][goods_service_name]" placeholder="Enter goods/service name" required>
            </td>
            <td>
                <select class="form-select form-select-sm budget-select" name="items[${itemRowCounter}][budget_id]" data-row="${itemRowCounter}" required>
                    ${budgetOptions}
                </select>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm budget-value bg-light" readonly>
            </td>
            <td>
                <select class="form-select form-select-sm unit-select" name="items[${itemRowCounter}][unit_id]" required>
                    ${unitOptions}
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm qty-input" name="items[${itemRowCounter}][quantity]" min="1" value="1" data-row="${itemRowCounter}" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm price-input" name="items[${itemRowCounter}][price]" data-row="${itemRowCounter}" placeholder="0" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm total-input bg-light" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeItemRow(${itemRowCounter})">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        </tr>
    `;
            $('#itemsTableBody').append(html);

            // Add event listeners
            $(`[data-row="${itemRowCounter}"] .budget-select`).on('change', function() {
                updateBudgetValue($(this));
            });

            $(`[data-row="${itemRowCounter}"] .qty-input, [data-row="${itemRowCounter}"] .price-input`).on('input',
                function() {
                    calculateRowTotal($(this).data('row'));
                });

            // Initialize price input with thousand separator
            $(`[data-row="${itemRowCounter}"] .price-input`).on('input', function() {
                formatPriceInput($(this));
            });
        }

        // Remove item row
        function removeItemRow(rowId) {
            $(`tr[data-row="${rowId}"]`).remove();
            calculateEstimatedValue();
        }

        // Update budget value when budget is selected
        function updateBudgetValue(selectElement) {
            const row = selectElement.data('row');
            const selectedOption = selectElement.find('option:selected');
            const budgetValue = selectedOption.data('value');
            const unitId = selectedOption.data('unit-id');
            const unitName = selectedOption.data('unit-name');

            $(`tr[data-row="${row}"] .budget-value`).val(formatCurrency(budgetValue));

            // Auto-fill unit from the selected budget item
            const unitSelect = $(`tr[data-row="${row}"] .unit-select`);
            $(`tr[data-row="${row}"] .unit-hidden-input`).remove();
            if (unitId) {
                unitSelect.val(unitId).prop('disabled', true);
                unitSelect.after(
                    `<input type="hidden" class="unit-hidden-input" name="${unitSelect.attr('name')}" value="${unitId}">`
                    );
            } else {
                unitSelect.prop('disabled', false);
            }
        }

        // Format price input with thousand separator
        function formatPriceInput(input) {
            let value = input.val().replace(/[^\d]/g, '');
            input.val(formatNumber(value));
            calculateRowTotal(input.data('row'));
        }

        // Calculate row total
        function calculateRowTotal(rowId) {
            const qty = parseFloat($(`tr[data-row="${rowId}"] .qty-input`).val()) || 0;
            const price = parseFloat($(`tr[data-row="${rowId}"] .price-input`).val().replace(/[^\d]/g, '')) || 0;
            const total = qty * price;

            // Get budget value for this row
            const budgetValueStr = $(`tr[data-row="${rowId}"] .budget-value`).val();
            const budgetValue = parseFloat(budgetValueStr.replace(/[^\d]/g, '')) || 0;

            // Set total value
            $(`tr[data-row="${rowId}"] .total-input`).val(formatCurrency(total));

            // Validate: check if total exceeds budget value
            const row = $(`tr[data-row="${rowId}"]`);
            if (total > budgetValue && budgetValue > 0) {
                // Add error styling
                row.find('.total-input').addClass('is-invalid');
                row.find('.price-input').addClass('is-invalid');
                row.find('.qty-input').addClass('is-invalid');

                // Add or update error message
                row.find('.validation-error').remove();
                row.find('td:last').before(`
                    <td colspan="8" class="validation-error">
                        <div class="alert alert-danger alert-sm mb-0 py-1 px-2">
                            <i class="ri-error-warning-line me-1"></i>
                            <small><strong>Warning:</strong> Total (${formatCurrency(total)}) exceeds Budget Value (${formatCurrency(budgetValue)})</small>
                        </div>
                    </td>
                `);
            } else {
                // Remove error styling
                row.find('.total-input').removeClass('is-invalid');
                row.find('.price-input').removeClass('is-invalid');
                row.find('.qty-input').removeClass('is-invalid');
                row.find('.validation-error').remove();
            }

            calculateEstimatedValue();
        }

        // Calculate estimated value (sum of all item totals)
        function calculateEstimatedValue() {
            let total = 0;
            $('.total-input').each(function() {
                const value = parseFloat($(this).val().replace(/[^\d]/g, '')) || 0;
                total += value;
            });
            $('#estimatedValue').val(formatCurrency(total));
        }

        // Save submission
        function saveSubmission() {
            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#modalErrorAlert').remove();

            // Validate budget values before submit
            let hasExceededBudget = false;
            let exceededItems = [];

            $('#itemsTableBody tr').each(function(index) {
                const row = $(this);
                const qty = parseFloat(row.find('.qty-input').val()) || 0;
                const price = parseFloat(row.find('.price-input').val().replace(/[^\d]/g, '')) || 0;
                const total = qty * price;
                const budgetValueStr = row.find('.budget-value').val();
                const budgetValue = parseFloat(budgetValueStr.replace(/[^\d]/g, '')) || 0;
                const goodsName = row.find('.goods-name-input').val() || `Item ${index + 1}`;

                if (total > budgetValue && budgetValue > 0) {
                    hasExceededBudget = true;
                    exceededItems.push({
                        name: goodsName,
                        total: formatCurrency(total),
                        budget: formatCurrency(budgetValue)
                    });
                }
            });

            // If any item exceeds budget, show error and prevent submit
            if (hasExceededBudget) {
                let errorMessage = '<strong>Budget Validation Failed!</strong><br><br>';
                errorMessage += 'The following items exceed their budget values:<br><ul class="mb-0 mt-2">';
                exceededItems.forEach(function(item) {
                    errorMessage +=
                        `<li><strong>${item.name}:</strong> Total ${item.total} exceeds Budget ${item.budget}</li>`;
                });
                errorMessage +=
                    '</ul><br><small class="text-muted">Please adjust the quantity or price to match the available budget.</small>';

                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errorMessage,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK, I\'ll Fix It'
                });
                return false;
            }

            const submissionId = $('#submissionId').val();
            const url = submissionId ?
                `{{ route('userSubmission.update', ':id') }}`.replace(':id', submissionId) :
                `{{ route('userSubmission.store') }}`;
            const method = submissionId ? 'PUT' : 'POST';

            // Build data object properly
            const data = {
                transaction_date: $('#transactionDate').val(),
                planned_usage_date: $('#plannedUsageDate').val() || null,
                job_level_id: $('#jobLevel').val(),
                job_position_id: $('#jobPosition').val(),
                program_id: $('#programId').val(),
                purpose: $('#purpose').val(),
                urgency: $('#urgency').val(),
                items: []
            };

            // Collect items data from table rows
            $('#itemsTableBody tr').each(function() {
                const row = $(this);
                const item = {
                    goods_service_name: row.find('.goods-name-input').val(),
                    budget_id: row.find('.budget-select').val(),
                    unit_id: row.find('.unit-select').val(),
                    quantity: parseFloat(row.find('.qty-input').val()) || 0,
                    price: parseFloat(row.find('.price-input').val().replace(/[^\d]/g, '')) || 0
                };
                data.items.push(item);
            });

            console.log('Sending data:', data);

            $.ajax({
                url: url,
                type: method,
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);

                    if (response.success) {
                        $('#submissionModal').modal('hide');
                        showAlert(response.message || 'Submission saved successfully', 'success');
                        loadData();
                        loadSummary(); // Reload summary after save
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);

                    // Handle budget validation errors from backend
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.budget_errors) {
                        const budgetErrors = xhr.responseJSON.budget_errors;
                        let errorMessage = '<strong>' + xhr.responseJSON.message + '</strong><br><br>';
                        errorMessage += '<ul class="mb-0 mt-2">';
                        budgetErrors.forEach(function(error) {
                            errorMessage +=
                                `<li><strong>${error.item}:</strong> Total ${error.total} exceeds Budget ${error.budget} (${error.budget_code})</li>`;
                        });
                        errorMessage +=
                            '</ul><br><small class="text-muted">Please adjust the quantity or price to match the available budget.</small>';

                        Swal.fire({
                            icon: 'error',
                            title: 'Budget Validation Error',
                            html: errorMessage,
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'OK, I\'ll Fix It'
                        });
                        return;
                    }

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        showModalError('Please correct the following errors:', errors);

                        // Highlight fields with errors
                        Object.keys(errors).forEach(function(field) {
                            // Handle items array errors
                            if (field.startsWith('items.')) {
                                const parts = field.split('.');
                                const index = parseInt(parts[1]);
                                const fieldName = parts[2];

                                const row = $('#itemsTableBody tr').eq(index);
                                if (row.length) {
                                    if (fieldName === 'goods_service_name') {
                                        row.find('.goods-name-input').addClass('is-invalid')
                                            .after(
                                                `<div class="invalid-feedback d-block">${errors[field][0]}</div>`
                                            );
                                    } else if (fieldName === 'budget_id') {
                                        row.find('.budget-select').addClass('is-invalid')
                                            .parent().append(
                                                `<div class="invalid-feedback d-block">${errors[field][0]}</div>`
                                            );
                                    } else if (fieldName === 'unit_id') {
                                        row.find('.unit-select').addClass('is-invalid')
                                            .parent().append(
                                                `<div class="invalid-feedback d-block">${errors[field][0]}</div>`
                                            );
                                    } else if (fieldName === 'quantity') {
                                        row.find('.qty-input').addClass('is-invalid')
                                            .after(
                                                `<div class="invalid-feedback d-block">${errors[field][0]}</div>`
                                            );
                                    } else if (fieldName === 'price') {
                                        row.find('.price-input').addClass('is-invalid')
                                            .after(
                                                `<div class="invalid-feedback d-block">${errors[field][0]}</div>`
                                            );
                                    }
                                }
                            } else {
                                // Handle regular field errors
                                const fieldMap = {
                                    'transaction_date': '#transactionDate',
                                    'planned_usage_date': '#plannedUsageDate',
                                    'job_level_id': '#jobLevel',
                                    'job_position_id': '#jobPosition',
                                    'program_id': '#programId',
                                    'purpose': '#purpose',
                                    'urgency': '#urgency'
                                };

                                if (fieldMap[field]) {
                                    $(fieldMap[field]).addClass('is-invalid')
                                        .after(
                                            `<div class="invalid-feedback d-block">${errors[field][0]}</div>`
                                        );
                                }
                            }
                        });

                        // Scroll to top of modal to see errors
                        $('.modal-body').animate({
                            scrollTop: 0
                        }, 300);
                    } else {
                        // General error
                        let message = 'An error occurred while saving the submission';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showModalError(message);
                    }
                }
            });
        }

        function renderSubmissionDetailLpjProofPreview(lpj) {
            const proofUrl = lpj.proof_of_payment_url;
            const proofName = escapeHtml(lpj.proof_of_payment_name || 'Proof of payment');
            const previewType = lpj.proof_of_payment_preview_type;
            const openLink = $('#view_lpj_proof_open_link');

            if (!proofUrl) {
                openLink.addClass('d-none').attr('href', '#');
                $('#view_lpj_proof_preview_body').html('<p class="text-muted mb-0">No proof file uploaded.</p>');
                return;
            }

            openLink.removeClass('d-none').attr('href', proofUrl);

            if (previewType === 'image') {
                $('#view_lpj_proof_preview_body').html(`
                    <div class="text-center">
                        <a href="${proofUrl}" target="_blank" rel="noopener">
                            <img src="${proofUrl}" alt="${proofName}" class="img-fluid rounded border" style="max-height: 420px;">
                        </a>
                        <div class="small text-muted mt-2">${proofName}</div>
                    </div>
                `);
                return;
            }

            if (previewType === 'pdf') {
                $('#view_lpj_proof_preview_body').html(`
                    <iframe src="${proofUrl}" title="${proofName}" class="w-100 border rounded" style="height: 420px;"></iframe>
                    <div class="small text-muted mt-2">${proofName}</div>
                `);
                return;
            }

            $('#view_lpj_proof_preview_body').html(`
                <div class="alert alert-secondary mb-0">
                    Preview is not available for this file type.
                    <a href="${proofUrl}" target="_blank" rel="noopener" class="alert-link">Open ${proofName}</a>
                </div>
            `);
        }

        // View submission
        function viewSubmission(id) {
            $.ajax({
                url: `{{ route('userSubmission.show', ':id') }}`.replace(':id', id),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;

                        // Populate basic information
                        $('#view_transaction_date').text(formatDate(data.transaction_date));
                        $('#view_planned_usage_date').text(data.planned_usage_date ? formatDate(data
                            .planned_usage_date) : '-');
                        $('#view_user_name').text(data.user_name || '-');
                        $('#view_job_level').text(data.job_level ? data.job_level.job_level_name : '-');
                        $('#view_job_position').text(data.job_position ? data.job_position.job_position_name :
                            '-');
                        $('#view_program').text(data.program ? data.program.program_name : '-');
                        $('#view_unit').text(data.unit_name || '-');
                        $('#view_purpose').text(data.purpose || '-');
                        $('#view_estimated_amount').html('<strong>' + formatCurrency(data.estimated_amount) +
                            '</strong>');
                        $('#view_status').html(getStatusBadge(data.status, data.id));
                        $('#view_urgency').text(data.urgency || '-');

                        // Populate items
                        let itemsHtml = '';
                        if (data.details && data.details.length > 0) {
                            data.details.forEach(function(item, index) {
                                itemsHtml += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.goods_service_name || '-'}</td>
                                <td>${item.budget_name || '-'}</td>
                                <td class="text-end">${formatCurrency(item.balance || 0)}</td>
                                <td>${item.unit_name || '-'}</td>
                                <td class="text-center">${item.estimated_quantity || 0}</td>
                                <td class="text-end">${formatCurrency(item.estimated_price || 0)}</td>
                                <td class="text-end"><strong>${formatCurrency(item.estimated_total || 0)}</strong></td>
                            </tr>
                        `;
                            });
                        } else {
                            itemsHtml = '<tr><td colspan="8" class="text-center">No items found</td></tr>';
                        }
                        $('#view_items_body').html(itemsHtml);

                        const lpj = data.lpj_submission || data.lpjSubmission || null;
                        if (lpj) {
                            const lpjStatusColor = getLpjStatusColor(lpj.status_approval);
                            $('#view_lpj_status_alert')
                                .removeClass('alert-info alert-warning alert-success alert-danger')
                                .addClass(`alert-${lpjStatusColor}`);
                            $('#view_lpj_status_text').text(getLpjStatusLabel(lpj.status_approval));
                            $('#view_lpj_submission_date').text(formatDate(lpj.submission_date));
                            $('#view_lpj_realization_date').text(formatDate(lpj.realization_date));
                            $('#view_lpj_estimated_amount').html('<strong>' + formatCurrency(data.estimated_amount || 0) + '</strong>');
                            $('#view_lpj_actual_amount').html('<strong>' + formatCurrency(data.actual_amount || 0) + '</strong>');
                            $('#view_lpj_open_detail_btn').off('click').on('click', function() {
                                viewLpjDetail(data.id);
                            });

                            renderSubmissionDetailLpjProofPreview(lpj);

                            let lpjItemsHtml = '';
                            if (data.details && data.details.length > 0) {
                                data.details.forEach(function(item, index) {
                                    lpjItemsHtml += `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${item.goods_service_name || '-'}</td>
                                            <td class="text-center">${item.estimated_quantity || 0}</td>
                                            <td class="text-end">${formatCurrency(item.estimated_price || 0)}</td>
                                            <td class="text-end">${formatCurrency(item.estimated_total || 0)}</td>
                                            <td class="text-center">${item.fix_quantity || 0}</td>
                                            <td class="text-end">${formatCurrency(item.fix_price || 0)}</td>
                                            <td class="text-end"><strong>${formatCurrency(item.fix_total || 0)}</strong></td>
                                        </tr>
                                    `;
                                });
                            } else {
                                lpjItemsHtml = '<tr><td colspan="8" class="text-center">No LPJ items found</td></tr>';
                            }

                            $('#view_lpj_items_body').html(lpjItemsHtml);
                            $('#view_lpj_section').show();
                        } else {
                            $('#view_lpj_section').hide();
                            $('#view_lpj_items_body').html('');
                            $('#view_lpj_proof_open_link').addClass('d-none').attr('href', '#');
                            $('#view_lpj_proof_preview_body').html('<p class="text-muted mb-0">No proof file uploaded.</p>');
                        }

                        // Populate approval history - check new dynamic system first
                        let approvalHtml = '';
                        let hasApprovalData = false;

                        // Check for new dynamic approval system data
                        if (data.approval_request && data.approval_request.details && data.approval_request
                            .details.length > 0) {
                            hasApprovalData = true;
                            data.approval_request.details.forEach(function(detail) {
                                const statusLabel = detail.status === 'pending' ?
                                    '<span class="badge bg-warning">Pending</span>' :
                                    detail.status === 'approved' ?
                                    '<span class="badge bg-success">Approved</span>' :
                                    detail.status === 'rejected' ?
                                    '<span class="badge bg-danger">Rejected</span>' :
                                    detail.status === 'skipped' ?
                                    '<span class="badge bg-secondary">Skipped</span>' :
                                    '<span class="badge bg-secondary">-</span>';

                                const approvedDate = detail.approved_at ? formatDate(detail
                                    .approved_at) : '-';
                                const phaseLabel = detail.phase === 'uppline' ? 'Uppline' :
                                    'Master Flow';

                                approvalHtml += `
                                    <tr>
                                        <td>${phaseLabel} - Level ${detail.level_sequence}</td>
                                        <td>${detail.employment_name || '-'}</td>
                                        <td>${statusLabel}</td>
                                        <td>${approvedDate}</td>
                                        <td>-</td>
                                    </tr>
                                `;
                            });
                        }
                        // Fallback to legacy approval data
                        else if (data.approvals && data.approvals.length > 0) {
                            hasApprovalData = true;
                            data.approvals.forEach(function(approval) {
                                const statusLabel = approval.status === 0 ?
                                    '<span class="badge bg-warning">Pending</span>' :
                                    approval.status === 1 ?
                                    '<span class="badge bg-success">Approved</span>' :
                                    approval.status === 2 ?
                                    '<span class="badge bg-danger">Rejected</span>' :
                                    '<span class="badge bg-secondary">-</span>';

                                const approvedDate = approval.approved_at ? formatDate(approval
                                    .approved_at) : '-';

                                approvalHtml += `
                                    <tr>
                                        <td>Level ${approval.approval_level}</td>
                                        <td>${approval.approver_name || '-'}</td>
                                        <td>${statusLabel}</td>
                                        <td>${approvedDate}</td>
                                        <td>${approval.comments || '-'}</td>
                                    </tr>
                                `;
                            });
                        }

                        if (hasApprovalData) {
                            $('#view_approval_body').html(approvalHtml);
                            $('#view_approval_section').show();
                        } else {
                            $('#view_approval_section').hide();
                        }

                        // Show modal
                        $('#viewModal').modal('show');
                    }
                },
                error: function(xhr) {
                    let message = 'Error loading submission';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }

        // Edit submission
        function editSubmission(id) {
            // Reset form first
            resetForm();

            $.ajax({
                url: `{{ route('userSubmission.show', ':id') }}`.replace(':id', id),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;

                        // Check if transaction can still be edited
                        if (!data.can_edit) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Cannot Edit',
                                text: 'This transaction cannot be edited because it has already been approved by one or more approvers.',
                                confirmButtonColor: '#3085d6'
                            });
                            return;
                        }

                        // Set submission ID and modal title
                        $('#submissionId').val(data.id);
                        $('#submissionModalLabel').text('Edit Submission');

                        // Set basic fields
                        $('#transactionDate').val(data.transaction_date);
                        $('#plannedUsageDate').val(data.planned_usage_date || '');
                        $('#purpose').val(data.purpose);
                        $('#urgency').val(data.urgency);

                        // Set Job Level first
                        $('#jobLevel').val(data.job_level_id);

                        // Load Job Positions based on Job Level, then set the value
                        if (data.job_level_id) {
                            loadJobPositions(data.job_level_id);

                            // Wait a bit for job positions to load, then set value
                            setTimeout(function() {
                                $('#jobPosition').val(data.job_position_id);
                            }, 500);

                            // Load Programs based on Job Level
                            $.ajax({
                                url: `{{ route('userSubmission.programs', ':jobLevelId') }}`.replace(
                                    ':jobLevelId', data.job_level_id),
                                type: 'GET',
                                success: function(programResponse) {
                                    if (programResponse.success) {
                                        let options = '<option value="">Select Program</option>';
                                        programResponse.data.forEach(function(prog) {
                                            options +=
                                                `<option value="${prog.id}">${prog.name}</option>`;
                                        });
                                        $('#programId').html(options).prop('disabled', false);
                                        $('#programId').val(data.program_id);

                                        // Load Budget Items based on Program
                                        if (data.program_id) {
                                            $.ajax({
                                                url: `{{ route('userSubmission.budgetItems', ':programId') }}`
                                                    .replace(':programId', data.program_id),
                                                type: 'GET',
                                                success: function(budgetResponse) {
                                                    if (budgetResponse.success) {
                                                        availableBudgetItems =
                                                            budgetResponse.data;

                                                        // Clear existing items
                                                        $('#itemsTableBody').html('');
                                                        itemRowCounter = 0;

                                                        // Populate item rows with existing data
                                                        if (data.details && data.details
                                                            .length > 0) {
                                                            data.details.forEach(
                                                                function(detail) {
                                                                    itemRowCounter++;

                                                                    // Prepare budget options
                                                                    let budgetOptions =
                                                                        '<option value="">Select Budget</option>';
                                                                    availableBudgetItems
                                                                        .forEach(
                                                                            function(
                                                                                item
                                                                            ) {
                                                                                const
                                                                                    selected =
                                                                                    item
                                                                                    .id ==
                                                                                    detail
                                                                                    .budget_id ?
                                                                                    'selected' :
                                                                                    '';
                                                                                budgetOptions
                                                                                    +=
                                                                                    `<option value="${item.id}" data-value="${item.total}" data-code="${item.budget_code}" data-unit-id="${item.unit_id || ''}" data-unit-name="${item.unit_name || ''}" ${selected}>${item.label}</option>`;
                                                                            });

                                                                    // Prepare unit options
                                                                    let unitOptions =
                                                                        '<option value="">Select Unit</option>';
                                                                    units.forEach(
                                                                        function(
                                                                            unit
                                                                        ) {
                                                                            const
                                                                                selected =
                                                                                unit
                                                                                .id ==
                                                                                detail
                                                                                .unit_id ?
                                                                                'selected' :
                                                                                '';
                                                                            unitOptions
                                                                                +=
                                                                                `<option value="${unit.id}" ${selected}>${unit.unit || unit.unit_name}</option>`;
                                                                        });

                                                                    // Get budget value
                                                                    const
                                                                        selectedBudget =
                                                                        availableBudgetItems
                                                                        .find(
                                                                            item =>
                                                                            item
                                                                            .id ==
                                                                            detail
                                                                            .budget_id
                                                                        );
                                                                    const
                                                                        budgetValue =
                                                                        selectedBudget ?
                                                                        formatCurrency(
                                                                            selectedBudget
                                                                            .total
                                                                        ) : '';

                                                                    // Create row HTML
                                                                    let html = `
                                                            <tr data-row="${itemRowCounter}">
                                                                <td class="text-center">${itemRowCounter}</td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm goods-name-input" 
                                                                           name="items[${itemRowCounter}][goods_service_name]" 
                                                                           value="${detail.goods_service_name || ''}" 
                                                                           placeholder="Goods/Service Name" required>
                                                                </td>
                                                                <td>
                                                                    <select class="form-select form-select-sm budget-select" 
                                                                            name="items[${itemRowCounter}][budget_id]" 
                                                                            data-row="${itemRowCounter}" required>
                                                                        ${budgetOptions}
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm budget-value" 
                                                                           value="${budgetValue}" readonly>
                                                                </td>
                                                                <td>
                                                                    <select class="form-select form-select-sm unit-select" 
                                                                            name="items[${itemRowCounter}][unit_id]" required>
                                                                        ${unitOptions}
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control form-control-sm qty-input" 
                                                                           name="items[${itemRowCounter}][quantity]" 
                                                                           value="${detail.estimated_quantity || 0}" 
                                                                           data-row="${itemRowCounter}" 
                                                                           min="1" step="1" required>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm price-input" 
                                                                           name="items[${itemRowCounter}][price]" 
                                                                           value="${formatNumber(detail.estimated_price || 0)}" 
                                                                           data-row="${itemRowCounter}" 
                                                                           placeholder="0" required>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm total-input" 
                                                                           value="${formatCurrency(detail.estimated_total || 0)}" 
                                                                           readonly>
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                                            onclick="removeItemRow(${itemRowCounter})">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        `;

                                                                    $('#itemsTableBody')
                                                                        .append(
                                                                            html);

                                                                    // Auto-fill and lock unit from selected budget item
                                                                    if (selectedBudget &&
                                                                        selectedBudget
                                                                        .unit_id) {
                                                                        const
                                                                            unitSel =
                                                                            $(
                                                                                `tr[data-row="${itemRowCounter}"] .unit-select`);
                                                                        unitSel
                                                                            .prop(
                                                                                'disabled',
                                                                                true
                                                                                );
                                                                        unitSel
                                                                            .after(
                                                                                `<input type="hidden" class="unit-hidden-input" name="${unitSel.attr('name')}" value="${selectedBudget.unit_id}">`
                                                                                );
                                                                    }

                                                                    // Add event listeners for this row
                                                                    $(`[data-row="${itemRowCounter}"] .budget-select`)
                                                                        .on('change',
                                                                            function() {
                                                                                updateBudgetValue
                                                                                    ($(
                                                                                        this
                                                                                    ));
                                                                            });

                                                                    $(`[data-row="${itemRowCounter}"] .qty-input, [data-row="${itemRowCounter}"] .price-input`)
                                                                        .on('input',
                                                                            function() {
                                                                                calculateRowTotal
                                                                                    ($(this)
                                                                                        .data(
                                                                                            'row'
                                                                                        )
                                                                                    );
                                                                            });

                                                                    // Initialize price input with thousand separator
                                                                    $(`[data-row="${itemRowCounter}"] .price-input`)
                                                                        .on('input',
                                                                            function() {
                                                                                formatPriceInput
                                                                                    ($(
                                                                                        this
                                                                                    ));
                                                                            });
                                                                });

                                                            // Calculate initial estimated value
                                                            calculateEstimatedValue();
                                                        }
                                                    }
                                                },
                                                error: function(xhr) {
                                                    console.error(
                                                        'Error loading budget items:',
                                                        xhr);
                                                }
                                            });
                                        }
                                    }
                                },
                                error: function(xhr) {
                                    console.error('Error loading programs:', xhr);
                                }
                            });
                        }

                        // Show modal
                        $('#submissionModal').modal('show');
                    }
                },
                error: function(xhr) {
                    let message = 'Error loading submission for edit';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }

        // Delete submission
        function deleteSubmission(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('userSubmission.destroy', ':id') }}`.replace(':id', id),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                showAlert(response.message, 'success');
                                loadData();
                                loadSummary(); // Reload summary after delete
                            }
                        },
                        error: function(xhr) {
                            let message = 'Error deleting submission';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            showAlert(message, 'danger');
                        }
                    });
                }
            });
        }

        // Reset form
        function resetForm() {
            $('#submissionForm')[0].reset();
            $('#submissionId').val('');
            $('#itemsTableBody').html('');
            itemRowCounter = 0;
            $('#estimatedValue').val('');
            availableBudgetItems = [];

            // Clear validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#modalErrorAlert').remove();

            // Reset cascading dropdowns
            $('#jobPosition').html('<option value="">Select Job Position</option>').prop('disabled', false);
            // $('#programId').html('<option value="">Select Program</option>').prop('disabled', false);
        }

        // Helper functions
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function formatCurrency(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        function getStatusBadge(status, transaction_id) {
            const statusMap = {
                0: {
                    label: 'Submission',
                    class: 'bg-primary'
                },
                1: {
                    label: 'Progress',
                    class: 'bg-info'
                },
                2: {
                    label: 'Approved',
                    class: 'bg-info'
                },
                3: {
                    label: 'Paid',
                    class: 'bg-success'
                },
                4: {
                    label: 'Completed',
                    class: 'bg-info'
                },
                5: {
                    label: 'Rejected',
                    class: 'bg-danger'
                },
                '-1': {
                    label: 'Cancelled',
                    class: 'bg-dark'
                }
            };

            const statusInfo = statusMap[status] || {
                label: 'Unknown',
                class: 'bg-secondary'
            };

            return `<span data-bs-toggle="modal" data-bs-target="#trackingModal" onclick="getbadgeinfo(${transaction_id})" class="badge ${statusInfo.class} badge-status sts">${statusInfo.label}</span>`;
        }

        function getbadgeinfo(id) {
            let url = "{{ route('userSubmission.badgeinfo', ':id') }}".replace(':id', id);
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $("#timeline").html(response.data);
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showAlert(response.message || 'Error get data badge info', 'error');
                }
            });
        }

        function viewPdf(id) {
            let url = "{{ route('userSubmission.viewPdf', ':id') }}".replace(':id', id);
            window.open(url);
        }

        function showAlert(message, type) {
            const icon = type === 'success' ? 'success' : 'error';
            const title = type === 'success' ? 'Success!' : 'Error!';

            Swal.fire({
                icon: icon,
                title: title,
                text: message,
                confirmButtonColor: type === 'success' ? '#28a745' : '#dc3545',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: true
            });
        }

        function showModalError(message, errors = null) {
            let errorMessage = message;

            if (errors) {
                errorMessage += '<br><br><ul style="text-align: left; margin-left: 20px;">';
                Object.keys(errors).forEach(function(field) {
                    // Make error messages more readable
                    let fieldLabel = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    if (field.startsWith('items.')) {
                        const parts = field.split('.');
                        const index = parseInt(parts[1]) + 1;
                        const fieldName = parts[2].replace(/_/g, ' ');
                        fieldLabel = `Item ${index} - ${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)}`;
                    }
                    errorMessage += `<li><strong>${fieldLabel}:</strong> ${errors[field][0]}</li>`;
                });
                errorMessage += '</ul>';
            }

            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: errorMessage,
                confirmButtonColor: '#dc3545'
            });
        }

        // Approve submission
        function approveSubmission(id) {
            Swal.fire({
                title: 'Approve Submission?',
                text: 'Are you sure you want to approve this submission?',
                icon: 'question',
                input: 'textarea',
                inputLabel: 'Comments (optional)',
                inputPlaceholder: 'Enter your comments here...',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = "{{ route('userSubmission.approve', ':id') }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            comments: result.value
                        },
                        success: function(response) {
                            showAlert("Sukses Approve Submission", 'success');
                            loadData();
                            loadSummary();
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            showAlert(response.message || 'Error approving submission', 'error');
                        }
                    });
                }
            });
        }

        // Reject submission
        function rejectSubmission(id) {
            Swal.fire({
                title: 'Reject Submission?',
                text: 'Are you sure you want to reject this submission?',
                icon: 'warning',
                input: 'textarea',
                inputLabel: 'Rejection Reason (required)',
                inputPlaceholder: 'Please provide a reason for rejection...',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Rejection reason is required!';
                    }
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = "{{ route('userSubmission.reject', ':id') }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            comments: result.value
                        },
                        success: function(response) {
                            console.log(response);

                            showAlert("Sukses Reject Submission", 'success');
                            loadData();
                            loadSummary();
                        },
                        error: function(xhr) {
                            console.log(xhr);

                            const response = xhr.responseJSON;
                            showAlert(response.message || 'Error rejecting submission', 'error');
                        }
                    });
                }
            });
        }

        // ==================== LPJ FUNCTIONS ====================

        let lpjItemsData = [];

        function openLpjModal(transactionId) {
            // Reset form
            resetLpjForm();
            $('#lpj_transaction_id').val(transactionId);
            $('#lpjErrorAlert').addClass('d-none');

            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            $('#lpj_submission_date').val(today);
            $('#lpj_realization_date').val(today);

            // Load transaction data
            let url = "{{ route('userSubmission.lpj.form', ':id') }}".replace(':id', transactionId);
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        const transaction = data.transaction;
                        const details = data.details;

                        // Populate transaction info
                        $('#lpj_user_name').text(transaction.user_name);
                        $('#lpj_job_level').text(transaction.job_level?.name || '-');
                        $('#lpj_program_id').text(transaction.program_id || '-');
                        $('#lpj_purpose').text(transaction.purpose);
                        $('#lpj_submission_value').text(formatCurrency(transaction.estimated_amount));
                        $('#lpj_submission_total').text(formatCurrency(transaction.estimated_amount));

                        // Populate items table
                        lpjItemsData = details;
                        renderLpjItems(details);

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('lpjModal'));
                        modal.show();
                    } else {
                        showAlert(response.message || 'Error loading LPJ data', 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showAlert(response?.message || 'Error loading LPJ data', 'error');
                }
            });
        }

        function renderLpjItems(details) {
            let html = '';
            let submissionTotal = 0;

            if (details.length === 0) {
                html = '<tr><td colspan="10" class="text-center text-muted">No items found</td></tr>';
            } else {
                details.forEach((item, index) => {
                    const estTotal = parseFloat(item.estimated_total) || 0;
                    submissionTotal += estTotal;

                    html += `
                        <tr>
                            <td>
                                <strong>${item.goods_service_name}</strong>
                                <input type="hidden" name="items[${index}][detail_id]" value="${item.id}">
                            </td>
                            <td><small>${item.budget_name || '-'}</small></td>
                            <td class="text-center">${item.unit_name || '-'}</td>
                            <td class="text-center">${item.estimated_quantity}</td>
                            <td class="text-end">${formatCurrency(item.estimated_price)}</td>
                            <td class="text-end">${formatCurrency(estTotal)}</td>
                            <td class="text-center">${item.unit_name || '-'}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm lpj-qty" 
                                    name="items[${index}][fix_quantity]" 
                                    value="${item.fix_quantity || item.estimated_quantity}" 
                                    min="0" step="1" 
                                    data-index="${index}"
                                    onchange="calculateLpjTotal()">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm lpj-price" 
                                    name="items[${index}][fix_price]" 
                                    value="${item.fix_price || item.estimated_price}" 
                                    min="0" step="0.01"
                                    data-index="${index}"
                                    onchange="calculateLpjTotal()">
                            </td>
                            <td class="text-end lpj-item-total" data-index="${index}">
                                ${formatCurrency((item.fix_quantity || item.estimated_quantity) * (item.fix_price || item.estimated_price))}
                            </td>
                        </tr>
                    `;
                });
            }

            $('#lpj_items_body').html(html);
            calculateLpjTotal();
        }

        function calculateLpjTotal() {
            let realizationTotal = 0;
            let submissionTotal = 0;

            $('.lpj-qty').each(function() {
                const index = $(this).data('index');
                const qty = parseFloat($(this).val()) || 0;
                const price = parseFloat($(`input.lpj-price[data-index="${index}"]`).val()) || 0;
                const total = qty * price;

                $(`.lpj-item-total[data-index="${index}"]`).text(formatCurrency(total));
                realizationTotal += total;
            });

            // Calculate submission total from original data
            lpjItemsData.forEach(item => {
                submissionTotal += parseFloat(item.estimated_total) || 0;
            });

            $('#lpj_realization_total').text(formatCurrency(realizationTotal));
            $('#lpj_realization_value').text(formatCurrency(realizationTotal));
            $('#lpj_submission_total').text(formatCurrency(submissionTotal));

            // Update variance badge
            const variance = realizationTotal - submissionTotal;
            const variancePercent = submissionTotal > 0 ? ((variance / submissionTotal) * 100).toFixed(1) : 0;

            if (variance > 0) {
                $('#lpj_variance_badge').removeClass('bg-success bg-info').addClass('bg-danger')
                    .text(`Over Budget: +${formatCurrency(variance)} (${variancePercent}%)`);
            } else if (variance < 0) {
                $('#lpj_variance_badge').removeClass('bg-danger bg-info').addClass('bg-success')
                    .text(`Under Budget: ${formatCurrency(variance)} (${variancePercent}%)`);
            } else {
                $('#lpj_variance_badge').removeClass('bg-danger bg-success').addClass('bg-info')
                    .text('On Budget');
            }
        }

        function resetLpjForm() {
            $('#lpjForm')[0].reset();
            $('#lpj_items_body').html('<tr><td colspan="10" class="text-center text-muted">No items</td></tr>');
            $('#lpj_realization_total').text('Rp 0');
            $('#lpj_realization_value').text('Rp 0');
            $('#lpj_variance_badge').text('');
            lpjItemsData = [];
        }

        // Submit LPJ Form
        $('#lpjForm').on('submit', function(e) {
            e.preventDefault();

            const transactionId = $('#lpj_transaction_id').val();
            const formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');

            // Disable submit button
            $('#btnSubmitLpj').prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm"></span> Submitting...');

            let url = "{{ route('userSubmission.lpj.submit', ':id') }}".replace(':id', transactionId);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'LPJ submitted successfully',
                            confirmButtonColor: '#198754'
                        });

                        // Close modal and reload data
                        bootstrap.Modal.getInstance(document.getElementById('lpjModal')).hide();
                        loadData();
                        loadSummary();
                    } else {
                        $('#lpjErrorAlert').removeClass('d-none').text(response.message ||
                            'Error submitting LPJ');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMsg = response?.message || 'Error submitting LPJ';

                    if (response?.errors) {
                        errorMsg += '<ul class="mb-0 mt-2">';
                        Object.values(response.errors).forEach(errors => {
                            errors.forEach(err => {
                                errorMsg += `<li>${err}</li>`;
                            });
                        });
                        errorMsg += '</ul>';
                    }

                    $('#lpjErrorAlert').removeClass('d-none').html(errorMsg);
                },
                complete: function() {
                    $('#btnSubmitLpj').prop('disabled', false).html(
                        '<i class="ri-save-line me-1"></i>Submit LPJ');
                }
            });
        });

        // ==================== LPJ APPROVAL FUNCTIONS ====================

        function loadLpjApprovalCounts() {
            console.log('[LPJ] Loading LPJ approval counts...');
            const url = `{{ route('userSubmission.lpj.counts') }}`;
            console.log('[LPJ] URL:', url);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    console.log('[LPJ] Counts response:', response);
                    if (response.success && response.data) {
                        $('#lpjApprovalCount').text(response.data.pending || 0);
                        console.log('[LPJ] Count set to:', response.data.pending || 0);
                    } else {
                        console.warn('[LPJ] Invalid response format:', response);
                        $('#lpjApprovalCount').text('0');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('[LPJ] Error loading counts:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error,
                        response: xhr.responseJSON
                    });
                    $('#lpjApprovalCount').text('!');

                    // Show error in badge
                    $('#lpjApprovalCount').attr('title', 'Error loading count: ' + (xhr.responseJSON?.message ||
                        error));
                }
            });
        }

        function loadPendingLpjApprovals() {
            console.log('[LPJ] Loading pending LPJ approvals...');
            const url = `{{ route('userSubmission.lpj.pending') }}`;
            console.log('[LPJ] URL:', url);

            // Show loading spinner
            $('#lpjApprovalTableBody').html(`
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading LPJ approvals...
                    </td>
                </tr>
            `);

            $.ajax({
                url: url,
                type: 'GET',
                timeout: 10000, // 10 second timeout
                success: function(response) {
                    console.log('[LPJ] Pending approvals response:', response);
                    if (response.success) {
                        if (response.data && response.data.length > 0) {
                            console.log('[LPJ] Found', response.data.length, 'pending approvals');
                            renderLpjApprovalTable(response.data);
                        } else {
                            console.log('[LPJ] No pending approvals found');
                            $('#lpjApprovalTableBody').html(
                                '<tr><td colspan="9" class="text-center text-muted">No pending LPJ approvals</td></tr>'
                            );
                        }
                    } else {
                        console.warn('[LPJ] Invalid response:', response);
                        $('#lpjApprovalTableBody').html(
                            '<tr><td colspan="9" class="text-center text-warning">Invalid response from server</td></tr>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error('[LPJ] Error loading pending approvals:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error,
                        response: xhr.responseJSON,
                        timeout: status === 'timeout'
                    });

                    let errorMsg = 'Error loading data';
                    if (status === 'timeout') {
                        errorMsg = 'Request timeout - server took too long to respond';
                    } else if (xhr.status === 404) {
                        errorMsg = 'Endpoint not found (404) - Route may not be registered';
                    } else if (xhr.status === 500) {
                        errorMsg = 'Server error (500) - ' + (xhr.responseJSON?.message ||
                            'Internal server error');
                    } else if (xhr.status === 0) {
                        errorMsg = 'Network error - Cannot reach server';
                    } else if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    $('#lpjApprovalTableBody').html(`
                        <tr>
                            <td colspan="9" class="text-center text-danger">
                                <i class="ri-error-warning-line me-2"></i>${errorMsg}
                                <br><small class="text-muted">Check console for details (F12)</small>
                            </td>
                        </tr>
                    `);
                }
            });
        }

        function renderLpjApprovalTable(data) {
            console.log('[LPJ] Rendering approval table with', data.length, 'items');
            let html = '';

            if (!data || data.length === 0) {
                html = '<tr><td colspan="9" class="text-center text-muted">No pending LPJ approvals</td></tr>';
            } else {
                try {
                    data.forEach((item, index) => {
                        console.log('[LPJ] Processing item', index, ':', item);

                        // Backend returns LpjApprovalDetail with lpjSubmission relationship
                        const lpj = item.lpj_submission || item.lpjSubmission || item.lpj || item;
                        const transaction = lpj?.transaction || {};
                        const user = transaction?.user || {};

                        if (!lpj || !transaction) {
                            console.warn('[LPJ] Invalid item structure at index', index, ':', item);
                            return; // Skip this item
                        }

                        const statusBadge = lpj.status_approval === 'pending' ? 'bg-warning' :
                            lpj.status_approval === 'in_progress' ? 'bg-info' :
                            lpj.status_approval === 'approved' ? 'bg-success' : 'bg-secondary';

                        // Get user name from relationship or fallback
                        const userName = user.name || transaction.user_name || '-';

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${formatDate(lpj.submission_date)}</td>
                                <td>${formatDate(transaction.transaction_date)}</td>
                                <td>${userName}</td>
                                <td>${transaction.purpose || '-'}</td>
                                <td>${formatCurrency(transaction.estimated_amount || 0)}</td>
                                <td>${formatCurrency(transaction.actual_amount || 0)}</td>
                                <td><span class="badge ${statusBadge}">${lpj.status_approval || 'unknown'}</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-info" onclick="viewLpjDetailForApproval(${lpj.id}, ${transaction.id})" title="View Detail">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="approveLpjSubmission(${lpj.id})" title="Approve">
                                            <i class="ri-check-line"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="rejectLpjSubmission(${lpj.id})" title="Reject">
                                            <i class="ri-close-line"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } catch (err) {
                    console.error('[LPJ] Error rendering table:', err);
                    html =
                        `<tr><td colspan="9" class="text-center text-danger">Error rendering table: ${err.message}</td></tr>`;
                }
            }

            $('#lpjApprovalTableBody').html(html);
            console.log('[LPJ] Table rendered successfully');
        }

        function viewLpjDetailForApproval(lpjId, transactionId) {
            viewLpjDetail(transactionId);
        }

        function approveLpjSubmission(lpjId) {
            Swal.fire({
                title: 'Approve LPJ?',
                text: 'Are you sure you want to approve this LPJ submission?',
                icon: 'question',
                input: 'textarea',
                inputLabel: 'Notes (optional)',
                inputPlaceholder: 'Enter approval notes...',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = "{{ route('userSubmission.lpj.approve', ':lpjId') }}".replace(':lpjId', lpjId);
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            notes: result.value
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message || 'LPJ approved successfully',
                                    confirmButtonColor: '#198754'
                                });
                                loadPendingLpjApprovals();
                                loadLpjApprovalCounts();
                                loadData();
                                loadSummary();
                            } else {
                                showAlert(response.message || 'Error approving LPJ', 'error');
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            showAlert(response?.message || 'Error approving LPJ', 'error');
                        }
                    });
                }
            });
        }

        function rejectLpjSubmission(lpjId) {
            Swal.fire({
                title: 'Reject LPJ?',
                text: 'Are you sure you want to reject this LPJ submission?',
                icon: 'warning',
                input: 'textarea',
                inputLabel: 'Rejection Reason (required)',
                inputPlaceholder: 'Please provide a reason for rejection...',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Rejection reason is required!';
                    }
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = "{{ route('userSubmission.lpj.reject', ':lpjId') }}".replace(':lpjId', lpjId);
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            reason: result.value
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message || 'LPJ rejected',
                                    confirmButtonColor: '#dc3545'
                                });
                                loadPendingLpjApprovals();
                                loadLpjApprovalCounts();
                                loadData();
                                loadSummary();
                            } else {
                                showAlert(response.message || 'Error rejecting LPJ', 'error');
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            showAlert(response?.message || 'Error rejecting LPJ', 'error');
                        }
                    });
                }
            });
        }

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function renderLpjProofPreview(lpj) {
            const proofUrl = lpj.proof_of_payment_url;
            const proofName = escapeHtml(lpj.proof_of_payment_name || 'Proof of payment');
            const previewType = lpj.proof_of_payment_preview_type;
            const openLink = $('#lpj_proof_open_link');

            if (!proofUrl) {
                openLink.addClass('d-none').attr('href', '#');
                $('#lpj_proof_preview_body').html('<p class="text-muted mb-0">No proof file uploaded.</p>');
                return;
            }

            openLink.removeClass('d-none').attr('href', proofUrl);

            if (previewType === 'image') {
                $('#lpj_proof_preview_body').html(`
                    <div class="text-center">
                        <a href="${proofUrl}" target="_blank" rel="noopener">
                            <img src="${proofUrl}" alt="${proofName}" class="img-fluid rounded border" style="max-height: 520px;">
                        </a>
                        <div class="small text-muted mt-2">${proofName}</div>
                    </div>
                `);
                return;
            }

            if (previewType === 'pdf') {
                $('#lpj_proof_preview_body').html(`
                    <iframe src="${proofUrl}" title="${proofName}" class="w-100 border rounded" style="height: 520px;"></iframe>
                    <div class="small text-muted mt-2">${proofName}</div>
                `);
                return;
            }

            $('#lpj_proof_preview_body').html(`
                <div class="alert alert-secondary mb-0">
                    Preview is not available for this file type.
                    <a href="${proofUrl}" target="_blank" rel="noopener" class="alert-link">Open ${proofName}</a>
                </div>
            `);
        }

        // View LPJ Detail
        function viewLpjDetail(transactionId) {
            let url = "{{ route('userSubmission.lpj.byTransaction', ':id') }}".replace(':id', transactionId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        const lpj = response.data;
                        const transaction = lpj.transaction;

                        $('#lpj_view_id').val(lpj.id);
                        $('#lpj_view_submission_date').text(formatDate(lpj.submission_date));
                        $('#lpj_view_realization_date').text(formatDate(lpj.realization_date));
                        $('#lpj_view_submission_value').text(formatCurrency(transaction.estimated_amount));
                        $('#lpj_view_realization_value').text(formatCurrency(transaction.actual_amount));

                        // Status
                        const statusColor = getLpjStatusColor(lpj.status_approval);
                        $('#lpj_view_status_alert').removeClass(
                                'alert-info alert-warning alert-success alert-danger')
                            .addClass(`alert-${statusColor}`);
                        $('#lpj_view_status_text').text(getLpjStatusLabel(lpj.status_approval).replace('LPJ ',
                            ''));

                        renderLpjProofPreview(lpj);

                        // Items
                        let itemsHtml = '';
                        let no = 1;
                        transaction.details.forEach(item => {
                            itemsHtml += `
                                <tr>
                                    <td>${no++}</td>
                                    <td>${item.goods_service_name}</td>
                                    <td class="text-center">${item.estimated_quantity}</td>
                                    <td class="text-end">${formatCurrency(item.estimated_price)}</td>
                                    <td class="text-end">${formatCurrency(item.estimated_total)}</td>
                                    <td class="text-center">${item.fix_quantity || 0}</td>
                                    <td class="text-end">${formatCurrency(item.fix_price || 0)}</td>
                                    <td class="text-end">${formatCurrency(item.fix_total || 0)}</td>
                                </tr>
                            `;
                        });
                        $('#lpj_view_items_body').html(itemsHtml);

                        // Approval Timeline
                        let timelineHtml = '';
                        lpj.approval_details.forEach(detail => {
                            const employee = detail.employment?.employee;
                            const name = employee ? `${employee.first_name} ${employee.last_name}` :
                                'Unknown';
                            const statusBadge = detail.status === 'approved' ? 'bg-success' :
                                detail.status === 'rejected' ? 'bg-danger' : 'bg-warning';
                            const icon = detail.status === 'approved' ? 'ri-check-line' :
                                detail.status === 'rejected' ? 'ri-close-line' : 'ri-time-line';

                            timelineHtml += `
                                <div class="tt-item">
                                    <div class="tt-icon">
                                        <span class="tt-dot ${statusBadge}"><i class="${icon} text-white"></i></span>
                                    </div>
                                    <div class="tt-content">
                                        <div class="d-flex justify-content-between">
                                            <strong>Level ${detail.level_sequence}: ${name}</strong>
                                            <span class="badge ${statusBadge}">${detail.status.charAt(0).toUpperCase() + detail.status.slice(1)}</span>
                                        </div>
                                        ${detail.notes ? `<small class="text-muted">${detail.notes}</small>` : ''}
                                        ${detail.actioned_at ? `<small class="text-muted d-block">${formatDate(detail.actioned_at)}</small>` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        $('#lpj_approval_timeline').html(timelineHtml ||
                            '<p class="text-muted">No approval history</p>');

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('lpjViewModal'));
                        modal.show();
                    } else {
                        showAlert(response.message || 'LPJ not found', 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showAlert(response?.message || 'Error loading LPJ detail', 'error');
                }
            });
        }

        /* =====================================================
            MACFRAME GA IMPORT — Two-Phase JavaScript Logic
        ===================================================== */

        let mfChoicesProgramId = null;   // Choices.js instance for #mf_program_id
        let mfParsedData       = null;   // Cached server response from Phase 1
        let mfBudgetItems      = [];     // Available budget items for selected program

        // ── Import-type radio switch ──
        $('input[name="import_type"]').on('change', function () {
            const isTemplate = $(this).val() === 'template';

            $('#sectionTemplate').toggleClass('d-none', !isTemplate);
            $('#sectionMacframe').toggleClass('d-none', isTemplate);
            $('#btnDoImport').toggleClass('d-none', !isTemplate);
            $('#btnProcessMacframe').toggleClass('d-none', isTemplate);

            // Visual card highlight
            $('#cardTemplate').toggleClass('border-primary', isTemplate).toggleClass('border-secondary', !isTemplate);
            $('#cardMacframe').toggleClass('border-primary', !isTemplate).toggleClass('border-secondary', isTemplate);
        });

        // Trigger once to set initial state
        $('input[name="import_type"]:checked').trigger('change');

        // ── Phase 1 : Process & Preview button ──
        $('#btnProcessMacframe').on('click', function () {
            const fileInput = document.getElementById('macframeFile');
            if (!fileInput.files.length) {
                Swal.fire({ icon: 'warning', title: 'File Belum Dipilih', text: 'Pilih file MacframeGA (.xlsx) terlebih dahulu.', confirmButtonColor: '#f39c12' });
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            Swal.fire({ title: 'Memproses file…', text: 'Mohon tunggu sebentar.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '{{ route("userSubmission.importMacframePreview") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    Swal.close();
                    if (!res.success) {
                        Swal.fire({ icon: 'error', title: 'Gagal Memproses', text: res.message });
                        return;
                    }

                    mfParsedData = res;
                    renderMacframePreview(res);

                    // Close import modal first, then open preview modal
                    bootstrap.Modal.getInstance(document.getElementById('importModal'))?.hide();
                    setTimeout(() => {
                        new bootstrap.Modal(document.getElementById('macframePreviewModal')).show();
                    }, 350);
                },
                error: function (xhr) {
                    Swal.close();
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses file.';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        });

        // ── Fetch Budget Items when program changes ──
        $(document).on('change', '#mf_program_id', function() {
            const programId = $(this).val();
            if (!programId) {
                mfBudgetItems = [];
                updateMacframeBudgetDropdowns();
                return;
            }

            $.ajax({
                url: `{{ route('userSubmission.budgetItems', ':programId') }}`.replace(':programId', programId),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        mfBudgetItems = response.data;
                        updateMacframeBudgetDropdowns();
                    }
                },
                error: function() {
                    mfBudgetItems = [];
                    updateMacframeBudgetDropdowns();
                }
            });
        });

        function updateMacframeBudgetDropdowns() {
            let options = '<option value="">-- Pilih Budget --</option>';
            mfBudgetItems.forEach(item => {
                options += `<option value="${item.id}">${item.label}</option>`;
            });

            $('.mf-budget-select').each(function() {
                const currentVal = $(this).val();
                $(this).html(options);
                if (currentVal) $(this).val(currentVal);
            });
        }

        function renderMacframePreview(res) {
            // Fill date, purpose, urgency
            $('#mf_transaction_date').val(res.transaction_date || '{{ date("Y-m-d") }}');
            $('#mf_purpose').val(res.purpose || '');
            $('#mf_urgency').val(res.urgency || '');

            // Initialise Choices.js on program select (one instance)
            if (mfChoicesProgramId) {
                mfChoicesProgramId.destroy();
            }
            mfChoicesProgramId = new Choices('#mf_program_id', {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
                placeholder: true,
                placeholderValue: '-- Pilih Program --',
            });

            // Build preview table
            const items      = res.data || [];
            let html         = '';
            let grandTotal   = 0;
            let hasUnresolved = false;

            items.forEach((item, idx) => {
                const total = item.total || 0;
                grandTotal += total;
                if (item.unit_unresolved) { hasUnresolved = true; }

                const unitCell = item.unit_unresolved
                    ? `<span class="badge bg-warning text-dark">?</span> ${item.unit_name}`
                    : (item.unit_name || '-');

                html += `
                    <tr class="mf-item-row" data-index="${idx}">
                        <td class="text-center">${idx + 1}</td>
                        <td>${item.goods_service_name}</td>
                        <td><small class="text-muted">${item.purpose || '-'}</small></td>
                        <td><small class="text-muted">${item.urgency || '-'}</small></td>
                        <td>
                            <select class="form-select form-select-sm mf-budget-select" required>
                                <option value="">-- Pilih Budget --</option>
                            </select>
                        </td>
                        <td>${unitCell}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${formatCurrency(item.price)}</td>
                        <td class="text-end fw-semibold">${formatCurrency(total)}</td>
                    </tr>
                `;
            });

            $('#mf_preview_body').html(html || '<tr><td colspan="9" class="text-center text-muted">Tidak ada data</td></tr>');
            $('#mf_grand_total').text(formatCurrency(grandTotal));
            $('#mf_unit_warning').toggleClass('d-none', !hasUnresolved);
            
            // If program already selected (rare on initial load), trigger fetch
            const currentProgramId = $('#mf_program_id').val();
            if (currentProgramId) {
                $('#mf_program_id').trigger('change');
            }
        }

        // ── Phase 2 : Konfirmasi & Simpan ──
        $('#btnConfirmMacframe').on('click', function () {
            const programId       = $('#mf_program_id').val();
            const transactionDate = $('#mf_transaction_date').val();
            const purpose         = $('#mf_purpose').val().trim();
            const urgency         = $('#mf_urgency').val().trim();

            if (!programId) {
                Swal.fire({ icon: 'warning', title: 'Program Belum Dipilih', text: 'Pilih Program ID terlebih dahulu.', confirmButtonColor: '#f39c12' });
                return;
            }
            if (!transactionDate) {
                Swal.fire({ icon: 'warning', title: 'Tanggal Kosong', text: 'Isi tanggal transaksi terlebih dahulu.', confirmButtonColor: '#f39c12' });
                return;
            }
            if (!purpose) {
                Swal.fire({ icon: 'warning', title: 'Purpose Kosong', text: 'Isi purpose pengajuan.', confirmButtonColor: '#f39c12' });
                return;
            }
            if (!urgency) {
                Swal.fire({ icon: 'warning', title: 'Urgency Kosong', text: 'Isi urgency pengajuan.', confirmButtonColor: '#f39c12' });
                return;
            }

            // Collect items and their selected budget IDs
            let items = [];
            let allBudgetSelected = true;

            $('.mf-item-row').each(function() {
                const idx = $(this).data('index');
                const budgetId = $(this).find('.mf-budget-select').val();
                
                if (!budgetId) {
                    allBudgetSelected = false;
                    $(this).find('.mf-budget-select').addClass('is-invalid');
                } else {
                    $(this).find('.mf-budget-select').removeClass('is-invalid');
                }

                const originalItem = mfParsedData.data[idx];
                items.push({
                    ...originalItem,
                    budget_id: parseInt(budgetId)
                });
            });

            if (!allBudgetSelected) {
                Swal.fire({ icon: 'warning', title: 'Budget Belum Dipilih', text: 'Harap pilih Budget Item untuk setiap baris data.', confirmButtonColor: '#f39c12' });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Import',
                html: `Anda akan mengimpor <strong>${items.length} item</strong> dari MacframeGA ke Program yang dipilih.<br><br>Lanjutkan?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Import!',
                cancelButtonText: 'Batal',
            }).then(result => {
                if (!result.isConfirmed) { return; }

                Swal.fire({ title: 'Menyimpan data…', text: 'Mohon tunggu.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: '{{ route("userSubmission.importMacframeCommit") }}',
                    type: 'POST',
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: JSON.stringify({
                        program_id:       parseInt(programId),
                        transaction_date: transactionDate,
                        purpose:          purpose,
                        urgency:          urgency,
                        items:            items,
                    }),
                    success: function (res) {
                        Swal.close();
                        if (res.success) {
                            bootstrap.Modal.getInstance(document.getElementById('macframePreviewModal'))?.hide();
                            mfParsedData = null;

                            Swal.fire({
                                icon: 'success',
                                title: 'Import Berhasil!',
                                text: res.message,
                                confirmButtonColor: '#198754',
                            }).then(() => {
                                loadData();
                                loadSummary();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function (xhr) {
                        Swal.close();
                        const msg = xhr.responseJSON?.message || 'Gagal menyimpan data MacframeGA.';
                        let detail = '';
                        if (xhr.responseJSON?.errors) {
                            detail = '<ul class="mb-0 mt-2 text-start">';
                            Object.values(xhr.responseJSON.errors).forEach(errs => {
                                errs.forEach(e => { detail += `<li>${e}</li>`; });
                            });
                            detail += '</ul>';
                        }
                        Swal.fire({ icon: 'error', title: 'Error', html: msg + detail });
                    }
                });
            });
        });

        // Reset MacframeGA modal on close
        document.getElementById('macframePreviewModal').addEventListener('hidden.bs.modal', function () {
            if (mfChoicesProgramId) {
                mfChoicesProgramId.destroy();
                mfChoicesProgramId = null;
            }
            $('#mf_preview_body').html('<tr><td colspan="9" class="text-center text-muted">Belum ada data</td></tr>');
            $('#mf_grand_total').text('Rp 0');
            $('#mf_unit_warning').addClass('d-none');
        });

    </script>
@endsection
