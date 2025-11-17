<!-- ADD PERMISSION -->
<div class="modal fade" id="modalAddPermission" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Permission</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="permissionCreateForm">
                    @csrf

                    <label class="form-label">Module Name</label>
                    <input type="text" class="form-control" id="moduleName" placeholder="ex: employee">

                    <small class="text-muted">Generator akan membuat: module.view, module.create, module.edit, module.delete</small>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnCreatePermission">Generate Permissions</button>
            </div>

        </div>
    </div>
</div>


<!-- EDIT PERMISSION -->
<div class="modal fade" id="modalEditPermission" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Permission</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="permissionEditForm">
                    @csrf
                    <input type="hidden" id="editPermissionId">

                    <label class="form-label">Permission Name</label>
                    <input type="text" class="form-control" id="editPermissionName">
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnUpdatePermission">Update</button>
            </div>

        </div>
    </div>
</div>


<!-- DELETE CONFIRMATION -->
<div class="modal fade" id="modalDeletePermission" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-body text-center">
                <p class="fw-bold mb-2">Delete this permission?</p>
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" id="btnDeletePermission">Delete</button>
            </div>

        </div>
    </div>
</div>