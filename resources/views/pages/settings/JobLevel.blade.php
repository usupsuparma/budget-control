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
<div class="modal fade" id="addJobLevel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Job Level</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="jobLevelCreateForm" method="POST" action="{{ route('jobLevel.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Job Level Name</label>
                            <input type="text" name="jobLevel_name" class="form-control" placeholder="Enter Job Level Name" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="jobLevelCreateForm">Save Data</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
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
                            <label>Status</label>
                            <select name="status" id="edit_status_jobLevel" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnUpdateJobLevel">Update</button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
$(document).ready(function () {

    var ROUTES = {
        data:    "{{ route('jobLevel.data') }}",
        store:   "{{ route('jobLevel.store') }}",
        edit:    "{{ route('jobLevel.edit', ':id') }}",
        update:  "{{ route('jobLevel.update', ':id') }}",
        destroy: "{{ route('jobLevel.delete', ':id') }}",
    };

    /* ---- DATATABLE ---- */
    $('#jobLevelTable').DataTable({
        processing: true, serverSide: true, ajax: ROUTES.data,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'job_level_name', name: 'job_level_name' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[0, 'asc']]
    });

    /* ---- CREATE ---- */
    $('#jobLevelCreateForm').on('submit', function (e) {
        e.preventDefault();
        Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.ajax({
            url: ROUTES.store, method: 'POST', data: $(this).serialize(),
            success: function () {
                bootstrap.Modal.getInstance(document.getElementById('addJobLevel')).hide();
                $('#jobLevelTable').DataTable().ajax.reload(null, false);
                refreshMasterOptions();
                Swal.fire({ icon: 'success', title: 'Success', text: 'Job Level added', timer: 1500, showConfirmButton: false });
                $('#jobLevelCreateForm')[0].reset();
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to save.' });
            }
        });
    });

    /* ---- EDIT ---- */
    var _epJL = {};

    $(document).on('click', '.jobLevel-edit-btn', function () {
        var id = $(this).data('id');
        var url = ROUTES.edit.replace(':id', id);
        Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.get(url, function (res) {
            Swal.close();
            _epJL = res;
            $('#edit_jobLevel_name').val(res.job_level_name || res.name);
            $('#edit_status_jobLevel').val(res.status || 'Active');
            $('#jobLevelEditForm').attr('action', ROUTES.update.replace(':id', res.id));
            new bootstrap.Modal(document.getElementById('editJobLevel')).show();
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load data.' });
        });
    });

    $('#btnUpdateJobLevel').on('click', function () {
        Swal.fire({ title: 'Updating...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.ajax({
            url: $('#jobLevelEditForm').attr('action'), method: 'POST',
            data: $('#jobLevelEditForm').serialize(),
            success: function () {
                bootstrap.Modal.getInstance(document.getElementById('editJobLevel')).hide();
                $('#jobLevelTable').DataTable().ajax.reload(null, false);
                refreshMasterOptions();
                Swal.fire({ icon: 'success', title: 'Updated!', timer: 1500, showConfirmButton: false });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to update.' });
            }
        });
    });

    /* ---- DELETE ---- */
    $(document).on('click', '.jobLevel-delete-btn', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete Job Level?', text: 'This action cannot be undone.',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Delete', cancelButtonText: 'Cancel', confirmButtonColor: '#d33'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Deleting...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
            $.ajax({
                url: ROUTES.destroy.replace(':id', id), method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    Swal.fire({ icon: 'success', title: 'Deleted', timer: 1500, showConfirmButton: false });
                    $('#jobLevelTable').DataTable().ajax.reload(null, false);
                    refreshMasterOptions();
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete.' });
                }
            });
        });
    });

});
</script>
@endpush
