<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary" id="btnCreateEmployee">
                            <i class="bi bi-plus-lg me-1"></i>Create Employee
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="employeeTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>NIP</th>
                                <th>Names &amp; Email</th>
                                <th>Job Position</th>
                                <th>Division</th>
                                <th>Role</th>
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

{{-- CREATE MODAL --}}
<div class="modal fade" id="createEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="employeeCreateForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_last_name" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employee Code (NIP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_employee_code" name="employee_code" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Job Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_job_position_id" name="job_position_id" required>
                                <option value="">-- Select Job Position --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="create_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="create_password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Uppline <small class="text-muted">(auto)</small></label>
                            <div id="create_uppline_info" class="form-control bg-light text-muted fst-italic">-- Pilih Job Position terlebih dahulu --</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_role_name" name="role_name" required>
                                <option value="">-- Select Role --</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0 mt-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="employeeEditForm">
                    @csrf
                    <input type="hidden" id="edit_employee_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employee Code (NIP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_employee_code" name="employee_code" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Job Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_job_position_id" name="job_position_id" required>
                                <option value="">-- Select Job Position --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_role_name" name="role_name" required>
                                <option value="">-- Select Role --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Uppline <small class="text-muted">(auto)</small></label>
                            <div id="edit_uppline_info" class="form-control bg-light text-muted fst-italic">-- Pilih Job Position terlebih dahulu --</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="">-- Select Status --</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnUpdateEmployee"><i class="bi bi-save me-1"></i>Update</button>
            </div>
        </div>
    </div>
</div>

{{-- DETAIL MODAL --}}
<div class="modal fade" id="detailEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-vcard me-2"></i>Employee Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailEmployeeBody">
                <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
$(document).ready(function () {

    /* ---- ROUTES ---- */
    var R = {
        data:   "{{ route('employee.data') }}",
        store:          "{{ route('employee.store') }}",
        edit:           "{{ route('employee.edit', ':id') }}",
        update:         "{{ route('employee.update', ':id') }}",
        show:           "{{ route('employee.show', ':id') }}",
        resolveUppline: "{{ route('employee.resolve-uppline', ':jobPosId') }}",
    };

    // var ROLES = [
    //     {id:'Admin',name:'Admin'},{id:'User',name:'User'},
    //     {id:'Director',name:'Director'},{id:'Manager',name:'Manager'}
    // ];
    var ROLES = @json(
        $roles->map(function($role){
            return [
                'id' => $role->id,
                'name' => $role->name
            ];
        })
    );
    var STATUSES = [{id:'Active',name:'Active'},{id:'Inactive',name:'Inactive'}];

    /* ---- DATATABLE ---- */
    var dt = $('#employeeTable').DataTable({
        processing: true, serverSide: true, ajax: R.data,
        columns: [
            {data:'id',name:'id'},
            {data:'employee_code',name:'employee_code'},
            {data:'full_name',name:'full_name'},
            {data:'job_info',name:'job_info'},
            {data:'division',name:'division'},
            {data:'roles',name:'roles'},
            {data:'status_badge',name:'status',orderable:false},
            {data:'action',name:'action',orderable:false,searchable:false}
        ],
        order:[[0,'desc']]
    });

    /* ================================================================
       CHOICES.JS HELPERS
       Setiap modal mengelola store instance-nya sendiri.
       Init HANYA saat shown.bs.modal → destroy saat hidden.bs.modal
       ================================================================ */
    var CFG = { searchEnabled:true, itemSelectText:'', allowHTML:true, shouldSort:false };

    /** Buat instance Choices.js baru. Destroy dulu jika sudah ada di store. */
    function makeChoices(store, id, placeholder) {
        var el = document.getElementById(id);
        if (!el) return null;
        if (store[id]) { try { store[id].destroy(); } catch(e){} }
        store[id] = new Choices(el, Object.assign({}, CFG, { placeholderValue: placeholder || '-- Select --' }));
        return store[id];
    }

    /** Isi Choices instance dengan array data. */
    function fill(instance, data, selectedVal, labelField, placeholder) {
        if (!instance) return;
        labelField = labelField || 'name';
        var items = [{ value:'', label: placeholder || '-- Select --', selected: !selectedVal, disabled:true }];
        (data || []).forEach(function(d) {
            items.push({ value: String(d.id), label: d[labelField] || '', selected: selectedVal && String(selectedVal) === String(d.id) });
        });
        instance.clearStore();
        instance.setChoices(items, 'value', 'label', true);
    }

    /** Ambil nilai dari Choices instance. */
    function val(store, id) {
        if (store[id]) { var v = store[id].getValue(true); return v || ''; }
        return $('#' + id).val() || '';
    }

    /** Destroy semua instance dalam store. */
    function destroyAll(store) {
        Object.keys(store).forEach(function(k) { try { store[k].destroy(); } catch(e){} delete store[k]; });
    }

    /* ================================================================
       UPPLINE AUTO-RESOLVER
       Dipanggil saat user memilih Job Position di modal create/edit.
       Mengambil uppline dari server berdasarkan hierarchy org,
       lalu menampilkan nama uppline sebagai info read-only.
       ================================================================ */
    function loadUpplineInfo(jobPositionId, infoElementId) {
        var $el = $('#' + infoElementId);
        if (!jobPositionId) {
            $el.text('-- Pilih Job Position terlebih dahulu --').removeClass('text-dark').addClass('text-muted fst-italic');
            return;
        }
        $el.text('Memuat uppline...').removeClass('text-dark text-danger').addClass('text-muted fst-italic');
        $.get(R.resolveUppline.replace(':jobPosId', jobPositionId), function(res) {
            if (res.success && res.data) {
                $el.text(res.data.name).removeClass('text-muted fst-italic text-danger').addClass('text-dark');
            } else {
                $el.text('Tidak ada uppline (posisi tertinggi)').removeClass('text-dark text-danger').addClass('text-muted fst-italic');
            }
        }).fail(function() {
            $el.text('Gagal memuat uppline').removeClass('text-muted fst-italic text-dark').addClass('text-danger');
        });
    }

    /* ================================================================
       CREATE MODAL
       ================================================================ */
    var C = {};  // store instance create modal
    var $CM = document.getElementById('createEmployeeModal');

    $('#btnCreateEmployee').on('click', function () {
        $('#employeeCreateForm')[0].reset();
        $('#create_uppline_info').text('-- Pilih Job Position terlebih dahulu --').removeClass('text-dark text-danger').addClass('text-muted fst-italic');
        new bootstrap.Modal($CM).show();
    });

    $CM.addEventListener('shown.bs.modal', function () {
        var jobs = (window.masterData && window.masterData.job_positions) ? window.masterData.job_positions : [];
        fill(makeChoices(C, 'create_job_position_id', '-- Select Job Position --'), jobs, null, 'name', '-- Select Job Position --');
        fill(makeChoices(C, 'create_role_name', '-- Select Role --'), ROLES, null, 'name', '-- Select Role --');

        // Listen for job position change to auto-resolve uppline
        C['create_job_position_id'].passedElement.element.addEventListener('change', function() {
            loadUpplineInfo(this.value, 'create_uppline_info');
        });
    });

    $CM.addEventListener('hidden.bs.modal', function () { destroyAll(C); });

    $('#employeeCreateForm').on('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(this);
        fd.set('job_position_id', val(C, 'create_job_position_id'));
        fd.set('role_name',       val(C, 'create_role_name'));

        Swal.fire({ title:'Saving...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });
        $.ajax({
            url: R.store, method:'POST', data: fd, processData:false, contentType:false,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(res) {
                Swal.close();
                if (res.success) {
                    bootstrap.Modal.getInstance($CM).hide();
                    Swal.fire({icon:'success', title:'Success', text:res.message, timer:2000, showConfirmButton:false});
                    dt.ajax.reload(null, false);
                    if (typeof refreshMasterOptions === 'function') refreshMasterOptions();
                } else {
                    Swal.fire({icon:'error', title:'Gagal', text: res.message || 'Terjadi kesalahan.'});
                }
            },
            error: function(xhr) {
                Swal.close();
                var msg = 'Terjadi kesalahan server.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors)
                    msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                Swal.fire({icon:'error', title:'Error', text: msg});
            }
        });
    });

    /* ================================================================
       EDIT MODAL
       ================================================================ */
    var E = {};       // store instance edit modal
    var _ep = {};     // payload sementara hasil fetch
    var $EM = document.getElementById('editEmployeeModal');

    $(document).on('click', '.employee-edit-btn', function () {
        var url = R.edit.replace(':id', $(this).data('id'));
        Swal.fire({ title:'Loading...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });
        $.get(url, function(res) {
            Swal.close();
            _ep = res;
            $('#edit_employee_id').val(res.id);
            $('#edit_first_name').val(res.first_name);
            $('#edit_last_name').val(res.last_name);
            $('#edit_employee_code').val(res.employee_code);
            $('#edit_email').val(res.email);
            new bootstrap.Modal($EM).show();
        }).fail(function() {
            Swal.fire({icon:'error', title:'Error', text:'Gagal memuat data employee.'});
        });
    });

    $EM.addEventListener('shown.bs.modal', function () {
        var emp      = _ep.employment || {};
        var jobPosId = emp.job_position_id || null;
        var roleName = (_ep.roles && _ep.roles.length) ? _ep.roles[0].id : '';
        var status   = _ep.status || 'Active';
        var jobs     = (window.masterData && window.masterData.job_positions) ? window.masterData.job_positions : [];

        fill(makeChoices(E, 'edit_job_position_id', '-- Select Job Position --'), jobs, jobPosId, 'name', '-- Select Job Position --');
        fill(makeChoices(E, 'edit_role_name', '-- Select Role --'), ROLES, roleName, 'name', '-- Select Role --');
        fill(makeChoices(E, 'edit_status', '-- Select Status --'), STATUSES, status, 'name', '-- Select Status --');

        // Load current uppline info, then listen for job position change
        loadUpplineInfo(jobPosId, 'edit_uppline_info');
        E['edit_job_position_id'].passedElement.element.addEventListener('change', function() {
            loadUpplineInfo(this.value, 'edit_uppline_info');
        });
    });

    $EM.addEventListener('hidden.bs.modal', function () { destroyAll(E); _ep = {}; });

    $('#btnUpdateEmployee').on('click', function () {
        var url = R.update.replace(':id', $('#edit_employee_id').val());
        var payload = {
            _token:          $('meta[name="csrf-token"]').attr('content'),
            first_name:      $('#edit_first_name').val(),
            last_name:       $('#edit_last_name').val(),
            employee_code:   $('#edit_employee_code').val(),
            email:           $('#edit_email').val(),
            job_position_id: val(E, 'edit_job_position_id'),
            role_name:       val(E, 'edit_role_name'),
            status:          val(E, 'edit_status'),
        };

        Swal.fire({ title:'Updating...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });
        $.ajax({
            url: url, method:'POST', data: payload,
            success: function(res) {
                Swal.close();
                if (res.success) {
                    bootstrap.Modal.getInstance($EM).hide();
                    Swal.fire({icon:'success', title:'Success', text:res.message, timer:2000, showConfirmButton:false});
                    dt.ajax.reload(null, false);
                    if (typeof refreshMasterOptions === 'function') refreshMasterOptions();
                } else {
                    Swal.fire({icon:'error', title:'Gagal', text: res.message || 'Terjadi kesalahan.'});
                }
            },
            error: function(xhr) {
                Swal.close();
                var msg = 'Terjadi kesalahan server.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors)
                    msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                Swal.fire({icon:'error', title:'Error', text: msg});
            }
        });
    });

    /* ================================================================
       DETAIL MODAL
       ================================================================ */
    $(document).on('click', '.open-detail', function () {
        var url = R.show.replace(':id', $(this).data('id'));
        $('#detailEmployeeBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');
        new bootstrap.Modal(document.getElementById('detailEmployeeModal')).show();

        $.get(url, function(res) {
            var emp = res.employment || {};
            var role = (res.roles && res.roles.length) ? res.roles[0].name : '-';
            var div  = (emp.job_position && emp.job_position.structure) ? emp.job_position.structure.name : (emp.organization_name || '-');
            var badge = res.status === 'Active'
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>';

            $('#detailEmployeeBody').html(
                '<div class="row g-3">'
                + '<div class="col-md-6"><p class="text-muted small mb-0">NIP</p><p class="fw-semibold">' + (res.employee_code||'-') + '</p></div>'
                + '<div class="col-md-6"><p class="text-muted small mb-0">Full Name</p><p class="fw-semibold">' + (res.first_name||'') + ' ' + (res.last_name||'') + '</p></div>'
                + '<div class="col-md-6"><p class="text-muted small mb-0">Email</p><p class="fw-semibold">' + (res.email||'-') + '</p></div>'
                + '<div class="col-md-6"><p class="text-muted small mb-0">Status</p><p>' + badge + '</p></div>'
                + '<div class="col-md-6"><p class="text-muted small mb-0">Job Position</p><p class="fw-semibold">' + (emp.job_position_name||'-') + '</p></div>'
                + '<div class="col-md-6"><p class="text-muted small mb-0">Job Level</p><p class="fw-semibold">' + (emp.job_level_name||'-') + '</p></div>'
                + '<div class="col-md-6"><p class="text-muted small mb-0">Division</p><p class="fw-semibold text-primary">' + div + '</p></div>'
                + '<div class="col-md-6"><p class="text-muted small mb-0">Role</p><p><span class="badge border border-primary text-primary">' + role + '</span></p></div>'
                + '</div>'
            );
        }).fail(function() {
            $('#detailEmployeeBody').html('<div class="alert alert-danger">Gagal memuat detail employee.</div>');
        });
    });

    /* ================================================================
       DELETE
       ================================================================ */
    $(document).on('click', '.employee-delete-btn', function () {
        var id = $(this).data('id');
        Swal.fire({
            icon:'warning', title:'Hapus Employee?', text:'Tindakan ini tidak dapat dibatalkan.',
            showCancelButton:true, confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal', confirmButtonColor:'#d33'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            Swal.fire({ title:'Deleting...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });
            $.ajax({
                url: '/employee/delete/' + id, method:'DELETE',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(res) {
                    Swal.close();
                    if (res.success) {
                        Swal.fire({icon:'success', title:'Deleted!', text:'Employee berhasil dihapus.', timer:2000, showConfirmButton:false});
                        dt.ajax.reload(null, false);
                    } else {
                        Swal.fire({icon:'error', title:'Gagal', text: res.message||'Gagal menghapus.'});
                    }
                },
                error: function() { Swal.fire({icon:'error', title:'Error', text:'Terjadi kesalahan saat menghapus.'}); }
            });
        });
    });

});
</script>
@endpush
