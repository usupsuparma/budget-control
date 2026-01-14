<div class="row">
    <div class="col-12">

        <div class="card shadow-sm border">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Role & Permission Management</h5>

                <div>


                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddRole">
                        <i class="bi bi-plus-circle me-1"></i> Add Role
                    </button>

                    <button class="btn btn-secondary btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#modalAssignRole">
                        <i class="bi bi-person-check me-1"></i> Assign Role to User
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table id="roleTable" class="table table-bordered table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">ID</th>
                            <th width="30%">Role Name</th>
                            <th width="40%">Permissions</th>
                            <th width="20%">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>

                            <td>
                                <span class="fw-semibold">{{ $role->name }}</span>
                            </td>

                            <td>
                                @php
                                $perms = $role->permissions->pluck('name')->toArray();
                                @endphp

                                @if(count($perms) == 0)
                                <span class="badge bg-light text-muted">No Permission</span>
                                @else
                                @foreach($perms as $p)
                                <span class="badge bg-primary-subtle text-primary border">{{ $p }}</span>
                                @endforeach
                                @endif
                            </td>

                            <td>
                                <div class="btn-group">

                                    <!-- Manage Permission -->
                                    <button
                                        class="btn btn-sm btn-light-secondary managePermission"
                                        data-id="{{ $role->id }}">
                                        <i class="bi bi-list-check"></i>
                                    </button>

                                    <!-- Edit Role -->
                                    <button
                                        class="btn btn-sm btn-light-primary editRole"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->name }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Delete -->
                                    @if($role->name != 'Super Admin')
                                    <button
                                        class="btn btn-sm btn-light-danger deleteRole"
                                        data-id="{{ $role->id }}">
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
<div class="modal fade" id="modalAddRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- PERBAIKI: id form harus sesuai dengan yang dipanggil di JavaScript -->
                <form id="formAddRole" method="POST">
                    @csrf
                    <input type="hidden" id="role_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Name</label>
                        <input type="text" class="form-control" id="role_name" name="name" placeholder="Enter role name" required>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveRole">Save</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Edit Role -->
<div class="modal fade" id="modalEditRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formEditRole" method="POST">
                    @csrf
                    <input type="hidden" id="edit_role_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Name</label>
                        <input type="text" class="form-control" id="edit_role_name" name="name" placeholder="Enter role name" required>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnUpdateRole">Update</button>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================
     MODAL 2 — MANAGE PERMISSIONS (ASSIGN PERMISSIONS TO ROLE)
=============================================================== -->
<div class="modal fade" id="modalPermissions" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Manage Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-info py-2 px-3 mb-3">
                    <strong>Role:</strong> <span id="perm_role_name" class="fw-bold text-primary"></span>
                </div>

                <form id="formPermissions">
                    @csrf
                    <input type="hidden" id="perm_role_id">

                    <div class="row" id="permissionList">
                        <!-- List permission akan di-load via AJAX -->
                    </div>
                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btnSavePermissions">Save Permissions</button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {

        // ==============================
        // 1. ADD ROLE (FIXED VERSION)
        // ==============================
        $('#btnSaveRole').on('click', function(e) {
            e.preventDefault();

            let roleName = $('#role_name').val();

            if (!roleName || roleName.trim() === '') {
                alert('Role name is required!');
                return;
            }

            $.ajax({
                url: "{{ route('auth.roles.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: roleName
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Role created successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        $('#modalAddRole').modal('hide');

                        // Clear form
                        $('#role_name').val('');

                        // Reload page after 1.5 seconds
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        Swal.fire('Error', response.message || 'Failed to create role', 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Unable to create role';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = '';
                        for (let field in xhr.responseJSON.errors) {
                            errorMessage += xhr.responseJSON.errors[field][0] + '\n';
                        }
                    }

                    Swal.fire('Error', errorMessage, 'error');
                }
            });
        });

        // ==============================
        // 2. EDIT ROLE (OPEN MODAL)
        // ==============================
        $(document).on('click', '.editRole', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');

            $('#edit_role_id').val(id);
            $('#edit_role_name').val(name);

            // Show edit modal (pastikan modal ini ada)
            $('#modalEditRole').modal('show');
        });

        // ==============================
        // 3. UPDATE ROLE (perbaiki URL)
        // ==============================
        $('#formEditRole').on('submit', function(e) {
            e.preventDefault();

            let id = $('#edit_role_id').val();
            let name = $('#edit_role_name').val();

            $.ajax({
                url: "{{ url('authorization/roles/update') }}/" + id,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: name
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Role updated successfully',
                            timer: 1200,
                            showConfirmButton: false
                        });
                        $('#modalEditRole').modal('hide');
                        location.reload();
                    } else {
                        Swal.fire('Error', response.message || 'Failed to update role', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to update role', 'error');
                }
            });
        });

        // ==============================
        // 4. DELETE ROLE
        // ==============================
        $(document).on('click', '.deleteRole', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: "Are you sure?",
                text: "This role will be deleted!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('authorization/roles/delete') }}/" + id,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Role has been deleted.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                location.reload();
                            } else {
                                Swal.fire('Error', response.message || 'Failed to delete role', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Failed to delete role', 'error');
                        }
                    });
                }
            });
        });

        // ==============================
        // 5. MANAGE PERMISSIONS (OPEN)
        // ==============================
        $(document).on('click', '.managePermission', function() {
            let roleId = $(this).data('id');
            let roleName = $(this).closest('tr').find('.fw-semibold').text();

            // Set role info
            $('#perm_role_id').val(roleId);
            $('#perm_role_name').text(roleName);

            // Show loading
            $('#permissionList').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading permissions...</p>
                </div>
            `);

            // Show modal
            $('#modalPermissions').modal('show');

            // Load permissions
            $.ajax({
                url: "{{ url('authorization/roles') }}/" + roleId + "/permissions",
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        buildPermissionList(response.permissions, response.selected);
                    } else {
                        $('#permissionList').html(`
                            <div class="alert alert-danger">
                                Failed to load permissions
                            </div>
                        `);
                    }
                },
                error: function(xhr) {
                    $('#permissionList').html(`
                        <div class="alert alert-danger">
                            Error loading permissions
                        </div>
                    `);
                }
            });
        });

        // Function to build permission list
        function buildPermissionList(permissions, selected) {
            let html = '';

            // Group by module if possible
            let grouped = {};

            permissions.forEach(permission => {
                let module = permission.modul_menu_name || 'General';
                if (!grouped[module]) {
                    grouped[module] = [];
                }
                grouped[module].push(permission);
            });

            // Create checkboxes
            for (let module in grouped) {
                html += `<div class="col-12 mb-3">`;
                html += `<h6 class="mb-2 text-primary">${module}</h6>`;
                html += `<div class="row">`;

                grouped[module].forEach(permission => {
                    let isChecked = selected.includes(permission.name) ? 'checked' : '';

                    html += `
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input permission-checkbox" 
                                   id="perm_${permission.id}"
                                   value="${permission.name}" 
                                   ${isChecked}>
                            <label class="form-check-label" for="perm_${permission.id}">
                                <code class="small">${permission.name}</code>
                                ${permission.modul_menu_name ? `<br><small class="text-muted">${permission.modul_menu_name}</small>` : ''}
                            </label>
                        </div>
                    </div>
                    `;
                });

                html += `</div></div>`;
            }

            $('#permissionList').html(html);
        }

        // ==============================
        // 6. SAVE PERMISSION UPDATE
        // ==============================
        $('#btnSavePermissions').on('click', function() {
            let roleId = $('#perm_role_id').val();

            // Collect selected permissions
            let selected = [];
            $('.permission-checkbox:checked').each(function() {
                selected.push($(this).val());
            });

            $.ajax({
                url: "{{ url('authorization/roles') }}/" + roleId + "/permissions/update",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    permissions: selected
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: "Permissions updated successfully",
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $('#modalPermissions').modal('hide');
                        location.reload();
                    } else {
                        Swal.fire('Error', response.message || 'Failed to update permissions', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to update permissions', 'error');
                }
            });
        });

    });

    // Update button for edit role
    $('#btnUpdateRole').on('click', function() {
        let id = $('#edit_role_id').val();
        let name = $('#edit_role_name').val();

        if (!name || name.trim() === '') {
            alert('Role name is required!');
            return;
        }

        $.ajax({
            url: "{{ url('authorization/roles/update') }}/" + id,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: name
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Role updated successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#modalEditRole').modal('hide');
                    location.reload();
                } else {
                    Swal.fire('Error', response.message || 'Failed to update role', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to update role', 'error');
            }
        });
    });
</script>
@endpush