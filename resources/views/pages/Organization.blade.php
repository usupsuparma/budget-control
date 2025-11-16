<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">

                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrganization">
                            <i class="bi bi-plus-lg me-1"></i>Add Organization
                        </button>

                    </div>
                </div>

                <div class="card-body">
                    <table id="organizationTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Organization</th>
                                <th>Job Level</th>
                                <th>Status</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Create Modal -->
<!-- Create Employee Modal -->
<div class="modal fade" id="addOrganization" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createEmployeeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Add Organization</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-large-line fw-semibold"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="organizationCreateForm" method="POST" action="{{ route('organization.create') }}">
                    @csrf

                    <div class="row g-3">

                        <!-- Organization Name -->
                        <div class="col-12">
                            <label class="form-label">Organization Name</label>
                            <input type="text" name="organization_name" class="form-control" placeholder="Enter organization name" required>
                        </div>

                        <!-- Job Level -->
                        <div class="col-12">
                            <label class="form-label">Job Level</label>
                            <select name="job_level_name" class="form-select" required>
                                <option value="" selected disabled>-- Select Job Level --</option>
                                <option value="Director">Director</option>
                                <option value="Division">Division</option>
                                <option value="Department">Department</option>
                                <option value="Section">Section</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>

                    </div>

                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="organizationCreateForm">
                    Save Data
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Submit Section -->



@push('scripts')
<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#organizationTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('organization.data') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },

                {
                    data: 'organization_name',
                    name: 'organization_name'
                },
                {
                    data: 'job_level_name',
                    name: 'job_level_name'
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [0, 'asc']
            ]
        });
    });
</script>

@endpush