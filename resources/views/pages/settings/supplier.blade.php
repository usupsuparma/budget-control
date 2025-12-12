<div class="card">
    <div class="card-header d-flex justify-content-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="bi bi-plus-lg me-1"></i> Add Supplier
        </button>
    </div>

    <div class="card-body">
        <table id="supplierTable" class="table table-bordered table-striped w-100">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Supplier</th>
                    <th>Call Sign</th>
                    <th>Status</th>
                    <th width="120px">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- ================= ADD ================= --}}
<div class="modal fade" id="addSupplierModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Add Supplier</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createSupplierForm" method="POST" action="{{ route('supplier.store') }}">

                @csrf

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label>Supplier Name</label>
                            <input type="text" name="supplier" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label>Call Sign</label>
                            <input type="text" name="callSign" class="form-control">
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
                    <button class="btn btn-primary" id="saveSupplier">Save</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ================= EDIT ================= --}}
<div class="modal fade" id="editSupplierModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Edit Supplier</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editSupplierForm" method="POST">
                @csrf


                <input type="hidden" id="edit_id">

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label>Supplier Name</label>
                            <input type="text" id="edit_supplier" name="supplier" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label>Call Sign</label>
                            <input type="text" id="edit_callSign" name="callSign" class="form-control">
                        </div>

                        <div class="col-12">
                            <label>Notes</label>
                            <textarea id="edit_notes" name="notes" class="form-control" rows="3"></textarea>
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
                    <button class="btn btn-primary" form="editSupplierForm">Update</button>
                </div>

            </form>
        </div>
    </div>
</div>


@push('scripts')
<script>
    // INIT DATATABLE
    let supplierTable = $('#supplierTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('supplier.data') }}",
        columns: [{
                data: 'id'
            },
            {
                data: 'supplier'
            },
            {
                data: 'callSign'
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
    $('#saveSupplier').click(function(e) {
        e.preventDefault();

        let form = $('#createSupplierForm');
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {

                // Tutup modal
                $('#addSupplierModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                // Reload DataTable tanpa reload halaman
                $('#supplierTable').DataTable().ajax.reload(null, false);

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
    $(document).on('click', '.supplier-edit-btn', function() {
        let id = $(this).data('id');

        $.get("{{ url('/supplier') }}/" + id + "/edit", function(data) {
            $('#edit_supplier').val(data.supplier);
            $('#edit_callSign').val(data.callSign);
            $('#edit_notes').val(data.notes);
            $('#edit_status').val(data.status);
            $('#editSupplierForm').attr('action', "{{ url('/supplier') }}/" + id);

            $('#editSupplierModal').modal('show');
        });
    });

    // UPDATE
    $('#editSupplierForm').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "PUT",
            data: form.serialize(),
            success: function(res) {
                $('#editSupplierModal').modal('hide');

                // Fix overlay nyangkut
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#supplierTable').DataTable().ajax.reload();

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Customer updated successfully",
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
    $(document).on('click', '.supplier-delete-btn', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: "Delete Supplier?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Delete"
        }).then(result => {
            if (result.isConfirmed) {

                $.ajax({
                    url: "/supplier/" + id,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: res => {
                        supplierTable.ajax.reload();
                        Swal.fire("Deleted!", res.message, "success");
                    }
                });

            }
        });
    });
</script>
@endpush