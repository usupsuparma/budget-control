@extends('layouts.master')

@section('title', 'Approval Dashboard | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Approval Dashboard')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    .table-responsive {
        min-height: 150px;
    }
    .badge-status {
        padding: 0.35rem 0.65rem;
        font-size: 0.75rem;
    }
    .approval-timeline {
        position: relative;
        padding-left: 30px;
    }
    .approval-timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    .approval-item {
        position: relative;
        margin-bottom: 20px;
    }
    .approval-item::before {
        content: '';
        position: absolute;
        left: -22px;
        top: 5px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #6c757d;
        border: 2px solid #fff;
    }
    .approval-item.approved::before {
        background: #198754;
    }
    .approval-item.rejected::before {
        background: #dc3545;
    }
    .approval-item.pending::before {
        background: #ffc107;
    }
    .approval-item.current::before {
        background: #0d6efd;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
        100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }
    .progress-approval {
        height: 8px;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Pending Approval</h6>
                                <h3 class="mb-0" id="stat-pending">{{ $stats['pending_for_user'] ?? 0 }}</h3>
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
                                <h3 class="mb-0" id="stat-inprogress">{{ $stats['in_progress'] ?? 0 }}</h3>
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
                                <h3 class="mb-0" id="stat-approved">{{ $stats['approved'] ?? 0 }}</h3>
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
                                <h3 class="mb-0" id="stat-rejected">{{ $stats['rejected'] ?? 0 }}</h3>
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
                                        <p class="mb-0 fw-semibold" id="detail-date"></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Pengaju</label>
                                        <p class="mb-0 fw-semibold" id="detail-user"></p>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="text-muted small">Keperluan</label>
                                        <p class="mb-0 fw-semibold" id="detail-purpose"></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Estimasi Nilai</label>
                                        <p class="mb-0 fw-semibold text-primary" id="detail-amount"></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Urgensi</label>
                                        <p class="mb-0" id="detail-urgency"></p>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="mb-3">
                                    <label class="text-muted small">Progress Approval</label>
                                    <div class="progress progress-approval mt-2">
                                        <div class="progress-bar bg-success" role="progressbar" id="detail-progress" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted" id="detail-progress-text">0/0 Level</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Approval Form -->
                        <div class="card" id="approval-form-card">
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
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    loadPendingApprovals();
    loadStatistics();
});

// Load pending approvals
function loadPendingApprovals() {
    $.ajax({
        url: '{{ route("approval.pending") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderPendingTable(response.data);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            showAlert('Gagal memuat data pending approvals', 'error');
        }
    });
}

// Render pending table
function renderPendingTable(data) {
    const tbody = $('#pendingTableBody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center text-muted">
                    <i class="ri-checkbox-circle-line fs-1 d-block mb-2"></i>
                    Tidak ada pending approval
                </td>
            </tr>
        `);
        return;
    }
    
    data.forEach((item, index) => {
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td>${formatDate(item.transaction_date)}</td>
                <td>${item.user_name}</td>
                <td>${item.purpose}</td>
                <td class="text-end">${formatCurrency(item.estimated_amount)}</td>
                <td>${getUrgencyBadge(item.urgency)}</td>
                <td><span class="badge bg-info">Level ${item.approval_level}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="showApprovalDetail(${item.id}, ${item.transaction_id})">
                        <i class="ri-eye-line me-1"></i> Review
                    </button>
                </td>
            </tr>
        `);
    });
}

// Load statistics
function loadStatistics() {
    $.ajax({
        url: '{{ route("approval.statistics") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#stat-pending').text(response.data.pending_for_user || 0);
                $('#stat-inprogress').text(response.data.in_progress || 0);
                $('#stat-approved').text(response.data.approved || 0);
                $('#stat-rejected').text(response.data.rejected || 0);
            }
        }
    });
}

// Show approval detail
function showApprovalDetail(approvalId, transactionId) {
    $.ajax({
        url: `{{ url('approval/transaction') }}/${transactionId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Fill transaction info
                $('#detail-date').text(formatDate(data.transaction_date));
                $('#detail-user').text(data.user_name);
                $('#detail-purpose').text(data.purpose);
                $('#detail-amount').text(formatCurrency(data.estimated_amount));
                $('#detail-urgency').html(getUrgencyBadge(data.urgency));
                
                // Progress
                const progress = data.required_approval_levels > 0 
                    ? Math.round((data.current_approval_level / data.required_approval_levels) * 100)
                    : 0;
                $('#detail-progress').css('width', progress + '%');
                $('#detail-progress-text').text(`${data.current_approval_level}/${data.required_approval_levels} Level`);
                
                // Timeline
                renderApprovalTimeline(data.approvals, data.current_approval_level);
                
                // Set approval ID
                $('#approval-id').val(approvalId);
                
                // Show modal
                $('#approvalDetailModal').modal('show');
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            showAlert('Gagal memuat detail transaksi', 'error');
        }
    });
}

// Render approval timeline
function renderApprovalTimeline(approvals, currentLevel) {
    const timeline = $('#approval-timeline');
    timeline.empty();
    
    if (!approvals || approvals.length === 0) {
        timeline.append('<p class="text-muted">Tidak ada data approval</p>');
        return;
    }
    
    approvals.forEach((approval, index) => {
        let statusClass = 'pending';
        let statusText = 'Menunggu';
        let statusIcon = 'ri-time-line';
        
        if (approval.status === 1) {
            statusClass = 'approved';
            statusText = 'Disetujui';
            statusIcon = 'ri-checkbox-circle-line text-success';
        } else if (approval.status === 2) {
            statusClass = 'rejected';
            statusText = 'Ditolak';
            statusIcon = 'ri-close-circle-line text-danger';
        } else if (approval.status === 3) {
            statusClass = '';
            statusText = 'Dilewati';
            statusIcon = 'ri-skip-forward-line text-secondary';
        } else if (approval.sequence_order === currentLevel + 1) {
            statusClass = 'current';
            statusText = 'Menunggu Review';
            statusIcon = 'ri-loader-4-line text-primary';
        }
        
        timeline.append(`
            <div class="approval-item ${statusClass}">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>Level ${approval.approval_level}</strong>
                        <p class="mb-1 text-muted small">${approval.approver_name}</p>
                        <span class="badge bg-${getStatusBadgeColor(approval.status)}">
                            <i class="${statusIcon} me-1"></i>${statusText}
                        </span>
                    </div>
                </div>
                ${approval.approved_at ? `<small class="text-muted">${formatDateTime(approval.approved_at)}</small>` : ''}
                ${approval.comments ? `<p class="mt-2 mb-0 small fst-italic">"${approval.comments}"</p>` : ''}
            </div>
        `);
    });
}

// Process approval
function processApproval(action) {
    const approvalId = $('#approval-id').val();
    const comments = $('#approval-comments').val();
    
    if (action === 'reject' && !comments.trim()) {
        showAlert('Komentar wajib diisi untuk reject', 'warning');
        return;
    }
    
    const actionText = action === 'approve' ? 'menyetujui' : 'menolak';
    
    Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda yakin ingin ${actionText} pengajuan ini?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'approve' ? '#198754' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: action === 'approve' ? 'Ya, Setujui' : 'Ya, Tolak',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = action === 'approve' 
                ? `{{ url('approval/approve') }}/${approvalId}`
                : `{{ url('approval/reject') }}/${approvalId}`;
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    comments: comments
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        $('#approvalDetailModal').modal('hide');
                        loadPendingApprovals();
                        loadStatistics();
                    } else {
                        showAlert(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Terjadi kesalahan';
                    showAlert(message, 'error');
                }
            });
        }
    });
}

// Helper functions
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', { 
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function formatCurrency(number) {
    if (!number) return 'Rp 0';
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
}

function getUrgencyBadge(urgency) {
    const badges = {
        'low': '<span class="badge bg-success">Low</span>',
        'medium': '<span class="badge bg-warning">Medium</span>',
        'high': '<span class="badge bg-danger">High</span>'
    };
    return badges[urgency] || '<span class="badge bg-secondary">-</span>';
}

function getStatusBadgeColor(status) {
    const colors = {
        0: 'warning',
        1: 'success',
        2: 'danger',
        3: 'secondary'
    };
    return colors[status] || 'light';
}

function showAlert(message, type) {
    Swal.fire({
        icon: type,
        title: type === 'error' ? 'Error!' : (type === 'warning' ? 'Perhatian!' : 'Info'),
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}
</script>
@endsection
