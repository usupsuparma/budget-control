<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">

                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartment">
                            <i class="bi bi-plus-lg me-1"></i>Add Department
                        </button>

                    </div>
                </div>

                <div class="card-body">
                    <table id="departmentTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Department</th>
                                <th>Division (Parent)</th>
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
<div class="modal fade" id="addDepartment" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createDepartmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Department</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-large-line fw-semibold"></i>
                </button>
            </div>

            <div class="modal-body">
                <form id="departmentCreateForm" method="POST" action="{{ route('department.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="department_name" class="form-control" placeholder="Enter Department Name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Division</label>
                            <select name="division_id" id="division_id" class="form-control" required>
                                <option value="" selected disabled>-- Select Division --</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnCreateDepartment">Save Data</button>
            </div>

        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editDepartment" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="departmentEditForm" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="department_name" id="edit_department_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Division</label>
                            <select name="division_id" id="edit_division_department_id" class="form-control" required>
                                <option value="" disabled>-- Select Division --</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label> Status </label>
                            <select name="status" id="edit_status_department" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" form="departmentEditForm">Update</button>
            </div>

        </div>
    </div>
</div>

@push('page-scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi dropdown awal
        if (window.masterData && window.masterData.divisions) {
            populateSelect('division_id', window.masterData.divisions);
            populateSelect('edit_division_department_id', window.masterData.divisions);
        }

        // Event listener untuk refresh data master
        $(document).on('masterDataRefreshed', function(e, data) {
            populateSelect('division_id', data.divisions);
            populateSelect('edit_division_department_id', data.divisions);
        });

        $('#departmentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('department.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'division', name: 'division' },
                { data: 'status_badge', name: 'status', orderable: false, searchables: false },
                { data: 'action', name: 'action', orderable: false, searchables: false }
            ],
            order: [[0, 'desc']]
        });
    });

    // CREATE (AJAX)
    $('#btnCreateDepartment').click(function(e) {
        e.preventDefault();
        let form = $('#departmentCreateForm');
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {
                $('#addDepartment').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#departmentTable').DataTable().ajax.reload(null, false);
                refreshMasterOptions();

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Department added successfully",
                    timer: 1500,
                    showConfirmButton: false
                });

                form.trigger('reset');
            }
        });
    });

    // EDIT
    var $deptEditModal = document.getElementById('editDepartment');
    var _deptData = {};

    $(document).on('click', '.department-edit-btn', function() {
        var id = $(this).data('id');
        var editUrl = "{{ route('department.edit', ':id') }}".replace(':id', id);
        Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.get(editUrl, function(response) {
            Swal.close();
            _deptData = response;
            $('#edit_department_name').val(response.name);
            $('#edit_status_department').val(response.status || 'Active');
            var updateUrl = "{{ route('department.update', ':id') }}".replace(':id', response.id);
            $('#departmentEditForm').attr('action', updateUrl);
            new bootstrap.Modal($deptEditModal).show();
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load data.' });
        });
    });

    $deptEditModal.addEventListener('shown.bs.modal', function () {
        var divs = (window.masterData && window.masterData.divisions) ? window.masterData.divisions : [];
        populateSelect('edit_division_department_id', divs, _deptData.division_id);
        if (!window.masterChoices['edit_division_department_id']) {
            initChoices('edit_division_department_id');
        }
    });

    $deptEditModal.addEventListener('hidden.bs.modal', function () {
        if (window.masterChoices['edit_division_department_id']) {
            window.masterChoices['edit_division_department_id'].destroy();
            delete window.masterChoices['edit_division_department_id'];
        }
        _deptData = {};
    });


    $('#departmentEditForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {
                $('#editDepartment').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#departmentTable').DataTable().ajax.reload();
                refreshMasterOptions();

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Department updated successfully",
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

    // DELETE
    $(document).on('click', '.department-delete-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: "Delete Department?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Delete",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/department/delete/" + id,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        $('#departmentTable').DataTable().ajax.reload(null, false);
                        refreshMasterOptions();
                        Swal.fire({
                            icon: "success",
                            title: "Deleted",
                            text: "Department deleted successfully",
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            }
        });
    });
</script>
@endpush
