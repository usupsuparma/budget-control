@extends('layouts.master')

@section('title', 'Approval Management | Budget Control')
@section('title-sub', 'Budget Control')
@section('pagetitle', 'Approval Management')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
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
        min-height: 200px;
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
    .level-badge {
        display: inline-block;
        width: 28px;
        height: 28px;
        line-height: 28px;
        text-align: center;
        border-radius: 50%;
        font-weight: bold;
        font-size: 12px;
    }
    .level-1 { background: #d1e7dd; color: #0f5132; }
    .level-2 { background: #cff4fc; color: #055160; }
    .level-3 { background: #fff3cd; color: #664d03; }
    .level-4 { background: #f8d7da; color: #842029; }
    .level-5 { background: #d3d3d4; color: #41464b; }
</style>
@endsection

@section('content')

<div class="col-12 col-lg-12">
    <!-- CARD PEMBUNGKUS UTAMA -->
    <div class="card card-h-100 shadow-sm border">
        <div class="card-body">
            <div class="row">
                <!-- LEFT SIDEBAR (Tab) -->
                <div class="col-md-2 border-end">
                    <ul class="nav nav-pills flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#dashboard" role="tab">
                                <i class="ri-dashboard-line me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#authorizer" role="tab">
                                <i class="ri-user-settings-line me-2"></i> Authorizer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#threshold" role="tab">
                                <i class="ri-settings-3-line me-2"></i> Threshold
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="col-md-10">
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="dashboard">
                            @include('pages.approval.partials.dashboard')
                        </div>
                        <div class="tab-pane fade" id="authorizer">
                            @include('pages.approval.partials.authorizer-content')
                        </div>
                        <div class="tab-pane fade" id="threshold">
                            @include('pages.approval.partials.threshold-content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Include all JavaScript from dashboard, authorizer, and threshold
let isEditMode = false;
let employeeSelect;

$(document).ready(function() {
    // Initialize Choices.js for employee select
    const employeeSelectElement = document.getElementById('employee_id');
    if (employeeSelectElement) {
        employeeSelect = new Choices('#employee_id', {
            searchEnabled: true,
            removeItemButton: false,
            placeholder: true,
            placeholderValue: 'Pilih Employee'
        });
        
        // Auto-fill authorizer name when employee is selected
        $('#employee_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const name = selectedOption.data('name');
            if (name) {
                $('#authorizer_name').val(name);
            }
        });
    }
    
    // Load dashboard data
    loadPendingApprovals();
    loadStatistics();
    
    // Handle tab changes
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr("href");
        
        if (target === '#dashboard') {
            loadPendingApprovals();
            loadStatistics();
        } else if (target === '#authorizer') {
            loadAuthorizers();
        } else if (target === '#threshold') {
            loadThresholds();
        }
    });
    
    // Form submissions
    $('#authorizerForm').on('submit', function(e) {
        e.preventDefault();
        saveAuthorizer();
    });
    
    $('#thresholdForm').on('submit', function(e) {
        e.preventDefault();
        saveThreshold();
    });
});

// ========== DASHBOARD FUNCTIONS ==========

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

function renderPendingTable(data) {
    const tbody = $('#pendingTableBody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center text-muted">
                    <i class="ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-2">Tidak ada pending approvals</p>
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
                    <button class="btn btn-sm btn-primary" onclick="showApprovalDetail(${item.approval_id}, ${item.transaction_id})">
                        <i class="ri-eye-line"></i> Detail
                    </button>
                </td>
            </tr>
        `);
    });
}

function loadStatistics() {
    $.ajax({
        url: '{{ route("approval.statistics") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#stat-pending').text(response.data.pending || 0);
                $('#stat-in-progress').text(response.data.in_progress || 0);
                $('#stat-approved').text(response.data.approved || 0);
                $('#stat-rejected').text(response.data.rejected || 0);
            }
        }
    });
}

function showApprovalDetail(approvalId, transactionId) {
    $.ajax({
        url: `{{ url('approval/transaction') }}/${transactionId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#approval-id').val(approvalId);
                $('#approval-transaction-date').text(formatDate(data.transaction_date));
                $('#approval-user').text(data.user_name);
                $('#approval-purpose').text(data.purpose);
                $('#approval-amount').text(formatCurrency(data.estimated_amount));
                $('#approval-urgency').html(getUrgencyBadge(data.urgency));
                $('#approval-description').text(data.description || '-');
                
                renderApprovalTimeline(data.approvals, data.current_approval_level);
                
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
                        <h6 class="mb-1"><i class="${statusIcon}"></i> Level ${approval.sequence_order} - ${approval.approver_name}</h6>
                        <p class="text-muted small mb-1">${statusText}</p>
                        ${approval.comments ? `<p class="small text-muted mb-0"><em>"${approval.comments}"</em></p>` : ''}
                    </div>
                    <small class="text-muted">${approval.approved_at ? formatDateTime(approval.approved_at) : '-'}</small>
                </div>
            </div>
        `);
    });
}

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
                    showAlert(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
        }
    });
}

// ========== AUTHORIZER FUNCTIONS ==========

function loadAuthorizers() {
    $.ajax({
        url: '{{ route("approval.authorizer.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderAuthorizerTable(response.data);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            showAlert('Gagal memuat data authorizer', 'error');
        }
    });
}

function renderAuthorizerTable(data) {
    const tbody = $('#authorizerTableBody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="10" class="text-center text-muted">
                    Tidak ada data authorizer
                </td>
            </tr>
        `);
        return;
    }
    
    data.forEach((item, index) => {
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td><strong>${item.authorizer_name}</strong></td>
                <td>${item.employee ? item.employee.first_name + ' ' + (item.employee.last_name || '') : '-'}</td>
                <td><span class="level-badge level-${item.approval_level}">${item.approval_level}</span></td>
                <td>${item.position_code || '-'}</td>
                <td class="text-end">${item.max_approval_amount ? formatCurrency(item.max_approval_amount) : 'Unlimited'}</td>
                <td>
                    <span class="badge bg-${item.can_override ? 'primary' : 'secondary'}">
                        ${item.can_override ? 'Ya' : 'Tidak'}
                    </span>
                </td>
                <td class="text-center">${item.priority_order || 1}</td>
                <td>
                    <span class="badge bg-${item.status ? 'success' : 'secondary'}">
                        ${item.status ? 'Aktif' : 'Nonaktif'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editAuthorizer(${item.id})">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteAuthorizer(${item.id})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function showAddAuthorizerModal() {
    isEditMode = false;
    $('#authorizerModalTitle').text('Tambah Authorizer');
    $('#authorizerForm')[0].reset();
    $('#authorizer-id').val('');
    if (employeeSelect) {
        employeeSelect.setChoiceByValue('');
    }
    $('#status').prop('checked', true);
    $('#can_override').prop('checked', false);
    $('#authorizerModal').modal('show');
}

function editAuthorizer(id) {
    $.ajax({
        url: '{{ route("approval.authorizer.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const item = response.data.find(a => a.id === id);
                if (item) {
                    isEditMode = true;
                    $('#authorizerModalTitle').text('Edit Authorizer');
                    $('#authorizer-id').val(item.id);
                    if (employeeSelect) {
                        employeeSelect.setChoiceByValue(item.employee_id.toString());
                    }
                    $('#authorizer_name').val(item.authorizer_name);
                    $('#level_number').val(item.level_number);
                    $('#approval_level').val(item.approval_level);
                    $('#position_code').val(item.position_code);
                    $('#authority').val(item.authority);
                    $('#max_approval_amount').val(item.max_approval_amount);
                    $('#priority_order').val(item.priority_order);
                    $('#can_override').prop('checked', item.can_override);
                    $('#status').prop('checked', item.status);
                    $('#authorizerModal').modal('show');
                }
            }
        }
    });
}

function saveAuthorizer() {
    const data = {
        _token: '{{ csrf_token() }}',
        employee_id: $('#employee_id').val(),
        authorizer_name: $('#authorizer_name').val(),
        level_number: $('#level_number').val(),
        approval_level: $('#approval_level').val(),
        position_code: $('#position_code').val(),
        authority: $('#authority').val(),
        max_approval_amount: $('#max_approval_amount').val() || null,
        priority_order: $('#priority_order').val() || 1,
        can_override: $('#can_override').is(':checked'),
        status: $('#status').is(':checked') ? 1 : 0
    };
    
    const authorizerId = $('#authorizer-id').val();
    let url = '{{ route("approval.authorizer.store") }}';
    let method = 'POST';
    
    if (isEditMode && authorizerId) {
        url = `{{ url("approval/authorizer/update") }}/${authorizerId}`;
        method = 'PUT';
    }
    
    $.ajax({
        url: url,
        type: method,
        data: data,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#authorizerModal').modal('hide');
                loadAuthorizers();
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                const errorMessages = Object.values(errors).flat().join('\n');
                showAlert(errorMessages, 'error');
            } else {
                showAlert(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
            }
        }
    });
}

function deleteAuthorizer(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menghapus authorizer ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ url("approval/authorizer/delete") }}/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        loadAuthorizers();
                    } else {
                        showAlert(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
        }
    });
}

// ========== THRESHOLD FUNCTIONS ==========

function loadThresholds() {
    $.ajax({
        url: '{{ route("approval.threshold.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderThresholdTable(response.data);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            showAlert('Gagal memuat data threshold', 'error');
        }
    });
}

function renderThresholdTable(data) {
    const tbody = $('#thresholdTableBody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center text-muted">
                    Tidak ada data threshold
                </td>
            </tr>
        `);
        return;
    }
    
    data.forEach((item, index) => {
        const requiredLevels = item.required_levels || [];
        const levelBadges = requiredLevels.map(level => 
            `<span class="level-badge level-${level}">${level}</span>`
        ).join(' ');
        
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td class="text-end">${formatCurrency(item.min_amount)}</td>
                <td class="text-end">${formatCurrency(item.max_amount)}</td>
                <td class="text-center">${item.approval_sequence}</td>
                <td>${levelBadges || '-'}</td>
                <td>${item.description || '-'}</td>
                <td>
                    <span class="badge bg-${item.is_active ? 'success' : 'secondary'}">
                        ${item.is_active ? 'Aktif' : 'Nonaktif'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editThreshold(${item.id})">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteThreshold(${item.id})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function showAddThresholdModal() {
    isEditMode = false;
    $('#thresholdModalTitle').text('Tambah Threshold');
    $('#thresholdForm')[0].reset();
    $('#threshold-id').val('');
    $('.level-check').prop('checked', false);
    $('#is_active').prop('checked', true);
    $('#thresholdModal').modal('show');
}

function editThreshold(id) {
    $.ajax({
        url: '{{ route("approval.threshold.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const item = response.data.find(t => t.id === id);
                if (item) {
                    isEditMode = true;
                    $('#thresholdModalTitle').text('Edit Threshold');
                    $('#threshold-id').val(item.id);
                    $('#min_amount').val(item.min_amount);
                    $('#max_amount').val(item.max_amount);
                    $('#description').val(item.description);
                    $('#is_active').prop('checked', item.is_active);
                    
                    $('.level-check').prop('checked', false);
                    if (item.required_levels) {
                        item.required_levels.forEach(level => {
                            $(`.level-check[value="${level}"]`).prop('checked', true);
                        });
                    }
                    
                    $('#thresholdModal').modal('show');
                }
            }
        }
    });
}

function saveThreshold() {
    const requiredLevels = [];
    $('.level-check:checked').each(function() {
        requiredLevels.push(parseInt($(this).val()));
    });
    
    if (requiredLevels.length === 0) {
        showAlert('Pilih minimal satu level approval', 'warning');
        return;
    }
    
    const data = {
        _token: '{{ csrf_token() }}',
        min_amount: $('#min_amount').val(),
        max_amount: $('#max_amount').val(),
        approval_sequence: requiredLevels.length,
        required_levels: requiredLevels.sort((a, b) => a - b),
        description: $('#description').val(),
        is_active: $('#is_active').is(':checked')
    };
    
    const thresholdId = $('#threshold-id').val();
    let url = '{{ route("approval.threshold.store") }}';
    let method = 'POST';
    
    if (isEditMode && thresholdId) {
        url = `{{ url("approval/threshold/update") }}/${thresholdId}`;
        method = 'PUT';
    }
    
    $.ajax({
        url: url,
        type: method,
        data: data,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#thresholdModal').modal('hide');
                loadThresholds();
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                const errorMessages = Object.values(errors).flat().join('\n');
                showAlert(errorMessages, 'error');
            } else {
                showAlert(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
            }
        }
    });
}

function deleteThreshold(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menghapus threshold ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ url("approval/threshold/delete") }}/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        loadThresholds();
                    } else {
                        showAlert(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
        }
    });
}

// ========== HELPER FUNCTIONS ==========

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
