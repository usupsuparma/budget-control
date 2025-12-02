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
                                <th>Full Names</th>
                                <th>Email</th>
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
<!-- Create Employee Modal -->
<div class="modal fade" id="createEmployee" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createEmployeeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="createEmployeeLabel">Create New Employee</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-large-line fw-semibold"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="employeeCreateForm" method="POST" action="{{ route('employee.create') }}">
                    @csrf
                    <div class="row g-3">

                        <!-- First Name -->
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" placeholder="Enter first name" required>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" placeholder="Enter last name" required>
                        </div>

                        <!-- Employee ID -->
                        <div class="col-md-6">
                            <label class="form-label">Employee ID</label>
                            <input type="text" class="form-control" id="employee_id" placeholder="EX: EMP-00123" required>
                        </div>

                        <!-- Job Position -->
                        <div class="col-md-6">
                            <label class="form-label">Job Position</label>
                            <select class="form-select" id="job_position_name" required>
                                <option value="" selected disabled>-- Select Job Position --</option>
                                <option value="Manager">Manager</option>
                                <option value="Supervisor">Supervisor</option>
                                <option value="Senior Staff">Senior Staff</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email" required>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" placeholder="Enter password" required>
                        </div>

                        <!-- Role -->
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select class="form-select" id="role_id" required>
                                <option value="" selected disabled>-- Select Role --</option>
                                <option value="1">Admin</option>
                                <option value="2">Approver</option>
                                <option value="3">User</option>
                            </select>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="employeeCreateForm">
                    Save Employee
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Submit Section -->
<!-- Employee Detail Modal -->
<div class="modal fade" id="employeeDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Employee Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="text-center mb-4">
                    <img id="detailPhoto" class="rounded-circle img-thumbnail"
                        style="width: 100px; height: 100px; object-fit: cover;">
                    <h5 id="detailName" class="mt-3 fw-bold"></h5>
                    <p id="detailEmail" class="text-muted"></p>
                </div>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="fw-bold">Employee ID</label>
                        <p id="detailEmployeeId"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">Job Position</label>
                        <p id="detailJobPosition"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">Organization</label>
                        <p id="detailOrganization"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">Job Level</label>
                        <p id="detailJobLevel"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">Role</label>
                        <p id="detailRole"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">Status</label>
                        <p id="detailStatus"></p>
                    </div>

                    <div class="col-12 mt-3">
                        <button class="btn btn-warning w-100 resetPasswordBtn">
                            <i class="bi bi-shield-lock me-2"></i> Reset Password
                        </button>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>


@push('scripts')
<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#employeeTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('employee.data') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },

                {
                    data: 'full_name',
                    name: 'full_name'
                },
                {
                    data: 'emails',
                    name: 'email'
                },
                {
                    data: 'roles',
                    name: 'role_id'
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [0, 'asc']
            ]
        });
    });
</script>
<script>
    $(document).on('click', '.open-detail', function() {
        let id = $(this).data('id');

        $.get("/employee/" + id, function(data) {

            // Set data ke modal
            $("#detailPhoto").attr("src", data.photo_url ?? "/assets/images/avatar/15.jpg");
            $("#detailName").text(data.first_name + " " + data.last_name);
            $("#detailEmail").text(data.email);
            $("#detailEmployeeId").text(data.employee_id ?? '-');
            $("#detailJobPosition").text(data.job_position?.job_position_name ?? '-');
            $("#detailOrganization").text(data.organization?.organization_name ?? "-");
            $("#detailJobLevel").text(data.job_level?.job_level_name ?? "-");
            $("#detailRole").text(data.role?.role_name ?? "-");

            let status = data.status === "Active" ?
                `<span class="badge bg-success">Active</span>` :
                `<span class="badge bg-secondary">Inactive</span>`;
            $("#detailStatus").html(status);

            $("#employeeDetailModal").modal("show");
        });
    });
</script>
@endpush