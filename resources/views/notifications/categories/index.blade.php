@extends('layouts.master')

@section('title', 'Notification Categories')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0" id="form-title">Create Category</h5>
                </div>
                <div class="card-body">
                    <form id="category-form">
                        <input type="hidden" name="id" id="category-id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="e.g. Announcement" required>
                        </div>
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon (Class name)</label>
                            <input type="text" class="form-control" name="icon" id="icon" placeholder="e.g. bi bi-bell">
                            <small class="text-muted">Use Bootstrap Icons or FontAwesome class.</small>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="save-btn">Save Category</button>
                            <button type="button" class="btn btn-secondary d-none" id="cancel-btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">Notification Categories</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="categories-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Icon</th>
                                    <th width="100px">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    var table = $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('notifications.categories.data') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'icon', name: 'icon'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

    $('#category-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: "{{ route('notifications.categories.store') }}",
            type: "POST",
            data: formData + "&_token={{ csrf_token() }}",
            success: function(response) {
                $('#category-form')[0].reset();
                resetForm();
                table.ajax.reload();
                alert(response.success);
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    });

    $(document).on('click', '.edit-category', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var icon = $(this).data('icon');
        
        $('#category-id').val(id);
        $('#name').val(name);
        $('#icon').val(icon);
        
        $('#form-title').text('Edit Category');
        $('#save-btn').text('Update Category').removeClass('btn-success').addClass('btn-warning');
        $('#cancel-btn').removeClass('d-none');
    });

    $('#cancel-btn').on('click', function() {
        resetForm();
    });

    function resetForm() {
        $('#category-id').val('');
        $('#category-form')[0].reset();
        $('#form-title').text('Create Category');
        $('#save-btn').text('Save Category').removeClass('btn-warning').addClass('btn-success');
        $('#cancel-btn').addClass('d-none');
    }

    $(document).on('click', '.delete-category', function() {
        if(confirm('Are you sure you want to delete this category?')) {
            var id = $(this).data('id');
            $.ajax({
                url: "{{ url('notifications/categories') }}/" + id,
                type: "DELETE",
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    table.ajax.reload();
                    alert(response.success);
                }
            });
        }
    });
});
</script>
@endsection
