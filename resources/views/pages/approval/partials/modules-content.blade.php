{{-- Modules Tab Content --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="ri-apps-line me-2"></i>Approval Modules</h5>
        {{-- Disbale action buttons for modules --}}
        {{-- <button class="btn btn-primary btn-sm" onclick="showAddModuleModal()">
            <i class="ri-add-line me-1"></i> Tambah Module
        </button> --}}
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Daftar modul yang menggunakan sistem approval (contoh: transactions, bookings, invoices)</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="modulesTable">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Module Name</th>
                        <th>Table Name</th>
                        <th>Condition Field</th>
                        <th width="10%">Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="modulesTableBody">
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="ri-loader-4-line ri-spin"></i> Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Module Modal --}}
<div class="modal fade" id="moduleModal" tabindex="-1" aria-labelledby="moduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moduleModalTitle">Tambah Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="moduleForm">
                <div class="modal-body">
                    <input type="hidden" id="module-id">
                    
                    <div class="mb-3">
                        <label for="module_name" class="form-label">Module Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="module_name" name="module_name" required
                               placeholder="contoh: transactions, bookings, invoices">
                        <small class="text-muted">Nama unik untuk identifikasi modul</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="table_name" class="form-label">Table Name <span class="text-danger">*</span></label>
                        <select class="form-control" id="table_name" name="table_name" required>
                            <option value="">-- Pilih Table --</option>
                        </select>
                        <small class="text-muted">Pilih tabel database yang akan menggunakan approval</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="condition_field" class="form-label">Condition Field</label>
                        <input type="text" class="form-control" id="condition_field" name="condition_field" 
                               placeholder="contoh: amount, total, budget" readonly>
                        <small class="text-muted">Field yang digunakan untuk threshold (opsional)</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="module_is_active" checked>
                            <label class="form-check-label" for="module_is_active">Active</label>
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
