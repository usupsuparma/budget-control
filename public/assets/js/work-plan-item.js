/**
 * Work Plan Budget Items Management
 * Dynamic category tabs and AJAX CRUD operations
 */

let currentParentCategory = null;
let currentChildCategory = null;
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
        selectParentCategory(categoryId);
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
    
    // Toggle expand/collapse for child categories
    $(document).on('click', '.expand-btn-item', function() {
        toggleChildCategory($(this));
    });
}

/**
 * Load categories from server
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
                    selectParentCategory(response.data[0].id);
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
 * Render parent category tabs (Level 1)
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
 * Select parent category and load children
 */
function selectParentCategory(categoryId) {
    currentParentCategory = categoryId;
    
    // Update active state
    $('.parent-category-tab').removeClass('active');
    $(`.parent-category-tab[data-category-id="${categoryId}"]`).addClass('active');
    
    // Find category data
    $.ajax({
        url: `/workplan/${WORKPLAN_ID}/item/categories`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const category = response.data.find(c => c.id == categoryId);
                if (category && category.children) {
                    renderChildCategories(category.children);
                    // Auto-select first child
                    if (category.children.length > 0) {
                        selectChildCategory(category.children[0].id);
                    }
                }
            }
        }
    });
}

/**
 * Render child category tabs (Level 2) with expand/collapse
 */
function renderChildCategories(children) {
    const container = $('#childCategoriesContainer');
    container.empty();

    if (children.length === 0) {
        container.html('<div class="text-center py-4 text-muted"><i class="bi bi-info-circle fa-2x mb-2"></i><p>No sub-categories available</p></div>');
        return;
    }

    children.forEach((child, index) => {
        const html = `
            <div class="child-category-section mb-3" data-category-id="${child.id}">
                <div class="child-category-header">
                    <div>
                        <strong>${child.name.toUpperCase()}</strong>
                    </div>
                    <button class="expand-btn-item collapsed" data-target="child-${child.id}">
                        <i class="bi bi-chevron-right"></i> Expand
                    </button>
                </div>
                <div id="child-${child.id}" class="collapse-section-item p-3" style="background: white; border-radius: 6px;">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div> Loading items...
                    </div>
                </div>
            </div>
        `;
        container.append(html);
    });
    
    // Auto-expand first child category
    if (children.length > 0) {
        const firstBtn = container.find('.expand-btn-item').first();
        setTimeout(() => {
            firstBtn.click();
        }, 100);
    }
}

/**
 * Select child category and load items
 */
function selectChildCategory(categoryId) {
    currentChildCategory = categoryId;
    
    // Load items
    loadItems(categoryId);
}

/**
 * Load items for selected category
 */
function loadItems(categoryId) {
    const targetContainer = $(`#child-${categoryId}`);
    
    if (!targetContainer.length) {
        showError('Container not found');
        return;
    }
    
    // Show loading in specific container
    targetContainer.html('<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> Loading items...</div>');
    
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
            targetContainer.html('<div class="text-center py-3 text-danger"><i class="bi bi-exclamation-circle"></i> Failed to load items</div>');
            showError('Failed to load items');
        }
    });
}

/**
 * Render items table
 */
function renderItemsTable(items, categoryId) {
    const container = $(`#child-${categoryId}`);
    
    let html = `
        <div class="mb-3">
            <button type="button" class="btn btn-primary btn-sm btn-add-item" data-category-id="${categoryId}" style="padding: 8px 16px; font-size: 13px;">
                <i class="bi bi-plus-circle me-2"></i> Add Item
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered items-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 100px; min-width: 100px;">Action</th>
                        <th rowspan="2" style="width: 200px;">Description</th>
                        <th rowspan="2" style="width: 80px;">Stock Code</th>
                        <th rowspan="2" style="width: 80px;">Budget Code</th>
                        <th rowspan="2" style="width: 100px;">Product Line</th>
                        <th rowspan="2" style="width: 80px;">Cost Center</th>
                        <th rowspan="2" style="width: 80px;">Beg Balance</th>
                        <th rowspan="2" style="width: 80px;">Cons Rate</th>
                        <th rowspan="2" style="width: 60px;">Unit</th>
                        <th rowspan="2" style="width: 100px;">Total</th>
                        <th colspan="12" class="month-header">Activity Quantities</th>
                    </tr>
                    <tr class="month-header">
                        <th>Jan</th>
                        <th>Feb</th>
                        <th>Mar</th>
                        <th>Apr</th>
                        <th>May</th>
                        <th>Jun</th>
                        <th>Jul</th>
                        <th>Aug</th>
                        <th>Sep</th>
                        <th>Oct</th>
                        <th>Nov</th>
                        <th>Dec</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody-${categoryId}">
    `;

    if (items.length === 0) {
        html += `
            <tr>
                <td colspan="22" class="no-data">
                    <i class="bi bi-inbox fa-2x mb-2 d-block"></i>
                    No items found. Click "Add Item" to create new budget item.
                </td>
            </tr>
        `;
    } else {
        items.forEach(item => {
            html += renderItemRow(item);
        });
    }

    html += `
                </tbody>
            </table>
        </div>
    `;

    container.html(html);
    
    // Initialize tooltips for dynamically added elements
    initializeTooltips();
}

/**
 * Render single item row
 */
function renderItemRow(item) {
    const isApproved = item.status === 'approved';
    const rowClass = isApproved ? 'table-success' : '';
    
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    
    let html = `
        <tr data-item-id="${item.id}" class="${rowClass}" data-category-id="${item.budget_category_id}">
            <td class="text-center action-column">
    `;
    
    if (isApproved) {
        html += `<span class="badge bg-success status-badge">Approved</span>`;
    } else {
        html += `
            <button type="button" class="btn btn-sm btn-primary btn-action-item btn-edit-item" data-item-id="${item.id}" title="Edit Item" data-bs-toggle="tooltip">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger btn-action-item btn-delete-item" data-item-id="${item.id}" title="Delete Item" data-bs-toggle="tooltip">
                <i class="bi bi-trash"></i>
            </button>
        `;
    }
    
    html += `
            </td>
            <td>${item.description}</td>
            <td>${item.stock_code || '-'}</td>
            <td>${item.budget_code || '-'}</td>
            <td>${item.product_line || '-'}</td>
            <td>${item.cost_center || '-'}</td>
            <td>${item.beg_balance || '-'}</td>
            <td>${item.cons_rate || '-'}</td>
            <td>${item.unit || '-'}</td>
            <td class="text-end">${parseFloat(item.total).toLocaleString('id-ID')}</td>
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
    currentChildCategory = categoryId;
    
    // Reset form
    $('#itemForm')[0].reset();
    $('#itemId').val('');
    $('#categoryId').val(categoryId);
    
    // Set modal title
    $('#itemModalLabel').text('Add Budget Item');
    
    // Populate budget codes
    populateBudgetCodes();
    
    // Reset all activity quantities to 0
    for (let i = 1; i <= 12; i++) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $(`#activity${months[i-1]}`).val(0);
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('itemModal'));
    modal.show();
}

/**
 * Open modal for editing existing item
 */
function openEditModal(itemId) {
    // Find item data from table row
    const row = $(`tr[data-item-id="${itemId}"]`);
    
    if (!row.length) {
        showError('Item not found');
        return;
    }
    
    // Get item data via AJAX to ensure we have the latest data
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
                $('#description').val(item.description);
                $('#stockCode').val(item.stock_code || '');
                $('#productLine').val(item.product_line || '');
                $('#costCenter').val(item.cost_center || '');
                $('#begBalance').val(item.beg_balance || '');
                $('#consRate').val(item.cons_rate || '');
                $('#unit').val(item.unit || '');
                $('#total').val(item.total);
                $('#notes').val(item.notes || '');
                
                // Populate budget codes and set selected
                populateBudgetCodes(item.budget_code);
                
                // Set activity quantities
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                months.forEach((month, index) => {
                    const key = month.toLowerCase();
                    $(`#activity${month}`).val(item[`activity_${key}`] || 0);
                });
                
                currentChildCategory = item.budget_category_id;
                
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
    
    // Collect data from form
    const data = {
        budget_category_id: $('#categoryId').val(),
        description: $('#description').val(),
        stock_code: $('#stockCode').val(),
        budget_code: $('#budgetCode').val(),
        product_line: $('#productLine').val(),
        cost_center: $('#costCenter').val(),
        beg_balance: $('#begBalance').val(),
        cons_rate: $('#consRate').val(),
        unit: $('#unit').val(),
        total: $('#total').val(),
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
                if (currentChildCategory) {
                    loadItems(currentChildCategory);
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
        confirmButtonText: 'Yes, delete it!'
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
                        if (currentChildCategory) {
                            loadItems(currentChildCategory);
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
 * Toggle expand/collapse for child category
 */
function toggleChildCategory(button) {
    const targetId = button.data('target');
    const target = $(`#${targetId}`);
    const categoryId = button.closest('.child-category-section').data('category-id');
    
    if (target.hasClass('show')) {
        // Collapse
        target.removeClass('show');
        button.addClass('collapsed');
        button.html('<i class="bi bi-chevron-right"></i> Expand');
    } else {
        // Expand
        target.addClass('show');
        button.removeClass('collapsed');
        button.html('<i class="bi bi-chevron-down"></i> Collapse');
        
        // Load items if not already loaded
        if (target.find('.text-muted').length > 0) {
            selectChildCategory(categoryId);
        }
    }
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
    // Remove old tooltips first
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        // Dispose existing tooltip if any
        const existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
        if (existingTooltip) {
            existingTooltip.dispose();
        }
        // Initialize new tooltip
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
