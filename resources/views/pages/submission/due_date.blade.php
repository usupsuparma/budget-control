@extends('layouts.master')

@section('title', 'Budget Due Date | Budget Control')

@section('title-sub', 'Transactions')
@section('pagetitle', 'Budget Due Date')

@section('css')
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
            
            {{-- === SUMMARY CARD === --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card stat-card border-danger shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1 fw-bold">Overdue Transactions</h6>
                                    <h4 class="mb-0 fw-extrabold text-danger" id="dueDateCountText">{{ $dueDateCount }}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="stat-icon text-danger">
                                        <i class="ri-time-line"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted mt-2 mb-0 small">Submission > D+2 / Accountability Report > D+7</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- === FILTER SECTION === --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Year</label>
                            <select id="filterYear" class="form-select">
                                <option value="all">All Years</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary" id="btnFilter">
                                <i class="ri-filter-line"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- === MAIN TABLE === --}}
            <div class="col-12">
                <div class="card card-h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="ri-calendar-todo-line me-2 text-danger"></i>Overdue Submission & Accountability Report Transactions</h5>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- === TRACKING MODAL === --}}
    <div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trackingModalLabel">Status Tracking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="tracking-timeline" id="timeline"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- === LPJ MODAL === --}}
    <div class="modal fade" id="lpjModal" tabindex="-1" aria-labelledby="lpjModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="lpjModalLabel">
                        <i class="ri-file-text-line me-2"></i>Accountability Report (LPJ)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="lpjForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="lpj_transaction_id" name="transaction_id">
                        <div id="lpjErrorAlert" class="alert alert-danger d-none" role="alert"></div>
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
                                                <label class="form-label fw-semibold">Realization Date</label>
                                                <input type="date" class="form-control" id="lpj_realization_date"
                                                    name="realization_date" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                                <th rowspan="2" class="align-middle text-center">Description</th>
                                                <th colspan="3" class="text-center bg-secondary bg-opacity-10">Submission</th>
                                                <th colspan="3" class="text-center bg-success bg-opacity-10">Realization Report</th>
                                            </tr>
                                            <tr>
                                                <th class="bg-secondary bg-opacity-10 text-center">Qty</th>
                                                <th class="bg-secondary bg-opacity-10 text-end">Price</th>
                                                <th class="bg-secondary bg-opacity-10 text-end">Total</th>
                                                <th class="bg-success bg-opacity-10 text-center">Qty</th>
                                                <th class="bg-success bg-opacity-10 text-end">Price</th>
                                                <th class="bg-success bg-opacity-10 text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="lpj_items_body">
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">Loading items...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card border">
                            <div class="card-body">
                                <label class="form-label fw-semibold"><i class="ri-attachment-2 me-1"></i>Attach Proof of Payment</label>
                                <input type="file" class="form-control" id="lpj_proof_of_payment" name="proof_of_payment" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="btnSubmitLpj"><i class="ri-save-line me-1"></i>Submit Accountability Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .tracking-timeline { position: relative; display: flex; flex-direction: column; gap: 18px; padding: 6px 4px; }
        .tt-item { position: relative; display: grid; grid-template-columns: 44px 1fr; gap: 12px; align-items: start; }
        .tt-item::before { content: ""; position: absolute; left: 22px; top: 44px; bottom: -18px; width: 2px; background: rgba(0, 0, 0, .12); }
        .tt-item:last-child::before { display: none; }
        .tt-icon { width: 44px; height: 44px; border-radius: 50%; display: grid; place-items: center; color: #fff; box-shadow: 0 6px 18px rgba(0, 0, 0, .08); position: relative; }
        .tt-dot { width: 12px; height: 12px; border-radius: 50%; background: rgba(255, 255, 255, .92); display: inline-block; }
        .tt-content { background: #fff; border: 1px solid rgba(0, 0, 0, .08); border-radius: 12px; padding: 12px 12px; }
        .sts { cursor: pointer; }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentPage = 1;

        $(document).ready(function() {
            loadData();

            $('#btnFilter').on('click', function() {
                currentPage = 1;
                loadData();
            });
        });

        function loadData() {
            const year = $('#filterYear').val();
            let url = `{{ route('userSubmission.dueDateData') }}`;

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    year: year,
                    page: currentPage
                },
                success: function(response) {
                    if (response.success) {
                        renderTable(response.data);
                        renderPagination(response.data);
                        $('#dueDateCountText').text(response.data.total);
                    }
                },
                error: function(xhr) {
                    showAlert('Error loading data', 'danger');
                }
            });
        }

        function renderTable(data) {
            let html = '';
            if (data.data.length === 0) {
                html = '<tr><td colspan="7" class="text-center">No overdue transactions found</td></tr>';
            } else {
                data.data.forEach((item, index) => {
                    const rowNumber = (data.current_page - 1) * data.per_page + index + 1;
                    const canSubmitLpj = item.can_submit_lpj || item.status === 2 || item.status === 3;
                    const lpjButton = canSubmitLpj ? `
                        <button type="button" class="btn btn-success" onclick="openLpjModal(${item.id})" title="Create LPJ">
                            <i class="ri-file-text-line"></i> LPJ
                        </button>
                    ` : '';

                    html += `
                        <tr>
                            <td>${rowNumber}</td>
                            <td>${formatDate(item.transaction_date)}</td>
                            <td>${item.user_name}</td>
                            <td>${item.purpose}</td>
                            <td>${formatCurrency(item.estimated_amount)}</td>
                            <td>${getStatusBadge(item.status, item.id)}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-info" onclick="viewSubmission(${item.id})" title="View Detail">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    ${lpjButton}
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#tableBody').html(html);
        }

        function renderPagination(data) {
            let paginationInfo = `Showing ${data.from || 0} to ${data.to || 0} of ${data.total || 0} entries`;
            $('#paginationInfo').text(paginationInfo);
            let paginationHtml = '<ul class="pagination mb-0">';
            paginationHtml += `<li class="page-item ${!data.prev_page_url ? 'disabled' : ''}"><a class="page-link" href="#" onclick="changePage(${data.current_page - 1}); return false;">«</a></li>`;
            for (let i = 1; i <= data.last_page; i++) {
                paginationHtml += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a></li>`;
            }
            paginationHtml += `<li class="page-item ${!data.next_page_url ? 'disabled' : ''}"><a class="page-link" href="#" onclick="changePage(${data.current_page + 1}); return false;">»</a></li>`;
            paginationHtml += '</ul>';
            $('#paginationLinks').html(paginationHtml);
        }

        function changePage(page) {
            currentPage = page;
            loadData();
        }

        function viewSubmission(id) {
            $.ajax({
                url: `{{ route('userSubmission.show', ':id') }}`.replace(':id', id),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#view_transaction_date').text(formatDate(data.transaction_date));
                        $('#view_planned_usage_date').text(data.planned_usage_date ? formatDate(data.planned_usage_date) : '-');
                        $('#view_user_name').text(data.user_name || '-');
                        $('#view_job_level').text(data.job_level ? data.job_level.job_level_name : '-');
                        $('#view_job_position').text(data.job_position ? data.job_position.job_position_name : '-');
                        $('#view_program').text(data.program ? data.program.activity : '-');
                        $('#view_unit').text(data.unit_name || '-');
                        $('#view_purpose').text(data.purpose || '-');
                        $('#view_estimated_amount').html('<strong>' + formatCurrency(data.estimated_amount) + '</strong>');
                        $('#view_status').html(getStatusBadge(data.status, data.id));
                        $('#view_urgency').text(data.urgency || '-');
                        let itemsHtml = '';
                        data.details.forEach((item, index) => {
                            itemsHtml += `<tr><td>${index + 1}</td><td>${item.goods_service_name}</td><td>${item.budget_name}</td><td class="text-end">${formatCurrency(item.balance)}</td><td>${item.unit_name}</td><td>${item.estimated_quantity}</td><td class="text-end">${formatCurrency(item.estimated_price)}</td><td class="text-end"><strong>${formatCurrency(item.estimated_total)}</strong></td></tr>`;
                        });
                        $('#view_items_body').html(itemsHtml);
                        $('#viewModal').modal('show');
                    }
                }
            });
        }

        function openLpjModal(transactionId) {
            $('#lpj_transaction_id').val(transactionId);
            const today = new Date().toISOString().split('T')[0];
            $('#lpj_submission_date').val(today);
            $('#lpj_realization_date').val(today);
            $.ajax({
                url: "{{ route('userSubmission.lpj.form', ':id') }}".replace(':id', transactionId),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#lpj_user_name').text(data.transaction.user_name);
                        $('#lpj_purpose').text(data.transaction.purpose);
                        let html = '';
                        data.details.forEach((item, index) => {
                            html += `<tr><td>${item.goods_service_name}<input type="hidden" name="items[${index}][detail_id]" value="${item.id}"></td><td>${item.estimated_quantity}</td><td>${formatCurrency(item.estimated_price)}</td><td>${formatCurrency(item.estimated_total)}</td><td><input type="number" class="form-control form-control-sm lpj-qty" name="items[${index}][fix_quantity]" value="${item.estimated_quantity}" min="0"></td><td><input type="number" class="form-control form-control-sm lpj-price" name="items[${index}][fix_price]" value="${item.estimated_price}" min="0"></td><td class="text-end lpj-item-total">${formatCurrency(item.estimated_total)}</td></tr>`;
                        });
                        $('#lpj_items_body').html(html);
                        $('#lpjModal').modal('show');
                    }
                }
            });
        }

        function formatDate(dateString) { const date = new Date(dateString); return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }); }
        function formatCurrency(number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number); }
        
        function getStatusBadge(status, id) { 
            let badgeClass = 'bg-secondary';
            let label = 'Unknown';
            
            switch(status) {
                case 0: badgeClass = 'bg-warning'; label = 'Submission'; break;
                case 1: badgeClass = 'bg-info'; label = 'Progress'; break;
                case 2: badgeClass = 'bg-primary'; label = 'Approved'; break;
                case 3: badgeClass = 'bg-success'; label = 'Paid'; break;
                case 4: badgeClass = 'bg-dark'; label = 'Completed'; break;
                case 5: badgeClass = 'bg-danger'; label = 'Rejected'; break;
                case -1: badgeClass = 'bg-secondary'; label = 'Cancelled'; break;
            }
            
            return `<span onclick="getbadgeinfo(${id})" class="badge ${badgeClass} badge-status sts">${label}</span>`; 
        }

        function getbadgeinfo(id) { $.ajax({ url: "{{ route('userSubmission.badgeinfo', ':id') }}".replace(':id', id), type: 'GET', success: function(r) { $("#timeline").html(r.data); $("#trackingModal").modal('show'); } }); }
        function showAlert(m, t) { Swal.fire({ icon: t === 'success' ? 'success' : 'error', title: t === 'success' ? 'Success!' : 'Error!', text: m, timer: 3000 }); }

        $('#lpjForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#lpj_transaction_id').val();
            $.ajax({
                url: "{{ route('userSubmission.lpj.submit', ':id') }}".replace(':id', id),
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(r) { if(r.success) { $('#lpjModal').modal('hide'); loadData(); showAlert(r.message, 'success'); } }
            });
        });
    </script>
@endsection
