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
<!-- Create Employee Modal -->
<div class="modal fade" id="addDepartment" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createDepartmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Add Department</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-large-line fw-semibold"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="departmentCreateForm" method="POST" action="{{ route('department.store') }}">
                    @csrf

                    <div class="row g-3">

                        <!-- Organization Name -->
                        <div class="col-12">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="department_name" class="form-control" placeholder="Enter Department Name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Division</label>
                            <select name="division_id" id="division_id" class="form-control" required>
                                <option value="" selected disabled>-- Select Division --</option>
                                @foreach ($division as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>


                    </div>

                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnCreateDepartment">Save Data</button>
            </div>

        </div>
    </div>
</div>
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
                            <select name="division_id" id="edit_division_department_name" class="form-control" required>
                                <option value="" disabled>-- Select Division --</option>
                                @foreach ($division as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
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


<!-- Submit Section -->



@push('scripts')
<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#departmentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('department.data') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },

                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'division',
                    name: 'division'
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

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "{{ session('success') }}",
        timer: 2000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
</script>
@endif

<script>
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

                // Tutup modal
                $('#addDepartment').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                // Reload DataTable tanpa reload halaman
                $('#departmentTable').DataTable().ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Section added successfully",
                    timer: 1500,
                    showConfirmButton: false
                });

                // Reset form
                form.trigger('reset');
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to add data"
                });
            }
        });
    });
</script>

<script>
    $(document).ready(function() {

        $(document).on('click', '.department-edit-btn', function() {
            var id = $(this).data('id');

            // Buat URL edit dengan dummy ID
            var editUrl = "{{ route('department.edit', ['id' => 0]) }}";
            editUrl = editUrl.replace('/0/edit', '/' + id + '/edit');

            $.get(editUrl, function(response) {

                // response = data dari controller
                $('#edit_department_name').val(response.name);
                $('#edit_division_department_name').val(response.division_id).change();
                $('#edit_status_department').val(response.status);

                // Atur URL update
                var updateUrl = "{{ route('department.update', ['id' => 0]) }}";
                updateUrl = updateUrl.replace('/0', '/' + id);

                $('#departmentEditForm').attr('action', updateUrl);

                $('#editDepartment').modal('show');
            });
        });
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

                // Fix overlay nyangkut
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#departmentTable').DataTable().ajax.reload();

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Department updated successfully",
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });
</script>

<script>
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

                let deleteUrl = "/department/delete/" + id;

                $.ajax({
                    url: deleteUrl,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {

                        $('#departmentTable').DataTable().ajax.reload(null, false);

                        Swal.fire({
                            icon: "success",
                            title: "Deleted",
                            text: "Department deleted successfully",
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            }
        });
    });
</script>



@endpush