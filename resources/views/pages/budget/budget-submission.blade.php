@extends('layouts.master')

@section('title', 'Budget Submission | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Budget Submission')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <style>
        .badge {
            padding: 0.35em 0.65em;
            font-size: 0.85em;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#budgetSubmissionModal" onclick="resetForm()">
                            <i class="ri-add-line align-bottom me-1"></i> Add Data
                        </button>
                    </div>
                    <div class="card-body">
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
                                            <td class="text-end">Rp
                                                {{ number_format($submission->estimation_amount, 0, ',', '.') }}</td>
                                            <td>
                                                <small>{{$submission->budgetAccount->stock_code ?? '-'}} | {{ $submission->budgetAccount->name ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $submission->status_color }}">
                                                    {{ $submission->status_label }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($submission->status == 0)
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-warning"
                                                            onclick="editSubmission({{ $submission->id }})" title="Edit">
                                                            <i class="ri-edit-line"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="deleteSubmission({{ $submission->id }})"
                                                            title="Delete">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-success"
                                                            onclick="approveSubmission({{ $submission->id }})"
                                                            title="Approve">
                                                            <i class="ri-check-line"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
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
                        <h5 class="modal-title" id="budgetSubmissionModalLabel">Add Budget Submission</h5>
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
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}"
                                                {{ (Auth::user()->division_id ?? '') == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="user_name" class="col-sm-3 col-form-label">User</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="user_name"
                                        value="{{ $user->first_name ?? Auth::user()->first_name }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="submission_date" class="col-sm-3 col-form-label">Date <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="submission_date"
                                        name="submission_date" value="{{ date('Y-m-d') }}" required>
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
                                        <input class="form-check-input" type="radio" name="type"
                                            id="type_relocation" value="relocation">
                                        <label class="form-check-label" for="type_relocation">Relocation</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="work_plan_id" class="col-sm-3 col-form-label">Work Plan <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select" id="work_plan_id" name="work_plan_id" required>
                                        <option value="">Select Work Plan</option>
                                        @foreach ($workPlans as $workPlan)
                                            <option value="{{ $workPlan->id }}">
                                                [{{ $workPlan->year }}] {{ $workPlan->activity }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="budget_account_id" class="col-sm-3 col-form-label">Budget Account <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select" id="budget_account_id" name="budget_account_id" required>
                                        <option value="">Loading budget accounts...</option>
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
                                <label for="estimation_amount" class="col-sm-3 col-form-label">Estimation <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" id="estimation_amount"
                                        name="estimation_amount"  min="0" required>
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
        let divisionChoice, workPlanChoice, budgetAccountChoice;
        let budgetCodesData = [];
        let budgetCodesLoaded = false;
        let dataTable;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            initDataTable();
            
            // Load budget codes immediately on page load
            loadBudgetCodes();

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

            // Initialize Choices.js for Budget Account (will be populated after AJAX loads)
            budgetAccountChoice = new Choices('#budget_account_id', {
                searchEnabled: true,
                removeItemButton: false,
                placeholder: true,
                placeholderValue: 'Select Budget Account',
                searchPlaceholderValue: 'Search budget account...'
            });

            // Handle form submission with AJAX
            document.getElementById('budgetSubmissionForm').addEventListener('submit', handleFormSubmit);
        });

        /**
         * Initialize DataTable
         */
        function initDataTable() {
            dataTable = $('#budgetSubmissionTable').DataTable({
                processing: true,
                serverSide: false,
                pageLength: 15,
                order: [[1, 'desc']], // Order by submission date
                language: {
                    search: 'Search:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total entries)',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Previous'
                    }
                },
                columnDefs: [
                    { orderable: false, targets: -1 } // Disable sorting on action column
                ]
            });
        }

        /**
         * Refresh table data via AJAX without page reload
         */
        function refreshTable() {
            fetch('{{ route('budget.submission.data') }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Destroy existing DataTable
                    if (dataTable) {
                        dataTable.destroy();
                    }
                    
                    // Update table body
                    $('#budgetSubmissionTable tbody').html(result.html);
                    
                    // Reinitialize DataTable
                    initDataTable();
                    
                    console.log('Table refreshed successfully');
                } else {
                    console.error('Failed to refresh table:', result.message);
                }
            })
            .catch(error => {
                console.error('Error refreshing table:', error);
            });
        }

        /**
         * Handle form submission with AJAX
         */
        function handleFormSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const submissionId = document.getElementById('submission_id').value;
            const method = document.getElementById('form_method').value;
            
            let url = '{{ route('budget.submission.store') }}';
            if (submissionId) {
                url = `/budget-submission/${submissionId}`;
                formData.append('_method', 'PUT');
            }

            // Disable submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('budgetSubmissionModal'));
                    modal.hide();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    // Refresh table without page reload
                    setTimeout(() => {
                        refreshTable();
                    }, 1500);
                } else {
                    // Show error message
                    let errorMsg = result.message || 'Failed to save budget submission.';
                    
                    if (result.errors) {
                        errorMsg += '<ul class="text-start mt-2">';
                        Object.values(result.errors).forEach(errors => {
                            errors.forEach(error => {
                                errorMsg += `<li>${error}</li>`;
                            });
                        });
                        errorMsg += '</ul>';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMsg
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred. Please try again.'
                });
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }

        /**
         * Load budget codes via AJAX with localStorage caching (1 day)
         */
        function loadBudgetCodes() {
            const CACHE_KEY = 'budgetCodes_cache';
            const CACHE_DURATION = 24 * 60 * 60 * 1000; // 1 day in milliseconds

            // Show loading state
            if (budgetAccountChoice) {
                budgetAccountChoice.setChoices([{
                    value: '',
                    label: 'Loading budget accounts...',
                    disabled: true
                }], 'value', 'label', true);
            }

            // Check if data exists in localStorage and is not expired
            const cachedData = localStorage.getItem(CACHE_KEY);
            if (cachedData) {
                try {
                    const parsedCache = JSON.parse(cachedData);
                    const now = new Date().getTime();
                    
                    // Check if cache is still valid (less than 1 day old)
                    if (parsedCache.timestamp && (now - parsedCache.timestamp) < CACHE_DURATION) {
                        console.log('Loading budget codes from cache');
                        
                        // Use cached data
                        budgetCodesData = parsedCache.data;
                        budgetCodesLoaded = true;

                        if (budgetAccountChoice) {
                            // Clear existing choices
                            budgetAccountChoice.clearStore();

                            // Add placeholder
                            budgetAccountChoice.setChoices([{
                                value: '',
                                label: 'Select Budget Account',
                                disabled: true,
                                selected: true
                            }], 'value', 'label', true);

                            // Add all budget codes
                            budgetAccountChoice.setChoices(parsedCache.data, 'value', 'label', false);
                        }

                        return Promise.resolve(parsedCache.data);
                    } else {
                        console.log('Cache expired, fetching fresh data');
                    }
                } catch (e) {
                    console.warn('Error parsing cached data:', e);
                    localStorage.removeItem(CACHE_KEY);
                }
            }

            // Fetch from API if no valid cache
            return fetch('{{ route('budget.submission.budgetCodesAll') }}')
                .then(response => response.json())
                .then(data => {
                    budgetCodesData = data;
                    budgetCodesLoaded = true;

                    // Store in localStorage with timestamp
                    try {
                        const cacheObject = {
                            data: data,
                            timestamp: new Date().getTime()
                        };
                        localStorage.setItem(CACHE_KEY, JSON.stringify(cacheObject));
                        console.log('Budget codes cached successfully');
                    } catch (e) {
                        console.warn('Error caching budget codes:', e);
                    }

                    if (budgetAccountChoice) {
                        // Clear existing choices
                        budgetAccountChoice.clearStore();

                        // Add placeholder
                        budgetAccountChoice.setChoices([{
                            value: '',
                            label: 'Select Budget Account',
                            disabled: true,
                            selected: true
                        }], 'value', 'label', true);

                        // Add all budget codes
                        budgetAccountChoice.setChoices(data, 'value', 'label', false);
                    }

                    console.log('Budget codes loaded from API:', data.length);
                    return data;
                })
                .catch(error => {
                    console.error('Error loading budget codes:', error);
                    budgetCodesLoaded = false;
                    if (budgetAccountChoice) {
                        budgetAccountChoice.setChoices([{
                            value: '',
                            label: 'Error loading budget accounts',
                            disabled: true
                        }], 'value', 'label', true);
                    }
                    throw error;
                });
        }

        function resetForm() {
            document.getElementById('budgetSubmissionForm').reset();
            document.getElementById('submission_id').value = '';
            document.getElementById('form_method').value = 'POST';
            document.getElementById('budgetSubmissionModalLabel').textContent = 'Add Budget Submission';
            document.getElementById('submission_date').value = '{{ date('Y-m-d') }}';

            // Reset choices
            if (divisionChoice) divisionChoice.setChoiceByValue('');
            if (workPlanChoice) workPlanChoice.setChoiceByValue('');
            if (budgetAccountChoice) budgetAccountChoice.setChoiceByValue('');
        }

        function editSubmission(id) {
            // Ensure budget codes are loaded first
            const loadPromise = budgetCodesLoaded ? Promise.resolve() : loadBudgetCodes();

            loadPromise.then(() => {
                return fetch(`/budget-submission/${id}/edit`);
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;

                    document.getElementById('submission_id').value = data.id;
                    document.getElementById('form_method').value = 'PUT';
                    document.getElementById('budgetSubmissionModalLabel').textContent = 'Edit Budget Submission';

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
                    
                    // Set budget account - with a slight delay to ensure Choices.js is ready
                    if (budgetAccountChoice && data.budget_account_id) {
                        setTimeout(() => {
                            budgetAccountChoice.setChoiceByValue(String(data.budget_account_id));
                        }, 100);
                    }
                    
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
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                
                                // Refresh table without page reload
                                setTimeout(() => {
                                    refreshTable();
                                }, 1500);
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
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                
                                // Refresh table without page reload
                                setTimeout(() => {
                                    refreshTable();
                                }, 1500);
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
