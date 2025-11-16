<!-- ============================================================
     MODAL 1 — ADD / EDIT ROLE
=============================================================== -->
<div class="modal fade" id="modalAddRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formRole">
                    @csrf
                    <input type="hidden" id="role_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Name</label>
                        <input type="text" class="form-control" id="role_name" placeholder="Enter role name" required>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btnSaveRole">Save</button>
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