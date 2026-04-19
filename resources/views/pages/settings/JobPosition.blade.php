<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobPosition">
                            <i class="bi bi-plus-lg me-1"></i>Add Job Position
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="jobPositionTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Job Position</th>
                                <th>Organization</th>
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
<div class="modal fade" id="addJobPosition" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Job Position</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="jobPositionCreateForm" method="POST" action="{{ route('jobPosition.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Job Position Name</label>
                            <input type="text" name="job_position_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Level</label>
                            <select name="job_level_id" id="job_level_id" class="form-select" required>
                                <option value="" disabled selected>-- Select Job Level --</option>
                            </select>
                        </div>
                        <div id="dynamicOrganization"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnCreateJobPosition">Save Data</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editJobPosition" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Job Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="jobPositionEditForm" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Job Position Name</label>
                            <input type="text" name="job_position_name" id="edit_jobPosition_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Level</label>
                            <select name="job_level_id" id="edit_job_level_id" class="form-select" required>
                                <option value="" disabled selected>-- Select Job Level --</option>
                            </select>
                        </div>
                        <div class="col-12 mt-3" id="dynamicEditOrganization"></div>
                        <div class="col-12">
                            <label>Status</label>
                            <select name="status" id="edit_status_jobPosition" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" form="jobPositionEditForm">Update</button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
    $(document).ready(function() {
        if (window.masterData && window.masterData.job_levels) {
            populateSelect('job_level_id', window.masterData.job_levels);
            populateSelect('edit_job_level_id', window.masterData.job_levels);
        }

        $(document).on('masterDataRefreshed', function(e, data) {
            populateSelect('job_level_id', data.job_levels);
            populateSelect('edit_job_level_id', data.job_levels);
        });

        $('#jobPositionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('jobPosition.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'job_position_name', name: 'job_position_name' },
                { data: 'organization', name: 'organization' },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });
    });

    // Handle Level change for Organization (Keep existing AJAX logic for sub-filtering)
    $(document).on('change', 'select[name="job_level_id"]', function() {
        let levelId = $(this).val();
        let isEdit = $(this).attr('id') === 'edit_job_level_id';
        let targetDiv = isEdit ? '#dynamicEditOrganization' : '#dynamicOrganization';
        
        if (!levelId) return;

        $.ajax({
            url: "{{ route('jobPosition.orgByLevel', ['level_id' => 'LEVEL']) }}".replace('LEVEL', levelId),
            success: function(res) {
                let html = `<label class="form-label">Organization</label>
                            <select name="structure_id" id="${isEdit ? 'edit_structure_id' : 'structure_id'}" class="form-select" required>
                                <option value="" disabled selected>-- Select --</option>`;
                res.items.forEach(item => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                html += `</select>`;
                $(targetDiv).html(html);
                
                // Init Choices.js for dynamic element
                initChoices(isEdit ? 'edit_structure_id' : 'structure_id');
            }
        });
    });

    // Create & Update logic with refreshMasterOptions()
    $('#btnCreateJobPosition').click(function(e) {
        e.preventDefault();
        let form = $('#jobPositionCreateForm');
        $.ajax({
            url: form.attr('action'),
            method: "POST",
            data: form.serialize(),
            success: function() {
                $('#addJobPosition').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                $('#jobPositionTable').DataTable().ajax.reload(null, false);
                refreshMasterOptions();
                Swal.fire({ icon: "success", title: "Success", text: "Job Position added", timer: 1500, showConfirmButton: false });
                form.trigger('reset');
                $('#dynamicOrganization').html('');
            }
        });
    });

    // ... (EDIT & DELETE same pattern)
</script>
@endpush
