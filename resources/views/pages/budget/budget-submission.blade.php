@extends('layouts.master')

@section('title', 'Budget Movement | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Budget Movement')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <style>
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
            white-space: nowrap;
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

        .badge {
            padding: 0.35em 0.65em;
            font-size: 0.85em;
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .timeline-container {
            position: relative;
            margin-left: 16px;
            padding-left: 20px;
            border-left: 2px solid #dee2e6;
        }

        .timeline-item {
            position: relative;
            padding: 0 0 16px 12px;
            margin-bottom: 8px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -28px;
            top: 6px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #adb5bd;
            border: 2px solid #6c757d;
        }

        .timeline-item.completed::before {
            background: #198754;
            border-color: #198754;
        }

        .timeline-item.pending::before {
            background: #ffc107;
            border-color: #ffc107;
        }

        .timeline-item.current::before {
            background: #0d6efd;
            border-color: #0d6efd;
        }

        .timeline-item.rejected::before {
            background: #dc3545;
            border-color: #dc3545;
        }

        .timeline-item.skipped::before {
            background: #adb5bd;
        }

        .submission-item-details dl {
            margin-bottom: 0;
        }

        .submission-item-details dt {
            width: 130px;
            color: #6c757d;
        }

        .submission-item-details dd {
            margin-bottom: 0.45rem;
        }

        .approval-status-badge {
            cursor: pointer;
        }

        #submission_date {
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="row">
            <div class="col-12">
                <div class="card card-h-100 shadow-sm border">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h6 class="text-muted text-uppercase small mb-0">Budget Movement</h6>
                        </div>

                        <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#dataTab" role="tab">
                                    <i class="ri-exchange-funds-line me-2"></i>
                                    Budget Movement
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#approvalTab" role="tab">
                                    <i class="ri-check-double-line me-2"></i>
                                    Approval
                                    <span class="badge bg-danger ms-2 d-none" id="approvalPendingBadge">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#approvalHistoryTab" role="tab">
                                    <i class="ri-history-line me-2"></i>
                                    Approval History
                                </a>
                            </li>
                        </ul>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="dataTab">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <h5 class="card-title mb-0">Budget Movement</h5>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#budgetSubmissionModal" onclick="resetForm()">
                                        <i class="ri-add-line align-bottom me-1"></i> Add Data
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="budgetSubmissionTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="10%">Date</th>
                                                <th width="12%">Division</th>
                                                <th width="10%">Type</th>
                                                <th width="15%">Work Plan</th>
                                                <th width="15%">Description</th>
                                                <th width="10%">Estimation</th>
                                                <th width="10%">Budget Account</th>
                                                <th width="8%">Status</th>
                                                <th width="10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($budgetSubmissions as $index => $submission)
                                                <tr>
                                                    <td>{{ $budgetSubmissions->firstItem() + $index }}</td>
                                                    <td>{{ $submission->submission_date->format('d/m/Y') }}</td>
                                                    <td>{{ $submission->division->name ?? '-' }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $submission->type == 'add' ? 'info' : 'secondary' }}">
                                                            {{ $submission->type_label }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>{{ $submission->workPlan->activity ?? '-' }}</small>
                                                    </td>
                                                    <td>
                                                        <small>{{ Str::limit($submission->description, 50) }}</small>
                                                    </td>
                                                    <td class="text-end">Rp {{ number_format($submission->estimation_amount, 0, ',', '.') }}
                                                    </td>
                                                    <td>
                                                        <small>{{ $submission->budget_account_label }}</small>
                                                    </td>
                                                    <td>
                                                        @if ($submission->isPending() && $submission->hasPendingApproval())
                                                            <button type="button"
                                                                class="badge bg-info border-0 approval-status-badge"
                                                                onclick="showBudgetSubmissionApprovalTimeline({{ $submission->id }})"
                                                                title="View approval progress">
                                                                {{ $submission->approval_progress_label }}
                                                            </button>
                                                        @else
                                                            <span class="badge bg-{{ $submission->status == 0 ? 'warning' : $submission->status_color }}">
                                                                {{ $submission->approval_progress_label }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($submission->canBeEdited())
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-sm btn-warning"
                                                                    onclick="editSubmission({{ $submission->id }})" title="Edit">
                                                                    <i class="ri-edit-line"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-danger"
                                                                    onclick="deleteSubmission({{ $submission->id }})" title="Delete">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-primary"
                                                                    onclick="submitForApproval({{ $submission->id }})" title="Submit for Approval">
                                                                    <i class="ri-send-plane-2-line"></i>
                                                                </button>
                                                            </div>
                                                        @else
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-sm btn-info"
                                                                    onclick="viewBudgetSubmissionDetail({{ $submission->id }})" title="View Detail">
                                                                    <i class="ri-eye-line"></i>
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="approvalTab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        Pending Approval
                                        <span class="badge bg-danger" id="approvalCountHeader">0</span>
                                    </h6>
                                    <div id="bulkApprovalActions" style="display:none">
                                        <span id="selectedApprovalCount" class="me-2"></span>
                                        <button type="button" class="btn btn-sm btn-success me-2" onclick="handleBulkApproveSubmission()">
                                            <i class="ri-check-line me-1"></i>Approve Selected
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="handleBulkRejectSubmission()">
                                            <i class="ri-close-line me-1"></i>Reject Selected
                                        </button>
                                    </div>
                                </div>
                                <div id="pendingApprovalContainer">
                                    <p class="text-muted">No data loaded yet.</p>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="approvalHistoryTab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Approval History</h6>
                                    <span class="badge bg-success" id="approvalHistoryCountHeader">0</span>
                                </div>
                                <div id="approvedApprovalContainer">
                                    <p class="text-muted">No data loaded yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Submission Modal -->
        <div class="modal fade" id="budgetSubmissionModal" tabindex="-1" aria-labelledby="budgetSubmissionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="budgetSubmissionModalLabel">Add Budget Movement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="budgetSubmissionForm">
                        <input type="hidden" id="submission_id" name="submission_id">
                        <input type="hidden" id="form_method" name="_method" value="POST">

                        <div class="modal-body">
                            <div class="row mb-3">
                                <label for="division_id" class="col-sm-3 col-form-label">Division <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select" id="division_id" name="division_id" required>
                                        <option value="">Select Division</option>
                                        @php
                                            $userDivisionId = auth()->user()?->employment?->division_id ?? auth()->user()->division_id ?? null;
                                        @endphp
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}"
                                                {{ (count($divisions) == 1 || $userDivisionId == $division->id) ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="user_name" class="col-sm-3 col-form-label">User</label>
                                <div class="col-sm-9">
                                    @php
                                        $currentUser = $user ?? Auth::user();
                                        $currentUserName = trim(($currentUser->first_name ?? '') . ' ' . ($currentUser->last_name ?? ''));
                                    @endphp
                                    <input type="text" class="form-control" id="user_name"
                                        value="{{ $currentUserName ?: ($currentUser->name ?? '-') }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="submission_date" class="col-sm-3 col-form-label">Date <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="submission_date"
                                        name="submission_date" value="{{ date('Y-m-d') }}"
                                        min="{{ date('Y') }}-01-01" max="{{ date('Y') }}-12-31" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Type <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="type" id="type_add"
                                            value="add" checked>
                                        <label class="form-check-label" for="type_add">Add Budget</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="type" id="type_relocation"
                                            value="relocation">
                                        <label class="form-check-label" for="type_relocation">Relocation</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="work_plan_id" class="col-sm-3 col-form-label">Work Plan <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select" id="work_plan_id" name="work_plan_id" required>
                                        <option value="">Select Division first</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3 d-none" id="source_budget_account_row">
                                <label for="source_budget_account_id" class="col-sm-3 col-form-label">Source Budget Account <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select" id="source_budget_account_id" name="source_budget_account_id">
                                        <option value="">Select relocation type first</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="budget_account_id" class="col-sm-3 col-form-label">Budget Account <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select" id="budget_account_id" name="budget_account_id" required>
                                        <option value="">Select work plan first</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="description" class="col-sm-3 col-form-label">Description</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="estimation_amount_display" class="col-sm-3 col-form-label">Estimation <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="estimation_amount_display"
                                        inputmode="numeric" autocomplete="off" placeholder="0" required>
                                    <input type="hidden" id="estimation_amount" name="estimation_amount">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Budget Submission Detail Modal -->
        <div class="modal fade" id="budgetSubmissionDetailModal" tabindex="-1" aria-labelledby="budgetSubmissionDetailModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="budgetSubmissionDetailModalLabel">Budget Movement Detail</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="submission-item-details">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Date</dt>
                                <dd class="col-sm-8" id="detailSubmissionDate">-</dd>
                                <dt class="col-sm-4">Division</dt>
                                <dd class="col-sm-8" id="detailSubmissionDivision">-</dd>
                                <dt class="col-sm-4">Type</dt>
                                <dd class="col-sm-8" id="detailSubmissionType">-</dd>
                                <dt class="col-sm-4">Work Plan</dt>
                                <dd class="col-sm-8" id="detailSubmissionWorkPlan">-</dd>
                                <dt class="col-sm-4">Budget Account</dt>
                                <dd class="col-sm-8" id="detailSubmissionBudgetAccount">-</dd>
                                <dt class="col-sm-4" id="detailSourceBudgetAccountLabel">Source Budget Account</dt>
                                <dd class="col-sm-8" id="detailSourceBudgetAccount">-</dd>
                                <dt class="col-sm-4">Estimation</dt>
                                <dd class="col-sm-8" id="detailSubmissionAmount">-</dd>
                                <dt class="col-sm-4">Description</dt>
                                <dd class="col-sm-8" id="detailSubmissionDescription">-</dd>
                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8" id="detailSubmissionStatus">-</dd>
                                <dt class="col-sm-4">Created By</dt>
                                <dd class="col-sm-8" id="detailSubmissionCreator">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Timeline Modal -->
        <div class="modal fade" id="submissionApprovalTimelineModal" tabindex="-1" aria-labelledby="submissionApprovalTimelineModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="submissionApprovalTimelineModalLabel">Approval Detail</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="submission-item-details mb-3" id="submissionApprovalItemDetails"></div>
                        <div class="timeline-container" id="submissionApprovalTimeline"></div>
                    </div>
                    <div class="modal-footer" id="submissionApprovalModalFooter"></div>
                </div>
            </div>
        </div>

        <!-- Reject comment modal -->
        <div class="modal fade" id="submissionRejectModal" tabindex="-1" aria-labelledby="submissionRejectModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Approval</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="rejectDetailId">
                        <div class="mb-3">
                            <label for="rejectComments" class="form-label">Reason</label>
                            <textarea id="rejectComments" class="form-control" rows="4" required
                                placeholder="Please provide rejection reason"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" onclick="confirmSubmissionReject()">Reject</button>
                    </div>
                </div>
            </div>
        </div>

        @php
            $budgetSubmissionRoutes = [
                'data' => route('budget.submission.data'),
                'store' => route('budget.submission.store'),
                'edit' => route('budget.submission.edit', ['id' => '__ID__']),
                'detail' => route('budget.submission.detail', ['id' => '__ID__']),
                'approvalTimeline' => route('budget.submission.approval.timeline', ['id' => '__ID__']),
                'update' => route('budget.submission.update', ['id' => '__ID__']),
                'destroy' => route('budget.submission.destroy', ['id' => '__ID__']),
                'submit' => route('budget.submission.submit', ['id' => '__ID__']),
                'workplansByDivision' => route('budget.submission.workplansByDivision'),
                'budgetCodesAll' => route('budget.submission.budgetCodesAll'),
                'budgetCodes' => route('budget.submission.budgetCodes'),
                'approvalDetailApprove' => route('budget.submission.approval.detail.approve', ['detailId' => '__DETAIL_ID__']),
                'approvalDetailReject' => route('budget.submission.approval.detail.reject', ['detailId' => '__DETAIL_ID__']),
                'approvalPending' => route('budget.submission.approvals.pending'),
                'approvalApproved' => route('budget.submission.approvals.approved'),
                'approvalBulkProcess' => route('budget.submission.approvals.bulk-process'),
            ];
        @endphp
        <div id="budget-submission-route-config" style="display:none;"
            data-routes="{{ json_encode($budgetSubmissionRoutes) }}"
            data-csrf="{{ csrf_token() }}"></div>
    </div>
@endsection

@section('js')

    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

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

    <script>
        const routeConfigElement = document.getElementById('budget-submission-route-config');
        const ROUTES = routeConfigElement ? JSON.parse(routeConfigElement.dataset.routes) : {};
        const CSRF_TOKEN = routeConfigElement ? routeConfigElement.dataset.csrf : '';

        let divisionChoice, workPlanChoice, budgetAccountChoice, sourceBudgetAccountChoice;
        let budgetAccountQuery = '';
        let budgetAccountPage = 1;
        let budgetAccountLoading = false;
        let budgetAccountHasMore = true;
        let budgetAccountSearchTimer;
        let budgetAccountScrollBound = false;
        let sourceBudgetAccountQuery = '';
        let sourceBudgetAccountPage = 1;
        let sourceBudgetAccountLoading = false;
        let sourceBudgetAccountHasMore = true;
        let sourceBudgetAccountSearchTimer;
        let sourceBudgetAccountScrollBound = false;
        const budgetAccountPageSize = 20;
        let dataTable;
        let pendingApprovals = [];
        let approvedApprovals = [];
        let approvalSourceBudgetAccountChoice;
        let approvalSourceBudgetAccountSearchTimer;

        const routeWithId = (template, id, placeholder = '__ID__') => {
            return template.replace(placeholder, String(id));
        };

        const routeWithDetailId = (template, detailId) => {
            return template.replace('__DETAIL_ID__', String(detailId));
        };

        document.addEventListener('DOMContentLoaded', function() {
            initDataTable();
            initTabEvents();
            initBudgetChoices();

            divisionChoice = new Choices('#division_id', {
                searchEnabled: true,
                removeItemButton: false,
                placeholder: true,
                placeholderValue: 'Select Division'
            });

            workPlanChoice = new Choices('#work_plan_id', {
                searchEnabled: true,
                removeItemButton: false,
                placeholder: true,
                placeholderValue: 'Select Work Plan'
            });

            // Filter work plans when division changes
            document.getElementById('division_id').addEventListener('change', function(e) {
                loadWorkPlans(e.target.value);
            });

            document.getElementById('work_plan_id').addEventListener('change', function() {
                resetBudgetItemChoices();
            });

            document.querySelectorAll('input[name="type"]').forEach(input => {
                input.addEventListener('change', toggleMovementTypeFields);
            });

            if (document.getElementById('division_id').value) {
                loadWorkPlans(document.getElementById('division_id').value);
            }

            initBudgetAccountChoices();
            initSourceBudgetAccountChoices();
            toggleMovementTypeFields();
            initEstimationAmountFormatter();
            initSubmissionDatePicker();

            document.getElementById('budgetSubmissionForm').addEventListener('submit', handleFormSubmit);

            // Default load
            loadPendingSubmissionApprovals();
            loadApprovedSubmissionApprovals();
        });

        function initSubmissionDatePicker() {
            const dateInput = document.getElementById('submission_date');
            if (!dateInput) return;

            const openDatePicker = () => {
                if (dateInput.disabled || dateInput.readOnly) return;

                if (typeof dateInput.showPicker === 'function') {
                    try {
                        dateInput.showPicker();
                        return;
                    } catch (error) {
                        // Some browsers restrict showPicker outside direct user interaction.
                    }
                }

                dateInput.focus();
            };

            dateInput.addEventListener('click', openDatePicker);
        }

        function initTabEvents() {
            document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(event) {
                    const target = event.target.getAttribute('href');
                    if (target === '#approvalTab') {
                        loadPendingSubmissionApprovals();
                    } else if (target === '#approvalHistoryTab') {
                        loadApprovedSubmissionApprovals();
                    }
                });
            });
        }

        function initDataTable() {
            dataTable = $('#budgetSubmissionTable').DataTable({
                processing: true,
                serverSide: false,
                pageLength: 15,
                order: [
                    [1, 'desc']
                ],
                language: {
                    search: 'Search:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total entries)',
                    emptyTable: 'No data available',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Previous'
                    }
                },
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }]
            });
        }

        function refreshTable() {
            fetch(ROUTES.data, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        if (dataTable) {
                            dataTable.destroy();
                        }
                        $('#budgetSubmissionTable tbody').html(result.html);
                        initDataTable();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: result.message || 'Failed to load data'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load budget movement list.'
                    });
                });
        }

        function handleFormSubmit(e) {
            e.preventDefault();

            const form = e.target;
            syncEstimationAmount();
            const formData = new FormData(form);
            const submissionId = document.getElementById('submission_id').value;

            let url = ROUTES.store;
            if (submissionId) {
                url = routeWithId(ROUTES.update, submissionId);
                formData.append('_method', 'PUT');
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const oldText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('budgetSubmissionModal')).hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: result.message,
                            timer: 1200,
                            showConfirmButton: false
                        });
                        setTimeout(() => refreshTable(), 1000);
                    } else {
                        let errorMessage = result.message || 'Failed to save budget submission.';
                        if (result.errors) {
                            errorMessage += '<ul class="text-start mt-2">';
                            Object.values(result.errors).forEach(list => {
                                list.forEach(err => {
                                    errorMessage += `<li>${err}</li>`;
                                });
                            });
                            errorMessage += '</ul>';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            html: errorMessage
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred. Please try again.'
                    });
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = oldText;
                });
        }

        function getEstimationDigits(value) {
            return String(value || '').replace(/\D/g, '');
        }

        function formatEstimationAmount(value) {
            const digits = getEstimationDigits(value);
            if (!digits) return '';

            return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function setEstimationAmount(value) {
            const displayEl = document.getElementById('estimation_amount_display');
            const hiddenEl = document.getElementById('estimation_amount');
            const digits = getEstimationDigits(value);

            if (displayEl) {
                displayEl.value = formatEstimationAmount(digits);
            }

            if (hiddenEl) {
                hiddenEl.value = digits;
            }
        }

        function syncEstimationAmount() {
            const displayEl = document.getElementById('estimation_amount_display');
            if (!displayEl) return;

            setEstimationAmount(displayEl.value);
        }

        function initEstimationAmountFormatter() {
            const displayEl = document.getElementById('estimation_amount_display');
            if (!displayEl) return;

            displayEl.addEventListener('input', function() {
                const cursorAtEnd = this.selectionStart === this.value.length;
                setEstimationAmount(this.value);
                if (cursorAtEnd) {
                    this.setSelectionRange(this.value.length, this.value.length);
                }
            });

            displayEl.addEventListener('blur', syncEstimationAmount);
        }

        function getSelectedWorkPlanId() {
            return document.getElementById('work_plan_id').value || '';
        }

        function setChoicePlaceholder(choice, label) {
            if (!choice) return;
            choice.clearStore();
            choice.setChoices([{
                value: '',
                label,
                disabled: true,
                selected: true
            }], 'value', 'label', true);
        }

        function resetBudgetItemChoices() {
            const hasWorkPlan = Boolean(getSelectedWorkPlanId());
            setChoicePlaceholder(
                budgetAccountChoice,
                hasWorkPlan ? 'Select Budget Account' : 'Select work plan first'
            );
            setChoicePlaceholder(
                sourceBudgetAccountChoice,
                hasWorkPlan ? 'Select Source Budget Account' : 'Select work plan first'
            );
            budgetAccountQuery = '';
            budgetAccountPage = 1;
            budgetAccountHasMore = true;
            sourceBudgetAccountQuery = '';
            sourceBudgetAccountPage = 1;
            sourceBudgetAccountHasMore = true;
        }

        function toggleMovementTypeFields() {
            const isRelocation = document.getElementById('type_relocation').checked;
            const sourceRow = document.getElementById('source_budget_account_row');
            const sourceSelect = document.getElementById('source_budget_account_id');

            if (sourceRow) {
                sourceRow.classList.toggle('d-none', !isRelocation);
            }

            if (sourceSelect) {
                sourceSelect.required = isRelocation;
            }

            if (!isRelocation && sourceBudgetAccountChoice) {
                sourceBudgetAccountChoice.setChoiceByValue('');
            }
        }

        function initBudgetAccountChoices() {
            const selectEl = document.getElementById('budget_account_id');
            if (!selectEl) return;

            selectEl.innerHTML = '<option value="">Select Budget Account</option>';

            budgetAccountChoice = new Choices(selectEl, {
                searchEnabled: true,
                searchChoices: false,
                removeItemButton: false,
                placeholder: true,
                placeholderValue: 'Select Budget Account',
                searchPlaceholderValue: 'Search budget account...',
                shouldSort: false
            });

            function bindScrollListener() {
                if (budgetAccountScrollBound) return;
                budgetAccountScrollBound = true;

                const listEl = budgetAccountChoice.choiceList && budgetAccountChoice.choiceList.element;
                if (!listEl) return;

                listEl.addEventListener('scroll', function() {
                    const threshold = 60;
                    if (listEl.scrollTop + listEl.clientHeight >= listEl.scrollHeight - threshold) {
                        if (!budgetAccountLoading && budgetAccountHasMore) {
                            budgetAccountPage++;
                            fetchBudgetAccountCodes(budgetAccountQuery, budgetAccountPage, false);
                        }
                    }
                });
            }

            function triggerLoad() {
                budgetAccountPage = 1;
                budgetAccountHasMore = true;
                fetchBudgetAccountCodes(budgetAccountQuery, 1, true);
                bindScrollListener();
            }

            selectEl.addEventListener('showDropdown', triggerLoad);
            setTimeout(function() {
                const containerEl = budgetAccountChoice.containerOuter && budgetAccountChoice.containerOuter.element;
                if (!containerEl) return;
                containerEl.addEventListener('click', function() {
                    if (budgetAccountChoice.isOpen) {
                        triggerLoad();
                    }
                });
            }, 0);

            selectEl.addEventListener('search', function(e) {
                const query = (e && e.detail && e.detail.value) ? e.detail.value : '';
                clearTimeout(budgetAccountSearchTimer);
                budgetAccountSearchTimer = setTimeout(() => {
                    budgetAccountQuery = query;
                    budgetAccountPage = 1;
                    budgetAccountHasMore = true;
                    fetchBudgetAccountCodes(budgetAccountQuery, 1, true);
                }, 300);
            });

            setTimeout(function() {
                const inputEl = budgetAccountChoice.input && budgetAccountChoice.input.element;
                if (!inputEl) return;
                inputEl.addEventListener('input', function() {
                    if (this.value === '') {
                        clearTimeout(budgetAccountSearchTimer);
                        budgetAccountQuery = '';
                        budgetAccountPage = 1;
                        budgetAccountHasMore = true;
                        fetchBudgetAccountCodes('', 1, true);
                    }
                });
            }, 0);
        }

        function normalizeLabel(item) {
            const record = item || {};
            const code = record.budget_code || record.stock_code || record.value || '-';
            const name = record.name || '';
            return `${code} - ${name}`.trim().replace(/ - $/, '');
        }

        function fetchBudgetAccountCodes(query, page, replace = false) {
            if (budgetAccountLoading) return;
            if (!replace && !budgetAccountHasMore) return;
            if (!budgetAccountChoice) return;
            if (!getSelectedWorkPlanId()) {
                setChoicePlaceholder(budgetAccountChoice, 'Select work plan first');
                return;
            }

            budgetAccountLoading = true;
            const params = new URLSearchParams({
                q: query || '',
                page: String(page),
                limit: String(budgetAccountPageSize),
                work_plan_id: getSelectedWorkPlanId(),
            });

            fetch(`${ROUTES.budgetCodesAll}?${params.toString()}`)
                .then(response => response.json())
                .then(payload => {
                    if (!payload || !payload.success) return;
                    budgetAccountHasMore = payload.has_more || false;
                    const results = payload.data || [];
                    const choices = results.map(item => ({
                        value: String(item.value),
                        label: item.label || normalizeLabel(item),
                    }));
                    budgetAccountChoice.setChoices(choices, 'value', 'label', replace);
                })
                .catch(() => {})
                .finally(() => {
                    budgetAccountLoading = false;
                });
        }

        function ensureBudgetAccountChoiceSelected(budgetAccountId) {
            if (!budgetAccountChoice || !budgetAccountId) return Promise.resolve();

            const target = String(budgetAccountId);
            const store = budgetAccountChoice._store || {};
            const existingChoices = Array.isArray(store.choices) ? store.choices : [];
            const exists = existingChoices.some(item => String(item.value) === target);

            if (exists) {
                budgetAccountChoice.setChoiceByValue(target);
                return Promise.resolve();
            }

            const params = new URLSearchParams({ id: target });

            return fetch(`${ROUTES.budgetCodesAll}?${params.toString()}`)
                .then(response => response.json())
                .then(payload => {
                    if (!payload || !payload.success || !payload.data || payload.data.length === 0) return;

                    const item = payload.data[0];
                    budgetAccountChoice.setChoices([{
                        value: String(item.value),
                        label: item.label || normalizeLabel(item),
                    }], 'value', 'label', false);
                    budgetAccountChoice.setChoiceByValue(String(item.value));
                });
        }

        function initSourceBudgetAccountChoices() {
            const selectEl = document.getElementById('source_budget_account_id');
            if (!selectEl) return;

            selectEl.innerHTML = '<option value="">Select Source Budget Account</option>';

            sourceBudgetAccountChoice = new Choices(selectEl, {
                searchEnabled: true,
                searchChoices: false,
                removeItemButton: false,
                placeholder: true,
                placeholderValue: 'Select Source Budget Account',
                searchPlaceholderValue: 'Search source budget account...',
                shouldSort: false
            });

            function bindScrollListener() {
                if (sourceBudgetAccountScrollBound) return;
                sourceBudgetAccountScrollBound = true;

                const listEl = sourceBudgetAccountChoice.choiceList && sourceBudgetAccountChoice.choiceList.element;
                if (!listEl) return;

                listEl.addEventListener('scroll', function() {
                    const threshold = 60;
                    if (listEl.scrollTop + listEl.clientHeight >= listEl.scrollHeight - threshold) {
                        if (!sourceBudgetAccountLoading && sourceBudgetAccountHasMore) {
                            sourceBudgetAccountPage++;
                            fetchSourceBudgetAccountCodes(sourceBudgetAccountQuery, sourceBudgetAccountPage, false);
                        }
                    }
                });
            }

            function triggerLoad() {
                sourceBudgetAccountPage = 1;
                sourceBudgetAccountHasMore = true;
                fetchSourceBudgetAccountCodes(sourceBudgetAccountQuery, 1, true);
                bindScrollListener();
            }

            selectEl.addEventListener('showDropdown', triggerLoad);
            setTimeout(function() {
                const containerEl = sourceBudgetAccountChoice.containerOuter && sourceBudgetAccountChoice.containerOuter.element;
                if (!containerEl) return;
                containerEl.addEventListener('click', function() {
                    if (sourceBudgetAccountChoice.isOpen) {
                        triggerLoad();
                    }
                });
            }, 0);

            selectEl.addEventListener('search', function(e) {
                const query = (e && e.detail && e.detail.value) ? e.detail.value : '';
                clearTimeout(sourceBudgetAccountSearchTimer);
                sourceBudgetAccountSearchTimer = setTimeout(() => {
                    sourceBudgetAccountQuery = query;
                    sourceBudgetAccountPage = 1;
                    sourceBudgetAccountHasMore = true;
                    fetchSourceBudgetAccountCodes(sourceBudgetAccountQuery, 1, true);
                }, 300);
            });

            setTimeout(function() {
                const inputEl = sourceBudgetAccountChoice.input && sourceBudgetAccountChoice.input.element;
                if (!inputEl) return;
                inputEl.addEventListener('input', function() {
                    if (this.value === '') {
                        clearTimeout(sourceBudgetAccountSearchTimer);
                        sourceBudgetAccountQuery = '';
                        sourceBudgetAccountPage = 1;
                        sourceBudgetAccountHasMore = true;
                        fetchSourceBudgetAccountCodes('', 1, true);
                    }
                });
            }, 0);
        }

        function fetchSourceBudgetAccountCodes(query, page, replace = false) {
            if (sourceBudgetAccountLoading) return;
            if (!replace && !sourceBudgetAccountHasMore) return;
            if (!sourceBudgetAccountChoice) return;
            if (!getSelectedWorkPlanId()) {
                setChoicePlaceholder(sourceBudgetAccountChoice, 'Select work plan first');
                return;
            }

            sourceBudgetAccountLoading = true;
            const params = new URLSearchParams({
                q: query || '',
                page: String(page),
                limit: String(budgetAccountPageSize),
                work_plan_id: getSelectedWorkPlanId(),
            });

            fetch(`${ROUTES.budgetCodesAll}?${params.toString()}`)
                .then(response => response.json())
                .then(payload => {
                    if (!payload || !payload.success) return;
                    sourceBudgetAccountHasMore = payload.has_more || false;
                    const results = payload.data || [];
                    const choices = results.map(item => ({
                        value: String(item.value),
                        label: item.label || normalizeLabel(item),
                    }));
                    sourceBudgetAccountChoice.setChoices(choices, 'value', 'label', replace);
                })
                .catch(() => {})
                .finally(() => {
                    sourceBudgetAccountLoading = false;
                });
        }

        function ensureSourceBudgetAccountChoiceSelected(budgetAccountId) {
            if (!sourceBudgetAccountChoice || !budgetAccountId) return Promise.resolve();

            const target = String(budgetAccountId);
            const store = sourceBudgetAccountChoice._store || {};
            const existingChoices = Array.isArray(store.choices) ? store.choices : [];
            const exists = existingChoices.some(item => String(item.value) === target);

            if (exists) {
                sourceBudgetAccountChoice.setChoiceByValue(target);
                return Promise.resolve();
            }

            const params = new URLSearchParams({ id: target });

            return fetch(`${ROUTES.budgetCodesAll}?${params.toString()}`)
                .then(response => response.json())
                .then(payload => {
                    if (!payload || !payload.success || !payload.data || payload.data.length === 0) return;

                    const item = payload.data[0];
                    sourceBudgetAccountChoice.setChoices([{
                        value: String(item.value),
                        label: item.label || normalizeLabel(item),
                    }], 'value', 'label', false);
                    sourceBudgetAccountChoice.setChoiceByValue(String(item.value));
                });
        }

        function loadWorkPlans(divisionId, selectedWorkPlanId = null) {
            if (!divisionId) {
                if (workPlanChoice) {
                    workPlanChoice.clearStore();
                    workPlanChoice.setChoices([{
                        value: '',
                        label: 'Select Division first',
                        disabled: true,
                        selected: true
                    }], 'value', 'label', true);
                }
                resetBudgetItemChoices();
                return Promise.resolve();
            }

            if (workPlanChoice) {
                workPlanChoice.clearStore();
                workPlanChoice.setChoices([{
                    value: '',
                    label: 'Loading work plans...',
                    disabled: true
                }], 'value', 'label', true);
            }

            resetBudgetItemChoices();

            return fetch(`${ROUTES.workplansByDivision}?division_id=${divisionId}`)
                .then(response => response.json())
                .then(data => {
                    if (!workPlanChoice) return;

                    workPlanChoice.clearStore();

                    if (data.length === 0) {
                        workPlanChoice.setChoices([{
                            value: '',
                            label: 'No approved work plans found for this year',
                            disabled: true,
                            selected: true
                        }], 'value', 'label', true);
                        return;
                    }

                    workPlanChoice.setChoices([{
                        value: '',
                        label: 'Select Work Plan',
                        disabled: true,
                        selected: !selectedWorkPlanId
                    }], 'value', 'label', true);

                    workPlanChoice.setChoices(data.map(item => ({
                        value: String(item.value),
                        label: item.label,
                        selected: selectedWorkPlanId && String(item.value) === String(selectedWorkPlanId)
                    })), 'value', 'label', false);
                })
                .catch(() => {
                    if (!workPlanChoice) return;
                    workPlanChoice.setChoices([{
                        value: '',
                        label: 'Error loading work plans',
                        disabled: true
                    }], 'value', 'label', true);
                });
        }

        function initBudgetChoices() {
            // intentionally left for future custom enhancements
        }

        function resetForm() {
            document.getElementById('budgetSubmissionForm').reset();
            document.getElementById('submission_id').value = '';
            document.getElementById('form_method').value = 'POST';
            document.getElementById('budgetSubmissionModalLabel').textContent = 'Add Budget Movement';
            document.getElementById('submission_date').value = '{{ date('Y-m-d') }}';
            setEstimationAmount('');

            if (divisionChoice) {
                const selectedDefault = document.querySelector('#division_id option[selected]');
                if (selectedDefault) {
                    divisionChoice.setChoiceByValue(selectedDefault.value);
                    loadWorkPlans(selectedDefault.value);
                } else {
                    divisionChoice.setChoiceByValue('');
                    loadWorkPlans('');
                }
            }

            if (budgetAccountChoice) budgetAccountChoice.setChoiceByValue('');
            if (sourceBudgetAccountChoice) sourceBudgetAccountChoice.setChoiceByValue('');
            toggleMovementTypeFields();
        }

        function editSubmission(id) {
            fetch(routeWithId(ROUTES.edit, id))
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: result.message
                        });
                        return;
                    }

                    const data = result.data;
                    document.getElementById('submission_id').value = data.id;
                    document.getElementById('form_method').value = 'PUT';
                    document.getElementById('budgetSubmissionModalLabel').textContent = 'Edit Budget Submission';
                    document.getElementById('submission_date').value = data.submission_date;
                    document.getElementById('description').value = data.description || '';
                    setEstimationAmount(data.estimation_amount);

                    let workPlanLoad = Promise.resolve();
                    if (divisionChoice) {
                        divisionChoice.setChoiceByValue(String(data.division_id));
                        workPlanLoad = loadWorkPlans(data.division_id, data.work_plan_id);
                    }

                    if (data.type === 'add') {
                        document.getElementById('type_add').checked = true;
                    } else {
                        document.getElementById('type_relocation').checked = true;
                    }

                    toggleMovementTypeFields();

                    workPlanLoad.then(() => Promise.all([
                        budgetAccountChoice && data.budget_account_id
                            ? ensureBudgetAccountChoiceSelected(data.budget_account_id)
                            : Promise.resolve(),
                        sourceBudgetAccountChoice && data.source_budget_account_id
                            ? ensureSourceBudgetAccountChoiceSelected(data.source_budget_account_id)
                            : Promise.resolve()
                    ]));

                    const modal = new bootstrap.Modal(document.getElementById('budgetSubmissionModal'));
                    modal.show();
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load submission data.'
                    });
                });
        }

        function viewBudgetSubmissionDetail(id) {
            fetch(routeWithId(ROUTES.detail, id), {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: result.message
                        });
                        return;
                    }

                    const data = result.data;
                    document.getElementById('detailSubmissionDate').textContent = data.submission_date || '-';
                    document.getElementById('detailSubmissionDivision').textContent = data.division || '-';
                    document.getElementById('detailSubmissionType').textContent = data.type_label || '-';
                    document.getElementById('detailSubmissionWorkPlan').textContent = data.work_plan || '-';
                    document.getElementById('detailSubmissionBudgetAccount').textContent = data.budget_account || '-';
                    const sourceLabel = document.getElementById('detailSourceBudgetAccountLabel');
                    const sourceValue = document.getElementById('detailSourceBudgetAccount');
                    const showSource = (data.source_budget_account && data.source_budget_account !== '-');
                    sourceLabel.classList.toggle('d-none', !showSource);
                    sourceValue.classList.toggle('d-none', !showSource);
                    sourceValue.textContent = data.source_budget_account || '-';
                    document.getElementById('detailSubmissionAmount').textContent = formatIdr(data.estimation_amount || 0);
                    document.getElementById('detailSubmissionDescription').textContent = data.description || '-';
                    document.getElementById('detailSubmissionCreator').textContent = data.created_by || '-';
                    document.getElementById('detailSubmissionStatus').innerHTML =
                        `<span class="badge bg-${data.status_color}">${data.status_label}</span>`;

                    const modal = new bootstrap.Modal(document.getElementById('budgetSubmissionDetailModal'));
                    modal.show();
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load budget submission detail.'
                    });
                });
        }

        function deleteSubmission(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(routeWithId(ROUTES.destroy, id), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: result.message,
                                timer: 1200,
                                showConfirmButton: false
                            });
                            setTimeout(() => refreshTable(), 1000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: result.message
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete submission.'
                        });
                    });
            });
        }

        function submitForApproval(id) {
            Swal.fire({
                title: 'Submit for Approval?',
                text: 'The submission will enter approval workflow.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Submit'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(routeWithId(ROUTES.submit, id), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            });
                            refreshTable();
                            loadPendingSubmissionApprovals();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: data.message
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to submit approval.'
                        });
                    });
            });
        }

        function formatIdr(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(value || 0));
        }

        function toDateLabel(value) {
            if (!value) return '-';
            const date = new Date(value);
            return date.toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function loadPendingSubmissionApprovals() {
            $('#pendingApprovalContainer').html(`<div class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Loading...</div>`);
            fetch(ROUTES.approvalPending, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(response => {
                    if (!response.success) {
                        $('#pendingApprovalContainer').html(`<div class="alert alert-warning">${response.message || 'Failed to load pending approvals'}</div>`);
                        return;
                    }

                    pendingApprovals = response.data || [];
                    const count = response.count || 0;
                    const badge = document.getElementById('approvalPendingBadge');
                    const countEl = document.getElementById('approvalCountHeader');
                    if (count > 0) {
                        badge.textContent = count;
                        badge.classList.remove('d-none');
                    } else {
                        badge.classList.add('d-none');
                    }
                    if (countEl) countEl.textContent = count;

                    renderPendingApprovals(pendingApprovals);
                })
                .catch(() => {
                    $('#pendingApprovalContainer').html(`<div class="alert alert-danger">Failed to load pending approvals.</div>`);
                });
        }

        function loadApprovedSubmissionApprovals() {
            $('#approvedApprovalContainer').html(`<div class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Loading...</div>`);
            fetch(ROUTES.approvalApproved, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(response => {
                    if (!response.success) {
                        $('#approvedApprovalContainer').html(`<div class="alert alert-warning">${response.message || 'Failed to load history'}</div>`);
                        return;
                    }
                    approvedApprovals = response.data || [];
                    const count = response.count || 0;
                    const countEl = document.getElementById('approvalHistoryCountHeader');
                    if (countEl) countEl.textContent = String(count);
                    renderApprovedApprovals(approvedApprovals);
                })
                .catch(() => {
                    $('#approvedApprovalContainer').html(`<div class="alert alert-danger">Failed to load approval history.</div>`);
                });
        }

        function renderPendingApprovals(items) {
            if (!items.length) {
                $('#pendingApprovalContainer').html(`<div class="text-muted text-center py-5">No pending approvals.</div>`);
                $('#bulkApprovalActions').hide();
                return;
            }

            let html = `<div class="table-responsive"><table class="table table-bordered table-hover align-middle" id="pendingApprovalListTable"><thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:40px;"><input type="checkbox" class="form-check-input" id="selectAllSubmissionApprovals"></th>
                        <th>#</th><th>Ref No</th><th>Requested At</th><th>Submission</th><th>Division</th><th>Type</th><th class="text-end">Amount</th><th>Level</th><th class="text-center">Action</th>
                    </tr>
                </thead><tbody>`;

            items.forEach((approval, index) => {
                const submission = approval.submission || {};
                const levelText = `${approval.level || 0} / ${approval.total_levels || 0}`;
                const requiresSource = Boolean(submission.requires_source_budget_selection);
                const checkboxState = requiresSource
                    ? 'disabled title="Pilih source budget melalui detail approval"'
                    : '';
                const approveAction = requiresSource
                    ? `showSubmissionApprovalDetail(${index}, 'pending')`
                    : `approveSubmissionDetail(${approval.detail_id})`;
                const approveTitle = requiresSource
                    ? 'Select source budget before approve'
                    : 'Approve';
                html += `<tr>
                    <td class="text-center"><input type="checkbox" class="form-check-input submission-approval-checkbox" value="${approval.detail_id}" ${checkboxState}></td>
                    <td>${index + 1}</td>
                    <td><span class="fw-semibold text-primary">${approval.reference_number || '-'}</span></td>
                    <td>${toDateLabel(approval.requested_at)}</td>
                    <td>${submission.description || '-'}</td>
                    <td>${submission.division_name || '-'}</td>
                    <td>${submission.type_label || '-'}</td>
                    <td class="text-end">${formatIdr(submission.estimation_amount || 0)}</td>
                    <td class="text-center"><span class="badge bg-info">${levelText}</span></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info" onclick="showSubmissionApprovalDetail(${index}, 'pending')" title="Detail">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-success" onclick="${approveAction}" title="${approveTitle}">
                                <i class="ri-check-line"></i>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="openSubmissionReject(${approval.detail_id})" title="Reject">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });

            html += '</tbody></table></div>';
            $('#pendingApprovalContainer').html(html);
            initSubmissionApprovalCheckboxListeners();
        }

        function renderApprovedApprovals(items) {
            if (!items.length) {
                $('#approvedApprovalContainer').html(`<div class="text-muted text-center py-5">No approved history.</div>`);
                return;
            }

            let html = `<div class="table-responsive"><table class="table table-bordered table-hover align-middle" id="approvedApprovalListTable"><thead class="table-light">
                    <tr>
                        <th>#</th><th>Ref No</th><th>Approved At</th><th>Submission</th><th>Division</th><th>Type</th><th class="text-end">Amount</th><th>Level</th><th class="text-center">Action</th>
                    </tr>
                </thead><tbody>`;

            items.forEach((approval, index) => {
                const submission = approval.submission || {};
                const levelText = `${approval.level || 0} / ${approval.total_levels || 0}`;
                html += `<tr>
                    <td>${index + 1}</td>
                    <td><span class="fw-semibold text-primary">${approval.reference_number || '-'}</span></td>
                    <td>${toDateLabel(approval.approved_at)}</td>
                    <td>${submission.description || '-'}</td>
                    <td>${submission.division_name || '-'}</td>
                    <td>${submission.type_label || '-'}</td>
                    <td class="text-end">${formatIdr(submission.estimation_amount || 0)}</td>
                    <td class="text-center"><span class="badge bg-success">${levelText}</span></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info" onclick="showSubmissionApprovalDetail(${index}, 'history')" title="Detail">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });

            html += '</tbody></table></div>';
            $('#approvedApprovalContainer').html(html);
        }

        function initSubmissionApprovalCheckboxListeners() {
            $('#selectAllSubmissionApprovals').off('change').on('change', function() {
                const checked = $(this).is(':checked');
                $('.submission-approval-checkbox:not(:disabled)').prop('checked', checked);
                updateBulkApprovalActionState();
            });

            $('.submission-approval-checkbox').off('change').on('change', function() {
                const total = $('.submission-approval-checkbox:not(:disabled)').length;
                const checked = $('.submission-approval-checkbox:not(:disabled):checked').length;
                $('#selectAllSubmissionApprovals').prop('checked', total > 0 && total === checked);
                updateBulkApprovalActionState();
            });
        }

        function updateBulkApprovalActionState() {
            const checkedCount = $('.submission-approval-checkbox:not(:disabled):checked').length;
            if (checkedCount > 0) {
                $('#selectedApprovalCount').text(`(${checkedCount}) selected`);
                $('#bulkApprovalActions').fadeIn();
            } else {
                $('#bulkApprovalActions').fadeOut();
            }
        }

        function handleBulkApproveSubmission() {
            const detailIds = [];
            $('.submission-approval-checkbox:not(:disabled):checked').each(function() {
                detailIds.push($(this).val());
            });

            if (detailIds.length === 0) {
                Swal.fire('Info', 'Please select at least one item.', 'info');
                return;
            }

            Swal.fire({
                title: 'Confirm Bulk Approve',
                text: `Approve ${detailIds.length} selected item(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve'
            }).then(result => {
                if (result.isConfirmed) {
                    processBulkSubmissionApproval(detailIds, 'approve');
                }
            });
        }

        function handleBulkRejectSubmission() {
            const detailIds = [];
            $('.submission-approval-checkbox:not(:disabled):checked').each(function() {
                detailIds.push($(this).val());
            });

            if (detailIds.length === 0) {
                Swal.fire('Info', 'Please select at least one item.', 'info');
                return;
            }

            Swal.fire({
                title: 'Reason for bulk rejection',
                input: 'textarea',
                inputLabel: 'Please provide a reason',
                inputPlaceholder: 'Type reason here...',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Reject All',
                preConfirm: (value) => {
                    if (!value) {
                        Swal.showValidationMessage('Reason is required');
                    }
                    return value;
                }
            }).then(result => {
                if (result.isConfirmed) {
                    processBulkSubmissionApproval(detailIds, 'reject', result.value);
                }
            });
        }

        function processBulkSubmissionApproval(detailIds, action, comments = null) {
            const payload = {
                detail_ids: detailIds,
                action: action,
            };
            if (comments) {
                payload.comments = comments;
            }

            fetch(ROUTES.approvalBulkProcess, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message,
                            timer: 1200,
                            showConfirmButton: false
                        });
                        loadPendingSubmissionApprovals();
                        loadApprovedSubmissionApprovals();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: result.message
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed process approval action.'
                    });
                })
                .finally(() => {
                    $('#bulkApprovalActions').fadeOut();
                });
        }

        function findPendingApprovalByDetailId(detailId) {
            return pendingApprovals.find(item => Number(item.detail_id) === Number(detailId));
        }

        function approveSubmissionDetail(detailId) {
            const approval = findPendingApprovalByDetailId(detailId);
            const submission = approval ? (approval.submission || {}) : {};
            let sourceBudgetAccountId = null;

            if (submission.requires_source_budget_selection) {
                const sourceSelect = document.getElementById('approval_source_budget_account_id');
                if (!sourceSelect) {
                    const approvalIndex = pendingApprovals.findIndex(item => Number(item.detail_id) === Number(detailId));
                    if (approvalIndex >= 0) {
                        showSubmissionApprovalDetail(approvalIndex, 'pending');
                    }
                    return;
                }

                sourceBudgetAccountId = sourceSelect.value;
                if (!sourceBudgetAccountId) {
                    Swal.fire('Validation', 'Source Budget Account wajib dipilih untuk Add Budget.', 'warning');
                    return;
                }
            }

            Swal.fire({
                title: 'Approve this submission?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve'
            }).then(result => {
                if (!result.isConfirmed) return;

                const payload = {
                    comments: ''
                };

                if (sourceBudgetAccountId) {
                    payload.source_budget_account_id = sourceBudgetAccountId;
                }

                fetch(routeWithDetailId(ROUTES.approvalDetailApprove, detailId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved',
                                text: result.message,
                                timer: 1200,
                                showConfirmButton: false
                            });
                            $('#submissionApprovalTimelineModal').modal('hide');
                            loadPendingSubmissionApprovals();
                            loadApprovedSubmissionApprovals();
                            refreshTable();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: result.message
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to approve submission.'
                        });
                    });
            });
        }

        function openSubmissionReject(detailId) {
            $('#rejectDetailId').val(detailId);
            $('#rejectComments').val('');
            const modal = new bootstrap.Modal(document.getElementById('submissionRejectModal'));
            modal.show();
        }

        function confirmSubmissionReject() {
            const detailId = $('#rejectDetailId').val();
            const comments = $('#rejectComments').val().trim();
            if (!comments) {
                Swal.fire('Validation', 'Reason is required.', 'warning');
                return;
            }

            fetch(routeWithDetailId(ROUTES.approvalDetailReject, detailId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        comments: comments
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Rejected',
                            text: result.message,
                            timer: 1200,
                            showConfirmButton: false
                        });
                        $('#submissionRejectModal').modal('hide');
                        loadPendingSubmissionApprovals();
                        loadApprovedSubmissionApprovals();
                        refreshTable();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: result.message
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to reject submission.'
                    });
                });
        }

        function showBudgetSubmissionApprovalTimeline(submissionId) {
            fetch(routeWithId(ROUTES.approvalTimeline, submissionId), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Approval Progress',
                            text: result.message || 'Approval timeline is not available.'
                        });
                        return;
                    }

                    const item = result.data || {};
                    const submission = item.submission || {};
                    const refNo = item.reference_number || '-';
                    const requester = item.requester_name || '-';
                    const currentApprover = item.current_approver || '-';
                    const levelText = `${item.current_level || 0} / ${item.total_levels || 0}`;

                    $('#submissionApprovalTimelineModalLabel').text('Approval Progress');
                    $('#submissionApprovalItemDetails').html(`
                        <table class="table table-sm table-borderless mb-0 submission-item-details">
                            <tr><td>Ref Number</td><td class="fw-semibold">${refNo}</td></tr>
                            <tr><td>Submission</td><td>${submission.description || '-'}</td></tr>
                            <tr><td>Division</td><td>${submission.division_name || '-'}</td></tr>
                            <tr><td>Work Plan</td><td>${submission.work_plan_activity || '-'}</td></tr>
                            ${submission.source_budget_account && submission.source_budget_account !== '-' ? `<tr><td>Source Budget Account</td><td>${submission.source_budget_account}</td></tr>` : ''}
                            <tr><td>Budget Account</td><td>${submission.budget_account || '-'}</td></tr>
                            <tr><td>Amount</td><td>${formatIdr(submission.estimation_amount || 0)}</td></tr>
                            <tr><td>Requester</td><td>${requester}</td></tr>
                            <tr><td>Current Level</td><td><span class="badge bg-info">${levelText}</span></td></tr>
                            <tr><td>Current Approver</td><td class="fw-semibold">${currentApprover}</td></tr>
                        </table>
                    `);

                    const timeline = item.timeline || [];
                    if (!timeline.length) {
                        $('#submissionApprovalTimeline').html(`<div class="text-muted">No timeline.</div>`);
                    } else {
                        const nextPending = timeline.find(t => t.status === 'pending');
                        let timelineHtml = '';
                        timeline.forEach(detailItem => {
                            const isCurrent = nextPending && nextPending.id === detailItem.id;
                            const className = getSubmissionTimelineClass(detailItem.status, isCurrent);
                            const phase = detailItem.phase ? `<span class="text-muted ms-2">${detailItem.phase}</span>` : '';
                            timelineHtml += `
                                <div class="timeline-item ${className} ${isCurrent ? 'current' : ''}">
                                    <div>
                                        <strong>Level ${detailItem.level_sequence || '-'}</strong>
                                        ${phase}
                                        <span class="badge bg-${getSubmissionTimelineBadge(detailItem.status)} ms-2">
                                            ${detailItem.status}
                                        </span>
                                    </div>
                                    <div>${detailItem.employment_name || '-'}</div>
                                    <div class="small text-muted">${detailItem.approved_at ? toDateLabel(detailItem.approved_at) : '-'}</div>
                                </div>
                            `;
                        });
                        $('#submissionApprovalTimeline').html(timelineHtml);
                    }

                    $('#submissionApprovalModalFooter').html(
                        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>'
                    );

                    const modal = new bootstrap.Modal(document.getElementById('submissionApprovalTimelineModal'));
                    modal.show();
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load approval progress.'
                    });
                });
        }

        function destroyApprovalSourceBudgetAccountChoice() {
            if (!approvalSourceBudgetAccountChoice) return;

            try {
                approvalSourceBudgetAccountChoice.destroy();
            } catch (error) {
                // Choice may already be detached when the modal content is replaced.
            }

            approvalSourceBudgetAccountChoice = null;
        }

        function initApprovalSourceBudgetAccountChoice(submission) {
            destroyApprovalSourceBudgetAccountChoice();

            const selectEl = document.getElementById('approval_source_budget_account_id');
            if (!selectEl) return;

            approvalSourceBudgetAccountChoice = new Choices(selectEl, {
                searchEnabled: true,
                searchChoices: false,
                removeItemButton: false,
                placeholder: true,
                placeholderValue: 'Select Source Budget Account',
                searchPlaceholderValue: 'Search source budget account...',
                shouldSort: false
            });

            const loadOptions = (query = '') => {
                if (!submission.work_plan_id) {
                    approvalSourceBudgetAccountChoice.setChoices([{
                        value: '',
                        label: 'Work plan data is not available',
                        disabled: true,
                        selected: true
                    }], 'value', 'label', true);
                    return;
                }

                approvalSourceBudgetAccountChoice.setChoices([{
                    value: '',
                    label: 'Loading source budget...',
                    disabled: true,
                    selected: true
                }], 'value', 'label', true);

                const params = new URLSearchParams({
                    q: query || '',
                    page: '1',
                    limit: '100',
                    work_plan_id: String(submission.work_plan_id),
                    exclude_id: String(submission.budget_account_id || ''),
                });

                fetch(`${ROUTES.budgetCodesAll}?${params.toString()}`)
                    .then(response => response.json())
                    .then(payload => {
                        if (!payload || !payload.success) {
                            throw new Error('Failed to load source budget accounts.');
                        }

                        const results = payload.data || [];
                        const choices = results.map(item => ({
                            value: String(item.value),
                            label: item.label || normalizeLabel(item),
                        }));

                        approvalSourceBudgetAccountChoice.clearStore();
                        approvalSourceBudgetAccountChoice.setChoices([{
                            value: '',
                            label: choices.length ? 'Select Source Budget Account' : 'No source budget available',
                            disabled: true,
                            selected: true
                        }], 'value', 'label', true);
                        approvalSourceBudgetAccountChoice.setChoices(choices, 'value', 'label', false);
                    })
                    .catch(() => {
                        approvalSourceBudgetAccountChoice.setChoices([{
                            value: '',
                            label: 'Error loading source budget',
                            disabled: true,
                            selected: true
                        }], 'value', 'label', true);
                    });
            };

            selectEl.addEventListener('search', function(e) {
                const query = (e && e.detail && e.detail.value) ? e.detail.value : '';
                clearTimeout(approvalSourceBudgetAccountSearchTimer);
                approvalSourceBudgetAccountSearchTimer = setTimeout(() => loadOptions(query), 300);
            });

            loadOptions();
        }

        function showSubmissionApprovalDetail(index, mode) {
            const list = mode === 'history' ? approvedApprovals : pendingApprovals;
            const item = list[index];
            if (!item) return;
            const submission = item.submission || {};
            const isPending = mode === 'pending';
            const detail = item;
            const requiresSource = isPending && Boolean(submission.requires_source_budget_selection);

            const requester = detail.requester_name || '-';
            const refNo = detail.reference_number || '-';

            $('#submissionApprovalTimelineModalLabel').text('Approval Detail');

            const sourceSelectorHtml = requiresSource ? `
                <div class="border rounded p-3 mt-3">
                    <label for="approval_source_budget_account_id" class="form-label fw-semibold">
                        Source Budget Account <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="approval_source_budget_account_id">
                        <option value="">Loading source budget...</option>
                    </select>
                    <div class="form-text">
                        Budget ini akan didebit sebagai dasar penambahan budget tujuan.
                    </div>
                </div>
            ` : '';

            const detailsHtml = `
                <table class="table table-sm table-borderless mb-0 submission-item-details">
                    <tr><td>Ref Number</td><td class="fw-semibold">${refNo}</td></tr>
                    <tr><td>Submission</td><td>${submission.description || '-'}</td></tr>
                    <tr><td>Division</td><td>${submission.division_name || '-'}</td></tr>
                    <tr><td>Work Plan</td><td>${submission.work_plan_activity || '-'}</td></tr>
                    ${submission.source_budget_account && submission.source_budget_account !== '-' ? `<tr><td>Source Budget Account</td><td>${submission.source_budget_account}</td></tr>` : ''}
                    <tr><td>Budget Account</td><td>${submission.budget_account || '-'}</td></tr>
                    <tr><td>Amount</td><td>${formatIdr(submission.estimation_amount || 0)}</td></tr>
                    <tr><td>Requester</td><td>${requester}</td></tr>
                    <tr><td>Type</td><td>${submission.type_label || '-'}</td></tr>
                </table>
                ${sourceSelectorHtml}
            `;

            $('#submissionApprovalItemDetails').html(detailsHtml);
            if (requiresSource) {
                initApprovalSourceBudgetAccountChoice(submission);
            } else {
                destroyApprovalSourceBudgetAccountChoice();
            }

            const timeline = item.timeline || [];
            if (!timeline.length) {
                $('#submissionApprovalTimeline').html(`<div class="text-muted">No timeline.</div>`);
            } else {
                const nextPending = timeline.find(t => t.status === 'pending');
                let timelineHtml = '';
                timeline.forEach(detailItem => {
                    const isCurrent = nextPending && nextPending.id === detailItem.id;
                    const className = getSubmissionTimelineClass(detailItem.status, isCurrent);
                    timelineHtml += `
                        <div class="timeline-item ${className} ${isCurrent ? 'current' : ''}">
                            <div>
                                <strong>Level ${detailItem.level_sequence || '-'}</strong>
                                <span class="badge bg-${getSubmissionTimelineBadge(detailItem.status)} ms-2">
                                    ${detailItem.status}
                                </span>
                            </div>
                            <div>${detailItem.employment_name || '-'}</div>
                            <div class="small text-muted">${detailItem.approved_at ? toDateLabel(detailItem.approved_at) : '-'}</div>
                        </div>
                    `;
                });
                $('#submissionApprovalTimeline').html(timelineHtml);
            }

            let footer = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
            if (isPending) {
                footer = `
                    <button type="button" class="btn btn-danger" onclick="openSubmissionReject(${item.detail_id})">Reject</button>
                    <button type="button" class="btn btn-success" onclick="approveSubmissionDetail(${item.detail_id})">Approve</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                `;
            }
            $('#submissionApprovalModalFooter').html(footer);

            const modal = new bootstrap.Modal(document.getElementById('submissionApprovalTimelineModal'));
            modal.show();
        }

        function getSubmissionTimelineClass(status, isCurrent = false) {
            if (status === 'approved') return 'completed';
            if (status === 'rejected') return 'rejected';
            if (status === 'skipped') return 'skipped';
            if (status === 'pending') return isCurrent ? 'current' : 'pending';
            return 'pending';
        }

        function getSubmissionTimelineBadge(status) {
            switch (status) {
                case 'approved':
                    return 'success';
                case 'rejected':
                    return 'danger';
                case 'skipped':
                    return 'secondary';
                case 'pending':
                    return 'warning';
                default:
                    return 'secondary';
            }
        }
    </script>
@endsection
