<!-- ADD PERMISSION -->
<div class="modal fade" id="modalAddPermission" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add New Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formAddPermission">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Modul / Menu</label>
                        <select name="modul_menu" id="modul_menu" class="form-select" required>
                            <option value="">-- Pilih Modul --</option>
                            @foreach($moduls as $m)
                            <option value="{{ $m->id }}">
                                {{ $m->modul_name }} - {{ $m->menu_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Permission Name</label>
                        <input type="text" name="name" id="permission_name"
                            class="form-control" placeholder="ex: Menu Create" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Route</label>
                        <input type="text" name="modul_menu_name" id="modul_menu_name"
                            class="form-control" placeholder="ex: asset.view" required>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" id="btnSavePermission">Save</button>
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