<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">

                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobLevel">
                            <i class="bi bi-plus-lg me-1"></i>Add Job Level
                        </button>

                    </div>
                </div>

                <div class="card-body">
                    <table id="jobLevelTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Job Level</th>
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
<div class="modal fade" id="addJobLevel" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createJobLevelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Add Job Level</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-large-line fw-semibold"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="jobLevelCreateForm" method="POST" action="{{ route('jobLevel.store') }}">
                    @csrf

                    <div class="row g-3">

                        <!-- Organization Name -->
                        <div class="col-12">
                            <label class="form-label">Job Level Name</label>
                            <input type="text" name="jobLevel_name" class="form-control" placeholder="Enter Job Level Name" required>
                        </div>



                    </div>

                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="jobLevelCreateForm">
                    Save Data
                </button>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="editJobLevel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Job Level</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="jobLevelEditForm" method="POST">
                    @csrf
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Job Level Name</label>
                            <input type="text" name="jobLevel_name" id="edit_jobLevel_name" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label> Status </label>
                            <select name="status" id="edit_status_jobLevel" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" form="jobLevelEditForm">Update</button>
            </div>

        </div>
    </div>
</div>


<!-- Submit Section -->



@push('page-scripts')

<!-- jQuery & DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {

        // =======================
        // INIT DATATABLES
        // =======================
        $('#jobLevelTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('jobLevel.data') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'job_level_name',
                    name: 'job_level_name'
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
                },
            ],
            order: [
                [0, 'asc']
            ]
        });

    });
</script>


{{-- CREATE (AJAX) --}}
<script>
    $('#jobLevelCreateForm').submit(function(e) {
        e.preventDefault();

        let url = $(this).attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {

                $('#addJobLevel').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#jobLevelTable').DataTable().ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Job Level added successfully",
                    timer: 1500,
                    showConfirmButton: false
                });

                $('#jobLevelCreateForm').trigger('reset');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "Failed to add data"
                });
                console.log(xhr.responseText);
            }
        });
    });
</script>


{{-- DELETE --}}
<script>
    $(document).on('click', '.jobLevel-delete-btn', function() {

        let id = $(this).data('id');

        Swal.fire({
            title: "Delete Job Level?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Delete",
            cancelButtonText: "Cancel"
        }).then((result) => {

            if (result.isConfirmed) {

                let deleteUrl = "{{ route('jobLevel.delete', ['id' => ':id']) }}".replace(':id', id);

                $.ajax({
                    url: deleteUrl,
                    method: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        $('#jobLevelTable').DataTable().ajax.reload(null, false);
                        Swal.fire({
                            icon: "success",
                            title: "Deleted",
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


{{-- EDIT (SHOW MODAL + FILL DATA) --}}
<script>
    $(document).on('click', '.jobLevel-edit-btn', function() {

        let id = $(this).data('id');

        $.get("{{ url('/jobLevel') }}/" + id + "/edit", function(data) {

            $('#edit_jobLevel_name').val(data.job_level_name);
            $('#edit_status_jobLevel').val(data.status);

            $('#jobLevelEditForm').attr('action', "{{ url('/jobLevel/update') }}/" + id);

            $('#editJobLevel').modal('show');
        });

    });
</script>


{{-- UPDATE (AJAX) --}}
<script>
    $('#jobLevelEditForm').submit(function(e) {
        e.preventDefault();

        let url = $(this).attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {

                $('#editJobLevel').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#jobLevelTable').DataTable().ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Job Level updated successfully",
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

@endpush