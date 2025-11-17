<!-- ASSIGN PERMISSIONS TO ROLE -->
<div class="modal fade" id="modalAssignPermission" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Manage Permissions</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="permRoleId">

                <div id="permissionList">
                    <!-- Dynamic permissions appear here via AJAX -->
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnSavePermissions">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- ASSIGN ROLE TO USER -->
<div class="modal fade" id="modalAssignRole" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Assign Role to User</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="assignRoleForm">
                    @csrf

                    <input type="hidden" id="userAssignId">

                    <label class="form-label">User</label>
                    <input class="form-control mb-3" id="userAssignName" readonly>

                    <label class="form-label">Select Role</label>
                    <div id="roleList">
                        @foreach($roles as $role)
                        <div class="form-check mb-1">
                            <input class="form-check-input roleCheck"
                                type="radio"
                                name="roleSelect"
                                value="{{ $role->name }}">
                            <label class="form-check-label">
                                {{ $role->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btnAssignRoleSave">Assign Role</button>
            </div>

        </div>
    </div>
</div>