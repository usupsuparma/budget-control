@extends('layouts.master')

@section('title', 'Approval Authorizers | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Pengaturan Authorizer')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
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
                    <i class="ri-user-settings-line me-2"></i>Daftar Authorizer
                </h5>
                <button class="btn btn-primary" onclick="showAddModal()">
                    <i class="ri-add-line"></i> Tambah Authorizer
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="authorizerTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Employee</th>
                                <th>Level</th>
                                <th>Position Code</th>
                                <th>Max Amount</th>
                                <th>Override</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="authorizerTableBody">
                            <tr>
                                <td colspan="10" class="text-center">
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
<div class="modal fade" id="authorizerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Authorizer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="authorizerForm">
                <div class="modal-body">
                    <input type="hidden" id="authorizer-id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option value="">Pilih Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" data-name="{{ $employee->first_name }} {{ $employee->last_name }}">
                                        {{ $employee->first_name }} {{ $employee->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="authorizer_name" class="form-label">Nama Authorizer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="authorizer_name" name="authorizer_name" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="level_number" class="form-label">Level Number <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="level_number" name="level_number" required min="1" max="10">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="approval_level" class="form-label">Approval Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="approval_level" name="approval_level" required>
                                <option value="">Pilih Level</option>
                                <option value="1">Level 1 - Supervisor</option>
                                <option value="2">Level 2 - Manager</option>
                                <option value="3">Level 3 - Direktur</option>
                                <option value="4">Level 4 - CEO</option>
                                <option value="5">Level 5 - Board</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="priority_order" class="form-label">Priority Order</label>
                            <input type="number" class="form-control" id="priority_order" name="priority_order" min="1" value="1">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="position_code" class="form-label">Position Code</label>
                            <input type="text" class="form-control" id="position_code" name="position_code" placeholder="Contoh: MGR-FIN">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="authority" class="form-label">Authority</label>
                            <input type="text" class="form-control" id="authority" name="authority" placeholder="Contoh: Finance">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_approval_amount" class="form-label">Max Approval Amount</label>
                            <input type="number" class="form-control" id="max_approval_amount" name="max_approval_amount" min="0" placeholder="Kosongkan jika unlimited">
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch me-4">
                                <input class="form-check-input" type="checkbox" id="can_override" name="can_override">
                                <label class="form-check-label" for="can_override">Can Override</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                <label class="form-check-label" for="status">Aktif</label>
                            </div>
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
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let isEditMode = false;
let employeeSelect;

$(document).ready(function() {
    // Initialize Choices.js for employee select
    employeeSelect = new Choices('#employee_id', {
        searchEnabled: true,
        removeItemButton: false,
        placeholder: true,
        placeholderValue: 'Pilih Employee'
    });
    
    loadAuthorizers();
    
    // Auto-fill authorizer name when employee is selected
    $('#employee_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const name = selectedOption.data('name');
        if (name) {
            $('#authorizer_name').val(name);
        }
    });
    
    $('#authorizerForm').on('submit', function(e) {
        e.preventDefault();
        saveAuthorizer();
    });
});

// Load authorizers
function loadAuthorizers() {
    $.ajax({
        url: '{{ route("approval.authorizer.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderTable(response.data);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            showAlert('Gagal memuat data authorizer', 'error');
        }
    });
}

// Render table
function renderTable(data) {
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
                    <span class="badge bg-${item.can_override ? 'success' : 'secondary'}">
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

// Show add modal
function showAddModal() {
    isEditMode = false;
    $('#modalTitle').text('Tambah Authorizer');
    $('#authorizerForm')[0].reset();
    $('#authorizer-id').val('');
    employeeSelect.setChoiceByValue('');
    $('#status').prop('checked', true);
    $('#can_override').prop('checked', false);
    $('#authorizerModal').modal('show');
}

// Edit authorizer
function editAuthorizer(id) {
    $.ajax({
        url: '{{ route("approval.authorizer.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const item = response.data.find(a => a.id === id);
                if (item) {
                    isEditMode = true;
                    $('#modalTitle').text('Edit Authorizer');
                    $('#authorizer-id').val(item.id);
                    employeeSelect.setChoiceByValue(item.employee_id.toString());
                    $('#authorizer_name').val(item.authorizer_name);
                    $('#level_number').val(item.level_number);
                    $('#approval_level').val(item.approval_level);
                    $('#position_code').val(item.position_code);
                    $('#authority').val(item.authority);
                    $('#max_approval_amount').val(item.max_approval_amount);
                    $('#priority_order').val(item.priority_order || 1);
                    $('#can_override').prop('checked', item.can_override);
                    $('#status').prop('checked', item.status);
                    
                    $('#authorizerModal').modal('show');
                }
            }
        }
    });
}

// Save authorizer
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
        method = 'POST';
        data._method = 'PUT';
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

// Delete authorizer
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
                type: 'POST',
                data: { 
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
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
                        loadAuthorizers();
                    } else {
                        showAlert(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON?.message || 'Gagal menghapus authorizer', 'error');
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
