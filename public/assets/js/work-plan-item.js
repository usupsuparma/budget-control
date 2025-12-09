/**
 * Work Plan Budget Items Management
 * Simplified version - Parent categories only
 */

let currentCategory = null;
let budgetCodesData = [];

$(document).ready(function() {
    loadCategories();
    initializeEventListeners();
});

/**
 * Initialize event listeners
 */
function initializeEventListeners() {
    // Parent category tab click
    $(document).on('click', '.parent-category-tab', function(e) {
        e.preventDefault();
        const categoryId = $(this).data('category-id');
        selectCategory(categoryId);
    });

    // Add new item - open modal
    $(document).on('click', '.btn-add-item', function() {
        const categoryId = $(this).data('category-id');
        openAddModal(categoryId);
    });

    // Edit item - open modal
    $(document).on('click', '.btn-edit-item', function() {
        const itemId = $(this).data('item-id');
        openEditModal(itemId);
    });

    // Delete item
    $(document).on('click', '.btn-delete-item', function() {
        const itemId = $(this).data('item-id');
        deleteItemById(itemId);
    });

    // Modal form submit
    $('#itemForm').on('submit', function(e) {
        e.preventDefault();
        saveItemFromModal();
    });
    
    // Auto-calculate total when cons_rate or beg_balance changes in modal
    $('#begBalance, #consRate').on('input', function() {
        calculateModalTotal();
    });
    
    // Calculate total activity when month inputs change
    $(document).on('input', '.month-input', function() {
        calculateTotalActivity();
    });
}

/**
 * Load categories from server (parent only)
 */
function loadCategories() {
    showLoading();
    
    $.ajax({
        url: `/workplan/${WORKPLAN_ID}/item/categories`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderParentCategories(response.data);
                // Auto-select first parent category
                if (response.data.length > 0) {
                    selectCategory(response.data[0].id);
                }
            }
            hideLoading();
        },
        error: function(xhr) {
            hideLoading();
            showError('Failed to load categories');
        }
    });
}

/**
 * Render parent category tabs
 */
function renderParentCategories(categories) {
    const container = $('#parentCategoryTabs');
    container.empty();

    categories.forEach((category, index) => {
        const icon = getCategoryIcon(category.code);
        const isActive = index === 0 ? 'active' : '';
        
        const html = `
            <li class="nav-item">
                <a class="nav-link parent-category-tab ${isActive}" 
                   data-category-id="${category.id}" 
                   href="#" 
                   role="tab">
                    <i class="${icon} me-2"></i> ${category.name.toUpperCase()}
                </a>
            </li>
        `;
        container.append(html);
    });
}

/**
 * Select category and load items
 */
function selectCategory(categoryId) {
    currentCategory = categoryId;
    
    // Update active state
    $('.parent-category-tab').removeClass('active');
    $(`.parent-category-tab[data-category-id="${categoryId}"]`).addClass('active');
    
    // Load items for this category
    loadItems(categoryId);
}

/**
 * Load items for selected category
 */
function loadItems(categoryId) {
    const targetContainer = $('#itemsContainer');
    
    // Show loading
    targetContainer.html('<div class="text-center py-5 text-muted"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Loading items...</p></div>');
    
    $.ajax({
        url: `/workplan/${WORKPLAN_ID}/item/list`,
        method: 'GET',
        data: { category_id: categoryId },
        success: function(response) {
            if (response.success) {
                budgetCodesData = response.budgetCodes;
                renderItemsTable(response.data, categoryId);
            }
        },
        error: function(xhr) {
            targetContainer.html('<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-circle fa-3x mb-3"></i><p>Failed to load items</p></div>');
            showError('Failed to load items');
        }
    });
}

/**
 * Render items table
 */
function renderItemsTable(items, categoryId) {
    const container = $('#itemsContainer');
    
    let html = `
        <div class="mb-3">
            <button type="button" class="btn btn-primary btn-add-item" data-category-id="${categoryId}">
                <i class="bi bi-plus-circle me-2"></i> Add New Item
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover items-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 50px;">No</th>
                        <th rowspan="2" style="width: 100px;">Action</th>
                        <th rowspan="2" style="width: 100px;">Category Type</th>
                        <th rowspan="2" style="width: 200px;">Description</th>
                        <th rowspan="2" style="width: 80px;">Stock Code</th>
                        <th rowspan="2" style="width: 100px;">Budget Code</th>
                        <th rowspan="2" style="width: 100px;">Product Line</th>
                        <th rowspan="2" style="width: 80px;">Cost Center</th>
                        <th rowspan="2" style="width: 80px;">Unit</th>
                        <th rowspan="2" style="width: 120px;">Beg Balance</th>
                        <th rowspan="2" style="width: 120px;">Cons Rate</th>
                        <th rowspan="2" style="width: 120px;">Total</th>
                        <th rowspan="2" style="width: 120px;">Price Est.</th>
                        <th rowspan="2" style="width: 150px;">Price Est. Desc.</th>
                        <th colspan="12" class="month-header text-center">Activity Quantities</th>
                    </tr>
                    <tr class="month-header">
                        <th class="text-center">Jan</th>
                        <th class="text-center">Feb</th>
                        <th class="text-center">Mar</th>
                        <th class="text-center">Apr</th>
                        <th class="text-center">May</th>
                        <th class="text-center">Jun</th>
                        <th class="text-center">Jul</th>
                        <th class="text-center">Aug</th>
                        <th class="text-center">Sep</th>
                        <th class="text-center">Oct</th>
                        <th class="text-center">Nov</th>
                        <th class="text-center">Dec</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (items.length === 0) {
        html += `
            <tr>
                <td colspan="27" class="no-data">
                    <i class="bi bi-inbox fa-3x mb-3 d-block"></i>
                    <p class="mb-0">No items found. Click "Add New Item" to create budget item.</p>
                </td>
            </tr>
        `;
    } else {
        items.forEach((item, index) => {
            html += renderItemRow(item, index + 1);
        });
    }

    html += `
                </tbody>
            </table>
        </div>
    `;

    container.html(html);
    initializeTooltips();
}

/**
 * Render single item row
 */
function renderItemRow(item, rowNumber) {
    const isApproved = item.status === 'approved';
    const rowClass = isApproved ? 'table-success' : '';
    
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    
    // Category badge colors
    const categoryColors = {
        'Routine': 'primary',
        'Carry Over': 'warning',
        'Turn Around': 'info',
        'Multi Year': 'success'
    };
    const categoryBadgeColor = categoryColors[item.category_type] || 'secondary';
    
    let html = `
        <tr data-item-id="${item.id}" class="${rowClass}" data-category-id="${item.budget_category_id}">
            <td class="text-center">${rowNumber}</td>
            <td class="text-center action-column">
    `;
    
    if (isApproved) {
        html += `<span class="badge bg-success status-badge">Approved</span>`;
    } else {
        html += `
            <button type="button" class="btn btn-sm btn-primary btn-action-item btn-edit-item" data-item-id="${item.id}" title="Edit" data-bs-toggle="tooltip">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger btn-action-item btn-delete-item" data-item-id="${item.id}" title="Delete" data-bs-toggle="tooltip">
                <i class="bi bi-trash"></i>
            </button>
        `;
    }
    
    html += `
            </td>
            <td><span class="badge bg-${categoryBadgeColor}">${item.category_type || 'Routine'}</span></td>
            <td>${item.description || '-'}</td>
            <td>${item.stock_code || '-'}</td>
            <td>${item.budget_code || '-'}</td>
            <td>${item.product_line || '-'}</td>
            <td>${item.cost_center || '-'}</td>
            <td>${item.unit || '-'}</td>
            <td>${item.beg_balance || '-'}</td>
            <td>${item.cons_rate || '-'}</td>
            <td class="text-end fw-bold">${item.total ? parseFloat(item.total).toLocaleString('id-ID') : '0'}</td>
            <td class="text-end">${item.price_estimation ? parseFloat(item.price_estimation).toLocaleString('id-ID') : '-'}</td>
            <td style="font-size: 10px;">${item.price_estimation_description || '-'}</td>
    `;
    
    months.forEach(month => {
        const qty = item[`activity_${month}`] || 0;
        const qtyClass = qty > 0 ? 'fw-bold text-primary' : 'text-muted';
        html += `<td class="text-center ${qtyClass}">${qty}</td>`;
    });
    
    html += `</tr>`;
    
    return html;
}

/**
 * Open modal for adding new item
 */
function openAddModal(categoryId) {
    currentCategory = categoryId;
    
    // Reset form
    $('#itemForm')[0].reset();
    $('#itemId').val('');
    $('#categoryId').val(categoryId);
    
    // Set default category radio to Routine
    $('#categoryRoutine').prop('checked', true);
    
    // Set modal title
    $('#itemModalLabel').text('Add Budget Item');
    
    // Populate budget codes
    populateBudgetCodes();
    
    // Reset all activity quantities to 0
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    months.forEach(month => {
        $(`#activity${month}`).val(0);
    });
    
    // Reset total activity
    $('#totalActivity').text('0');
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('itemModal'));
    modal.show();
}

/**
 * Open modal for editing existing item
 */
function openEditModal(itemId) {
    const row = $(`tr[data-item-id="${itemId}"]`);
    
    if (!row.length) {
        showError('Item not found');
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/workplan/${WORKPLAN_ID}/item/list`,
        method: 'GET',
        data: { category_id: row.data('category-id') },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                const item = response.data.find(i => i.id == itemId);
                
                if (!item) {
                    showError('Item not found');
                    return;
                }
                
                // Populate form
                $('#itemId').val(item.id);
                $('#categoryId').val(item.budget_category_id);
                
                // Set category radio button
                const categoryValue = item.category_type || 'Routine';
                $('input[name="category_type"]').prop('checked', false);
                $(`input[name="category_type"][value="${categoryValue}"]`).prop('checked', true);
                
                $('#description').val(item.description);
                $('#stockCode').val(item.stock_code || '');
                $('#productLine').val(item.product_line || '');
                $('#costCenter').val(item.cost_center || '');
                $('#begBalance').val(item.beg_balance || '');
                $('#consRate').val(item.cons_rate || '');
                $('#unit').val(item.unit || '');
                $('#total').val(item.total);
                $('#priceEstimation').val(item.price_estimation || '');
                $('#priceEstimationDescription').val(item.price_estimation_description || '');
                $('#notes').val(item.notes || '');
                
                // Populate budget codes and set selected
                populateBudgetCodes(item.budget_code);
                
                // Set activity quantities
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                months.forEach(month => {
                    const key = month.toLowerCase();
                    $(`#activity${month}`).val(item[`activity_${key}`] || 0);
                });
                
                // Calculate and display total activity
                calculateTotalActivity();
                
                currentCategory = item.budget_category_id;
                
                // Set modal title
                $('#itemModalLabel').text('Edit Budget Item');
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('itemModal'));
                modal.show();
            }
        },
        error: function(xhr) {
            hideLoading();
            showError('Failed to load item data');
        }
    });
}

/**
 * Populate budget codes dropdown
 */
function populateBudgetCodes(selectedCode = '') {
    const select = $('#budgetCode');
    select.empty();
    select.append('<option value="">Select Budget Code...</option>');
    
    budgetCodesData.forEach(bc => {
        const selected = bc.code === selectedCode ? 'selected' : '';
        select.append(`<option value="${bc.code}" ${selected}>${bc.code} - ${bc.name}</option>`);
    });
}

/**
 * Save item from modal
 */
function saveItemFromModal() {
    const itemId = $('#itemId').val();
    const isNew = !itemId;
    
    // Get selected category radio button
    const selectedCategory = $('input[name="category_type"]:checked').val();
    
    // Collect data from form
    const data = {
        budget_category_id: $('#categoryId').val(),
        category_type: selectedCategory,
        description: $('#description').val(),
        stock_code: $('#stockCode').val(),
        budget_code: $('#budgetCode').val(),
        product_line: $('#productLine').val(),
        cost_center: $('#costCenter').val(),
        beg_balance: $('#begBalance').val(),
        cons_rate: $('#consRate').val(),
        unit: $('#unit').val(),
        total: $('#total').val(),
        price_estimation: $('#priceEstimation').val(),
        price_estimation_description: $('#priceEstimationDescription').val(),
        notes: $('#notes').val(),
        activity_jan: parseInt($('#activityJan').val()) || 0,
        activity_feb: parseInt($('#activityFeb').val()) || 0,
        activity_mar: parseInt($('#activityMar').val()) || 0,
        activity_apr: parseInt($('#activityApr').val()) || 0,
        activity_may: parseInt($('#activityMay').val()) || 0,
        activity_jun: parseInt($('#activityJun').val()) || 0,
        activity_jul: parseInt($('#activityJul').val()) || 0,
        activity_aug: parseInt($('#activityAug').val()) || 0,
        activity_sep: parseInt($('#activitySep').val()) || 0,
        activity_oct: parseInt($('#activityOct').val()) || 0,
        activity_nov: parseInt($('#activityNov').val()) || 0,
        activity_dec: parseInt($('#activityDec').val()) || 0,
    };
    
    // Validate
    if (!data.description) {
        showError('Description is required');
        return;
    }
    
    showLoading();
    
    const url = isNew 
        ? `/workplan/${WORKPLAN_ID}/item`
        : `/workplan/${WORKPLAN_ID}/item/${itemId}`;
    
    const method = isNew ? 'POST' : 'PUT';
    
    $.ajax({
        url: url,
        method: method,
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        data: data,
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSuccess(response.message);
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('itemModal'));
                modal.hide();
                
                // Reload items in current category
                if (currentCategory) {
                    loadItems(currentCategory);
                }
            }
        },
        error: function(xhr) {
            hideLoading();
            const message = xhr.responseJSON?.message || 'Failed to save item';
            showError(message);
        }
    });
}

/**
 * Delete item by ID
 */
function deleteItemById(itemId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This budget item will be deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();
            
            $.ajax({
                url: `/workplan/${WORKPLAN_ID}/item/${itemId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        showSuccess(response.message);
                        // Reload items in current category
                        if (currentCategory) {
                            loadItems(currentCategory);
                        }
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    const message = xhr.responseJSON?.message || 'Failed to delete item';
                    showError(message);
                }
            });
        }
    });
}

/**
 * Calculate total in modal based on beg_balance and cons_rate
 */
function calculateModalTotal() {
    const begBalance = parseFloat($('#begBalance').val()) || 0;
    const consRate = parseFloat($('#consRate').val()) || 0;
    const total = begBalance * consRate;
    $('#total').val(total.toFixed(2));
}

/**
 * Calculate total activity from all months
 */
function calculateTotalActivity() {
    let total = 0;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    months.forEach(month => {
        const value = parseInt($(`#activity${month}`).val()) || 0;
        total += value;
    });
    
    $('#totalActivity').text(total.toLocaleString('id-ID'));
}

/**
 * Get icon for category
 */
function getCategoryIcon(code) {
    const icons = {
        '1': 'bi bi-box-seam',
        '2': 'bi bi-building',
        '3': 'bi bi-tools',
        '4': 'bi bi-arrow-repeat',
        '5': 'bi bi-graph-up-arrow',
        '6': 'bi bi-skip-forward',
        '7': 'bi bi-calendar-range'
    };
    return icons[code] || 'bi bi-folder';
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        const existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
        if (existingTooltip) {
            existingTooltip.dispose();
        }
        new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
            placement: 'top'
        });
    });
}

/**
 * UI Helper Functions
 */
function showLoading() {
    $('#loadingOverlay').addClass('show');
}

function hideLoading() {
    $('#loadingOverlay').removeClass('show');
}

function showSuccess(message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: message,
        showConfirmButton: false,
        timer: 3000
    });
}

function showError(message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: message,
        showConfirmButton: false,
        timer: 3000
    });
}
