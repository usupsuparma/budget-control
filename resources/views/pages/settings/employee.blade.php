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
                <form id="employeeCreateForm" method="POST" action="{{ route('employee.store') }}">
                    @csrf
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Employee ID</label>
                            <input type="text" class="form-control" id="employee_id" name="employee_id" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Job Position</label>
                            <select class="form-select" id="job_position_id" name="job_position_id" required>
                                <option value="" selected disabled>-- Select Job Position --</option>
                                @foreach ($jobPositions as $jp)
                                <option value="{{ $jp->id }}">{{ $jp->job_position_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select class="form-select" id="role_id" name="role_id" required>
                                <option value="" selected disabled>-- Select Role --</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Employee</button>
                    </div>
                </form>

            </div>

            <!-- Footer -->


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
                            <label class="form-label">First Name</label>
                            <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Employee ID</label>
                            <input type="text" id="edit_employee_id" name="employee_id" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Job Position</label>
                            <select class="form-select" name="job_position_id" id="edit_job_position_id" required>
                                <option value="" disabled selected>-- Select Job Position --</option>
                                @foreach ($jobPositions as $jp)
                                <option value="{{ $jp->id }}">{{ $jp->job_position_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" id="edit_email" name="email" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role_id" id="edit_role_id" required>
                                <option value="" disabled selected>-- Select Role --</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
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
                    data: 'email',
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
                [0, 'desc']
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

<script>
    $("#employeeCreateForm").on("submit", function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr("action"),
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {

                $("#createEmployee").modal("hide");
                $("#employeeTable").DataTable().ajax.reload(null, false);

                Swal.fire("Success", "Employee created successfully!", "success");
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                Swal.fire("Error", "Failed to save employee!", "error");
            }
        });
    });
</script>

<script>
    // EDIT BUTTON OPEN
    $(document).on('click', '.employee-edit-btn', function() {
        let id = $(this).data('id');

        $.get("/employee/" + id + "/edit", function(res) {

            $("#edit_first_name").val(res.first_name);
            $("#edit_last_name").val(res.last_name);
            $("#edit_employee_id").val(res.employee_id);
            $("#edit_email").val(res.email);
            $("#edit_job_position_id").val(res.job_position_id);
            $("#edit_role_id").val(res.role_id);
            $("#edit_status").val(res.status);

            $("#employeeEditForm").attr("action", "/employee/update/" + id);
            $("#editEmployeeModal").modal("show");
        });
    });
</script>
<script>
    $(document).on('click', '.employee-delete-btn', function() {

        let id = $(this).data('id');

        Swal.fire({
            title: "Delete employee?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Delete"
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: "/employee/delete/" + id,
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: "DELETE"
                    },
                    success: function() {
                        $("#employeeTable").DataTable().ajax.reload(null, false);
                        Swal.fire("Deleted!", "", "success");
                    }
                });

            }
        });

    });
</script>
<script>
    $(document).on('click', '.employee-edit-btn', function() {

        let id = $(this).data('id');

        $.get("/employee/" + id + "/edit", function(res) {

            $("#edit_first_name").val(res.first_name);
            $("#edit_last_name").val(res.last_name);
            $("#edit_employee_id").val(res.employee_id);
            $("#edit_email").val(res.email);

            $("#edit_job_position_id").val(res.job_position_id);
            $("#edit_role_id").val(res.role_id);

            $("#edit_status").val(res.status);

            $("#employeeEditForm").attr("action", "/employee/update/" + id);

            $("#editEmployeeModal").modal("show");
        });

    });

    $("#btnUpdateEmployee").click(function(e) {
        e.preventDefault();

        let form = $("#employeeEditForm");
        let url = form.attr("action");

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {

                $("#editEmployeeModal").modal("hide");
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $("#employeeTable").DataTable().ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Employee updated successfully!",
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                Swal.fire("Error", "Failed to update employee!", "error");
                console.log(xhr.responseText);
            }
        });
    });
</script>
<script>
    $(document).on('click', '.employee-resetpass-btn', function() {

        let id = $(this).data('id');

        Swal.fire({
            title: "Reset password?",
            input: "password",
            inputPlaceholder: "Enter new password",
            showCancelButton: true,
            confirmButtonText: "Reset"
        }).then((result) => {
            if (result.value) {

                $.post("/employee/" + id + "/reset-password", {
                    _token: "{{ csrf_token() }}",
                    password: result.value
                }, function() {
                    Swal.fire("Success!", "Password reset successfully.", "success");
                });

            }
        });
    });
</script>
@endpush