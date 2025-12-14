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

@include('authorization.modals')
@include('authorization.scripts')