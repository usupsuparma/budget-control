<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">

                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDivision">
                            <i class="bi bi-plus-lg me-1"></i>Add Division
                        </button>

                    </div>
                </div>

                <div class="card-body">
                    <table id="divisionTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Division</th>
                                <th>Director (Parent)</th>
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
<div class="modal fade" id="addDivision" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createDivisionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Add Division</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-large-line fw-semibold"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="divisionCreateForm" method="POST" action="{{ route('division.store') }}">
                    @csrf

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Division Name</label>
                            <input type="text" name="division_name" class="form-control" placeholder="Enter Division Name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Director</label>
                            <select name="director_id" id="director_id" class="form-control" required>
                                <option value="" selected disabled>-- Select Director --</option>
                            </select>
                        </div>

                    </div>

                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnCreateDivision">Save Data</button>
            </div>

        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editDivision" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Division</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="divisionEditForm" method="POST">
                    @csrf
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Division Name</label>
                            <input type="text" name="division_name" id="edit_division_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Director</label>
                            <select name="director_id" id="edit_director_division_id" class="form-control" required>
                                <option value="" disabled>-- Select Director --</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label>Status</label>
                            <select name="status" id="edit_status_division" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                    </div>
                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" form="divisionEditForm">Update</button>
            </div>

        </div>
    </div>
</div>

@push('page-scripts')
<script>
    $(document).ready(function() {
        // Event listener untuk refresh data master
        $(document).on('masterDataRefreshed', function(e, data) {
            console.log("Division partial: master data refreshed", data.directors);
            if (data.directors) {
                populateSelect('director_id', data.directors);
                populateSelect('edit_director_division_id', data.directors);
            }
        });

        // Inisialisasi dropdown awal jika data sudah ada
        if (window.masterData && window.masterData.directors) {
            populateSelect('director_id', window.masterData.directors);
            populateSelect('edit_director_division_id', window.masterData.directors);
        }

        $('#divisionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('division.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'director', name: 'director' },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });
    });

    // CREATE (AJAX)
    $('#btnCreateDivision').click(function(e) {
        e.preventDefault();
        let form = $('#divisionCreateForm');
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {
                $('#addDivision').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#divisionTable').DataTable().ajax.reload(null, false);
                
                // Refresh data global
                refreshMasterOptions();

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Division added successfully",
                    timer: 1500,
                    showConfirmButton: false
                });

                form.trigger('reset');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to add data"
                });
            }
        });
    });

    // EDIT
    var $divEditModal = document.getElementById('editDivision');
    var _divData = {};

    $(document).on('click', '.division-edit-btn', function() {
        var id = $(this).data('id');
        var url = "{{ route('division.edit', ':id') }}".replace(':id', id);
        Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.get(url, function(data) {
            Swal.close();
            _divData = data;
            $('#edit_division_name').val(data.name);
            $('#edit_status_division').val(data.status || 'Active');
            $('#divisionEditForm').attr('action', "{{ route('division.update', ':id') }}".replace(':id', data.id));
            new bootstrap.Modal($divEditModal).show();
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load data.' });
        });
    });

    $divEditModal.addEventListener('shown.bs.modal', function () {
        var dirs = (window.masterData && window.masterData.directors) ? window.masterData.directors : [];
        populateSelect('edit_director_division_id', dirs, _divData.director_id);
        if (!window.masterChoices['edit_director_division_id']) {
            initChoices('edit_director_division_id');
        }
    });

    $divEditModal.addEventListener('hidden.bs.modal', function () {
        if (window.masterChoices['edit_director_division_id']) {
            window.masterChoices['edit_director_division_id'].destroy();
            delete window.masterChoices['edit_director_division_id'];
        }
        _divData = {};
    });


    $('#divisionEditForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {
                $('#editDivision').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#divisionTable').DataTable().ajax.reload();
                refreshMasterOptions();

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Division updated successfully",
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

    // DELETE
    $(document).on('click', '.division-delete-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: "Delete Division?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Delete",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/division/delete/" + id,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        $('#divisionTable').DataTable().ajax.reload(null, false);
                        refreshMasterOptions();
                        Swal.fire({
                            icon: "success",
                            title: "Deleted",
                            text: "Division deleted successfully",
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
