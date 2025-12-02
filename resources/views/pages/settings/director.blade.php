<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">

                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDirector">
                            <i class="bi bi-plus-lg me-1"></i>Add Director
                        </button>

                    </div>
                </div>

                <div class="card-body">
                    <table id="directorTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Director</th>
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
<div class="modal fade" id="addDirector" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createDirectorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Add Director</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-large-line fw-semibold"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="directorCreateForm" method="POST" action="{{ route('director.store') }}">
                    @csrf

                    <div class="row g-3">

                        <!-- Organization Name -->
                        <div class="col-12">
                            <label class="form-label">Director Name</label>
                            <input type="text" name="director_name" class="form-control" placeholder="Enter Director Name" required>
                        </div>



                    </div>

                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnCreateDirector">Save Data</button>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="editDirector" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Director</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="directorEditForm" method="POST">
                    @csrf
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Director Name</label>
                            <input type="text" name="director_name" id="edit_director_name" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label> Status </label>
                            <select name="status" id="edit_status_director" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" form="directorEditForm">Update</button>
            </div>

        </div>
    </div>
</div>


<!-- Submit Section -->



@push('page-scripts')

<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#directorTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('director.data') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },

                    {
                        data: 'name',
                        name: 'name'
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
            }),
            $('#addDirector').on('shown.bs.modal', function() {
                $('#addDirector input[name="director_name"]').trigger('focus');
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
    $('#btnCreateDirector').click(function(e) {
        e.preventDefault();

        let form = $('#directorCreateForm');
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {

                // Tutup modal
                $('#addDirector').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                // Reload DataTable tanpa reload halaman
                $('#directorTable').DataTable().ajax.reload(null, false);

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
    $(document).on('click', '.director-delete-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: "Delete Director?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Delete",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {

                let deleteUrl = "{{ route('director.delete', ['id' => ':id']) }}".replace(':id', id);

                $.ajax({
                    url: deleteUrl,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {

                        $('#directorTable').DataTable().ajax.reload(null, false);

                        Swal.fire({
                            icon: "success",
                            title: "Deleted",
                            text: "Director deleted successfully",
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
<script>
    $(document).on('click', '.director-edit-btn', function() {
        var id = $(this).data('id');

        $.get("{{ url('/director') }}/" + id + "/edit", function(data) {
            $('#edit_director_name').val(data.name);
            $('#edit_status_director').val(data.status);

            $('#directorEditForm').attr('action', "{{ url('/director/update') }}/" + id);
            $('#editDirector').modal('show');
        });
    });


    $('#directorEditForm').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {
                $('#editDirector').modal('hide');

                // Fix overlay nyangkut
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#directorTable').DataTable().ajax.reload();

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Director updated successfully",
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