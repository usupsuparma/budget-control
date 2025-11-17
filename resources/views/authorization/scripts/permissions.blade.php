<script>
    $(document).ready(function() {

        /* ==========================
            CREATE (Generate 4 permissions)
        ========================== */
        $('#btnCreatePermission').click(function() {
            let module = $('#moduleName').val().trim();

            if (!module) {
                alert('Module name is required');
                return;
            }

            $.post("{{ route('auth.permissions.store') }}", {
                _token: "{{ csrf_token() }}",
                module: module
            }, function() {
                location.reload();
            });
        });



        /* ==========================
            OPEN EDIT MODAL
        ========================== */
        $('.editPermission').click(function() {
            $('#editPermissionId').val($(this).data('id'));
            $('#editPermissionName').val($(this).data('name'));
            $('#modalEditPermission').modal('show');
        });


        /* ==========================
            UPDATE PERMISSION
        ========================== */
        $('#btnUpdatePermission').click(function() {

            $.post("{{ url('/authorization/permissions/update') }}/" + $('#editPermissionId').val(), {
                _token: "{{ csrf_token() }}",
                name: $('#editPermissionName').val()
            }, function() {
                location.reload();
            });
        });



        /* ==========================
            DELETE
        ========================== */
        let deleteId = null;

        $('.deletePermission').click(function() {
            deleteId = $(this).data('id');
            $('#modalDeletePermission').modal('show');
        });

        $('#btnDeletePermission').click(function() {
            $.ajax({
                url: "{{ url('/authorization/permissions/delete') }}/" + deleteId,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    location.reload();
                }
            });
        });

    });
</script>