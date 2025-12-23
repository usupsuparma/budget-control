<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ri-settings-3-line me-2"></i>Daftar Threshold Approval
        </h5>
        <button class="btn btn-primary" onclick="showAddThresholdModal()">
            <i class="ri-add-line"></i> Tambah Threshold
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="thresholdTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Min Amount</th>
                        <th>Max Amount</th>
                        <th>Jumlah Level</th>
                        <th>Level yang Dibutuhkan</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="thresholdTableBody">
                    <tr>
                        <td colspan="8" class="text-center">
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
<div class="modal fade" id="thresholdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="thresholdModalTitle">Tambah Threshold</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="thresholdForm">
                <div class="modal-body">
                    <input type="hidden" id="threshold-id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="min_amount" class="form-label">Min Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="min_amount" name="min_amount" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="max_amount" class="form-label">Max Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_amount" name="max_amount" required min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Level yang Dibutuhkan <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input level-check" type="checkbox" value="1" id="level1">
                                    <label class="form-check-label" for="level1">
                                        <span class="level-badge level-1">1</span> Supervisor
                                    </label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input level-check" type="checkbox" value="2" id="level2">
                                    <label class="form-check-label" for="level2">
                                        <span class="level-badge level-2">2</span> Manager
                                    </label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input level-check" type="checkbox" value="3" id="level3">
                                    <label class="form-check-label" for="level3">
                                        <span class="level-badge level-3">3</span> Direktur
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input level-check" type="checkbox" value="4" id="level4">
                                    <label class="form-check-label" for="level4">
                                        <span class="level-badge level-4">4</span> CEO
                                    </label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input level-check" type="checkbox" value="5" id="level5">
                                    <label class="form-check-label" for="level5">
                                        <span class="level-badge level-5">5</span> Board
                                    </label>
                                </div>
                            </div>
                            <div class="col"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Contoh: Approval s/d Manager">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">Aktif</label>
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
