@extends('layouts.master')

@section('title', 'Budget Submission | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Budget Submission')
@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
    .badge {
        padding: 0.35em 0.65em;
        font-size: 0.85em;
    }
</style>
@endsection

@section('content')
<!-- Begin page -->
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Budget Submission List</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#budgetSubmissionModal" onclick="resetForm()">
                        <i class="ri-add-line align-bottom me-1"></i> Add Data
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

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
                                        <span class="badge bg-{{ $submission->type == 'add' ? 'info' : 'secondary' }}">
                                            {{ $submission->type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $submission->workPlan->activity ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($submission->description, 50) }}</small>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($submission->estimation_amount, 0, ',', '.') }}</td>
                                    <td>
                                        <small>{{ $submission->budgetAccount->name ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $submission->status_color }}">
                                            {{ $submission->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($submission->isPending())
                                                <button type="button" class="btn btn-sm btn-warning" onclick="editSubmission({{ $submission->id }})" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteSubmission({{ $submission->id }})" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success" onclick="approveSubmission({{ $submission->id }})" title="Approve">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $budgetSubmissions->firstItem() ?? 0 }} to {{ $budgetSubmissions->lastItem() ?? 0 }} of {{ $budgetSubmissions->total() }} entries
                        </div>
                        <div>
                            {{ $budgetSubmissions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Submission Modal -->
<div class="modal fade" id="budgetSubmissionModal" tabindex="-1" aria-labelledby="budgetSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="budgetSubmissionModalLabel">Add Budget Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="budgetSubmissionForm" method="POST" action="{{ route('budget.submission.store') }}">
                @csrf
                <div id="methodField"></div>
                <input type="hidden" id="submission_id" name="submission_id">
                
                <div class="modal-body">
                    <div class="row mb-3">
                        <label for="division_id" class="col-sm-3 col-form-label">Division <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-select" id="division_id" name="division_id" required>
                                <option value="">Select Division</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ (Auth::user()->division_id ?? '') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="user_name" class="col-sm-3 col-form-label">User</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="user_name" value="{{ $user->first_name ?? Auth::user()->first_name }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="submission_date" class="col-sm-3 col-form-label">Date <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="submission_date" name="submission_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Type <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="type_add" value="add" checked>
                                <label class="form-check-label" for="type_add">Add Budget</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="type_relocation" value="relocation">
                                <label class="form-check-label" for="type_relocation">Relocation</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="work_plan_id" class="col-sm-3 col-form-label">Work Plan <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-select" id="work_plan_id" name="work_plan_id" required>
                                <option value="">Select Work Plan</option>
                                @foreach($workPlans as $workPlan)
                                    <option value="{{ $workPlan->id }}">
                                        [{{ $workPlan->year }}] {{ $workPlan->activity }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="budget_account_id" class="col-sm-3 col-form-label">Budget Account <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-select" id="budget_account_id" name="budget_account_id" required>
                                <option value="">Select Budget Account</option>
                                @foreach($budgetAccounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->stock_code }} - {{ $account->name }}
                                    </option>
                                @endforeach
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
                        <label for="estimation_amount" class="col-sm-3 col-form-label">Estimation <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="estimation_amount" name="estimation_amount" step="0.01" min="0" required>
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
</div>
@endsection

@section('js')
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

<script>
    let divisionChoice, workPlanChoice, budgetAccountChoice;

    document.addEventListener('DOMContentLoaded', function() {
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

        budgetAccountChoice = new Choices('#budget_account_id', {
            searchEnabled: true,
            removeItemButton: false,
            placeholder: true,
            placeholderValue: 'Select Budget Account'
        });
    });

    function resetForm() {
        document.getElementById('budgetSubmissionForm').reset();
        document.getElementById('submission_id').value = '';
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('budgetSubmissionModalLabel').textContent = 'Add Budget Submission';
        document.getElementById('budgetSubmissionForm').action = '{{ route("budget.submission.store") }}';
        document.getElementById('submission_date').value = '{{ date("Y-m-d") }}';
        
        // Reset choices
        if (divisionChoice) divisionChoice.setChoiceByValue('');
        if (workPlanChoice) workPlanChoice.setChoiceByValue('');
        if (budgetAccountChoice) budgetAccountChoice.setChoiceByValue('');
    }

    function editSubmission(id) {
        fetch(`/budget-submission/${id}/edit`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    
                    document.getElementById('submission_id').value = data.id;
                    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
                    document.getElementById('budgetSubmissionModalLabel').textContent = 'Edit Budget Submission';
                    document.getElementById('budgetSubmissionForm').action = `/budget-submission/${data.id}`;
                    
                    // Set form values
                    if (divisionChoice) divisionChoice.setChoiceByValue(String(data.division_id));
                    
                    // Set date directly (already in Y-m-d format from controller)
                    document.getElementById('submission_date').value = data.submission_date;
                    
                    // Set type radio
                    if (data.type === 'add') {
                        document.getElementById('type_add').checked = true;
                    } else {
                        document.getElementById('type_relocation').checked = true;
                    }
                    
                    if (workPlanChoice) workPlanChoice.setChoiceByValue(String(data.work_plan_id));
                    if (budgetAccountChoice) budgetAccountChoice.setChoiceByValue(String(data.budget_account_id));
                    document.getElementById('description').value = data.description || '';
                    document.getElementById('estimation_amount').value = data.estimation_amount;
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('budgetSubmissionModal'));
                    modal.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: result.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load submission data. Please try again.'
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
            if (result.isConfirmed) {
                fetch(`/budget-submission/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: result.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to delete submission. Please try again.'
                    });
                });
            }
        });
    }

    function approveSubmission(id) {
        Swal.fire({
            title: 'Approve Submission?',
            text: "Are you sure you want to approve this budget submission?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/budget-submission/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Approved!',
                            text: result.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: result.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to approve submission. Please try again.'
                    });
                });
            }
        });
    }
</script>
@endsection
