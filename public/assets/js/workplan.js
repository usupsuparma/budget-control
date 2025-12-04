/**
 * Work Plan (Program Kerja) Management
 * Handles dynamic KPI hierarchy and workplan CRUD operations
 */

let currentKpiData = [];
let currentDivisionId = null;
let currentYear = null;

$(document).ready(function() {
    initializeEventListeners();
});

function initializeEventListeners() {
    // Load KPI Data
    $('#btnLoadKpi').on('click', function() {
        loadKpiData();
    });

    // Reset Filter
    $('#btnReset').on('click', function() {
        resetFilters();
    });

    // Delegated event for dynamic elements
    $(document).on('click', '.expand-btn', function() {
        toggleSection($(this));
    });

    $(document).on('click', '.btn-add-workplan', function() {
        const kpiType = $(this).data('kpi-type');
        const kpiId = $(this).data('kpi-id');
        openWorkplanModal('add', kpiType, kpiId);
    });

    $(document).on('click', '.btn-edit-workplan', function() {
        const row = $(this).closest('tr');
        const workplanId = row.data('workplan-id');
        const kpiType = row.data('kpi-type');
        const kpiId = row.data('kpi-id');
        openWorkplanModal('edit', kpiType, kpiId, workplanId, row);
    });

    $(document).on('click', '.btn-delete-workplan', function() {
        const row = $(this).closest('tr');
        deleteWorkplan(row);
    });

    $(document).on('click', '.btn-approve-workplan', function() {
        const workplanId = $(this).data('id');
        approveWorkplan(workplanId);
    });

    // Auto-calculate duration when dates change
    $(document).on('change', '.schedule-start, .schedule-end', function() {
        const row = $(this).closest('tr');
        calculateDuration(row);
    });

    // Open budget items page when clicking budget column
    $(document).on('click', '.budget-cell', function() {
        const row = $(this).closest('tr');
        const workplanId = row.data('workplan-id');
        
        if (workplanId && workplanId !== 'new') {
            // Open budget items page in new tab
            const url = `/workplan/${workplanId}/item`;
            window.open(url, '_blank');
        }
    });

    // Save workplan from modal
    $('#btnSaveWorkplan').on('click', function() {
        saveWorkplanFromModal();
    });

    // Auto-calculate duration in modal
    $('#schedule_start, #schedule_end').on('change', function() {
        calculateModalDuration();
    });
}

function openWorkplanModal(mode, kpiType, kpiId, workplanId = null, row = null) {
    const modal = $('#workplanModal');
    const modalTitle = mode === 'add' ? 'Add Work Plan' : 'Edit Work Plan';
    
    $('#workplanModalLabel').text(modalTitle);
    $('#workplanForm')[0].reset();
    
    // Set hidden fields
    $('#kpi_type').val(kpiType);
    $('#kpi_id').val(kpiId);
    
    if (mode === 'edit' && row) {
        // Populate form with existing data
        $('#workplan_id').val(workplanId);
        $('#activity').val(row.find('.activity-input').val());
        $('#duration_days').val(row.find('.duration-input').val());
        $('#description').val(row.find('.description-input').val() || '');
        
        // Set dates if available
        const startDate = row.find('.schedule-start').val();
        const endDate = row.find('.schedule-end').val();
        if (startDate) $('#schedule_start').val(startDate);
        if (endDate) $('#schedule_end').val(endDate);
        
        // Set planning months
        const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        months.forEach(month => {
            const isChecked = row.find(`.plan-month[data-month="${month}"]`).prop('checked');
            $(`#plan_${month}`).prop('checked', isChecked);
            
            const isRealChecked = row.find(`.real-month[data-month="${month}"]`).prop('checked');
            $(`#real_${month}`).prop('checked', isRealChecked);
        });
    } else {
        $('#workplan_id').val('');
    }
    
    modal.modal('show');
}

function openWorkplanModal(mode, kpiType, kpiId, workplanId = null, row = null) {
    const modal = $('#workplanModal');
    const modalTitle = mode === 'add' ? 'Add Work Plan' : 'Edit Work Plan';
    
    $('#workplanModalLabel').text(modalTitle);
    $('#workplanForm')[0].reset();
    
    // Set hidden fields
    $('#kpi_type').val(kpiType);
    $('#kpi_id').val(kpiId);
    
    if (mode === 'edit' && row) {
        // Populate form with existing data
        $('#workplan_id').val(workplanId);
        $('#activity').val(row.find('.activity-input').val());
        $('#duration_days').val(row.find('.duration-input').val());
        $('#description').val(row.find('.description-input').val() || '');
        
        // Set dates if available
        const startDate = row.find('.schedule-start').val();
        const endDate = row.find('.schedule-end').val();
        if (startDate) $('#schedule_start').val(startDate);
        if (endDate) $('#schedule_end').val(endDate);
        
        // Set planning months
        const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        months.forEach(month => {
            const isChecked = row.find(`.plan-month[data-month="${month}"]`).prop('checked');
            $(`#plan_${month}`).prop('checked', isChecked);
            
            const isRealChecked = row.find(`.real-month[data-month="${month}"]`).prop('checked');
            $(`#real_${month}`).prop('checked', isRealChecked);
        });
    } else {
        $('#workplan_id').val('');
    }
    
    modal.modal('show');
}

function loadKpiData() {
    const divisionId = $('#filter_division').val();
    const year = $('#filter_year').val();

    if (!divisionId || !year) {
        Swal.fire({
            icon: 'warning',
            title: 'Filter Required',
            text: 'Silakan pilih Divisi dan Tahun terlebih dahulu!',
        });
        return;
    }

    currentDivisionId = divisionId;
    currentYear = year;

    showLoading();

    $.ajax({
        url: '/workplan/get-kpi-data',
        method: 'GET',
        data: {
            division_id: divisionId,
            year: year
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                currentKpiData = response.data;
                renderKpiData(response.data);
            } else {
                showError('Gagal memuat data KPI');
            }
        },
        error: function(xhr) {
            hideLoading();
            showError('Terjadi kesalahan saat memuat data: ' + xhr.responseText);
        }
    });
}

function renderKpiData(data) {
    const container = $('#workplan-container');
    container.empty();

    if (!data || data.length === 0) {
        container.html(`
            <div class="no-data-message">
                <i class="bi bi-inbox" style="font-size: 48px;"></i>
                <p class="mt-3">Tidak ada data KPI untuk Divisi dan Tahun yang dipilih</p>
            </div>
        `);
        return;
    }

    let html = '';

    data.forEach((division, divIndex) => {
        html += `
            <div class="kpi-division-section mb-4" data-div-index="${divIndex}">
                <div class="kpi-division-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>I. KPI DIVISI</strong> - ${division.division_goals || 'N/A'}
                    </div>
                    <div>
                        <span class="badge bg-light text-dark me-2">Target: ${division.target_division || 'N/A'}</span>
                        <button class="expand-btn" data-target="div-${divIndex}">
                            <i class="bi bi-plus-circle"></i> Expand
                        </button>
                    </div>
                </div>
                <div id="div-${divIndex}" class="collapse-section">
        `;

        if (division.departments && division.departments.length > 0) {
            division.departments.forEach((dept, deptIndex) => {
                html += renderDepartment(dept, divIndex, deptIndex);
            });
        } else {
            html += '<div class="alert alert-info m-3">Belum ada Department untuk KPI Divisi ini</div>';
        }

        html += `
                </div>
            </div>
        `;
    });

    container.html(html);
}

function renderDepartment(dept, divIndex, deptIndex) {
    let html = `
        <div class="kpi-department-section mb-3" data-dept-index="${deptIndex}">
            <div class="kpi-department-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>I.A. KPI DEPARTMENT ${String.fromCharCode(65 + deptIndex)}</strong> - ${dept.department_name}
                </div>
                <div>
                    <span class="badge bg-light text-dark me-2">Target: ${dept.target_department || 'N/A'}</span>
                    <button class="expand-btn" data-target="dept-${divIndex}-${deptIndex}">
                        <i class="bi bi-plus-circle"></i> Expand
                    </button>
                </div>
            </div>
            <div id="dept-${divIndex}-${deptIndex}" class="collapse-section p-3">
                ${renderWorkplanTable(dept, 'department', dept.id, divIndex, deptIndex)}
    `;

    if (dept.sections && dept.sections.length > 0) {
        dept.sections.forEach((section, sectIndex) => {
            html += renderSection(section, divIndex, deptIndex, sectIndex);
        });
    }

    html += `
            </div>
        </div>
    `;

    return html;
}

function renderSection(section, divIndex, deptIndex, sectIndex) {
    let html = `
        <div class="kpi-section-section mb-3" data-sect-index="${sectIndex}">
            <div class="kpi-section-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>I.A. KPI SECTION ${String.fromCharCode(65 + sectIndex)}</strong> - ${section.section_name}
                </div>
                <div>
                    <span class="badge bg-light text-dark me-2">Target: ${section.target_section || 'N/A'}</span>
                    <button class="expand-btn" data-target="sect-${divIndex}-${deptIndex}-${sectIndex}">
                        <i class="bi bi-plus-circle"></i> Expand
                    </button>
                </div>
            </div>
            <div id="sect-${divIndex}-${deptIndex}-${sectIndex}" class="collapse-section p-3">
                ${renderWorkplanTable(section, 'section', section.id, divIndex, deptIndex, sectIndex)}
            </div>
        </div>
    `;

    return html;
}

function renderWorkplanTable(kpiData, kpiType, kpiId, ...indexes) {
    const workplans = kpiData.workplans || [];
    const targetLabel = kpiType === 'department' ? 'Target Department' : 'Target Section';
    const targetValue = kpiType === 'department' ? kpiData.target_department : kpiData.target_section;
    const goalsLabel = kpiType === 'department' ? 'Department Goals' : 'Section Goals';
    const goalsValue = kpiType === 'department' ? kpiData.department_goals : kpiData.section_goals;

    let html = `
        <div class="workplan-section mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>${goalsLabel}:</strong> ${goalsValue || 'N/A'}
                </div>
                <button class="btn btn-success btn-sm btn-add-workplan" data-kpi-type="${kpiType}" data-kpi-id="${kpiId}">
                    <i class="bi bi-plus-circle"></i> Add Work Plan
                </button>
            </div>
            <div class="table-responsive">
                <table class="workplan-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 80px;">Action</th>
                            <th rowspan="2" style="width: 200px;">Activities</th>
                            <th rowspan="2" style="width: 80px;">Duration<br>(Days)</th>
                            <th colspan="13" style="background: #0d6efd;">Activities</th>
                            <th rowspan="2" style="width: 120px;">Budget</th>
                            <th colspan="13" style="background: #dc3545;">Realization</th>
                        </tr>
                        <tr>
                            <th class="month-cell">Jan</th>
                            <th class="month-cell">Feb</th>
                            <th class="month-cell">Mar</th>
                            <th class="month-cell">Apr</th>
                            <th class="month-cell">Mei</th>
                            <th class="month-cell">Jun</th>
                            <th class="month-cell">Jul</th>
                            <th class="month-cell">Agu</th>
                            <th class="month-cell">Sep</th>
                            <th class="month-cell">Okt</th>
                            <th class="month-cell">Nov</th>
                            <th class="month-cell">Des</th>
                            <th class="month-cell">Des</th>
                            
                            <th class="realization-cell">Jan</th>
                            <th class="realization-cell">Feb</th>
                            <th class="realization-cell">Mar</th>
                            <th class="realization-cell">Apr</th>
                            <th class="realization-cell">Mei</th>
                            <th class="realization-cell">Jun</th>
                            <th class="realization-cell">Jul</th>
                            <th class="realization-cell">Agu</th>
                            <th class="realization-cell">Sep</th>
                            <th class="realization-cell">Okt</th>
                            <th class="realization-cell">Nov</th>
                            <th class="realization-cell">Des</th>
                            <th class="realization-cell">Des</th>
                        </tr>
                    </thead>
                    <tbody>
    `;

    if (workplans.length > 0) {
        workplans.forEach((wp, index) => {
            html += renderWorkplanRow(wp, kpiType, kpiId, index);
        });
    } else {
        html += `
            <tr class="no-data-row">
                <td colspan="29" class="text-center text-muted py-3">
                    <i>Belum ada work plan. Klik tombol "Add Work Plan" untuk menambahkan.</i>
                </td>
            </tr>
        `;
    }

    html += `
                    </tbody>
                </table>
            </div>
        </div>
    `;

    return html;
}

function renderWorkplanRow(workplan, kpiType, kpiId, index) {
    const isNew = !workplan.id;
    const isApproved = workplan.status === 'approved';
    const statusBadge = getStatusBadge(workplan.status);

    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

    let html = `
        <tr data-workplan-id="${workplan.id || 'new'}" data-kpi-type="${kpiType}" data-kpi-id="${kpiId}" class="${isNew ? 'new-row' : ''}">
            <td class="action-column">
    `;

    if (isApproved) {
        html += `
                <button class="btn btn-success btn-action btn-sm" disabled title="Sudah Disetujui">
                    <i class="bi bi-check-circle"></i>
                </button>
        `;
    } else if (isNew) {
        html += `
                <button class="btn btn-primary btn-action btn-save-workplan" title="Simpan Work Plan">
                    <i class="bi bi-save"></i>
                </button>
                <button class="btn btn-danger btn-action btn-delete-workplan" title="Hapus Work Plan">
                    <i class="bi bi-trash"></i>
                </button>
        `;
    } else {
        html += `
                <button class="btn btn-primary btn-action btn-edit-workplan" title="Edit Work Plan">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-danger btn-action btn-delete-workplan" title="Hapus Work Plan">
                    <i class="bi bi-trash"></i>
                </button>
                ${workplan.status === 'draft' ? `
                <button class="btn btn-success btn-action btn-approve-workplan" data-id="${workplan.id}" title="Setujui Work Plan">
                    <i class="bi bi-check"></i>
                </button>
                ` : ''}
        `;
    }

    html += `
            </td>
            <td>
                <div class="activity-text">${workplan.activity || ''}</div>
                <input type="hidden" class="activity-input" value="${workplan.activity || ''}">
                <input type="hidden" class="description-input" value="${workplan.description || ''}">
                <input type="hidden" class="schedule-start" value="${workplan.schedule_start || ''}">
                <input type="hidden" class="schedule-end" value="${workplan.schedule_end || ''}">
            </td>
            <td>
                <div class="text-center">${workplan.duration_days || '-'}</div>
                <input type="hidden" class="duration-input" value="${workplan.duration_days || ''}">
            </td>
    `;

    // Activities months (planning)
    months.forEach(month => {
        const checked = workplan[`plan_${month}`] ? 'checked' : '';
        html += `
            <td class="month-cell">
                <input type="checkbox" class="plan-month" data-month="${month}" ${checked} disabled>
            </td>
        `;
    });

    // Extra Des column for activities
    html += `<td class="month-cell"></td>`;

    // Budget - clickable to open items page
    const budgetValue = workplan.budget || 0;
    const hasWorkplanId = workplan.id && workplan.id !== 'new';
    html += `
            <td class="budget-cell ${hasWorkplanId ? 'cursor-pointer' : ''}" 
                style="${hasWorkplanId ? 'cursor: pointer; background-color: #f0f8ff;' : ''}" 
                title="${hasWorkplanId ? 'Click to manage budget items' : 'Save workplan first'}">
                <div class="d-flex align-items-center justify-content-between">
                    <span>${formatCurrency(budgetValue)}</span>
                    ${hasWorkplanId ? '<i class="bi bi-box-arrow-up-right ms-2 text-primary"></i>' : ''}
                </div>
            </td>
    `;

    // Realization months
    months.forEach(month => {
        const checked = workplan[`real_${month}`] ? 'checked' : '';
        html += `
            <td class="realization-cell">
                <input type="checkbox" class="real-month" data-month="${month}" ${checked} disabled>
            </td>
        `;
    });

    // Extra Des column for realization
    html += `<td class="realization-cell"></td>`;

    html += `
        </tr>
    `;

    return html;
}

function addWorkplanRow(kpiType, kpiId, container) {
    const table = container.find('tbody');
    
    // Remove no-data row if exists
    table.find('.no-data-row').remove();

    const newWorkplan = {
        activity: '',
        duration_days: '',
        budget: '',
        status: 'draft'
    };

    const rowHtml = renderWorkplanRow(newWorkplan, kpiType, kpiId, table.find('tr').length);
    table.append(rowHtml);
}

function saveWorkplanFromModal() {
    const workplanId = $('#workplan_id').val();
    const kpiType = $('#kpi_type').val();
    const kpiId = $('#kpi_id').val();
    const isNew = !workplanId;

    const activity = $('#activity').val().trim();
    if (!activity) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Activity name is required!',
        });
        return;
    }

    const data = {
        kpi_type: kpiType,
        kpi_id: kpiId,
        year: currentYear,
        activity: activity,
        duration_days: $('#duration_days').val(),
        schedule_start: $('#schedule_start').val(),
        schedule_end: $('#schedule_end').val(),
        budget: 0,
        description: $('#description').val()
    };

    // Collect planning months
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    months.forEach(month => {
        data[`plan_${month}`] = $(`#plan_${month}`).prop('checked') ? 1 : 0;
        data[`real_${month}`] = $(`#real_${month}`).prop('checked') ? 1 : 0;
    });

    showLoading();

    const url = isNew ? '/workplan/store' : `/workplan/${workplanId}`;
    const method = isNew ? 'POST' : 'PUT';

    $.ajax({
        url: url,
        method: method,
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                $('#workplanModal').modal('hide');
                
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: isNew ? 'Work plan created successfully!' : 'Work plan updated successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                
                // Reload KPI data to refresh the table
                loadKpiData();
            } else {
                showError(response.message || 'Failed to save work plan');
            }
        },
        error: function(xhr) {
            hideLoading();
            const message = xhr.responseJSON?.message || 'An error occurred while saving';
            showError(message);
        }
    });
}

function saveWorkplan(row) {
    const workplanId = row.data('workplan-id');
    const kpiType = row.data('kpi-type');
    const kpiId = row.data('kpi-id');
    const isNew = workplanId === 'new';

    const data = {
        kpi_type: kpiType,
        kpi_id: kpiId,
        year: currentYear,
        activity: row.find('.activity-input').val(),
        duration_days: row.find('.duration-input').val(),
        budget: 0, // Budget will be calculated from budget items
        description: ''
    };

    // Collect planning months
    row.find('.plan-month').each(function() {
        const month = $(this).data('month');
        data[`plan_${month}`] = $(this).is(':checked') ? 1 : 0;
    });

    // Collect realization months
    row.find('.real-month').each(function() {
        const month = $(this).data('month');
        data[`real_${month}`] = $(this).is(':checked') ? 1 : 0;
    });

    console.log('Saving workplan data:', data);
    console.log('Workplan ID:', workplanId, 'Is New:', isNew);

    if (!data.activity) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Activity harus diisi'
        });
        return;
    }

    showLoading();

    const url = isNew ? '/workplan/store' : `/workplan/${workplanId}`;
    const method = isNew ? 'POST' : 'PUT';

    $.ajax({
        url: url,
        method: method,
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                // Update row dengan data baru tanpa reload
                if (isNew) {
                    // Update row ID untuk workplan baru
                    row.attr('data-workplan-id', response.data.id);
                    row.removeClass('new-row');
                }
                
                // Update button actions
                updateRowActions(row, response.data);
                
                // Show success toast (non-blocking)
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Work plan berhasil disimpan',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            } else {
                showError(response.message || 'Gagal menyimpan work plan');
            }
        },
        error: function(xhr) {
            hideLoading();
            let errorMsg = 'Terjadi kesalahan saat menyimpan data';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            showError(errorMsg);
        }
    });
}

function updateRealization(row) {
    const workplanId = row.data('workplan-id');
    
    if (workplanId === 'new') {
        // For new rows, don't show error, just return silently
        return;
    }

    const data = {};
    
    // Collect realization months
    row.find('.real-month').each(function() {
        const month = $(this).data('month');
        data[`real_${month}`] = $(this).is(':checked') ? 1 : 0;
    });

    $.ajax({
        url: `/workplan/${workplanId}/update-realization`,
        method: 'PATCH',
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Show brief success toast (minimal distraction)
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: false,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                
                Toast.fire({
                    icon: 'success',
                    title: 'Realization updated',
                    text: ''
                });
                
                console.log('Realization updated successfully:', response.data);
            } else {
                showError(response.message || 'Failed to update realization');
            }
        },
        error: function(xhr) {
            console.error('Update realization error:', xhr);
            const errorMsg = xhr.responseJSON?.message || xhr.statusText || 'Unknown error';
            showError('Failed to update realization: ' + errorMsg);
        }
    });
}

function updateRowActions(row, workplan) {
    const actionsCell = row.find('.action-column');
    const isApproved = workplan.status === 'approved';
    
    let actionsHtml = '';
    
    if (isApproved) {
        actionsHtml = `
            <button class="btn btn-success btn-action btn-sm" disabled>
                <i class="bi bi-check-circle"></i> Approved
            </button>
        `;
    } else {
        actionsHtml = `
            <button class="btn btn-primary btn-action btn-save-workplan">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-danger btn-action btn-delete-workplan">
                <i class="bi bi-trash"></i>
            </button>
            ${workplan.status === 'draft' ? `
            <button class="btn btn-success btn-action btn-approve-workplan" data-id="${workplan.id}">
                <i class="bi bi-check"></i>
            </button>
            ` : ''}
        `;
    }
    
    actionsCell.html(actionsHtml);
}

function deleteWorkplan(row) {
    const workplanId = row.data('workplan-id');

    if (workplanId === 'new') {
        // Just remove the row for unsaved workplan
        row.fadeOut(300, function() {
            $(this).remove();
            // Check if table is empty, show no-data message
            const tbody = row.closest('tbody');
            if (tbody.find('tr').length === 0) {
                tbody.html(`
                    <tr class="no-data-row">
                        <td colspan="29" class="text-center text-muted py-3">
                            <i>Belum ada work plan. Klik tombol "Add Work Plan" untuk menambahkan.</i>
                        </td>
                    </tr>
                `);
            }
        });
        return;
    }

    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menghapus work plan ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();

            $.ajax({
                url: `/workplan/${workplanId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        // Remove row dengan animasi tanpa reload
                        row.fadeOut(300, function() {
                            $(this).remove();
                            // Check if table is empty, show no-data message
                            const tbody = row.closest('tbody');
                            if (tbody.find('tr').length === 0) {
                                tbody.html(`
                                    <tr class="no-data-row">
                                        <td colspan="29" class="text-center text-muted py-3">
                                            <i>Belum ada work plan. Klik tombol "Add Work Plan" untuk menambahkan.</i>
                                        </td>
                                    </tr>
                                `);
                            }
                        });
                        
                        // Show success toast
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Work plan berhasil dihapus',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    showError('Gagal menghapus work plan');
                }
            });
        }
    });
}

function approveWorkplan(workplanId) {
    Swal.fire({
        title: 'Konfirmasi Approval',
        text: 'Apakah Anda yakin ingin meng-approve work plan ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Approve!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();

            $.ajax({
                url: `/workplan/${workplanId}/approve`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        // Update row tanpa reload
                        const row = $(`tr[data-workplan-id="${workplanId}"]`);
                        
                        // Disable semua input
                        row.find('input').prop('readonly', true).prop('disabled', true);
                        
                        // Update action buttons
                        row.find('.action-column').html(`
                            <button class="btn btn-success btn-action btn-sm" disabled>
                                <i class="bi bi-check-circle"></i> Approved
                            </button>
                        `);
                        
                        // Add approved styling
                        row.addClass('table-success');
                        
                        // Show success toast
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Work plan berhasil di-approve',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    showError('Gagal approve work plan');
                }
            });
        }
    });
}

function toggleSection(button) {
    const targetId = button.data('target');
    const target = $(`#${targetId}`);
    const icon = button.find('i');

    target.toggleClass('show');
    
    if (target.hasClass('show')) {
        icon.removeClass('bi-plus-circle').addClass('bi-dash-circle');
        button.html('<i class="bi bi-dash-circle"></i> Collapse');
    } else {
        icon.removeClass('bi-dash-circle').addClass('bi-plus-circle');
        button.html('<i class="bi bi-plus-circle"></i> Expand');
    }
}

function calculateModalDuration() {
    const startDate = $('#schedule_start').val();
    const endDate = $('#schedule_end').val();
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const duration = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        $('#duration_days').val(duration > 0 ? duration : '');
    }
}

function calculateDuration(row) {
    const startDate = new Date(row.find('.schedule-start').val());
    const endDate = new Date(row.find('.schedule-end').val());

    if (startDate && endDate && startDate <= endDate) {
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        row.find('.duration-input').val(diffDays);
    }
}

function formatBudget(input) {
    const value = parseCurrency(input.val());
    if (value) {
        input.val(formatCurrency(value));
    }
}

function formatCurrency(value) {
    if (!value || value === 0) return '';
    return new Intl.NumberFormat('id-ID').format(value);
}

function parseCurrency(value) {
    if (!value) return 0;
    return parseFloat(value.toString().replace(/[^0-9.-]+/g, ''));
}

function getStatusBadge(status) {
    const badges = {
        draft: '<span class="badge bg-secondary status-badge">Draft</span>',
        pending: '<span class="badge bg-warning status-badge">Pending</span>',
        approved: '<span class="badge bg-success status-badge">Approved</span>',
        rejected: '<span class="badge bg-danger status-badge">Rejected</span>'
    };
    return badges[status] || badges.draft;
}

function resetFilters() {
    $('#filter_division').val('');
    $('#filter_year').val(new Date().getFullYear());
    $('#workplan-container').html(`
        <div class="no-data-message">
            <i class="bi bi-info-circle" style="font-size: 48px;"></i>
            <p class="mt-3">Silakan pilih Divisi dan Tahun kemudian klik "Load KPI Data" untuk menampilkan data KPI dan Work Plan</p>
        </div>
    `);
    currentKpiData = [];
    currentDivisionId = null;
    currentYear = null;
}

function showLoading() {
    $('#loadingOverlay').addClass('show');
}

function hideLoading() {
    $('#loadingOverlay').removeClass('show');
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message
    });
}
