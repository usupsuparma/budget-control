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



@push('scripts')
<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
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
                }
            ],
            order: [
                [0, 'asc']
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
    $(document).ready(function() {

        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');

            // Buat URL edit dengan dummy ID
            var editUrl = "{{ route('jobLevel.edit', ['id' => 0]) }}";
            editUrl = editUrl.replace('/0/edit', '/' + id + '/edit');

            $.get(editUrl, function(response) {

                // response = data dari controller
                $('#edit_jobLevel_name').val(response.job_level_name);
                $('#edit_status_jobLevel').val(response.status);

                // Atur URL update
                var updateUrl = "{{ route('jobLevel.update', ['id' => 0]) }}";
                updateUrl = updateUrl.replace('/0', '/' + id);

                $('#jobLevelEditForm').attr('action', updateUrl);

                $('#editJobLevel').modal('show');
            });
        });
    });

    // DELETE
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: "Are you sure?",
            text: "Data will be deleted permanently!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {

                var deleteUrl = "{{ route('jobLevel.delete', ['id' => 0]) }}";
                deleteUrl = deleteUrl.replace('/0', '/' + id);

                $.ajax({
                    url: deleteUrl,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {

                        $('#jobLevelTable').DataTable().ajax.reload();

                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text: "Data has been removed",
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