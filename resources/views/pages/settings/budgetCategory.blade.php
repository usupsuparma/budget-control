<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetCategory">
                            <i class="bi bi-plus-lg me-1"></i>Add Budget Category
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <table id="budgetCategoryTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Parent</th>
                                <th>Level</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th class="text-center" width="120px">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Budget Category Modal -->
<div class="modal fade" id="addBudgetCategory" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Budget Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="budgetCategoryCreateForm" action="{{ route('budgetCategory.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Level <span class="text-danger">*</span></label>
                            <select name="level" class="form-select" required>
                                <option value="">Select Level</option>
                                <option value="1">1 - Parent</option>
                                <option value="2">2 - Child</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnCreateBudgetCategory">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Budget Category Modal -->
<div class="modal fade" id="editBudgetCategory" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Budget Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="budgetCategoryEditForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_id">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="edit_code" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Level <span class="text-danger">*</span></label>
                            <select name="level" id="edit_level" class="form-select" required>
                                <option value="1">1 - Parent</option>
                                <option value="2">2 - Child</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" id="edit_parent_id" class="form-select">
                                <option value="">None</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="edit_is_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#budgetCategoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('budgetCategory.data') }}",
            columns: [
                { data: 'code', name: 'code' },
                { data: 'name', name: 'name' },
                { data: 'parent_name', name: 'parent_name' },
                { data: 'level', name: 'level' },
                { data: 'sort_order', name: 'sort_order' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // Load parent categories
        loadParentCategories();
    });

    function loadParentCategories() {
        $.get("{{ route('budgetCategory.parents') }}", function(data) {
            let options = '<option value="">None</option>';
            data.forEach(function(cat) {
                options += `<option value="${cat.id}">${cat.code} - ${cat.name}</option>`;
            });
            $('select[name="parent_id"]').html(options);
        });
    }

    // CREATE
    $('#budgetCategoryCreateForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                $('#addBudgetCategory').modal('hide');
                form[0].reset();
                $('#budgetCategoryTable').DataTable().ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMsg = '';
                $.each(errors, function(key, value) {
                    errorMsg += value[0] + '<br>';
                });
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMsg
                });
            }
        });
    });

    // EDIT
    $(document).on('click', '.budgetCategory-edit-btn', function() {
        let id = $(this).data('id');
        
        $.get("{{ url('/budgetCategory') }}/" + id + "/edit", function(data) {
            $('#edit_id').val(data.id);
            $('#edit_code').val(data.code);
            $('#edit_name').val(data.name);
            $('#edit_level').val(data.level);
            $('#edit_parent_id').val(data.parent_id);
            $('#edit_sort_order').val(data.sort_order);
            $('#edit_is_active').val(data.is_active ? '1' : '0');
            $('#edit_description').val(data.description);
            
            $('#budgetCategoryEditForm').attr('action', "{{ url('/budgetCategory') }}/" + id);
            $('#editBudgetCategory').modal('show');
        });
    });

    // UPDATE
    $('#budgetCategoryEditForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                $('#editBudgetCategory').modal('hide');
                $('#budgetCategoryTable').DataTable().ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMsg = '';
                $.each(errors, function(key, value) {
                    errorMsg += value[0] + '<br>';
                });
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMsg
                });
            }
        });
    });

    // DELETE
    $(document).on('click', '.budgetCategory-delete-btn', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('/budgetCategory') }}/" + id,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#budgetCategoryTable').DataTable().ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.message || 'Failed to delete'
                        });
                    }
                });
            }
        });
    });
</script>
@endpush
