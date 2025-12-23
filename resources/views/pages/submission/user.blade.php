@extends('layouts.master')

@section('title', 'Pengajuan Anggaran | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Daftar Pengajuan Anggaran')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<style>
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    .table-responsive {
        min-height: 400px;
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
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
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
</style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid">

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
        <div class="row mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm stat-card" style="border-left-color: #0d6efd !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">New Submission</h6>
                                <h3 class="mb-0">{{ $newSubmission }}</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-file-add-line stat-icon text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm stat-card" style="border-left-color: #ffc107 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Progress</h6>
                                <h3 class="mb-0">{{ $progress }}</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-time-line stat-icon text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm stat-card" style="border-left-color: #198754 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Paid</h6>
                                <h3 class="mb-0">{{ $paid }}</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-money-dollar-circle-line stat-icon text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm stat-card" style="border-left-color: #6c757d !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Completion</h6>
                                <h3 class="mb-0">{{ $completion }}</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-checkbox-circle-line stat-icon text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm stat-card" style="border-left-color: #6610f2 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total Submission</h6>
                                <h3 class="mb-0">{{ $totalSubmission }}</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-file-list-3-line stat-icon text-purple"></i>
                            </div>
                        </div>
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
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select id="filterStatus" class="form-select">
                            <option value="all">Semua Status</option>
                            @foreach($statuses as $status)
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
                        <button type="button" class="btn btn-success" id="btnAddData">
                            <i class="ri-add-line"></i> Add Data
                        </button>
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

{{-- === ADD/EDIT MODAL === --}}
<div class="modal fade" id="submissionModal" tabindex="-1" aria-labelledby="submissionModalLabel" aria-hidden="true">
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
                        <div class="col-md-6">
                            <label class="form-label">User</label>
                            <input type="text" class="form-control" id="userName" value="{{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="transactionDate" name="transaction_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Job Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="jobLevel" name="job_level_id" required>
                                <option value="">Select Job Level</option>
                                @foreach($jobLevels as $level)
                                    <option value="{{ $level->id }}">{{ $level->job_level_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Job Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="jobPosition" name="job_position_id" required>
                                <option value="">Select Job Position</option>
                                @foreach($jobPositions as $position)
                                    <option value="{{ $position->id }}">{{ $position->job_position_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program ID <span class="text-danger">*</span></label>
                            <select class="form-select" id="programId" name="program_id" required>
                                <option value="">Select Program</option>
                                @foreach($workplans as $workplan)
                                    <option value="{{ $workplan->id }}">{{ $workplan->activity }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
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
                                    <th width="20%">Description of Goods/Service <span class="text-danger">*</span></th>
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

                    <div class="row mt-3">
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

</main>
@endsection

@section('js')
<script type="module" src="{{ asset('assets/js/app.js') }}"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

<script>
// Global variables
let currentPage = 1;
let itemRowCounter = 0;
const budgetCodes = @json($budgetCodes);
const units = @json($units);
let availableBudgetItems = [];

$(document).ready(function() {
    // Load data on page load
    loadData();

    // Filter button
    $('#btnFilter').on('click', function() {
        currentPage = 1;
        loadData();
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
            $('#jobPosition').html('<option value="">Select Job Position</option>').prop('disabled', false);
            $('#programId').html('<option value="">Select Program</option>').prop('disabled', false);
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
let urlJobPositions = '{{ route("userSubmission.jobPositions", ":jobLevelId") }}'.replace(':jobLevelId', jobLevelId);
    $.ajax({
        url: urlJobPositions,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Job Position</option>';
                response.data.forEach(function(position) {
                    options += `<option value="${position.id}">${position.job_position_name}</option>`;
                });
                $('#jobPosition').html(options).prop('disabled', false);
            }
        },
        error: function(xhr) {
            showAlert('Error loading job positions', 'danger');
            $('#jobPosition').html('<option value="">Error loading positions</option>').prop('disabled', false);
        }
    });
}

// Load programs based on job level
function loadPrograms(jobLevelId) {
    let urlPrograms = '{{ route("userSubmission.programs", ":jobLevelId") }}'.replace(':jobLevelId', jobLevelId);
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
            $('#programId').html('<option value="">Error loading programs</option>').prop('disabled', false);
        }
    });
}

// Load budget items based on program ID
function loadBudgetItems(programId) {
    $.ajax({
        url: `/admission/budget-items/${programId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                availableBudgetItems = response.data;
                // Update all budget select dropdowns in item rows
                updateAllBudgetSelects();
            }
        },
        error: function(xhr) {
            showAlert('Error loading budget items', 'danger');
            availableBudgetItems = [];
            updateAllBudgetSelects();
        }
    });
}

// Update all budget select dropdowns
function updateAllBudgetSelects() {
    let options = '<option value="">Select Budget</option>';
    availableBudgetItems.forEach(function(item) {
        options += `<option value="${item.id}" data-value="${item.total}" data-code="${item.stock_code || item.budget_code}">${item.label}</option>`;
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
        url: '{{ route("userSubmission.data") }}',
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
        budgetOptions += `<option value="${item.id}" data-value="${item.total}" data-code="${item.stock_code || item.budget_code}">${item.label}</option>`;
    });
    
    // Prepare unit options
    let unitOptions = '<option value="">Select Unit</option>';
    units.forEach(function(unit) {
        unitOptions += `<option value="${unit.id}">${unit.unit || unit.unit_name}</option>`;
    });
    
    let html = `
        <tr data-row="${itemRowCounter}">
            <td>
                <input type="text" class="form-control form-control-sm goods-name-input" name="items[${itemRowCounter}][goods_service_name]" required>
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
                <input type="text" class="form-control form-control-sm price-input" name="items[${itemRowCounter}][price]" data-row="${itemRowCounter}" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm total-input bg-light" readonly>
            </td>
            <td>
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
    
    $(`[data-row="${itemRowCounter}"] .qty-input, [data-row="${itemRowCounter}"] .price-input`).on('input', function() {
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
        '{{ route("userSubmission.update", ":id") }}'.replace(':id', submissionId) :
        '{{ route("userSubmission.store") }}';
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
                                    .after(`<div class="invalid-feedback d-block">${errors[field][0]}</div>`);
                            } else if (fieldName === 'budget_id') {
                                row.find('.budget-select').addClass('is-invalid')
                                    .parent().append(`<div class="invalid-feedback d-block">${errors[field][0]}</div>`);
                            } else if (fieldName === 'unit_id') {
                                row.find('.unit-select').addClass('is-invalid')
                                    .parent().append(`<div class="invalid-feedback d-block">${errors[field][0]}</div>`);
                            } else if (fieldName === 'quantity') {
                                row.find('.qty-input').addClass('is-invalid')
                                    .after(`<div class="invalid-feedback d-block">${errors[field][0]}</div>`);
                            } else if (fieldName === 'price') {
                                row.find('.price-input').addClass('is-invalid')
                                    .after(`<div class="invalid-feedback d-block">${errors[field][0]}</div>`);
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
                                .after(`<div class="invalid-feedback d-block">${errors[field][0]}</div>`);
                        }
                    }
                });
                
                // Scroll to top of modal to see errors
                $('.modal-body').animate({ scrollTop: 0 }, 300);
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
        url: '{{ route("userSubmission.show", ":id") }}'.replace(':id', id),
        type: 'GET',
        success: function(response) {
            if (response.success) {
                // Display view modal (you can create a separate modal for viewing)
                alert('View functionality - ID: ' + id);
            }
        },
        error: function(xhr) {
            showAlert('Error loading submission', 'danger');
        }
    });
}

// Edit submission
function editSubmission(id) {
    $.ajax({
        url: '{{ route("userSubmission.show", ":id") }}'.replace(':id', id),
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                $('#submissionId').val(data.id);
                $('#submissionModalLabel').text('Edit Submission');
                $('#transactionDate').val(data.transaction_date);
                $('#purpose').val(data.purpose);
                $('#urgency').val(data.urgency);
                
                // Clear and add item rows
                $('#itemsTableBody').html('');
                data.details.forEach(detail => {
                    addItemRow();
                    const row = itemRowCounter;
                    $(`tr[data-row="${row}"] input[name="items[${row}][goods_service_name]"]`).val(detail.goods_service_name);
                    $(`tr[data-row="${row}"] select[name="items[${row}][budget_id]"]`).val(detail.budget_id).trigger('change');
                    $(`tr[data-row="${row}"] select[name="items[${row}][unit_id]"]`).val(detail.unit_id);
                    $(`tr[data-row="${row}"] input[name="items[${row}][quantity]"]`).val(detail.estimated_quantity);
                    $(`tr[data-row="${row}"] .price-input`).val(formatNumber(detail.estimated_price));
                    calculateRowTotal(row);
                });
                
                $('#submissionModal').modal('show');
            }
        },
        error: function(xhr) {
            showAlert('Error loading submission', 'danger');
        }
    });
}

// Delete submission
function deleteSubmission(id) {
    if (!confirm('Are you sure you want to delete this submission?')) {
        return;
    }

    $.ajax({
        url: '{{ route("userSubmission.destroy", ":id") }}'.replace(':id', id),
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                loadData();
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
    $('#programId').html('<option value="">Select Program</option>').prop('disabled', false);
}

// Helper functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
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
        0: { label: 'Submission', class: 'bg-primary' },
        1: { label: 'Approved Parent', class: 'bg-info' },
        2: { label: 'Approved Finance', class: 'bg-info' },
        3: { label: 'Approved Division', class: 'bg-info' },
        4: { label: 'Approved Finance Director', class: 'bg-info' },
        5: { label: 'Approved President Director', class: 'bg-success' },
        6: { label: 'Rejected', class: 'bg-danger' },
        7: { label: 'Paid', class: 'bg-success' },
        8: { label: 'Complete', class: 'bg-secondary' },
        '-1': { label: 'Cancelled', class: 'bg-dark' }
    };
    
    const statusInfo = statusMap[status] || { label: 'Unknown', class: 'bg-secondary' };
    return `<span class="badge ${statusInfo.class} badge-status">${statusInfo.label}</span>`;
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at the top of the container
    $('.container-fluid').prepend(alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
}

function showModalError(message, errors = null) {
    let errorHtml = `
        <div id="modalErrorAlert" class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="ri-error-warning-line me-2"></i>
            <strong>${message}</strong>
    `;
    
    if (errors) {
        errorHtml += '<ul class="mb-0 mt-2">';
        Object.keys(errors).forEach(function(field) {
            // Make error messages more readable
            let fieldLabel = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            if (field.startsWith('items.')) {
                const parts = field.split('.');
                const index = parseInt(parts[1]) + 1;
                const fieldName = parts[2].replace(/_/g, ' ');
                fieldLabel = `Item ${index} - ${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)}`;
            }
            errorHtml += `<li><strong>${fieldLabel}:</strong> ${errors[field][0]}</li>`;
        });
        errorHtml += '</ul>';
    }
    
    errorHtml += `
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Remove existing modal errors
    $('#modalErrorAlert').remove();
    
    // Add error at the top of modal body
    $('.modal-body').prepend(errorHtml);
}
</script>
@endsection