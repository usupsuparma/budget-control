<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Pending Approval</h6>
                        <h3 class="mb-0" id="stat-pending">0</h3>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="ri-time-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">In Progress</h6>
                        <h3 class="mb-0" id="stat-in-progress">0</h3>
                    </div>
                    <div class="stat-icon text-info">
                        <i class="ri-loader-4-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Approved</h6>
                        <h3 class="mb-0" id="stat-approved">0</h3>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="ri-checkbox-circle-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Rejected</h6>
                        <h3 class="mb-0" id="stat-rejected">0</h3>
                    </div>
                    <div class="stat-icon text-danger">
                        <i class="ri-close-circle-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Approvals Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ri-file-list-3-line me-2"></i>Pending Approvals
        </h5>
        <button class="btn btn-sm btn-outline-primary" onclick="loadPendingApprovals()">
            <i class="ri-refresh-line"></i> Refresh
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="pendingTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Pengaju</th>
                        <th>Keperluan</th>
                        <th>Estimasi</th>
                        <th>Urgensi</th>
                        <th>Level</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="pendingTableBody">
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

<!-- Approval Detail Modal -->
<div class="modal fade" id="approvalDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-file-text-line me-2"></i>Detail Pengajuan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Transaction Info -->
                    <div class="col-md-8">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Informasi Transaksi</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Tanggal Pengajuan</label>
                                        <p class="mb-0 fw-semibold" id="approval-transaction-date">-</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Pengaju</label>
                                        <p class="mb-0 fw-semibold" id="approval-user">-</p>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="text-muted small">Keperluan</label>
                                        <p class="mb-0 fw-semibold" id="approval-purpose">-</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Estimasi Nilai</label>
                                        <p class="mb-0 fw-semibold text-primary" id="approval-amount">-</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Urgensi</label>
                                        <p class="mb-0" id="approval-urgency">-</p>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="text-muted small">Deskripsi</label>
                                        <p class="mb-0" id="approval-description">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Approval Form -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Form Approval</h6>
                            </div>
                            <div class="card-body">
                                <input type="hidden" id="approval-id">
                                <div class="mb-3">
                                    <label for="approval-comments" class="form-label">Komentar</label>
                                    <textarea class="form-control" id="approval-comments" rows="3" placeholder="Masukkan komentar (opsional untuk approve, wajib untuk reject)"></textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success flex-fill" onclick="processApproval('approve')">
                                        <i class="ri-checkbox-circle-line me-1"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-danger flex-fill" onclick="processApproval('reject')">
                                        <i class="ri-close-circle-line me-1"></i> Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Approval Timeline -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Approval Timeline</h6>
                            </div>
                            <div class="card-body">
                                <div class="approval-timeline" id="approval-timeline">
                                    <!-- Timeline items will be inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
