<div class="card">
    <div class="card-header d-flex justify-content-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStockCodeModal">
            <i class="bi bi-plus-lg me-1"></i> Add Stock Code
        </button>
    </div>

    <div class="card-body">
        <table id="stockCodeTable" class="table table-bordered table-striped w-100">
            <thead class="table-light">
                <tr>
                    <th>Stock Code</th>
                    <th>Name</th>
                    <th>Unit</th>
                    <th>Budget Code</th>
                    <th>Warehouse</th>
                    <th>Category</th>
                    <th>Product Line</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- ================= ADD ================= --}}
<div class="modal fade" id="addStockCodeModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Add Stock Code</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createStockForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Stock Code</label>
                            <input type="text" name="stock_code" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Unit</label>
                            <input type="text" name="unit" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Budget Code</label>
                            <input type="text" name="budget_code" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Warehouse</label>
                            <input type="text" name="warehouse" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Category</label>
                            <input type="text" name="category" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Product Line</label>
                            <input type="text" name="product_line" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= EDIT ================= --}}
<div class="modal fade" id="editStockCodeModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Edit Stock Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editStockForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_stock_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Stock Code</label>
                            <input type="text" name="stock_code" id="edit_stock_code_val" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Name</label>
                            <input type="text" name="name" id="edit_stock_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Unit</label>
                            <input type="text" name="unit" id="edit_stock_unit" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Budget Code</label>
                            <input type="text" name="budget_code" id="edit_stock_budget_code" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Warehouse</label>
                            <input type="text" name="warehouse" id="edit_stock_warehouse" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Category</label>
                            <input type="text" name="category" id="edit_stock_category" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Product Line</label>
                            <input type="text" name="product_line" id="edit_stock_product_line" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="active" id="edit_stock_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let stockTable = $('#stockCodeTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('stock-code.data') }}",
            columns: [
                { data: 'stock_code', name: 'stock_code' },
                { data: 'name', name: 'name' },
                { data: 'unit', name: 'unit' },
                { data: 'budget_code', name: 'budget_code' },
                { data: 'warehouse', name: 'warehouse' },
                { data: 'category', name: 'category' },
                { data: 'product_line', name: 'product_line' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // CREATE
        $('#createStockForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('stock-code.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    $('#addStockCodeModal').modal('hide');
                    $('#createStockForm')[0].reset();
                    stockTable.ajax.reload();
                    Swal.fire("Success", res.message, "success");
                },
                error: function(err) {
                    let errors = err.responseJSON.errors;
                    let errorMsg = "";
                    $.each(errors, function(key, value) {
                        errorMsg += value[0] + "\n";
                    });
                    Swal.fire("Error", errorMsg, "error");
                }
            });
        });

        // EDIT SHOW
        $(document).on('click', '.edit-stock-btn', function() {
            let id = $(this).data('id');
            $.get("{{ url('stock-code') }}/" + id + "/edit", function(data) {
                $('#edit_stock_id').val(data.id);
                $('#edit_stock_code_val').val(data.stock_code);
                $('#edit_stock_name').val(data.name);
                $('#edit_stock_unit').val(data.unit);
                $('#edit_stock_budget_code').val(data.budget_code);
                $('#edit_stock_warehouse').val(data.warehouse);
                $('#edit_stock_category').val(data.category);
                $('#edit_stock_product_line').val(data.product_line);
                $('#edit_stock_active').val(data.active);
                $('#editStockCodeModal').modal('show');
            });
        });

        // UPDATE
        $('#editStockForm').submit(function(e) {
            e.preventDefault();
            let id = $('#edit_stock_id').val();
            $.ajax({
                url: "{{ url('stock-code') }}/" + id,
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    $('#editStockCodeModal').modal('hide');
                    stockTable.ajax.reload();
                    Swal.fire("Updated!", res.message, "success");
                },
                error: function(err) {
                    let errors = err.responseJSON.errors;
                    let errorMsg = "";
                    $.each(errors, function(key, value) {
                        errorMsg += value[0] + "\n";
                    });
                    Swal.fire("Error", errorMsg, "error");
                }
            });
        });

        // DELETE
        $(document).on('click', '.delete-stock-btn', function() {
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
                        url: "{{ url('stock-code') }}/" + id,
                        method: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            stockTable.ajax.reload();
                            Swal.fire("Deleted!", res.message, "success");
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
