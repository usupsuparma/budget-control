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
<div class="modal fade" id="addTransactionAuthorizerModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Add Authorizer</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="createTransactionAuthorizerForm" method="POST" action="{{ route('authorizationTransaction.store') }}">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">


                        <div class="col-12">
                            <label>Level Number</label>
                            <select name="level_number" class="form-select" required>
                                <option value="" selected disabled>-- Select Level --</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                            </select>
                        </div>


                        <div class="col-12">
                            <label>Authorizer</label>
                            <input type="text" name="authorizer" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label>Authority</label>
                            <input type="text" name="authority" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label>Employee</label>
                            <select name="employee" class="form-select">
                                <option value="" selected disabled>-- Select Employee --</option>
                                @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                                @endforeach
                            </select>
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
                    <button class="btn btn-primary" id="saveTransactionAuthorizer">Save</button>
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
                            <label>Level Number</label>
                            <select id="edit_level_number" name="level_number" class="form-select" required>
                                <option value="" selected disabled>-- Select Level --</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                            </select>
                        </div>


                        <div class="col-12">
                            <label>Authorizer</label>
                            <input type="text" id="edit_authorizer" name="authorizer" class="form-control">
                        </div>
                        <div class="col-12">
                            <label>Authority</label>
                            <input type="text" id="edit_authority" name="authority" class="form-control">
                        </div>

                        <div class="col-12">
                            <label>Employee</label>
                            <select id="edit_employee" name="employee" class="form-select">
                                <option value="" selected disabled>-- Select Employee --</option>
                                @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                                @endforeach
                            </select>
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
    let transactionAuthorizerTable = $('#transactionAuthorizerTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('authorizationTransaction.data') }}",
        columns: [{
                data: 'id'
            },
            {
                data: 'level_number'
            },
            {
                data: 'authorizer_name'
            },
            {
                data: 'authority'
            },
            {
                data: 'employee_name'
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

    // ========================= CREATE =========================
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
                    title: "Success",
                    text: "Customer added successfully",
                    timer: 1500,
                    showConfirmButton: false
                });

                // Reset form
                form.trigger('reset')
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

    // ========================= SHOW EDIT =========================
    $(document).on('click', '.transactionAuthorizer-edit-btn', function() {
        let id = $(this).data('id');

        $.get("{{ url('/authorizationTransaction') }}/" + id + "/edit", function(data) {

            $('#edit_id').val(data.id);
            $('#edit_level_number').val(data.level_number);
            $('#edit_authorizer').val(data.authorizer_name);
            $('#edit_authority').val(data.authority);
            $('#edit_employee').val(data.employee);
            $('#edit_status').val(data.status);

            $('#editTransactionAuthorizerForm').attr('action', "{{ url('/authorizationTransaction') }}/" + id);
            $('#editTransactionAuthorizer').modal('show');
        });
    });

    // ========================= UPDATE =========================
    $('#editTransactionAuthorizerForm').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),

            success: function(res) {
                $('#editTransactionAuthorizer').modal('hide');

                // Fix overlay nyangkut
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#transactionAuthorizerTable').DataTable().ajax.reload();

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Authorization updated successfully",
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }

        });
    });

    // ========================= DELETE =========================
    $(document).on('click', '.transactionAuthorizer-delete-btn', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: "Delete Authorizer?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Delete"
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/authorizationTransaction/" + id,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },

                    success: res => {
                        transactionAuthorizerTable.ajax.reload();

                        Swal.fire("Deleted!", res.message, "success");
                    }
                });
            }
        });
    });
</script>
@endpush