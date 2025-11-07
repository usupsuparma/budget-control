<div id="layout-wrapper">

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">

                    <div class="col-md-12 col-xl-12 col-xxl-12 text-end">
                        <a href="{{ route('company-policy.create') }}">
                            <button class="btn btn-primary"><i class="bi bi-plus-circle-dotted me-2"></i>Add Data</button>
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
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<!-- DataTables & jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(function() {
        $('#employeeTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/master/employee/data',

            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'fullname',
                    name: 'fullname',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'role_id',
                    name: 'role_id'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            order: [
                [0, 'asc']
            ]
        });
    });
</script>
@endpush