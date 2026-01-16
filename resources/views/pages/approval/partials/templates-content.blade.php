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
                                <label for="template_module_id" class="form-label">
                                    Module <span class="text-danger">*</span>
                                    <small class="text-muted d-block" style="font-weight: normal;">
                                        <i class="ri-information-line"></i> Module tidak bisa diubah setelah template dibuat
                                    </small>
                                </label>
                                <select class="form-select" id="template_module_id" name="module_id" required>
                                    <option value="">Pilih Module</option>
                                </select>
                                <small class="text-muted">Hanya module yang belum memiliki template yang ditampilkan</small>
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
                                <div class="form-control" id="display_condition_field" style="background-color: #f8f9fa;">
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

                    {{-- Uppline Chain Configuration Section --}}
                    <div id="upplineConfigSection" style="display: none;">
                        <hr class="my-4">
                        <h6 class="mb-3">
                            <i class="ri-link me-1"></i> Uppline Chain Configuration
                            <small class="text-muted d-block mt-1" style="font-weight: normal;">
                                Tentukan job level yang diperlukan untuk approval chain. Kosongkan Division untuk konfigurasi default (berlaku untuk semua divisi).
                            </small>
                        </h6>
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="showAddUpplineConfigModal()">
                                <i class="ri-add-line me-1"></i> Add Level Configuration
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">Step</th>
                                        <th width="30%">Division</th>
                                        <th width="45%">Job Level Name</th>
                                        <th width="20%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="upplineConfigsTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            <small>No configurations yet. Click "Add Level Configuration" to start.</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning small mt-2">
                            <i class="ri-information-line me-1"></i>
                            <strong>Catatan:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Step sequence menentukan urutan approval (1 = pertama)</li>
                                <li>Jika Division kosong = konfigurasi default (berlaku untuk semua divisi yang tidak memiliki konfigurasi khusus)</li>
                                <li>Jika Division terisi = konfigurasi khusus untuk divisi tersebut (override default)</li>
                                <li>Job Level: <strong>Section → Department → Division → Director</strong> (dari bawah ke atas)</li>
                            </ul>
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

{{-- Uppline Config Modal --}}
<div class="modal fade" id="upplineConfigModal" tabindex="-1" aria-labelledby="upplineConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="upplineConfigModalTitle">Add Level Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="upplineConfigForm">
                <div class="modal-body">
                    <input type="hidden" id="upplineconfig-id">
                    <input type="hidden" id="upplineconfig-template-id">
                    
                    <div class="mb-3">
                        <label for="upplineconfig_division_id" class="form-label">
                            Division
                            <small class="text-muted">(Optional - Leave empty for default)</small>
                        </label>
                        <select class="form-select" id="upplineconfig_division_id" name="division_id">
                            <option value="">Default (All Divisions)</option>
                        </select>
                        <small class="text-muted">Pilih divisi spesifik atau kosongkan untuk konfigurasi default</small>
                    </div>

                    <div class="mb-3">
                        <label for="upplineconfig_step_sequence" class="form-label">
                            Step Sequence <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="upplineconfig_step_sequence" 
                               name="step_sequence" required min="1" value="1">
                        <small class="text-muted">Urutan approval (1 = pertama, 2 = kedua, dst.)</small>
                    </div>

                    <div class="mb-3">
                        <label for="upplineconfig_job_level_name" class="form-label">
                            Job Level Name <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="upplineconfig_job_level_name" 
                                name="job_level_name" required>
                            <option value="">Pilih Job Level</option>
                            <option value="Director">Director</option>
                            <option value="Division">Division</option>
                            <option value="Department">Department</option>
                            <option value="Section">Section</option>
                        </select>
                        <small class="text-muted">Pilih level jabatan yang diperlukan untuk approval</small>
                    </div>

                    <div class="alert alert-info small">
                        <i class="ri-lightbulb-line me-1"></i>
                        <strong>Contoh Konfigurasi:</strong>
                        <ul class="mb-0 mt-1">
                            <li><strong>IT Division (khusus):</strong> Step 1: Section, Step 2: Department, Step 3: Division, Step 4: Director</li>
                            <li><strong>Default (umum):</strong> Step 1: Section, Step 2: Department, Step 3: Division</li>
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
