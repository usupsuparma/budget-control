<div class="card">
    <div class="card-header d-flex justify-content-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionAuthorizerModal">
            <i class="bi bi-plus-lg me-1"></i> Add Authorizer
        </button>
    </div>

    <div class="card-body">
        <table id="transactionAuthorizerTable" class="table table-bordered table-striped w-100">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Level Number</th>
                    <th>Authorizer</th>
                    <th>Authority</th>
                    <th>Employee</th>
                    <th>Status</th>
                    <th width="120px">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


{{-- ================= ADD ================= --}}
<div class="modal fade" id="addCustomerModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Add Authorizer</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createtransactionAuthorizerForm" method="POST" action="{{ route('transactionAuthorizer.store') }}">

                @csrf

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label>Customer Name</label>
                            <input type="text" name="customer" class="form-control" required>
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
                    <button class="btn btn-primary" id="savetransactionAuthorizer">Save</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ================= EDIT ================= --}}
<div class="modal fade" id="edittransactionAuthorizer">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Edit Authorizer</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editTransactionAuthorizerForm" method="POST">
                @csrf


                <input type="hidden" id="edit_id">

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label>Customer Name</label>
                            <input type="text" id="edit_customer" name="customer" class="form-control" required>
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
                    <button class="btn btn-primary" form="editTransactionAuthorizerForm">Update</button>
                </div>

            </form>
        </div>
    </div>
</div>


@push('scripts')
<script>
    // INIT DATATABLE
    let customerTable = $('#transactionAuthorizerTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('transactionAuthorizer.data') }}",
        columns: [{
                data: 'id'
            },
            {
                data: 'customer'
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
    $('#saveTransactionAuthorizer').click(function(e) {
                e.preventDefault();

                let form = $('#createTransactionAuthorizerForm');
                let url = form.attr('action');

                $.ajax({
                    url: url,
                    method: "POST",
                    data: form.serialize(),
                    success: function(res) {

                        // Tutup modal
                        $('#addTransactionAuthorizerModal').modal('hide');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();

                        // Reload DataTable tanpa reload halaman
                        $('#transactionAuthorizerTable').DataTable().ajax.reload(null, false);

                        Swal.fire({
                                icon: "success",
                                // Tutup modal
                                $('#addTransactionAuthorizerModal').modal('hide');
                                $('body').removeClass('modal-open');
                                $('.modal-backdrop').remove();

                                // Reload DataTable tanpa reload halaman
                                $('#transactionAuthorizerTable').DataTable().ajax.reload(null, false);

                                Swal.fire({
                                    icon: "success",
                                    title: "Success",
                                    text: "Customer added successfully",
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

                $(document).on('click', '.customer-edit-btn', function() {
                    var id = $(this).data('id');

                    $.get("{{ url('/transactionAuthorizer') }}/" + id + "/edit", function(data) {
                        $('#edit_customer').val(data.customer);
                        $('#edit_callSign').val(data.callSign).change();
                        $('#edit_notes').val(data.notes);
                        $('#edit_status').val(data.status);
                        $('#editTransactionAuthorizerForm').attr('action', "{{ url('/transactionAuthorizer') }}/" + id);
                        $('#editTransactionAuthorizer').modal('show');
                    });
                });


                $('#editTransactionAuthorizerForm').submit(function(e) {
                    e.preventDefault();

                    let form = $(this);
                    let url = form.attr('action');

                    $.ajax({
                        url: url,
                        method: "PUT",
                        data: form.serialize(),
                        success: function(res) {
                            $('#editCustomer').modal('hide');

                            // Fix overlay nyangkut
                            $('body').removeClass('modal-open');
                            $('.modal-backdrop').remove();

                            $('#customerTable').DataTable().ajax.reload();

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
                $(document).on('click', '.customer-delete-btn', function() {
                    let id = $(this).data('id');

                    Swal.fire({
                        title: "Delete Customer?",
                        text: "This action cannot be undone.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        confirmButtonText: "Delete"
                    }).then(result => {
                        if (result.isConfirmed) {

                            $.ajax({
                                url: "/customer/" + id,
                                type: "DELETE",
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: res => {
                                    customerTable.ajax.reload();
                                    Swal.fire("Deleted!", res.message, "success");
                                }
                            });

                        }
                    });
                });
</script>

@endpush