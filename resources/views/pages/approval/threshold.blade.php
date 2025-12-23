@extends('layouts.master')

@section('title', 'Approval Thresholds | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Pengaturan Threshold Approval')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .table-responsive {
        min-height: 200px;
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
<div id="layout-wrapper">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ri-settings-3-line me-2"></i>Daftar Threshold Approval
                </h5>
                <button class="btn btn-primary" onclick="showAddModal()">
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
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="thresholdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Threshold</h5>
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
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let isEditMode = false;

$(document).ready(function() {
    loadThresholds();
    
    $('#thresholdForm').on('submit', function(e) {
        e.preventDefault();
        saveThreshold();
    });
});

// Load thresholds
function loadThresholds() {
    $.ajax({
        url: '{{ route("approval.threshold.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderTable(response.data);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            showAlert('Gagal memuat data threshold', 'error');
        }
    });
}

// Render table
function renderTable(data) {
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

// Show add modal
function showAddModal() {
    isEditMode = false;
    $('#modalTitle').text('Tambah Threshold');
    $('#thresholdForm')[0].reset();
    $('#threshold-id').val('');
    $('.level-check').prop('checked', false);
    $('#is_active').prop('checked', true);
    $('#thresholdModal').modal('show');
}

// Edit threshold
function editThreshold(id) {
    $.ajax({
        url: '{{ route("approval.threshold.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const item = response.data.find(t => t.id === id);
                if (item) {
                    isEditMode = true;
                    $('#modalTitle').text('Edit Threshold');
                    $('#threshold-id').val(item.id);
                    $('#min_amount').val(item.min_amount);
                    $('#max_amount').val(item.max_amount);
                    $('#description').val(item.description);
                    $('#is_active').prop('checked', item.is_active);
                    
                    // Set level checkboxes
                    $('.level-check').prop('checked', false);
                    (item.required_levels || []).forEach(level => {
                        $(`#level${level}`).prop('checked', true);
                    });
                    
                    $('#thresholdModal').modal('show');
                }
            }
        }
    });
}

// Save threshold
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

// Delete threshold
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
                    showAlert(xhr.responseJSON?.message || 'Gagal menghapus threshold', 'error');
                }
            });
        }
    });
}

// Helper functions
function formatCurrency(number) {
    if (!number) return 'Rp 0';
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
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
