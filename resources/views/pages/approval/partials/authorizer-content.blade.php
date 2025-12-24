<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ri-user-settings-line me-2"></i>Daftar Authorizer
        </h5>
        <button class="btn btn-primary" onclick="showAddAuthorizerModal()">
            <i class="ri-add-line"></i> Tambah Authorizer
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="authorizerTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Employee</th>
                        <th>Level</th>
                        <th>Position Code</th>
                        <th>Max Amount</th>
                        <th>Override</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="authorizerTableBody">
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Level Info Card -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="ri-information-line me-2"></i>Keterangan Level Approval</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 col-4 mb-2">
                <span class="level-badge level-1 me-2">1</span> Supervisor
            </div>
            <div class="col-md-2 col-4 mb-2">
                <span class="level-badge level-2 me-2">2</span> Manager
            </div>
            <div class="col-md-2 col-4 mb-2">
                <span class="level-badge level-3 me-2">3</span> Direktur
            </div>
            <div class="col-md-2 col-4 mb-2">
                <span class="level-badge level-4 me-2">4</span> CEO
            </div>
            <div class="col-md-2 col-4 mb-2">
                <span class="level-badge level-5 me-2">5</span> Board
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="authorizerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authorizerModalTitle">Tambah Authorizer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="authorizerForm">
                <div class="modal-body">
                    <input type="hidden" id="authorizer-id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option value="">Pilih Employee</option>
                                @php
                                    $employees = \App\Models\Employee::orderBy('first_name')->get();
                                @endphp
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" data-name="{{ $employee->first_name }} {{ $employee->last_name }}">
                                        {{ $employee->first_name }} {{ $employee->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="authorizer_name" class="form-label">Nama Authorizer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="authorizer_name" name="authorizer_name" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="level_number" class="form-label">Level Number <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="level_number" name="level_number" required min="1" max="10">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="approval_level" class="form-label">Approval Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="approval_level" name="approval_level" required>
                                <option value="">Pilih Level</option>
                                <option value="1">Level 1 - Supervisor</option>
                                <option value="2">Level 2 - Manager</option>
                                <option value="3">Level 3 - Direktur</option>
                                <option value="4">Level 4 - CEO</option>
                                <option value="5">Level 5 - Board</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="priority_order" class="form-label">Priority Order</label>
                            <input type="number" class="form-control" id="priority_order" name="priority_order" min="1" value="1">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="position_code" class="form-label">Position Code</label>
                            <input type="text" class="form-control" id="position_code" name="position_code" placeholder="Contoh: MGR-FIN">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="authority" class="form-label">Authority</label>
                            <input type="text" class="form-control" id="authority" name="authority" placeholder="Contoh: Finance">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_approval_amount" class="form-label">Max Approval Amount</label>
                            <input type="number" class="form-control" id="max_approval_amount" name="max_approval_amount" min="0" placeholder="Kosongkan jika unlimited">
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch me-4">
                                <input class="form-check-input" type="checkbox" id="can_override" name="can_override">
                                <label class="form-check-label" for="can_override">Can Override</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                <label class="form-check-label" for="status">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
