@extends('layouts.master')

@section('title', 'Notification Monitoring')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white p-3 rounded-3 me-3">
                            <i class="bi bi-speedometer2 fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">Notification Dashboard</h4>
                            <p class="text-muted mb-0">Monitor all system notifications across categories</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">Recent Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="notifications-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Category</th>
                                    <th>Title</th>
                                    <th>Recipient</th>
                                    <th>Created At</th>
                                    <th width="50px">Action</th>
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
    var table = $('#notifications-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('notifications.monitoring.data') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'category_id', name: 'category_id'},
            {data: 'title', name: 'title'},
            {data: 'employee_id', name: 'employee_id'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [[0, 'desc']]
    });

    $(document).on('click', '.delete-notification', function() {
        if(confirm('Are you sure you want to delete this notification?')) {
            var id = $(this).data('id');
            $.ajax({
                url: "{{ url('notifications/monitoring') }}/" + id,
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
