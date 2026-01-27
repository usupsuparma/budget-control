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
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Employee Code (NIP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="employee_code" name="employee_code" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Job Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="job_position_id" name="job_position_id" required>
                                <option value="" selected disabled>-- Select Job Position --</option>
                                @foreach ($jobPositions as $jp)
                                <option value="{{ $jp->id }}">{{ $jp->job_position_name }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Uppline</label>
                            <select class="form-select" name="uppline_id">
                                <option value="" selected disabled>-- Select Uppline --</option>
                                @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->first_name }} {{ $emp->last_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="uppline_name" id="uppline_name">
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role_name" name="role_name" required>
                                <option value="" selected disabled>-- Select Role --</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
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
                                @foreach ($jobPositions as $jp)
                                <option value="{{ $jp->id }}">{{ $jp->job_position_name }}</option>
                                @endforeach
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
                                @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Uppline</label>
                            <select class="form-select" name="uppline_id">
                                <option value="" selected disabled>-- Select Uppline --</option>
                                @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->first_name }} {{ $emp->last_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="uppline_name" id="edit_uppline_name">
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
                    name: 'id',
                    searchable: true
                },
                {
                    data: 'employee_code',
                    name: 'employee_code',
                    searchable: true  // Search by NIP
                },
                {
                    data: 'full_name',
                    name: 'full_name',
                    searchable: true  // Search in first_name, last_name, and email
                },
                {
                    data: 'job_info',
                    name: 'job_info',
                    searchable: true  // Search in job position and job level
                },
                {
                    data: 'roles',
                    name: 'roles',
                    searchable: true  // Search in role name
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    orderable: false,
                    searchable: true  // Search by status (Active/Inactive)
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
            $("#detailEmployeeId").text(data.employee_code ?? '-');
            
            // Get job info from employment relationship
            let jobPosition = data.employment?.job_position?.job_position_name ?? '-';
            let organization = data.employment?.job_position?.structure_name ?? '-';
            let jobLevel = data.employment?.job_level?.job_level_name ?? '-';
            
            $("#detailJobPosition").text(jobPosition);
            $("#detailOrganization").text(organization);
            $("#detailJobLevel").text(jobLevel);
            $("#detailRole").text(data.roles && data.roles.length > 0 ? data.roles[0].name : '-');

            let status = data.status === "Active" ?
                `<span class="badge bg-success">Active</span>` :
                `<span class="badge bg-secondary">Inactive</span>`;
            $("#detailStatus").html(status);

            // Store employee ID for reset password button
            $("#employeeDetailModal").data('employee-id', data.id);
            
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
                $("#employeeCreateForm")[0].reset(); // Reset form
                $("#employeeTable").DataTable().ajax.reload(null, false);

                Swal.fire("Success", "Employee created successfully!", "success");
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                
                // Tangani ValidationError (422) dan ServerError (500)
                if (xhr.status === 422) {
                    // Validation Error - tampilkan semua pesan error
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    
                    for (let field in errors) {
                        errorMessages += '• ' + errors[field][0] + '\n';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: '<div style="text-align:left;">' + 
                              errorMessages.replace(/\n/g, '<br>') + 
                              '</div>',
                    });
                } else if (xhr.status === 500) {
                    // Server Error
                    let response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: response?.message || 'Terjadi kesalahan server. Silakan coba lagi.',
                    });
                } else {
                    Swal.fire("Error", "Gagal menyimpan employee!", "error");
                }
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
            $("#edit_employee_code").val(res.employee_code);
            $("#edit_email").val(res.email);

            // Get job_position_id from employment
            $("#edit_job_position_id").val(res.employment?.job_position_id ?? '');
            $("#edit_role_name").val(res.roles && res.roles.length > 0 ? res.roles[0].name : '');
            $("#edit_status").val(res.status);

            // UPPLINE (dari employment)
            if (res.employment) {
                $('#editEmployeeModal select[name="uppline_id"]').val(res.employment.uppline_id);
                $('#edit_uppline_name').val(res.employment.uppline_id_name);
            } else {
                $('#editEmployeeModal select[name="uppline_id"]').val('');
                $('#edit_uppline_name').val('');
            }


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
            text: "Data employee & employment akan dihapus.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Delete",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: "/employee/delete/" + id,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: "DELETE"
                    },
                    success: function(res) {
                        $("#employeeTable").DataTable().ajax.reload(null, false);
                        Swal.fire("Deleted!", "Employee berhasil dihapus.", "success");
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        Swal.fire("Error", "Gagal menghapus employee.", "error");
                    }
                });

            }
        });
    });
</script>
<script>
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
                console.log(xhr.responseText);
                
                // Tangani ValidationError (422) dan ServerError (500)
                if (xhr.status === 422) {
                    // Validation Error - tampilkan semua pesan error
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    
                    for (let field in errors) {
                        errorMessages += '• ' + errors[field][0] + '\n';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: '<div style="text-align:left;">' + 
                              errorMessages.replace(/\n/g, '<br>') + 
                              '</div>',
                    });
                } else if (xhr.status === 500) {
                    // Server Error
                    let response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: response?.message || 'Terjadi kesalahan server. Silakan coba lagi.',
                    });
                } else {
                    Swal.fire("Error", "Gagal update employee!", "error");
                }
            }
        });
    });
</script>
<script>
    $(document).on('click', '.employee-resetpass-btn, .resetPasswordBtn', function() {
        // Get ID from button data or from parent modal data
        let id = $(this).data('id') || $("#employeeDetailModal").data('employee-id');
        
        if (!id) {
            Swal.fire("Error", "Employee ID not found", "error");
            return;
        }

        // Close Bootstrap modal first to avoid focus conflict with SweetAlert
        let detailModal = bootstrap.Modal.getInstance(document.getElementById('employeeDetailModal'));
        if (detailModal) {
            detailModal.hide();
        }
        
        // Remove modal backdrop if it still exists
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');

        Swal.fire({
            title: "Reset password?",
            input: "password",
            inputPlaceholder: "Enter new password",
            showCancelButton: true,
            confirmButtonText: "Reset",
            didOpen: () => {
                // Ensure SweetAlert input gets focus
                const input = Swal.getInput();
                if (input) {
                    input.focus();
                }
            }
        }).then((result) => {
            if (result.value) {

                $.post("/employee/" + id + "/reset-password", {
                    _token: "{{ csrf_token() }}",
                    password: result.value
                }, function() {
                    Swal.fire("Success!", "Password reset successfully.", "success");
                }).fail(function(xhr) {
                    console.log(xhr.responseText);
                    Swal.fire("Error", "Failed to reset password", "error");
                });

            }
        });
    });
</script>
<script>
    $('select[name="uppline_id"]').on('change', function() {
        let name = $(this).find('option:selected').text();
        $('#uppline_name').val(name);
    });

    $('#editEmployeeModal select[name="uppline_id"]').on('change', function() {
        let name = $(this).find('option:selected').text();
        $('#edit_uppline_name').val(name);
    });
</script>

@endpush