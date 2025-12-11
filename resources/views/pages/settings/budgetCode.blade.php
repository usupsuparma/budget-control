<div class="card">
    <div class="card-header d-flex justify-content-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetCodeModal">
            <i class="bi bi-plus-lg me-1"></i> Add Budget Code
        </button>
    </div>

    <div class="card-body">
        <table id="budgetCodeTable" class="table table-bordered table-striped w-100">
            <thead class="table-light">
                <tr>
                    <th width="100px">Budget Code</th>
                    <th>Name</th>
                    <th>Incharge </th>
                    <th>Remarks</th>
                    <th>Goods Code</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- ================= ADD ================= --}}
<div class="modal fade" id="addBudgetCodeModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Add Budget Code</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="createBudgetForm">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Stock Code</label>
                            <input type="text" name="stock_code" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>User No</label>
                            <input type="number" name="user_no" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Goods Code</label>
                            <input type="text" name="goods_code" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Delivery Date</label>
                            <input type="date" name="delivdate" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label>Memo</label>
                            <textarea name="memo" rows="3" class="form-control"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" id="saveBudget">Save</button>
                </div>

            </form>
        </div>
    </div>
</div>


{{-- ================= EDIT ================= --}}
<div class="modal fade" id="editBudgetCodeModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Edit Budget Code</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editBudgetForm">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" id="edit_id">

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label>Stock Code</label>
                            <input type="text" name="stock_code" id="edit_stock_code" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>User No</label>
                            <input type="number" name="user_no" id="edit_user_no" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Goods Code</label>
                            <input type="text" name="goods_code" id="edit_goods_code" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Delivery Date</label>
                            <input type="date" name="delivdate" id="edit_delivdate" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label>Memo</label>
                            <textarea name="memo" id="edit_memo" rows="3" class="form-control"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Update</button>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts')
<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#budgetCodeTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('budgetCode.data') }}",
            columns: [{
                    data: 'stock_code',
                    name: 'stock_code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'inchargeCode',
                    name: 'inchargeCode'
                },
                {
                    data: 'remarks',
                    name: 'remarks'
                },
                {
                    data: 'goods_code',
                    name: 'goods_code'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [0, 'desc']
            ]
        });

        // CREATE
        $('#saveBudget').click(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('budgetCode.store') }}",
                method: "POST",
                data: $('#createBudgetForm').serialize(),
                success: function(res) {
                    $('#addBudgetCodeModal').modal('hide');
                    $('#createBudgetForm')[0].reset();
                    table.ajax.reload();
                    Swal.fire("Success", res.message, "success");
                }
            });
        });

        // EDIT SHOW
        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');

            $.get("{{ url('budgetCode') }}/" + id + "/edit", function(data) {
                $('#edit_id').val(data.id);
                $('#edit_stock_code').val(data.stock_code);
                $('#edit_name').val(data.name);
                $('#edit_user_no').val(data.user_no);
                $('#edit_goods_code').val(data.goods_code);
                $('#edit_delivdate').val(data.delivdate);
                $('#edit_status').val(data.status);
                $('#edit_memo').val(data.memo);

                $('#editBudgetCodeModal').modal('show');
            });
        });

        // UPDATE
        $('#editBudgetForm').submit(function(e) {
            e.preventDefault();

            let id = $('#edit_id').val();

            $.ajax({
                url: "{{ url('budgetCode') }}/" + id,
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    $('#editBudgetCodeModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire("Updated!", res.message, "success");
                }
            });
        });

        // DELETE
        $(document).on('click', '.delete-btn', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Delete"
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('budgetCode') }}/" + id,
                        method: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            table.ajax.reload();
                            Swal.fire("Deleted!", res.message, "success");
                        }
                    });
                }
            });
        });

    });
</script>

@endpush