<div class="card">
    <div class="card-header d-flex justify-content-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
            <i class="bi bi-plus-lg me-1"></i> Add Unit
        </button>
    </div>

    <div class="card-body">
        <table id="unitTable" class="table table-bordered table-striped w-100">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Unit</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th width="120px">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- ================= ADD ================= --}}
<div class="modal fade" id="addUnitModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Add Unit</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createUnitForm" method="POST" action="{{ route('unit.store') }}">

                @csrf

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label>Unit Name</label>
                            <input type="text" name="unit" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label>Code</label>
                            <input type="text" name="code" class="form-control">
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
                    <button class="btn btn-primary" id="saveUnit">Save</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ================= EDIT ================= --}}
<div class="modal fade" id="editUnitModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Edit Unit</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editUnitForm" method="POST">
                @csrf

                <input type="hidden" id="edit_id">

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label>Unit Name</label>
                            <input type="text" id="edit_unit" name="unit" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label>Code</label>
                            <input type="text" id="edit_code" name="code" class="form-control">
                        </div>



                        <div class="col-12">
                            <label>Status</label>
                            <select id="edit_status" name="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" form="editUnitForm">Update</button>
                </div>

            </form>
        </div>
    </div>
</div>


@push('scripts')
<script>
    // INIT DATATABLE
    let unitTable = $('#unitTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('unit.data') }}",
        columns: [{
                data: 'id'
            },
            {
                data: 'unit'
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
    $('#saveUnit').click(function(e) {
        e.preventDefault();

        let form = $('#createUnitForm');
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {

                // Tutup modal
                $('#addUnitModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                // Reload DataTable tanpa reload halaman
                $('#unitTable').DataTable().ajax.reload(null, false);

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
    $(document).on('click', '.unit-edit-btn', function() {
        let id = $(this).data('id');

        $.get("{{ url('/unit') }}/" + id + "/edit", function(data) {
            $('#edit_unit').val(data.unit);
            $('#edit_code').val(data.code);
            $('#edit_status').val(data.status);
            $('#editUnitForm').attr('action', "{{ url('/unit') }}/" + id);
            $('#editUnitModal').modal('show');
        });
    });

    // UPDATE
    $('#editUnitForm').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "PUT",
            data: form.serialize(),
            success: function(res) {
                $('#editUnitModal').modal('hide');

                // Fix overlay nyangkut
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#unitTable').DataTable().ajax.reload();

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Unit updated successfully",
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });


    // DELETE
    $(document).on('click', '.unit-delete-btn', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: "Delete Unit?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Delete"
        }).then(result => {
            if (result.isConfirmed) {

                $.ajax({
                    url: "/unit/" + id,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: res => {
                        unitTable.ajax.reload();
                        Swal.fire("Deleted!", res.message, "success");
                    }
                });

            }
        });
    });
</script>
@endpush