<script>
    $(document).ready(function() {

        // ============================================================
        // INITIALIZE DATATABLE
        // ============================================================
        var table = $('#roleTable').DataTable();


        // ============================================================
        // ADD ROLE MODAL — OPEN
        // ============================================================
        $('#modalAddRole').on('show.bs.modal', function() {
            $('#role_id').val('');
            $('#role_name').val('');
            $('.modal-title', this).text('Add Role');
        });

        // ============================================================
        // EDIT ROLE — OPEN MODAL
        // ============================================================
        $('.editRole').click(function() {
            let id = $(this).data('id');
            let name = $(this).data('name');

            $('#role_id').val(id);
            $('#role_name').val(name);

            $('#modalAddRole .modal-title').text('Edit Role');
            $('#modalAddRole').modal('show');
        });


        // ============================================================
        // SAVE ROLE (Add / Edit)
        // ============================================================
        $('#btnSaveRole').click(function() {

            let id = $('#role_id').val();
            let name = $('#role_name').val();

            if (name.trim() === '') {
                alert('Role name cannot be empty!');
                return;
            }

            let url = id === '' ?
                "{{ route('auth.roles.store') }}" :
                "{{ url('authorization/roles/update') }}/" + id;

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    name: name,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    $('#modalAddRole').modal('hide');
                    location.reload(); // reload table
                }
            });
        });



        // ============================================================
        // DELETE ROLE
        // ============================================================
        $('.deleteRole').click(function() {
            let id = $(this).data('id');

            if (!confirm("Delete this role?")) return;

            $.ajax({
                url: "{{ url('authorization/roles/delete') }}/" + id,
                method: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    location.reload();
                }
            });
        });



        // ============================================================
        // MANAGE PERMISSIONS — OPEN MODAL
        // ============================================================
        $('.managePermission').click(function() {

            let role_id = $(this).data('id');

            $('#perm_role_id').val(role_id);

            $.ajax({
                url: "{{ url('authorization/roles') }}/" + role_id + "/permissions",
                method: "GET",
                success: function(res) {

                    $('#perm_role_name').text(res.role.name);

                    let html = "";
                    let allPermissions = res.permissions;
                    let selected = res.selected;

                    allPermissions.forEach(function(p) {
                        html += `
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input permCheck" type="checkbox" 
                                       value="${p.name}"
                                       ${selected.includes(p.name) ? 'checked' : ''}>
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



        // ============================================================
        // SAVE PERMISSIONS FOR ROLE
        // ============================================================
        $('#btnSavePermissions').click(function() {

            let role_id = $('#perm_role_id').val();

            let selectedPermissions = [];
            $('.permCheck:checked').each(function() {
                selectedPermissions.push($(this).val());
            });

            $.ajax({
                url: "{{ url('authorization/roles') }}/" + role_id + "/permissions/update",
                method: "POST",
                data: {
                    permissions: selectedPermissions,
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    $('#modalPermissions').modal('hide');
                    location.reload();
                }
            });
        });



        // ============================================================
        // ASSIGN ROLE TO USER — OPEN MODAL
        // ============================================================
        $('.assignRole').click(function() {
            $('#assign_user_id').val('');
            $('#assign_user_select').val('');
            $('#assign_role_select').val('');

            $('#modalAssignRole').modal('show');
        });



        // ============================================================
        // SAVE ASSIGN ROLE TO USER
        // ============================================================
        $('#btnSaveAssignRole').click(function() {

            let user_id = $('#assign_user_select').val();
            let role_name = $('#assign_role_select').val();

            if (!user_id || !role_name) {
                alert("Please select user and role.");
                return;
            }

            $.ajax({
                url: "{{ route('auth.assign.role') }}",
                method: 'POST',
                data: {
                    user_id: user_id,
                    role: role_name,
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    $('#modalAssignRole').modal('hide');
                    location.reload();
                }
            });
        });

    });
</script>