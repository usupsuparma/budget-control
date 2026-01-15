{{-- Templates Tab Content --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="ri-flow-chart me-2"></i>Flow Templates</h5>
        <button class="btn btn-primary btn-sm" onclick="showAddTemplateModal()">
            <i class="ri-add-line me-1"></i> Tambah Template
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Aturan approval per modul: pakai uppline chain, threshold, atau keduanya</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="templatesTable">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Template Name</th>
                        <th>Module</th>
                        <th width="10%">Uppline Chain</th>
                        <th width="10%">Use Threshold</th>
                        <th>Condition Field</th>
                        <th width="8%">Priority</th>
                        <th width="8%">Status</th>
                        <th width="12%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="templatesTableBody">
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            <i class="ri-loader-4-line ri-spin"></i> Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Template Modal --}}
<div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalTitle">Tambah Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="templateForm">
                <div class="modal-body">
                    <input type="hidden" id="template-id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="template_module_id" class="form-label">Module <span class="text-danger">*</span></label>
                                <select class="form-select" id="template_module_id" name="module_id" required>
                                    <option value="">Pilih Module</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="template_name" class="form-label">Template Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="template_name" name="template_name" required
                                       placeholder="contoh: Invoice Approval, Booking Approval">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Condition Field</label>
                                <div class="form-control bg-light" id="display_condition_field" style="cursor: not-allowed;">
                                    <span class="text-muted">Pilih module terlebih dahulu</span>
                                </div>
                                <small class="text-muted">Field diambil dari module yang dipilih</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="template_priority" class="form-label">Priority</label>
                                <input type="number" class="form-control" id="template_priority" name="priority" 
                                       value="1" min="1">
                                <small class="text-muted">Prioritas template (1 = tertinggi)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="use_uppline_chain">
                                    <label class="form-check-label" for="use_uppline_chain">
                                        <strong>Use Uppline Chain</strong>
                                        <br><small class="text-muted">Approval dari atasan langsung</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="use_threshold">
                                    <label class="form-check-label" for="use_threshold">
                                        <strong>Use Threshold</strong>
                                        <br><small class="text-muted">Filter berdasarkan nominal</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="template_is_active" checked>
                                    <label class="form-check-label" for="template_is_active">
                                        <strong>Active</strong>
                                        <br><small class="text-muted">Template aktif</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info small">
                        <i class="ri-information-line me-1"></i>
                        <strong>Kombinasi:</strong>
                        <ul class="mb-0 mt-1">
                            <li>Uppline + Threshold: Uppline Chain → Master Flow (threshold-filtered)</li>
                            <li>Uppline only: Uppline Chain → Master Flow (all levels)</li>
                            <li>Threshold only: Master Flow saja (threshold-filtered)</li>
                            <li>None: Master Flow saja (all levels)</li>
                        </ul>
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
