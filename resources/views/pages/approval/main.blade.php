@extends('layouts.master')

@section('title', 'Master Approval | Budget Control')
@section('title-sub', 'Budget Control')
@section('pagetitle', 'Master Approval Setup')

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
    .table-responsive {
        min-height: 200px;
    }
    .badge-status {
        padding: 0.35rem 0.65rem;
        font-size: 0.75rem;
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
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
    }
    .nav-pills .nav-link {
        color: #495057;
        border-radius: 0.5rem;
        margin-bottom: 0.25rem;
    }
    .nav-pills .nav-link:hover:not(.active) {
        background-color: #e9ecef;
    }
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
                    <div class="mb-3">
                        <h6 class="text-muted text-uppercase small">Master Setup</h6>
                    </div>
                    <ul class="nav nav-pills flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#modules" role="tab">
                                <i class="ri-apps-line me-2"></i> Modules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#templates" role="tab">
                                <i class="ri-flow-chart me-2"></i> Templates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#flowdetails" role="tab">
                                <i class="ri-user-settings-line me-2"></i> Flow Details
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="col-md-10">
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="modules">
                            @include('pages.approval.partials.modules-content')
                        </div>
                        <div class="tab-pane fade" id="templates">
                            @include('pages.approval.partials.templates-content')
                        </div>
                        <div class="tab-pane fade" id="flowdetails">
                            @include('pages.approval.partials.flow-details-content')
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
// ========== GLOBAL VARIABLES ==========
let isEditMode = false;
let selectedTemplateId = null;
let employmentsData = [];

$(document).ready(function() {
    // Load modules on page load
    loadModules();
    
    // Handle tab changes
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr("href");
        
        if (target === '#modules') {
            loadModules();
        } else if (target === '#templates') {
            loadTemplates();
            loadModulesForDropdown();
        } else if (target === '#flowdetails') {
            loadTemplatesForDropdown();
            loadEmployments();
        }
    });
    
    // Template select change for flow details
    $('#flowDetailsTemplateSelect').on('change', function() {
        selectedTemplateId = $(this).val();
        if (selectedTemplateId) {
            $('#btnAddFlowDetail').prop('disabled', false);
            loadFlowDetails(selectedTemplateId);
        } else {
            $('#btnAddFlowDetail').prop('disabled', true);
            $('#flowDetailsPlaceholder').show();
            $('#flowDetailsTableContainer').hide();
        }
    });
    
    // Form submissions
    $('#moduleForm').on('submit', function(e) {
        e.preventDefault();
        saveModule();
    });
    
    $('#templateForm').on('submit', function(e) {
        e.preventDefault();
        saveTemplate();
    });
    
    $('#flowDetailForm').on('submit', function(e) {
        e.preventDefault();
        saveFlowDetail();
    });
});

// ========== HELPER FUNCTIONS ==========

function showAlert(message, icon = 'info') {
    Swal.fire({
        icon: icon,
        title: icon === 'error' ? 'Error!' : (icon === 'success' ? 'Berhasil!' : 'Info'),
        text: message,
        timer: icon === 'success' ? 2000 : undefined,
        showConfirmButton: icon !== 'success'
    });
}

function formatCurrency(amount) {
    if (!amount) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Format number with thousand separator (for input display)
function formatThousand(num) {
    if (!num) return '';
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Parse formatted string back to number
function parseThousand(str) {
    if (!str) return null;
    return parseInt(str.replace(/\./g, ''), 10) || null;
}

// Initialize currency input formatter
$(document).on('input', '.currency-input', function() {
    let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
    if (value) {
        $(this).val(formatThousand(value));
    }
});

// ========== MODULES FUNCTIONS ==========

function loadModules() {
    $.ajax({
        url: '{{ route("approval.modules.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderModulesTable(response.data);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            showAlert('Gagal memuat data modules', 'error');
        }
    });
}

function renderModulesTable(data) {
    const tbody = $('#modulesTableBody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="ri-inbox-line" style="font-size: 2rem; opacity: 0.3;"></i>
                    <p class="mt-2 mb-0">Tidak ada data modules</p>
                </td>
            </tr>
        `);
        return;
    }
    
    data.forEach((item, index) => {
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td><strong>${item.module_name}</strong></td>
                <td><code>${item.table_name}</code></td>
                <td>
                    <span class="badge bg-${item.is_active ? 'success' : 'secondary'}">
                        ${item.is_active ? 'Aktif' : 'Nonaktif'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editModule(${item.id})">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteModule(${item.id})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function loadAvailableTables(excludeId = null, currentTableName = null) {
    let url = '{{ route("approval.modules.tables") }}';
    if (excludeId) {
        url += `?exclude_id=${excludeId}`;
    }
    
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const select = $('#table_name');
                select.empty().append('<option value="">-- Pilih Table --</option>');
                
                // If editing and we have a current table, add it first as selected
                if (currentTableName && response.data[currentTableName]) {
                    select.append(`<option value="${currentTableName}" selected>${response.data[currentTableName]}</option>`);
                    delete response.data[currentTableName];
                } else if (currentTableName) {
                    // Current table is already in the list, we'll select it below
                }
                
                Object.entries(response.data).forEach(([key, label]) => {
                    const isSelected = currentTableName === key ? 'selected' : '';
                    select.append(`<option value="${key}" ${isSelected}>${label}</option>`);
                });
                
                // Check if no tables available for new module
                if (!excludeId && Object.keys(response.data).length === 0) {
                    showAlert('Semua tabel sudah memiliki module. Tidak ada tabel yang tersedia untuk membuat module baru.', 'warning');
                    $('#moduleModal').modal('hide');
                }
            }
        },
        error: function(xhr) {
            showAlert('Gagal memuat daftar tabel', 'error');
        }
    });
}

function showAddModuleModal() {
    isEditMode = false;
    $('#moduleModalTitle').text('Tambah Module');
    $('#moduleForm')[0].reset();
    $('#module-id').val('');
    $('#module_is_active').prop('checked', true);
    loadAvailableTables();
    $('#moduleModal').modal('show');
}

function editModule(id) {
    $.ajax({
        url: '{{ route("approval.modules.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const item = response.data.find(m => m.id === id);
                if (item) {
                    isEditMode = true;
                    $('#moduleModalTitle').text('Edit Module');
                    $('#module-id').val(item.id);
                    $('#module_name').val(item.module_name);
                    $('#module_is_active').prop('checked', item.is_active);
                    // Load available tables + current table, then show modal
                    loadAvailableTables(item.id, item.table_name);
                    $('#moduleModal').modal('show');
                }
            }
        }
    });
}

function saveModule() {
    const data = {
        _token: '{{ csrf_token() }}',
        module_name: $('#module_name').val(),
        table_name: $('#table_name').val(),
        is_active: $('#module_is_active').is(':checked')
    };
    
    const moduleId = $('#module-id').val();
    let url = '{{ route("approval.modules.store") }}';
    let method = 'POST';
    
    if (isEditMode && moduleId) {
        url = `{{ url("approval/modules/update") }}/${moduleId}`;
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
                $('#moduleModal').modal('hide');
                loadModules();
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

function deleteModule(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menghapus module ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ url("approval/modules/delete") }}/${id}`,
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
                        loadModules();
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

// ========== TEMPLATES FUNCTIONS ==========

function loadModulesForDropdown() {
    $.ajax({
        url: '{{ route("approval.modules.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const select = $('#template_module_id');
                select.empty().append('<option value="">Pilih Module</option>');
                response.data.filter(m => m.is_active).forEach(item => {
                    select.append(`<option value="${item.id}">${item.module_name}</option>`);
                });
            }
        }
    });
}

function loadTemplates() {
    $.ajax({
        url: '{{ route("approval.templates.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderTemplatesTable(response.data);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            showAlert('Gagal memuat data templates', 'error');
        }
    });
}

function renderTemplatesTable(data) {
    const tbody = $('#templatesTableBody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="ri-inbox-line" style="font-size: 2rem; opacity: 0.3;"></i>
                    <p class="mt-2 mb-0">Tidak ada data templates</p>
                </td>
            </tr>
        `);
        return;
    }
    
    data.forEach((item, index) => {
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td><strong>${item.template_name}</strong></td>
                <td><span class="badge bg-info">${item.module?.module_name || '-'}</span></td>
                <td class="text-center">
                    <i class="ri-${item.use_uppline_chain ? 'checkbox-circle-fill text-success' : 'close-circle-fill text-secondary'}"></i>
                </td>
                <td class="text-center">
                    <i class="ri-${item.use_threshold ? 'checkbox-circle-fill text-success' : 'close-circle-fill text-secondary'}"></i>
                </td>
                <td><code>${item.condition_field || '-'}</code></td>
                <td class="text-center">${item.priority}</td>
                <td>
                    <span class="badge bg-${item.is_active ? 'success' : 'secondary'}">
                        ${item.is_active ? 'Aktif' : 'Nonaktif'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editTemplate(${item.id})">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(${item.id})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function showAddTemplateModal() {
    isEditMode = false;
    $('#templateModalTitle').text('Tambah Template');
    $('#templateForm')[0].reset();
    $('#template-id').val('');
    $('#use_uppline_chain').prop('checked', false);
    $('#use_threshold').prop('checked', false);
    $('#template_is_active').prop('checked', true);
    $('#template_priority').val(1);
    loadModulesForDropdown();
    $('#templateModal').modal('show');
}

function editTemplate(id) {
    $.ajax({
        url: '{{ route("approval.templates.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const item = response.data.find(t => t.id === id);
                if (item) {
                    isEditMode = true;
                    loadModulesForDropdown();
                    setTimeout(() => {
                        $('#templateModalTitle').text('Edit Template');
                        $('#template-id').val(item.id);
                        $('#template_module_id').val(item.module_id);
                        $('#template_name').val(item.template_name);
                        $('#condition_field').val(item.condition_field);
                        $('#template_priority').val(item.priority);
                        $('#use_uppline_chain').prop('checked', item.use_uppline_chain);
                        $('#use_threshold').prop('checked', item.use_threshold);
                        $('#template_is_active').prop('checked', item.is_active);
                        $('#templateModal').modal('show');
                    }, 200);
                }
            }
        }
    });
}

function saveTemplate() {
    const data = {
        _token: '{{ csrf_token() }}',
        module_id: $('#template_module_id').val(),
        template_name: $('#template_name').val(),
        use_uppline_chain: $('#use_uppline_chain').is(':checked'),
        use_threshold: $('#use_threshold').is(':checked'),
        condition_field: $('#condition_field').val() || null,
        priority: $('#template_priority').val() || 1,
        is_active: $('#template_is_active').is(':checked')
    };
    
    const templateId = $('#template-id').val();
    let url = '{{ route("approval.templates.store") }}';
    let method = 'POST';
    
    if (isEditMode && templateId) {
        url = `{{ url("approval/templates/update") }}/${templateId}`;
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
                $('#templateModal').modal('hide');
                loadTemplates();
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

function deleteTemplate(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menghapus template ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ url("approval/templates/delete") }}/${id}`,
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
                        loadTemplates();
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

// ========== FLOW DETAILS FUNCTIONS ==========

function loadTemplatesForDropdown() {
    $.ajax({
        url: '{{ route("approval.templates.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const select = $('#flowDetailsTemplateSelect');
                select.empty().append('<option value="">-- Pilih Template --</option>');
                response.data.filter(t => t.is_active).forEach(item => {
                    select.append(`<option value="${item.id}">${item.template_name} (${item.module?.module_name || '-'})</option>`);
                });
            }
        }
    });
}

function loadEmployments() {
    $.ajax({
        url: '{{ route("approval.employments.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                employmentsData = response.data;
                const select = $('#employment_id');
                select.empty().append('<option value="">Pilih Employee</option>');
                response.data.forEach(item => {
                    select.append(`<option value="${item.id}">${item.employee_name}</option>`);
                });
            }
        }
    });
}

function loadFlowDetails(templateId) {
    $.ajax({
        url: `{{ url("approval/flow-details/data") }}/${templateId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#flowDetailsPlaceholder').hide();
                $('#flowDetailsTableContainer').show();
                renderFlowDetailsTable(response.data);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function(xhr) {
            showAlert('Gagal memuat data flow details', 'error');
        }
    });
}

function renderFlowDetailsTable(data) {
    const tbody = $('#flowDetailsTableBody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="ri-inbox-line" style="font-size: 2rem; opacity: 0.3;"></i>
                    <p class="mt-2 mb-0">Tidak ada approvers untuk template ini</p>
                </td>
            </tr>
        `);
        return;
    }
    
    data.forEach((item, index) => {
        const employeeName = item.employment?.employee 
            ? `${item.employment.employee.first_name} ${item.employment.employee.last_name || ''}` 
            : 'N/A';
        
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td><span class="level-badge level-${item.level_sequence}">${item.level_sequence}</span></td>
                <td><strong>${employeeName}</strong></td>
                <td>${formatCurrency(item.threshold_amount)}</td>
                <td>
                    <span class="badge bg-${item.is_required ? 'primary' : 'secondary'}">
                        ${item.is_required ? 'Required' : 'Optional'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editFlowDetail(${item.id})">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteFlowDetail(${item.id})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function showAddFlowDetailModal() {
    if (!selectedTemplateId) {
        showAlert('Pilih template terlebih dahulu', 'warning');
        return;
    }
    
    isEditMode = false;
    $('#flowDetailModalTitle').text('Tambah Approver');
    $('#flowDetailForm')[0].reset();
    $('#flowdetail-id').val('');
    $('#flowdetail_template_id').val(selectedTemplateId);
    $('#level_sequence').val(1);
    $('#is_required').prop('checked', true);
    $('#flowDetailModal').modal('show');
}

function editFlowDetail(id) {
    $.ajax({
        url: `{{ url("approval/flow-details/data") }}/${selectedTemplateId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const item = response.data.find(d => d.id === id);
                if (item) {
                    isEditMode = true;
                    $('#flowDetailModalTitle').text('Edit Approver');
                    $('#flowdetail-id').val(item.id);
                    $('#flowdetail_template_id').val(item.template_id);
                    $('#level_sequence').val(item.level_sequence);
                    $('#employment_id').val(item.employment_id);
                    $('#threshold_amount').val(formatThousand(item.threshold_amount));
                    $('#is_required').prop('checked', item.is_required);
                    $('#flowDetailModal').modal('show');
                }
            }
        }
    });
}

function saveFlowDetail() {
    const data = {
        _token: '{{ csrf_token() }}',
        template_id: $('#flowdetail_template_id').val(),
        level_sequence: $('#level_sequence').val(),
        employment_id: $('#employment_id').val(),
        threshold_amount: parseThousand($('#threshold_amount').val()),
        is_required: $('#is_required').is(':checked')
    };
    
    const detailId = $('#flowdetail-id').val();
    let url = '{{ route("approval.flowdetails.store") }}';
    let method = 'POST';
    
    if (isEditMode && detailId) {
        url = `{{ url("approval/flow-details/update") }}/${detailId}`;
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
                $('#flowDetailModal').modal('hide');
                loadFlowDetails(selectedTemplateId);
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

function deleteFlowDetail(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menghapus approver ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ url("approval/flow-details/delete") }}/${id}`,
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
                        loadFlowDetails(selectedTemplateId);
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
</script>
@endsection
