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
<div class="modal fade" id="addDirector" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Director</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="directorCreateForm" method="POST" action="{{ route('director.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Director Name</label>
                            <input type="text" name="director_name" class="form-control" placeholder="Enter Director Name" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnCreateDirector">Save Data</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
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
                            <label>Status</label>
                            <select name="status" id="edit_status_director" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnUpdateDirector">Update</button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
$(document).ready(function () {

    var ROUTES = {
        data:    "{{ route('director.data') }}",
        store:   "{{ route('director.store') }}",
        edit:    "{{ route('director.edit', ':id') }}",
        update:  "{{ route('director.update', ':id') }}",
        destroy: "{{ route('director.delete', ':id') }}",
    };

    /* ---- DATATABLE ---- */
    $('#directorTable').DataTable({
        processing: true, serverSide: true, ajax: ROUTES.data,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    /* ---- CREATE ---- */
    $('#btnCreateDirector').on('click', function (e) {
        e.preventDefault();
        Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.ajax({
            url: ROUTES.store, method: 'POST', data: $('#directorCreateForm').serialize(),
            success: function () {
                bootstrap.Modal.getInstance(document.getElementById('addDirector')).hide();
                $('#directorTable').DataTable().ajax.reload(null, false);
                refreshMasterOptions();
                Swal.fire({ icon: 'success', title: 'Success', text: 'Director added', timer: 1500, showConfirmButton: false });
                $('#directorCreateForm')[0].reset();
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to save.' });
            }
        });
    });

    /* ---- EDIT ---- */
    $(document).on('click', '.director-edit-btn', function () {
        var id = $(this).data('id');
        var url = ROUTES.edit.replace(':id', id);
        Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.get(url, function (res) {
            Swal.close();
            $('#edit_director_name').val(res.name);
            $('#edit_status_director').val(res.status || 'Active');
            $('#directorEditForm').attr('action', ROUTES.update.replace(':id', res.id));
            new bootstrap.Modal(document.getElementById('editDirector')).show();
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load data.' });
        });
    });

    $('#btnUpdateDirector').on('click', function () {
        Swal.fire({ title: 'Updating...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.ajax({
            url: $('#directorEditForm').attr('action'), method: 'POST',
            data: $('#directorEditForm').serialize(),
            success: function () {
                bootstrap.Modal.getInstance(document.getElementById('editDirector')).hide();
                $('#directorTable').DataTable().ajax.reload(null, false);
                refreshMasterOptions();
                Swal.fire({ icon: 'success', title: 'Updated!', timer: 1500, showConfirmButton: false });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to update.' });
            }
        });
    });

    /* ---- DELETE ---- */
    $(document).on('click', '.director-delete-btn', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete Director?', text: 'This action cannot be undone.',
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
                    $('#directorTable').DataTable().ajax.reload(null, false);
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
