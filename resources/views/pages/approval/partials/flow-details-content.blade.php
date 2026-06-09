{{-- Flow Details Tab Content --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="ri-user-settings-line me-2"></i>Flow Details (Approvers)</h5>
                <p class="text-muted small mb-0">Daftar template approval dengan approver masing-masing. Klik template
                    atau LPJ Master Approvers untuk melihat detail approver.</p>
            </div>
        </div>
    </div>
    <div class="card-body">
        {{-- Placeholder saat tidak ada template --}}
        <div id="noTemplatesPlaceholder" class="text-center py-5" style="display: none;">
            <i class="ri-inbox-line" style="font-size: 4rem; opacity: 0.3;"></i>
            <p class="text-muted mt-3 mb-0">Tidak ada template approval. Silahkan buat template terlebih dahulu di tab
                Templates.</p>
        </div>

        {{-- Accordion untuk menampilkan templates --}}
        <div id="templatesAccordion" class="accordion">
            {{-- Templates akan di-render di sini oleh JavaScript --}}
        </div>
    </div>
</div>

{{-- Flow Detail Modal (untuk tambah/edit approver) --}}
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
                        <label for="level_sequence" class="form-label">Level Sequence <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="level_sequence" name="level_sequence" required
                            min="1" value="1">
                        <small class="text-muted">Urutan approval: 1, 2, 3, dst.</small>
                    </div>

                    <div class="mb-3">
                        <label for="employment_id" class="form-label">Employee (Approver) <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="employment_id" name="employment_id" required>
                            <option value="">Pilih Employee</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="threshold_amount" class="form-label">Threshold Amount</label>
                        <input type="text" class="form-control currency-input" id="threshold_amount"
                            name="threshold_amount" inputmode="numeric" placeholder="contoh: 100.000.000">
                        <small class="text-muted">Batas nominal untuk approver ini (kosongkan jika tidak ada
                            batas)</small>
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

{{-- LPJ Approver Modal (untuk tambah/edit approver LPJ) --}}
<div class="modal fade" id="lpjApproverModal" tabindex="-1" aria-labelledby="lpjApproverModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lpjApproverModalTitle">Tambah LPJ Approver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="lpjApproverForm">
                <div class="modal-body">
                    <input type="hidden" id="lpjapprover-id">

                    <div class="mb-3">
                        <label for="lpj_approval_sequence" class="form-label">Approval Sequence <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="lpj_approval_sequence" name="approval_sequence" required
                            min="1" value="1">
                        <small class="text-muted">Urutan approval: 1, 2, 3, dst.</small>
                    </div>

                    <div class="mb-3" id="lpjEmploymentSelectDiv">
                        <label for="lpj_employment_id" class="form-label">Employee (Approver) <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="lpj_employment_id" name="employment_id" required>
                            <option value="">Pilih Employee</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="lpj_is_active" checked>
                            <label class="form-check-label" for="lpj_is_active">Active</label>
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
