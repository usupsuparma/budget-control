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
                            <select name="job_level_id" id="jp_create_job_level_id" class="form-select" required>
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
                            <select name="job_level_id" id="jp_edit_job_level_id" class="form-select" required>
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
                <button class="btn btn-primary" id="btnUpdateJobPosition">Update</button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
$(document).ready(function () {

    var ROUTES = {
        data:    "{{ route('jobPosition.data') }}",
        store:   "{{ route('jobPosition.store') }}",
        edit:    "{{ route('jobPosition.edit', ':id') }}",
        update:  "{{ route('jobPosition.update', ':id') }}",
        destroy: "{{ route('jobPosition.delete', ':id') }}",
        orgByLevel: "{{ route('jobPosition.orgByLevel', ['level_id' => 'LEVEL']) }}",
    };

    /* ---- DATATABLE ---- */
    $('#jobPositionTable').DataTable({
        processing: true, serverSide: true, ajax: ROUTES.data,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'job_position_name', name: 'job_position_name' },
            { data: 'organization', name: 'organization' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']]
    });

    /* ---- CHOICES.JS — per modal store ---- */
    var CFG = { searchEnabled: true, itemSelectText: '', allowHTML: true, shouldSort: false };
    var createCh = {}, editCh = {};

    function makeC(store, id) {
        var el = document.getElementById(id);
        if (!el) return null;
        if (store[id]) { try { store[id].destroy(); } catch(e){} }
        store[id] = new Choices(el, CFG);
        return store[id];
    }

    function fillC(inst, data, selVal) {
        if (!inst) return;
        var items = [{ value: '', label: '-- Select --', selected: !selVal, disabled: true }];
        (data || []).forEach(function(d) {
            items.push({ value: String(d.id), label: d.name || '', selected: selVal && String(selVal) === String(d.id) });
        });
        inst.clearStore();
        inst.setChoices(items, 'value', 'label', true);
    }

    function destroyCh(store) {
        Object.keys(store).forEach(function(k) { try { store[k].destroy(); } catch(e){} delete store[k]; });
    }

    /* ---- POPULATE JOB LEVELS (dari masterData) ---- */
    function getJobLevels() {
        return (window.masterData && window.masterData.job_levels) ? window.masterData.job_levels : [];
    }

    /* ---- CREATE MODAL ---- */
    var $CM = document.getElementById('addJobPosition');

    $CM.addEventListener('shown.bs.modal', function () {
        fillC(makeC(createCh, 'jp_create_job_level_id'), getJobLevels(), null);
        $('#dynamicOrganization').html('');
    });
    $CM.addEventListener('hidden.bs.modal', function () {
        destroyCh(createCh);
        $('#jobPositionCreateForm')[0].reset();
        $('#dynamicOrganization').html('');
    });

    // Handle level change → load org select (create)
    $(document).on('change', '#jp_create_job_level_id', function () {
        loadOrgSelect($(this).val(), '#dynamicOrganization', 'structure_id', false);
    });

    // Handle level change → load org select (edit)
    $(document).on('change', '#jp_edit_job_level_id', function () {
        loadOrgSelect($(this).val(), '#dynamicEditOrganization', 'edit_structure_id', false);
    });

    function loadOrgSelect(levelId, targetDiv, selectId, selectedVal) {
        if (!levelId) return;
        var url = ROUTES.orgByLevel.replace('LEVEL', levelId);
        $.get(url, function (res) {
            var html = '<label class="form-label">Organization</label>'
                + '<select name="structure_id" id="' + selectId + '" class="form-select" required>'
                + '<option value="" disabled selected>-- Select --</option>';
            (res.items || []).forEach(function(item) {
                var sel = selectedVal && String(selectedVal) === String(item.id) ? 'selected' : '';
                html += '<option value="' + item.id + '" ' + sel + '>' + item.name + '</option>';
            });
            html += '</select>';
            $(targetDiv).html(html);
            // Init Choices.js untuk elemen dinamis
            if (window.masterChoices && window.masterChoices[selectId]) {
                window.masterChoices[selectId].destroy();
                delete window.masterChoices[selectId];
            }
            if (typeof initChoices === 'function') initChoices(selectId);
        });
    }

    // CREATE submit
    $('#btnCreateJobPosition').on('click', function (e) {
        e.preventDefault();
        Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.ajax({
            url: ROUTES.store, method: 'POST', data: $('#jobPositionCreateForm').serialize(),
            success: function () {
                bootstrap.Modal.getInstance($CM).hide();
                $('#jobPositionTable').DataTable().ajax.reload(null, false);
                refreshMasterOptions();
                Swal.fire({ icon: 'success', title: 'Success', text: 'Job Position added', timer: 1500, showConfirmButton: false });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to save' });
            }
        });
    });

    /* ---- EDIT MODAL ---- */
    var $EM = document.getElementById('editJobPosition');
    var _epJP = {};

    $(document).on('click', '.jobPosition-edit-btn', function () {
        var id = $(this).data('id');
        var url = ROUTES.edit.replace(':id', id);
        Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.get(url, function (res) {
            Swal.close();
            _epJP = res;
            $('#edit_jobPosition_name').val(res.job_position_name);
            $('#edit_status_jobPosition').val(res.status);
            new bootstrap.Modal($EM).show();
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load data.' });
        });
    });

    $EM.addEventListener('shown.bs.modal', function () {
        fillC(makeC(editCh, 'jp_edit_job_level_id'), getJobLevels(), _epJP.job_level_id);

        if (_epJP.job_level_id && _epJP.structure_id) {
            var url = ROUTES.orgByLevel.replace('LEVEL', _epJP.job_level_id);
            $.get(url, function (res) {
                var html = '<label class="form-label">Organization</label>'
                    + '<select name="structure_id" id="edit_structure_id" class="form-select" required>'
                    + '<option value="" disabled selected>-- Select --</option>';
                (res.items || []).forEach(function(item) {
                    var sel = String(_epJP.structure_id) === String(item.id) ? 'selected' : '';
                    html += '<option value="' + item.id + '" ' + sel + '>' + item.name + '</option>';
                });
                html += '</select>';
                $('#dynamicEditOrganization').html(html);
                if (typeof initChoices === 'function') initChoices('edit_structure_id');
            });
        } else {
            $('#dynamicEditOrganization').html('');
        }
    });

    $EM.addEventListener('hidden.bs.modal', function () {
        destroyCh(editCh);
        $('#dynamicEditOrganization').html('');
        _epJP = {};
    });

    $('#btnUpdateJobPosition').on('click', function () {
        var id = _epJP.id;
        var url = ROUTES.update.replace(':id', id);
        Swal.fire({ title: 'Updating...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.ajax({
            url: url, method: 'POST', data: $('#jobPositionEditForm').serialize(),
            success: function () {
                bootstrap.Modal.getInstance($EM).hide();
                $('#jobPositionTable').DataTable().ajax.reload(null, false);
                refreshMasterOptions();
                Swal.fire({ icon: 'success', title: 'Updated!', text: 'Job Position updated.', timer: 1500, showConfirmButton: false });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to update' });
            }
        });
    });

    /* ---- DELETE ---- */
    $(document).on('click', '.jobPosition-delete-btn', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete Job Position?', text: 'This action cannot be undone.',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Delete', cancelButtonText: 'Cancel', confirmButtonColor: '#d33'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Deleting...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
            $.ajax({
                url: ROUTES.destroy.replace(':id', id), method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    Swal.fire({ icon: 'success', title: 'Deleted', timer: 1500, showConfirmButton: false });
                    $('#jobPositionTable').DataTable().ajax.reload(null, false);
                    refreshMasterOptions();
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete.' });
                }
            });
        });
    });

    /* ---- SYNC masterDataRefreshed ---- */
    $(document).on('masterDataRefreshed', function (e, data) {
        if (editCh['jp_edit_job_level_id']) {
            fillC(editCh['jp_edit_job_level_id'], data.job_levels || [], _epJP.job_level_id);
        }
    });

});
</script>
@endpush
