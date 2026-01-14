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
                            <th width="20%">Role Name</th>
                            <th width="30%">Karyawan</th>
                            <th width="20%">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>

                            <td class="fw-semibold">{{ $role->name }}</td>

                            <!-- Karyawan yg punya role -->
                            <td>
                                @php
                                $users = $role->users;
                                @endphp

                                @if($users->count() == 0)
                                <span class="text-muted small">Tidak ada karyawan</span>
                                @else
                                @foreach($users as $u)
                                <span class="badge bg-info text-dark me-1 mb-1 d-inline-flex align-items-center">
                                    {{ $u->first_name }} {{ $u->last_name }}
                                    <button
                                        class="btn btn-sm btn-link text-danger ms-1 p-0 removeUserFromRole"
                                        data-user="{{ $u->id }}"
                                        data-role="{{ $role->id }}"
                                        title="Remove user">
                                        <i class="bi bi-x-lg" style="font-size: 10px;"></i>
                                    </button>
                                </span>
                                @endforeach
                                @endif
                            </td>




                            <td>
                                <div class="btn-group">

                                    <!-- Manage Permission -->
                                    <button class="btn btn-sm btn-light-secondary managePermission"
                                        data-id="{{ $role->id }}">
                                        <i class="bi bi-list-check"></i>
                                    </button>

                                    <!-- Edit Role -->
                                    <button class="btn btn-sm btn-light-primary editRole"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->name }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Delete -->
                                    @if($role->name != 'Super Admin')
                                    <button class="btn btn-sm btn-light-danger deleteRole"
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

<!-- ============================================================
     MODAL 3 — ASSIGN ROLE TO USER
=============================================================== -->
<div class="modal fade" id="modalAssignRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Assign Role to User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="formAssignRole">
                    @csrf

                    <input type="hidden" id="assign_user_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select User</label>
                        <select class="form-select" id="assign_user_select" required>
                            <option value="">-- Choose User --</option>
                            @foreach(App\Models\Employee::all() as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->email }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Role</label>
                        <select class="form-select" id="assign_role_select" required>
                            <option value="">-- Choose Role --</option>
                            @foreach(Spatie\Permission\Models\Role::all() as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btnSaveAssignRole">Assign Role</button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
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
                url: "{{ route('authorization.permissions.create') }}",
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

@endpush