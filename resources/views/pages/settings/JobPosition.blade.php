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
                            <select name="job_level_id" class="form-select" required>
                                <option value="" disabled selected>-- Select Job Level --</option>
                                @foreach ($jobLevel as $level)
                                <option value="{{ $level->id }}">{{ $level->job_level_name }}</option>
                                @endforeach
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
                                @foreach ($jobLevel as $level)
                                <option value="{{ $level->id }}">{{ $level->job_level_name }}</option>
                                @endforeach
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

<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    let jobPositionTable;

    $(document).ready(function() {

        jobPositionTable = $('#jobPositionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('jobPosition.data') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'job_position_name',
                    name: 'job_position_name'
                },
                {
                    data: 'organization',
                    name: 'organization'
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
                [0, 'desc']
            ]
        });

    });
</script>


<!-- LOAD ORGANIZATION CREATE -->
<script>
    $('select[name="job_level_id"]').on('change', function() {

        let levelId = $(this).val();
        $('#dynamicOrganization').html('');

        $.ajax({
            url: "{{ route('jobPosition.orgByLevel', ['level_id' => 'LEVEL']) }}".replace('LEVEL', levelId),
            success: function(res) {

                if (res.items.length === 0) {
                    $('#dynamicOrganization').html(`<small class="text-danger">No organization found.</small>`);
                    return;
                }

                let html = `
                <div class="col-12 mt-2">
                    <label class="form-label">Organization</label>
                    <select name="structure_id" class="form-select" required>
                        <option value="" disabled selected>-- Select --</option>
            `;

                res.items.forEach(item => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });

                html += `</select></div>`;

                $('#dynamicOrganization').html(html);
            }
        });

    });
</script>


<!-- CREATE AJAX -->
<script>
    $('#btnCreateJobPosition').click(function(e) {
        e.preventDefault();

        let form = $('#jobPositionCreateForm');
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function() {

                $('#addJobPosition').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                jobPositionTable.ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Job Position added successfully",
                    timer: 1500,
                    showConfirmButton: false
                });

                form.trigger('reset');
                $('#dynamicOrganization').html('');
            }
        });
    });
</script>


<!-- EDIT BUTTON -->
<script>
    $(document).on('click', '.jobPosition-edit-btn', function() {

        let id = $(this).data('id');
        let editUrl = "{{ route('jobPosition.edit', ['id' => 0]) }}".replace('/0/edit', '/' + id + '/edit');

        $.get(editUrl, function(res) {

            $('#edit_jobPosition_name').val(res.job_position_name);
            $('#edit_job_level_id').val(res.job_level_id).change();
            $('#edit_status_jobPosition').val(res.status);

            loadEditStructure(res.job_level_id, res.structure_id);

            let updateUrl = "{{ route('jobPosition.update', ['id' => 0]) }}".replace('/0', '/' + id);
            $('#jobPositionEditForm').attr('action', updateUrl);

            $('#editJobPosition').modal('show');
        });

    });
</script>


<!-- LOAD STRUCTURE EDIT -->
<script>
    function loadEditStructure(levelId, selectedId = null) {

        $('#dynamicEditOrganization').html("");

        $.ajax({
            url: "{{ route('jobPosition.orgByLevel', ['level_id' => 'LEVEL']) }}"
                .replace('LEVEL', levelId),

            success: function(res) {

                if (!res.items || res.items.length === 0) {
                    $('#dynamicEditOrganization').html(`<small class="text-danger">No data found.</small>`);
                    return;
                }

                let html = `
                <label class="form-label">Parent Structure</label>
                <select name="structure_id" id="edit_structure_id" class="form-select" required>
                    <option value="" disabled>-- Select --</option>
            `;

                res.items.forEach(item => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });

                html += `</select>`;

                $('#dynamicEditOrganization').html(html);

                if (selectedId) {
                    $('#edit_structure_id').val(selectedId);
                }
            }
        });

    }
</script>

<script>
    $('#edit_job_level_id').on('change', function() {
        loadEditStructure($(this).val(), null);
    });
</script>


<!-- UPDATE AJAX -->
<script>
    $('#jobPositionEditForm').submit(function(e) {
        e.preventDefault();

        let url = $(this).attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: $(this).serialize(),
            success: function() {

                $('#editJobPosition').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                jobPositionTable.ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Job Position updated successfully",
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });

    });
</script>


<!-- DELETE -->
<script>
    $(document).on('click', '.jobPosition-delete-btn', function() {

        let id = $(this).data('id');

        Swal.fire({
            title: "Delete Job Position?",
            text: "This action is permanent.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Delete"
        }).then((result) => {

            if (result.isConfirmed) {

                let deleteUrl = "{{ route('jobPosition.delete', ['id' => 0]) }}".replace('/0', '/' + id);

                $.ajax({
                    url: deleteUrl,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {

                        jobPositionTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: "success",
                            title: "Deleted",
                            text: "Job Position removed",
                            timer: 1500,
                            showConfirmButton: false
                        });

                    }
                });
            }

        });

    });
</script>

@endpush