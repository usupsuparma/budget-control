@extends('layouts.master')

@section('title', 'Pengajuan Anggaran | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Daftar Pengajuan Anggaran')

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
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid">
            {{-- === PAGE TITLE & BREADCRUMB === --}}
            <div class="d-flex align-items-center mt-2 mb-2">
                <h6 class="mb-0 flex-grow-1">List Pengajuan</h6>
                <div class="flex-shrink-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Submission</li>
                        </ol>
                    </nav>
                </div>
            </div>

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


            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3 mb-3">

                {{-- New Submission --}}
                <div class="col">
                    <div class="card border-0 shadow-sm stat-card" style="border-left-color:#0d6efd;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted stat-title">New Submission</div>
                                <div class="h3 mb-0 stat-value" id="newSubmissionCount">
                                    <span class="spinner-border spinner-border-sm" role="status"
                                        aria-label="Loading"></span>
                                </div>
                            </div>
                            <i class="ri-file-add-line stat-icon text-primary"></i>
                        </div>
                    </div>
                </div>

                {{-- Progress --}}
                <div class="col">
                    <div class="card border-0 shadow-sm stat-card" style="border-left-color:#ffc107;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted stat-title">Progress</div>
                                <div class="h3 mb-0 stat-value" id="progressCount">
                                    <span class="spinner-border spinner-border-sm" role="status"
                                        aria-label="Loading"></span>
                                </div>
                            </div>
                            <i class="ri-time-line stat-icon text-warning"></i>
                        </div>
                    </div>
                </div>

                {{-- Paid --}}
                <div class="col">
                    <div class="card border-0 shadow-sm stat-card" style="border-left-color:#198754;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted stat-title">Paid</div>
                                <div class="h3 mb-0 stat-value" id="paidCount">
                                    <span class="spinner-border spinner-border-sm" role="status"
                                        aria-label="Loading"></span>
                                </div>
                            </div>
                            <i class="ri-money-dollar-circle-line stat-icon text-success"></i>
                        </div>
                    </div>
                </div>

                {{-- Completion --}}
                <div class="col">
                    <div class="card border-0 shadow-sm stat-card" style="border-left-color:#6c757d;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted stat-title">Completion</div>
                                <div class="h3 mb-0 stat-value" id="completionCount">
                                    <span class="spinner-border spinner-border-sm" role="status"
                                        aria-label="Loading"></span>
                                </div>
                            </div>
                            <i class="ri-checkbox-circle-line stat-icon text-secondary"></i>
                        </div>
                    </div>
                </div>

                {{-- Total Submission --}}
                <div class="col">
                    <div class="card border-0 shadow-sm stat-card" style="border-left-color:#6610f2;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted stat-title">Total Submission</div>
                                <div class="h3 mb-0 stat-value" id="totalSubmissionCount">
                                    <span class="spinner-border spinner-border-sm" role="status"
                                        aria-label="Loading"></span>
                                </div>
                            </div>
                            <i class="ri-file-list-3-line stat-icon text-purple"></i>
                        </div>
                    </div>
                </div>

            </div>

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
                                        {{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select id="filterStatus" class="form-select">
                                <option value="all">Semua Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary" id="btnFilter">
                                <i class="ri-filter-line"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-end">
                            @if (in_array($employment[0]->job_level_id, array(3,4)))
                            <button type="button" class="btn btn-success" id="btnAddData">
                                <i class="ri-add-line"></i> Add Data
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- === MAIN TABLE === --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-semibold">Daftar Pengajuan Anggaran</h6>
                </div>

                <div class="card-body p-0">
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
                                    value="{{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Job Level <span class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    @foreach ($jobLevels as $level)
                                        <option value="{{ $level->id }}"
                                            {{ $employment[0]->job_level_id == $level->id ? 'selected' : '' }}>
                                            {{ $level->job_level_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <input type="hidden" id="jobLevel" name="job_level_id" value="{{ $employment[0]->job_level_id }}">

                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Job Position <span class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    <option value="">Select Job Position</option>
                                    @foreach ($jobPositions as $position)
                                        <option value="{{ $position->id }}" {{ $employment[0]->job_position_id == $position->id ? 'selected' : '' }}>{{ $position->job_position_name }}</option>
                                    @endforeach
                                </select>

                                <input type="hidden" id="jobPosition" name="job_position_id" value="{{ $employment[0]->job_position_id }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="transactionDate" name="transaction_date"
                                    value="{{ date('Y-m-d') }}" required readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Program ID <span class="text-danger">*</span></label>
                                <select class="form-select" id="programId" name="program_id" required>
                                    <option value="">Select Program</option>
                                    @foreach ($workplans as $workplan)
                                        @if ($workplan->year==date("Y"))
                                            <option value="{{ $workplan->id }}">{{ $workplan->activity }} - {{ $workplan->year }}</option>
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
            let urlJobPositions = '{{ route('userSubmission.jobPositions', ':jobLevelId') }}'.replace(':jobLevelId',
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
            let urlPrograms = '{{ route('userSubmission.programs', ':jobLevelId') }}'.replace(':jobLevelId', jobLevelId);
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
            $.ajax({
                url: '{{ route('userSubmission.summary') }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#newSubmissionCount').text(response.data.newSubmission);
                        $('#progressCount').text(response.data.progress);
                        $('#paidCount').text(response.data.paid);
                        $('#completionCount').text(response.data.completion);
                        $('#totalSubmissionCount').text(response.data.totalSubmission);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading summary:', xhr);
                    $('#newSubmissionCount').text('0');
                    $('#progressCount').text('0');
                    $('#paidCount').text('0');
                    $('#completionCount').text('0');
                    $('#totalSubmissionCount').text('0');
                }
            });
        }

        // Update all budget select dropdowns
        function updateAllBudgetSelects() {
            let options = '<option value="">Select Budget</option>';
            availableBudgetItems.forEach(function(item) {
                options +=
                    `<option value="${item.id}" data-value="${item.total}" data-code="${item.stock_code || item.budget_code}">${item.label}</option>`;
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

            $.ajax({
                url: '{{ route('userSubmission.data') }}',
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
                    html += `
                <tr>
                    <td>${rowNumber}</td>
                    <td>${formatDate(item.transaction_date)}</td>
                    <td>${item.user_name}</td>
                    <td>${item.purpose}</td>
                    <td>${formatCurrency(item.estimated_amount)}</td>
                    <td>${getStatusBadge(item.status)}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-info" onclick="viewSubmission(${item.id})">
                                <i class="ri-eye-line"></i>
                            </button>
                            ${item.status == 0 ? `
                                            <button type="button" class="btn btn-warning" onclick="editSubmission(${item.id})">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="deleteSubmission(${item.id})">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        ` : ''}
                            ${item.can_approve ? `
                                            <button type="button" class="btn btn-success" onclick="approveSubmission(${item.id})">
                                                <i class="ri-check-line"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="rejectSubmission(${item.id})">
                                                <i class="ri-close-line"></i> Reject
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
            <a class="page-link" href="#" onclick="changePage(${data.current_page - 1}); return false;">Previous</a>
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
            <a class="page-link" href="#" onclick="changePage(${data.current_page + 1}); return false;">Next</a>
        </li>
    `;

            paginationHtml += '</ul>';
            $('#paginationLinks').html(paginationHtml);
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
                    `<option value="${item.id}" data-value="${item.total}" data-code="${item.stock_code || item.budget_code}">${item.label}</option>`;
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

            $(`tr[data-row="${row}"] .budget-value`).val(formatCurrency(budgetValue));
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

            $(`tr[data-row="${rowId}"] .total-input`).val(formatCurrency(total));
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

            const submissionId = $('#submissionId').val();
            const url = submissionId ?
                '{{ route('userSubmission.update', ':id') }}'.replace(':id', submissionId) :
                '{{ route('userSubmission.store') }}';
            const method = submissionId ? 'PUT' : 'POST';

            // Build data object properly
            const data = {
                transaction_date: $('#transactionDate').val(),
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
                    if (response.success) {
                        $('#submissionModal').modal('hide');
                        showAlert(response.message || 'Submission saved successfully', 'success');
                        loadData();
                        loadSummary(); // Reload summary after save
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);

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

        // View submission
        function viewSubmission(id) {
            $.ajax({
                url: '{{ route('userSubmission.show', ':id') }}'.replace(':id', id),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;

                        // Populate basic information
                        $('#view_transaction_date').text(formatDate(data.transaction_date));
                        $('#view_user_name').text(data.user_name || '-');
                        $('#view_job_level').text(data.job_level ? data.job_level.job_level_name : '-');
                        $('#view_job_position').text(data.job_position ? data.job_position.job_position_name :
                            '-');
                        $('#view_program').text(data.program_id || '-');
                        $('#view_unit').text(data.unit_name || '-');
                        $('#view_purpose').text(data.purpose || '-');
                        $('#view_estimated_amount').html('<strong>' + formatCurrency(data.estimated_amount) +
                            '</strong>');
                        $('#view_status').html(getStatusBadge(data.status));
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

                        // Populate approval history if available
                        if (data.approvals && data.approvals.length > 0) {
                            let approvalHtml = '';
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
                url: '{{ route('userSubmission.show', ':id') }}'.replace(':id', id),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;

                        // Set submission ID and modal title
                        $('#submissionId').val(data.id);
                        $('#submissionModalLabel').text('Edit Submission');

                        // Set basic fields
                        $('#transactionDate').val(data.transaction_date);
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
                                url: '{{ route('userSubmission.programs', ':jobLevelId') }}'.replace(
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
                                                url: '{{ route('userSubmission.budgetItems', ':programId') }}'
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
                                                                                    `<option value="${item.id}" data-value="${item.total}" data-code="${item.stock_code || item.budget_code}" ${selected}>${item.label}</option>`;
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
                        url: '{{ route('userSubmission.destroy', ':id') }}'.replace(':id', id),
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

        function getStatusBadge(status) {
            const statusMap = {
                0: {
                    label: 'Submission',
                    class: 'bg-primary'
                },
                1: {
                    label: 'Approved Parent',
                    class: 'bg-info'
                },
                2: {
                    label: 'Approved Finance',
                    class: 'bg-info'
                },
                3: {
                    label: 'Approved Division',
                    class: 'bg-info'
                },
                4: {
                    label: 'Approved Finance Director',
                    class: 'bg-info'
                },
                5: {
                    label: 'Approved President Director',
                    class: 'bg-success'
                },
                6: {
                    label: 'Rejected',
                    class: 'bg-danger'
                },
                7: {
                    label: 'Paid',
                    class: 'bg-success'
                },
                8: {
                    label: 'Complete',
                    class: 'bg-secondary'
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
            return `<span class="badge ${statusInfo.class} badge-status">${statusInfo.label}</span>`;
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
    </script>
@endsection
