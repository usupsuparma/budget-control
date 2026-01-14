<script>
    $(document).ready(function() {

        // ==============================
        // 1. ADD ROLE
        // ==============================
        $('#formAddRole').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('auth.roles.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Role created successfully',
                        timer: 1200
                    });
                    $('#modalAddRole').modal('hide');
                    location.reload();
                },
                error: function() {
                    Swal.fire('Error', 'Unable to create role', 'error');
                }
            });
        });

        // ==============================
        // 2. EDIT ROLE (OPEN MODAL)
        // ==============================
        $('.editRole').on('click', function() {

            let id = $(this).data('id');
            let name = $(this).data('name');

            $('#edit_role_id').val(id);
            $('#edit_role_name').val(name);

            $('#modalEditRole').modal('show');
        });

        // ==============================
        // 3. UPDATE ROLE
        // ==============================
        $('#formEditRole').on('submit', function(e) {
            e.preventDefault();

            let id = $('#edit_role_id').val();

            $.ajax({
                url: "/authorization/roles/update/" + id,
                type: "POST",
                data: $(this).serialize(),
                success: function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Role updated successfully',
                        timer: 1200
                    });
                    $('#modalEditRole').modal('hide');
                    location.reload();
                }
            });
        });

        // ==============================
        // 4. DELETE ROLE
        // ==============================
        $('.deleteRole').on('click', function() {

            let id = $(this).data('id');

            Swal.fire({
                    title: "Are you sure?",
                    text: "This role will be deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                })
                .then((result) => {
                    if (result.isConfirmed) {

                        $.ajax({
                            url: "/authorization/roles/delete/" + id,
                            type: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function() {
                                Swal.fire('Deleted!', 'Role has been deleted.', 'success');
                                location.reload();
                            }
                        });

                    }
                });
        });

        // ==============================
        // 5. MANAGE PERMISSIONS (OPEN)
        // ==============================
        $('.managePermission').on('click', function() {

            let roleId = $(this).data('id');

            $('#perm_role_id').val(roleId);
            $('#loadingPermissions').show();
            $('#permissionContainer').html('');

            $('#modalManagePermission').modal('show');

            $.ajax({
                url: "/authorization/roles/" + roleId + "/permissions",
                type: "GET",
                success: function(res) {

                    $('#loadingPermissions').hide();
                    $('#roleNameLabel').text(res.role);

                    let html = `<div class="row">`;

                    res.permissions.forEach(p => {
                        let checked = res.assigned.includes(p.name) ? 'checked' : '';

                        html += `
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input permissionCheckbox"
                                       value="${p.name}" ${checked}>
                                <label class="form-check-label">${p.name}</label>
                            </div>
                        </div>
                    `;
                    });

                    html += `</div>`;

                    $('#permissionContainer').html(html);
                }
            });
        });

        // ==============================
        // 6. SAVE PERMISSION UPDATE
        // ==============================
        $('#formManagePermission').on('submit', function(e) {
            e.preventDefault();

            let id = $('#perm_role_id').val();

            let selected = [];
            $('.permissionCheckbox:checked').each(function() {
                selected.push($(this).val());
            });

            $.ajax({
                url: "/authorization/roles/" + id + "/permissions/update",
                type: "POST",
                data: {
                    permissions: selected,
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    Swal.fire({
                        icon: "success",
                        title: "Permissions Updated",
                        timer: 1200
                    });
                    $('#modalManagePermission').modal('hide');
                    location.reload();
                }
            });
        });

        // ==============================
        // 7. ASSIGN ROLE TO USER
        // ==============================
        $('.assignRole').on('click', function() {

            let id = $(this).data('id');
            $('#assign_role_id').val(id);

            $('#modalAssignRole').modal('show');
        });

        $('#formAssignRole').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('auth.assign.role') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Role assigned to user',
                        timer: 1200
                    });

                    $('#modalAssignRole').modal('hide');
                    location.reload();
                }
            });
        });

        // ADD NEW PERMISSION


        $('#btnSavePermission').on('click', function() {
            $.ajax({
                url: "{{ route('authorization.permissions.store') }}",
                type: "POST",
                data: $('#formAddPermission').serialize(),
                success: function(res) {
                    $('#modalAddPermission').modal('hide');
                    location.reload();
                },
                error: function(err) {
                    alert(err.responseJSON.message ?? 'Gagal menyimpan');
                }
            });
        });



        // OPEN MANAGE PERMISSIONS MODAL
        $('.managePermission').click(function() {

            let roleId = $(this).data('id');

            $.ajax({
                url: "/authorization/role-permissions/" + roleId,
                type: "GET",
                success: function(res) {

                    $('#perm_role_id').val(res.role.id);
                    $('#perm_role_name').text(res.role.name);

                    let html = "";

                    res.permissions.forEach(p => {
                        let checked = res.selected.includes(p.name) ? "checked" : "";
                        html += `
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input permissionCheck" type="checkbox"
                                value="${p.name}" ${checked}>
                            <label class="form-check-label">${p.name}</label>
                        </div>
                    </div>
                `;
                    });

                    $('#permissionList').html(html);
                    $('#modalPermissions').modal('show');
                }
            });

        });

        // SAVE UPDATED PERMISSIONS
        $('#btnSavePermissions').click(function() {

            let roleId = $('#perm_role_id').val();
            let selectedPermissions = [];

            $('.permissionCheck:checked').each(function() {
                selectedPermissions.push($(this).val());
            });

            $.ajax({
                url: "/authorization/role-permissions/" + roleId,
                type: "POST",
                data: {
                    permissions: selectedPermissions,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success("Permissions updated successfully");
                        $('#modalPermissions').modal('hide');
                        location.reload();
                    }
                }
            });

        });

        $(document).on('click', '.removeUserFromRole', function(e) {
            e.preventDefault();

            let userId = $(this).data('user');
            let roleId = $(this).data('role');

            $.ajax({
                url: "{{ route('role.removeUser') }}",
                method: "POST",
                data: {
                    user_id: userId,
                    role_id: roleId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Removed!',
                        text: res.message,
                        timer: 1200,
                        showConfirmButton: false
                    });

                    location.reload(); // atau ajax refresh
                }
            });
        });



    });
</script>