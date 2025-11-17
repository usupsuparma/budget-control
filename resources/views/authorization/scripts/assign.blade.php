<script>
    $(document).ready(function() {

        /* ===========================================
            ASSIGN PERMISSION TO ROLE
        =========================================== */

        $('.managePermission').click(function() {
            let roleId = $(this).data('id');
            $('#permRoleId').val(roleId);

            $('#permissionList').html('<p class="text-center">Loading...</p>');

            $.get("{{ url('/authorization/roles') }}/" + roleId + "/permissions", function(data) {

                let html = '';
                $.each(data.modules, function(module, permissions) {

                    html += `
                    <div class="mb-3 p-2 border rounded bg-light">
                        <h6 class="fw-bold text-primary">${module.toUpperCase()}</h6>
                `;

                    $.each(permissions, function(index, perm) {
                        html += `
                        <div class="form-check">
                            <input class="form-check-input permCheck"
                                type="checkbox"
                                value="${perm.name}"
                                ${perm.assigned ? 'checked' : ''}>

                            <label class="form-check-label">
                                ${perm.name}
                            </label>
                        </div>
                    `;
                    });

                    html += `</div>`;
                });

                $('#permissionList').html(html);
                $('#modalAssignPermission').modal('show');
            });
        });

        $('#btnSavePermissions').click(function() {

            let roleId = $('#permRoleId').val();
            let selected = [];

            $('.permCheck:checked').each(function() {
                selected.push($(this).val());
            });

            $.post("{{ url('/authorization/roles') }}/" + roleId + "/permissions/update", {
                _token: "{{ csrf_token() }}",
                permissions: selected
            }, function() {
                location.reload();
            });
        });




        /* ===========================================
            ASSIGN ROLE TO USER
        =========================================== */

        $('.assignRole').click(function() {
            let userId = $(this).data('id');

            $.get("{{ url('/authorization/assign-role') }}/" + userId, function(data) {

                $('#userAssignId').val(data.user.id);
                $('#userAssignName').val(data.user.email);

                $('.roleCheck').prop('checked', false);

                $('input.roleCheck[value="' + data.currentRole + '"]').prop('checked', true);

                $('#modalAssignRole').modal('show');
            });
        });

        $('#btnAssignRoleSave').click(function() {

            let userId = $('#userAssignId').val();
            let roleName = $('input.roleCheck:checked').val();

            $.post("{{ route('auth.assign.role') }}", {
                _token: "{{ csrf_token() }}",
                user_id: userId,
                role_name: roleName
            }, function() {
                location.reload();
            });
        });

    });
</script>