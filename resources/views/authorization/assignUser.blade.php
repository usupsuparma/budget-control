<div class="row">
    <div class="col-12">

        <div class="card shadow-sm border">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>User Role Management</h5>

                <div>
                    <button class="btn btn-success btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalAddRole">
                        <i class="bi bi-plus-circle me-1"></i> Add Role
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAssignRole">
                        <i class="bi bi-person-check me-1"></i> Assign Role to User
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table id="userRoleTable" class="table table-bordered table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">ID</th>
                            <th width="25%">Role Name</th>
                            <th width="50%">Users with this Role</th>
                            <th width="20%">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td class="fw-semibold">{{ $role->name }}</td>

                            <!-- Users yang punya role ini -->
                            <td>
                                @php
                                    $usersWithRole = $role->users;
                                @endphp

                                @if($usersWithRole->count() == 0)
                                    <span class="text-muted small fst-italic">No users assigned</span>
                                @else
                                    @foreach($usersWithRole as $u)
                                    <span class="badge bg-info text-dark me-1 mb-1 d-inline-flex align-items-center">
                                        {{ $u->name }}
                                        <button
                                            class="btn btn-sm btn-link text-danger ms-1 p-0 removeUserFromRole"
                                            data-user="{{ $u->id }}"
                                            data-role="{{ $role->id }}"
                                            data-user-name="{{ $u->name }}"
                                            data-role-name="{{ $role->name }}"
                                            title="Remove user from role">
                                            <i class="bi bi-x-lg" style="font-size: 10px;"></i>
                                        </button>
                                    </span>
                                    @endforeach
                                @endif
                            </td>

                            <td>
                                <div class="btn-group btn-group-sm">
                                    <!-- Manage Permission -->
                                    <button class="btn btn-outline-secondary managePermission"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->name }}"
                                        data-bs-toggle="tooltip"
                                        title="Manage Permissions">
                                        <i class="bi bi-list-check"></i>
                                    </button>

                                    <!-- Edit Role -->
                                    <button class="btn btn-outline-primary editRole"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->name }}"
                                        data-bs-toggle="tooltip"
                                        title="Edit Role">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Delete -->
                                    @if($role->name != 'Super Admin')
                                    <button class="btn btn-outline-danger deleteRole"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->name }}"
                                        data-bs-toggle="tooltip"
                                        title="Delete Role">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
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

<!-- ============================================================
     MODAL — ASSIGN ROLE TO USER
=============================================================== -->
<div class="modal fade" id="modalAssignRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Assign Role to User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formAssignRole">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select User <span class="text-danger">*</span></label>
                        <select class="form-select" id="assign_user_select" name="user_id" required>
                            <option value="">-- Choose User --</option>
                            @if(isset($employees))
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->name }} ({{ $emp->email ?? 'No email' }})
                                </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="assign_role_select" name="role" required>
                            <option value="">-- Choose Role --</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btnSaveAssignRole">
                    <i class="bi bi-check-lg me-1"></i> Assign Role
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — ADD ROLE
=============================================================== -->
<div class="modal fade" id="modalAddRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formAddRole">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="role_name" name="name" 
                            placeholder="e.g., Manager, Editor, Viewer" required>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveRole">
                    <i class="bi bi-save me-1"></i> Save Role
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — EDIT ROLE
=============================================================== -->
<div class="modal fade" id="modalEditRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formEditRole">
                    @csrf
                    <input type="hidden" id="edit_role_id" name="id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_role_name" name="name" required>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnUpdateRole">
                    <i class="bi bi-save me-1"></i> Update Role
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — MANAGE PERMISSIONS FOR ROLE
=============================================================== -->
<div class="modal fade" id="modalPermissions" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-shield-check me-2"></i>
                    Manage Permissions for: <span id="perm_role_name" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="perm_role_id">
                
                <div class="alert alert-info py-2 px-3 mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Check permissions to grant access to this role.
                </div>

                <div class="row" id="permissionList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btnSavePermissions">
                    <i class="bi bi-save me-1"></i> Save Permissions
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#userRoleTable')) {
        $('#userRoleTable').DataTable().destroy();
    }
    $('#userRoleTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[0, 'asc']]
    });

    // ==============================
    // 1. ADD ROLE
    // ==============================
    $('#btnSaveRole').on('click', function() {
        let roleName = $('#role_name').val();

        if (!roleName || roleName.trim() === '') {
            showAlert('error', 'Error', 'Role name is required!');
            return;
        }

        let btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-hourglass me-1"></i> Saving...');

        $.ajax({
            url: "{{ route('auth.roles.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: roleName
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Success', 'Role created successfully!');
                    $('#modalAddRole').modal('hide');
                    $('#role_name').val('');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Failed to create role';
                showAlert('error', 'Error', msg);
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Save Role');
            }
        });
    });

    // ==============================
    // 2. EDIT ROLE - Open Modal
    // ==============================
    $(document).on('click', '.editRole', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');

        $('#edit_role_id').val(id);
        $('#edit_role_name').val(name);
        $('#modalEditRole').modal('show');
    });

    // ==============================
    // 3. UPDATE ROLE
    // ==============================
    $('#btnUpdateRole').on('click', function() {
        let id = $('#edit_role_id').val();
        let name = $('#edit_role_name').val();

        if (!name || name.trim() === '') {
            showAlert('error', 'Error', 'Role name is required!');
            return;
        }

        let btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-hourglass me-1"></i> Updating...');

        $.ajax({
            url: "{{ url('authorization/roles/update') }}/" + id,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: name
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Success', 'Role updated successfully!');
                    $('#modalEditRole').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Failed to update role';
                showAlert('error', 'Error', msg);
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Update Role');
            }
        });
    });

    // ==============================
    // 4. DELETE ROLE
    // ==============================
    $(document).on('click', '.deleteRole', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');

        Swal.fire({
            title: 'Delete Role?',
            text: `Are you sure you want to delete role "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('authorization/roles/delete') }}/" + id,
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', 'Deleted', 'Role has been deleted.');
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Failed to delete role';
                        showAlert('error', 'Error', msg);
                    }
                });
            }
        });
    });

    // ==============================
    // 5. ASSIGN ROLE TO USER
    // ==============================
    $('#btnSaveAssignRole').on('click', function() {
        let userId = $('#assign_user_select').val();
        let role = $('#assign_role_select').val();

        if (!userId) {
            showAlert('error', 'Error', 'Please select a user!');
            return;
        }
        if (!role) {
            showAlert('error', 'Error', 'Please select a role!');
            return;
        }

        let btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-hourglass me-1"></i> Assigning...');

        $.ajax({
            url: "{{ route('auth.assign.role') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                user_id: userId,
                role: role
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Success', 'Role assigned to user successfully!');
                    $('#modalAssignRole').modal('hide');
                    $('#assign_user_select').val('');
                    $('#assign_role_select').val('');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Failed to assign role';
                showAlert('error', 'Error', msg);
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Assign Role');
            }
        });
    });

    // ==============================
    // 6. REMOVE USER FROM ROLE
    // ==============================
    $(document).on('click', '.removeUserFromRole', function(e) {
        e.preventDefault();

        let userId = $(this).data('user');
        let roleId = $(this).data('role');
        let userName = $(this).data('user-name');
        let roleName = $(this).data('role-name');

        Swal.fire({
            title: 'Remove User from Role?',
            text: `Remove "${userName}" from role "${roleName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('role.removeUser') }}",
                    method: "POST",
                    data: {
                        user_id: userId,
                        role_id: roleId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        showAlert('success', 'Removed', res.message || 'User removed from role.');
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Failed to remove user from role';
                        showAlert('error', 'Error', msg);
                    }
                });
            }
        });
    });

    // ==============================
    // 7. MANAGE PERMISSIONS - Open Modal
    // ==============================
    $(document).on('click', '.managePermission', function() {
        let roleId = $(this).data('id');
        let roleName = $(this).data('name');

        $('#perm_role_id').val(roleId);
        $('#perm_role_name').text(roleName);

        // Show loading
        $('#permissionList').html(`
            <div class="col-12 text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        $('#modalPermissions').modal('show');

        // Load permissions
        $.ajax({
            url: "{{ url('authorization/roles') }}/" + roleId + "/permissions",
            type: "GET",
            success: function(res) {
                let html = '';
                
                // Group permissions by module
                let grouped = {};
                res.permissions.forEach(p => {
                    let moduleName = p.modul_menu_name || p.name.split('.')[0] || 'General';
                    if (!grouped[moduleName]) {
                        grouped[moduleName] = [];
                    }
                    grouped[moduleName].push(p);
                });

                // Build HTML
                for (let module in grouped) {
                    html += `<div class="col-12 mb-3">
                        <h6 class="fw-bold text-secondary border-bottom pb-1">${module}</h6>
                        <div class="row">`;
                    
                    grouped[module].forEach(p => {
                        let checked = res.selected.includes(p.name) ? 'checked' : '';
                        html += `
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input permissionCheck" type="checkbox"
                                        value="${p.name}" id="perm_${p.id}" ${checked}>
                                    <label class="form-check-label" for="perm_${p.id}">
                                        <code class="small">${p.name}</code>
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += '</div></div>';
                }

                if (res.permissions.length === 0) {
                    html = '<div class="col-12 text-center text-muted py-4">No permissions available</div>';
                }

                $('#permissionList').html(html);
            },
            error: function(xhr) {
                $('#permissionList').html('<div class="col-12 text-center text-danger py-4">Failed to load permissions</div>');
            }
        });
    });

    // ==============================
    // 8. SAVE PERMISSIONS
    // ==============================
    $('#btnSavePermissions').on('click', function() {
        let roleId = $('#perm_role_id').val();
        let selectedPermissions = [];

        $('.permissionCheck:checked').each(function() {
            selectedPermissions.push($(this).val());
        });

        let btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-hourglass me-1"></i> Saving...');

        $.ajax({
            url: "{{ url('authorization/roles') }}/" + roleId + "/permissions/update",
            type: "POST",
            data: {
                permissions: selectedPermissions,
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.success) {
                    showAlert('success', 'Success', 'Permissions updated successfully!');
                    $('#modalPermissions').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Failed to update permissions';
                showAlert('error', 'Error', msg);
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Save Permissions');
            }
        });
    });

    // ==============================
    // Helper: Show Alert
    // ==============================
    function showAlert(type, title, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type,
                title: title,
                text: message,
                timer: type === 'success' ? 2000 : undefined,
                showConfirmButton: type !== 'success'
            });
        } else {
            alert(title + ': ' + message);
        }
    }
});
</script>
@endpush
