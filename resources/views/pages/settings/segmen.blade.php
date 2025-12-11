<div class="card">
    <div class="card-header d-flex justify-content-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSegmenModal">
            <i class="bi bi-plus-lg me-1"></i> Add Segmen
        </button>
    </div>

    <div class="card-body">
        <table id="segmenTable" class="table table-bordered table-striped w-100">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Segmen</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th width="120px">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- ================= ADD ================= --}}
<div class="modal fade" id="addSegmenModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Add Segmen</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createSegmenForm" method="POST" action="{{ route('segmen.store') }}">

                @csrf

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label>Segmen Name</label>
                            <input type="text" name="supplier" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label>Code</label>
                            <input type="text" name="code" class="form-control">
                        </div>

                        <div class="col-12">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-12">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" id="saveSegmen">Save</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ================= EDIT ================= --}}
<div class="modal fade" id="editSegmenModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Edit Segmen</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editSegmenForm">
                @csrf
                @method('PUT')

                <input type="hidden" id="edit_id">

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label>Segmen Name</label>
                            <input type="text" id="edit_segmen" name="segmen" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label>Code</label>
                            <input type="text" id="edit_code" name="code" class="form-control">
                        </div>

                        <div class="col-12">
                            <label>Notes</label>
                            <textarea id="edit_notes" name="notes" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-12">
                            <label>Status</label>
                            <select id="edit_status" name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Update</button>
                </div>

            </form>
        </div>
    </div>
</div>


@push('scripts')
<script>
    // INIT DATATABLE
    let segmenTable = $('#segmenTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('segmen.data') }}",
        columns: [{
                data: 'id'
            },
            {
                data: 'segmen'
            },
            {
                data: 'code'
            },
            {
                data: 'status_badge',
                orderable: false,
                searchable: false
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            }
        ],
        order: [
            [0, 'desc']
        ]
    });

    // CREATE

    // CREATE (AJAX)
    $('#saveSegmen').click(function(e) {
        e.preventDefault();

        let form = $('#createSegmenForm');
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {

                // Tutup modal
                $('#addSegmenModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                // Reload DataTable tanpa reload halaman
                $('#segmenTable').DataTable().ajax.reload(null, false);

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

    // SHOW EDIT
    $(document).on('click', '.segmen-edit-btn', function() {
        let id = $(this).data('id');

        $.get("{{ url('/segmen') }}/" + id + "/edit", function(data) {
            $('#edit_id').val(data.id);
            $('#edit_segmen').val(data.supplier);
            $('#edit_code').val(data.address);
            $('#edit_notes').val(data.notes);
            $('#edit_status').val(data.status);

            $('#editSegmenForm').attr('action', "{{ url('/segmen') }}/" + id);

            $('#editSegmenModal').modal('show');
        });
    });

    // UPDATE
    $('#editSegmenForm').submit(function(e) {
        e.preventDefault();

        let id = $('#edit_id').val();

        $.ajax({
            url: "{{ url('/segmen') }}/" + id,
            method: "POST",
            data: $(this).serialize(),
            success: res => {
                $('#editSegmenModal').modal('hide');
                segmenTable.ajax.reload();

                Swal.fire("Updated!", res.message, "success");
            }
        });
    });

    // DELETE
    $(document).on('click', '.segmen-delete-btn', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: "Delete Segmen?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Delete"
        }).then(result => {
            if (result.isConfirmed) {

                $.ajax({
                    url: "/segmen/" + id,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: res => {
                        segmenTable.ajax.reload();
                        Swal.fire("Deleted!", res.message, "success");
                    }
                });

            }
        });
    });
</script>
@endpush