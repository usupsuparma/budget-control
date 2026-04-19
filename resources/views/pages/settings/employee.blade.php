<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEmployee">
                            <i class="bi bi-plus-lg me-1"></i>Create Employee
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="employeeTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>NIP</th>
                                <th>Names & Email</th>
                                <th>Job Position</th>
                                <th>Division</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createEmployee" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createEmployeeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createEmployeeLabel">Create New Employee</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="employeeCreateForm" method="POST" action="{{ route('employee.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employee Code (NIP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="employee_code" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Job Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="job_position_id" name="job_position_id" required>
                                <option value="" selected disabled>-- Select Job Position --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Uppline</label>
                            <select class="form-select" id="uppline_id" name="uppline_id">
                                <option value="" selected disabled>-- Select Uppline --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role_name" name="role_name" required>
                                <option value="" selected disabled>-- Select Role --</option>
                                {{-- Roles are usually static, but we can also fetch them if needed --}}
                                <option value="Admin">Admin</option>
                                <option value="User">User</option>
                                <option value="Director">Director</option>
                                <option value="Manager">Manager</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Employee</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="employeeEditForm" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employee Code (NIP) <span class="text-danger">*</span></label>
                            <input type="text" id="edit_employee_code" name="employee_code" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Job Position <span class="text-danger">*</span></label>
                            <select class="form-select" name="job_position_id" id="edit_job_position_id" required>
                                <option value="" disabled selected>-- Select Job Position --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="edit_email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role_name" id="edit_role_name" required>
                                <option value="" disabled selected>-- Select Role --</option>
                                <option value="Admin">Admin</option>
                                <option value="User">User</option>
                                <option value="Director">Director</option>
                                <option value="Manager">Manager</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Uppline</label>
                            <select class="form-select" id="edit_uppline_id" name="uppline_id">
                                <option value="" selected disabled>-- Select Uppline --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnUpdateEmployee">Update Employee</button>
            </div>
        </div>
    </div>
</div>

{{-- Detail Modal omitted for brevity, same as original but with data master sync --}}

@push('page-scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi dropdown awal
        function syncEmployeeOptions(data) {
            populateSelect('job_position_id', data.job_positions);
            populateSelect('edit_job_position_id', data.job_positions);
            
            // Untuk Uppline, kita bisa menggunakan data employee (bukan master options biasa)
            // Namun di issue.md kita fokus ke master options dulu.
            // Jika ingin sync employee juga, MasterDataService harus mengembalikannya.
        }

        if (window.masterData && window.masterData.job_positions) {
            syncEmployeeOptions(window.masterData);
        }

        $(document).on('masterDataRefreshed', function(e, data) {
            syncEmployeeOptions(data);
        });

        $('#employeeTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('employee.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'employee_code', name: 'employee_code' },
                { data: 'full_name', name: 'full_name' },
                { data: 'job_info', name: 'job_info' },
                { data: 'division', name: 'division' },
                { data: 'roles', name: 'roles' },
                { data: 'status_badge', name: 'status', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });
    });

    // Create, Edit, Update, Delete logic (AJAX) same as division/department
    // ...
</script>
@endpush
