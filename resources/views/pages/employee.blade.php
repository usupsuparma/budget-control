<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">
                        <a href="{{ route('company-policy.create') }}">
                            <button class="btn btn-primary">
                                <i class="bi bi-plus-circle-dotted me-2"></i>Add Data
                            </button>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <table id="employeeTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Role ID</th>
                                <th>Status</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee as $emp)
                            <tr>
                                <td>{{ $emp->id }}</td>
                                <td>{{ $emp->email }}</td>
                                <td>{{ $emp->first_name }} {{ $emp->last_name }}</td>
                                <td>{{ $emp->role_id }}</td>
                                <td>
                                    @if($emp->status == 'Active')
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-warning">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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
        $('#employeeTable').DataTable({
            pageLength: 10,
            order: [
                [0, 'asc']
            ]
        });
    });
</script>
@endpush