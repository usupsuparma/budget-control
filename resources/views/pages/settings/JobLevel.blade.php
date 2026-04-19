<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobLevel">
                            <i class="bi bi-plus-lg me-1"></i>Add Job Level
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="jobLevelTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
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
<div class="modal fade" id="addJobLevel" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createJobLevelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Job Level</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="jobLevelCreateForm" method="POST" action="{{ route('jobLevel.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Job Level Name</label>
                            <input type="text" name="jobLevel_name" class="form-control" placeholder="Enter Job Level Name" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="jobLevelCreateForm">Save Data</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editJobLevel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Job Level</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="jobLevelEditForm" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Job Level Name</label>
                            <input type="text" name="jobLevel_name" id="edit_jobLevel_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label> Status </label>
                            <select name="status" id="edit_status_jobLevel" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" form="jobLevelEditForm">Update</button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
    $(document).ready(function() {
        $('#jobLevelTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('jobLevel.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'job_level_name', name: 'job_level_name' },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[0, 'asc']]
        });
    });

    $('#jobLevelCreateForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                $('#addJobLevel').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                $('#jobLevelTable').DataTable().ajax.reload(null, false);
                
                // 🔥 Sync tab lain
                refreshMasterOptions();

                Swal.fire({ icon: "success", title: "Success", text: "Job Level added", timer: 1500, showConfirmButton: false });
                $('#jobLevelCreateForm').trigger('reset');
            }
        });
    });

    // ... (EDIT & DELETE same pattern with refreshMasterOptions())
</script>
@endpush
