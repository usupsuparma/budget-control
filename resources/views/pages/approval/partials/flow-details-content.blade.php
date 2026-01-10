{{-- Flow Details Tab Content --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0"><i class="ri-user-settings-line me-2"></i>Flow Details (Approvers)</h5>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-end gap-2">
                    <select class="form-select form-select-sm" id="flowDetailsTemplateSelect" style="width: 250px;">
                        <option value="">-- Pilih Template --</option>
                    </select>
                    <button class="btn btn-primary btn-sm" onclick="showAddFlowDetailModal()" id="btnAddFlowDetail" disabled>
                        <i class="ri-add-line me-1"></i> Tambah Approver
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Daftar approver untuk master flow. Pilih template terlebih dahulu untuk melihat dan mengelola approvers.</p>
        
        <div id="flowDetailsPlaceholder" class="text-center py-5">
            <i class="ri-file-list-3-line" style="font-size: 4rem; opacity: 0.3;"></i>
            <p class="text-muted mt-3">Pilih template untuk menampilkan flow details</p>
        </div>
        
        <div class="table-responsive" id="flowDetailsTableContainer" style="display: none;">
            <table class="table table-hover align-middle" id="flowDetailsTable">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Level</th>
                        <th>Employee (Approver)</th>
                        <th>Threshold Amount</th>
                        <th width="10%">Required</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="flowDetailsTableBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Flow Detail Modal --}}
<div class="modal fade" id="flowDetailModal" tabindex="-1" aria-labelledby="flowDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="flowDetailModalTitle">Tambah Approver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="flowDetailForm">
                <div class="modal-body">
                    <input type="hidden" id="flowdetail-id">
                    <input type="hidden" id="flowdetail_template_id">
                    
                    <div class="mb-3">
                        <label for="level_sequence" class="form-label">Level Sequence <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="level_sequence" name="level_sequence" 
                               required min="1" value="1">
                        <small class="text-muted">Urutan approval: 1, 2, 3, dst.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="employment_id" class="form-label">Employee (Approver) <span class="text-danger">*</span></label>
                        <select class="form-select" id="employment_id" name="employment_id" required>
                            <option value="">Pilih Employee</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="threshold_amount" class="form-label">Threshold Amount</label>
                        <input type="text" class="form-control currency-input" id="threshold_amount" name="threshold_amount" 
                               inputmode="numeric" placeholder="contoh: 100.000.000">
                        <small class="text-muted">Batas nominal untuk approver ini (kosongkan jika tidak ada batas)</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_required" checked>
                            <label class="form-check-label" for="is_required">Required (Wajib Approve)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
