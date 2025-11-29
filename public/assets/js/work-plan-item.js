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

    // Child category tab click
    $(document).on('click', '.child-category-tab', function(e) {
        e.preventDefault();
        const categoryId = $(this).data('category-id');
        selectChildCategory(categoryId);
    });

    // Add new item
    $(document).on('click', '.btn-add-item', function() {
        addNewItemRow();
    });

    // Save item
    $(document).on('click', '.btn-save-item', function() {
        const row = $(this).closest('tr');
        saveItem(row);
    });

    // Edit item
    $(document).on('click', '.btn-edit-item', function() {
        const row = $(this).closest('tr');
        enableEditMode(row);
    });

    // Delete item
    $(document).on('click', '.btn-delete-item', function() {
        const row = $(this).closest('tr');
        deleteItem(row);
    });

    // Cancel edit
    $(document).on('click', '.btn-cancel-item', function() {
        const row = $(this).closest('tr');
        const itemId = row.data('item-id');
        if (itemId) {
            disableEditMode(row);
        } else {
            row.remove();
        }
    });

    // Auto-calculate total when cons_rate or beg_balance changes
    $(document).on('input', '.cons-rate-input, .beg-balance-input', function() {
        const row = $(this).closest('tr');
        calculateRowTotal(row);
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
 * Render child category tabs (Level 2)
 */
function renderChildCategories(children) {
    const container = $('#childCategoryTabs');
    container.empty();

    if (children.length === 0) {
        container.html('<li class="text-muted">No sub-categories available</li>');
        return;
    }

    children.forEach((child, index) => {
        const isActive = index === 0 ? 'active' : '';
        const html = `
            <li class="nav-item">
                <a class="nav-link child-category-tab ${isActive}" 
                   data-category-id="${child.id}" 
                   href="#" 
                   role="tab">
                    ${child.name.toUpperCase()}
                </a>
            </li>
        `;
        container.append(html);
    });
}

/**
 * Select child category and load items
 */
function selectChildCategory(categoryId) {
    currentChildCategory = categoryId;
    
    // Update active state
    $('.child-category-tab').removeClass('active');
    $(`.child-category-tab[data-category-id="${categoryId}"]`).addClass('active');
    
    // Load items
    loadItems(categoryId);
}

/**
 * Load items for selected category
 */
function loadItems(categoryId) {
    showLoading();
    
    $.ajax({
        url: `/workplan/${WORKPLAN_ID}/item/list`,
        method: 'GET',
        data: { category_id: categoryId },
        success: function(response) {
            if (response.success) {
                budgetCodesData = response.budgetCodes;
                renderItemsTable(response.data);
            }
            hideLoading();
        },
        error: function(xhr) {
            hideLoading();
            showError('Failed to load items');
        }
    });
}

/**
 * Render items table
 */
function renderItemsTable(items) {
    const container = $('#itemsContent');
    
    let html = `
        <div class="mb-3">
            <button type="button" class="btn btn-primary btn-sm btn-add-item">
                <i class="fas fa-plus me-1"></i> Add Item
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered items-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 40px;">Action</th>
                        <th rowspan="2" style="width: 200px;">Description</th>
                        <th rowspan="2" style="width: 80px;">Stock Code</th>
                        <th rowspan="2" style="width: 80px;">Budget Code</th>
                        <th rowspan="2" style="width: 100px;">Product Line</th>
                        <th rowspan="2" style="width: 80px;">Cost Center</th>
                        <th rowspan="2" style="width: 80px;">Beg Balance</th>
                        <th rowspan="2" style="width: 80px;">Cons Rate</th>
                        <th rowspan="2" style="width: 60px;">Unit</th>
                        <th rowspan="2" style="width: 100px;">Total</th>
                        <th colspan="12" class="month-header">Activities</th>
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
                <tbody id="itemsTableBody">
    `;

    if (items.length === 0) {
        html += `
            <tr>
                <td colspan="22" class="no-data">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
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
}

/**
 * Render single item row
 */
function renderItemRow(item) {
    const isApproved = item.status === 'approved';
    const disabled = isApproved ? 'disabled' : '';
    const rowClass = isApproved ? 'table-success' : '';
    
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    
    let html = `
        <tr data-item-id="${item.id}" class="${rowClass}" data-edit-mode="false">
            <td class="text-center action-column">
    `;
    
    if (isApproved) {
        html += `<span class="badge bg-success status-badge">Approved</span>`;
    } else {
        html += `
            <button type="button" class="btn btn-sm btn-primary btn-action-item btn-edit-item" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger btn-action-item btn-delete-item" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        `;
    }
    
    html += `
            </td>
            <td><input type="text" class="form-control form-control-sm description-input" value="${item.description}" ${disabled}></td>
            <td><input type="text" class="form-control form-control-sm stock-code-input" value="${item.stock_code || ''}" ${disabled}></td>
            <td>
                <select class="form-select form-select-sm budget-code-input" ${disabled}>
                    <option value="">Select...</option>
                    ${budgetCodesData.map(bc => `<option value="${bc.code}" ${item.budget_code === bc.code ? 'selected' : ''}>${bc.code} - ${bc.name}</option>`).join('')}
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm product-line-input" value="${item.product_line || ''}" ${disabled}></td>
            <td><input type="text" class="form-control form-control-sm cost-center-input" value="${item.cost_center || ''}" ${disabled}></td>
            <td><input type="text" class="form-control form-control-sm beg-balance-input" value="${item.beg_balance || ''}" ${disabled}></td>
            <td><input type="text" class="form-control form-control-sm cons-rate-input" value="${item.cons_rate || ''}" ${disabled}></td>
            <td><input type="text" class="form-control form-control-sm unit-input" value="${item.unit || ''}" ${disabled}></td>
            <td><input type="number" class="form-control form-control-sm total-input" value="${item.total}" ${disabled} readonly></td>
    `;
    
    months.forEach(month => {
        const checked = item[`activity_${month}`] == 1 ? 'checked' : '';
        html += `<td class="text-center"><input type="checkbox" class="activity-checkbox activity-${month}" ${checked} ${disabled}></td>`;
    });
    
    html += `</tr>`;
    
    return html;
}

/**
 * Add new item row
 */
function addNewItemRow() {
    const tbody = $('#itemsTableBody');
    
    // Remove "no data" message if exists
    tbody.find('.no-data').closest('tr').remove();
    
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    
    let html = `
        <tr class="new-row" data-edit-mode="true">
            <td class="text-center action-column">
                <button type="button" class="btn btn-sm btn-success btn-action-item btn-save-item" title="Save">
                    <i class="fas fa-save"></i>
                </button>
                <button type="button" class="btn btn-sm btn-secondary btn-action-item btn-cancel-item" title="Cancel">
                    <i class="fas fa-times"></i>
                </button>
            </td>
            <td><input type="text" class="form-control form-control-sm description-input" placeholder="Description"></td>
            <td><input type="text" class="form-control form-control-sm stock-code-input" placeholder="Stock Code"></td>
            <td>
                <select class="form-select form-select-sm budget-code-input">
                    <option value="">Select...</option>
                    ${budgetCodesData.map(bc => `<option value="${bc.code}">${bc.code} - ${bc.name}</option>`).join('')}
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm product-line-input" placeholder="Product Line"></td>
            <td><input type="text" class="form-control form-control-sm cost-center-input" placeholder="Cost Center"></td>
            <td><input type="text" class="form-control form-control-sm beg-balance-input" placeholder="Beg Balance"></td>
            <td><input type="text" class="form-control form-control-sm cons-rate-input" placeholder="Cons Rate"></td>
            <td><input type="text" class="form-control form-control-sm unit-input" placeholder="Unit"></td>
            <td><input type="number" class="form-control form-control-sm total-input" value="0" readonly></td>
    `;
    
    months.forEach(month => {
        html += `<td class="text-center"><input type="checkbox" class="activity-checkbox activity-${month}"></td>`;
    });
    
    html += `</tr>`;
    
    tbody.prepend(html);
}

/**
 * Enable edit mode for existing row
 */
function enableEditMode(row) {
    row.attr('data-edit-mode', 'true');
    row.find('input, select').prop('disabled', false);
    
    const actionColumn = row.find('.action-column');
    actionColumn.html(`
        <button type="button" class="btn btn-sm btn-success btn-action-item btn-save-item" title="Save">
            <i class="fas fa-save"></i>
        </button>
        <button type="button" class="btn btn-sm btn-secondary btn-action-item btn-cancel-item" title="Cancel">
            <i class="fas fa-times"></i>
        </button>
    `);
}

/**
 * Disable edit mode
 */
function disableEditMode(row) {
    row.attr('data-edit-mode', 'false');
    row.find('input, select').prop('disabled', true);
    
    const itemId = row.data('item-id');
    const actionColumn = row.find('.action-column');
    actionColumn.html(`
        <button type="button" class="btn btn-sm btn-primary btn-action-item btn-edit-item" title="Edit">
            <i class="fas fa-edit"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger btn-action-item btn-delete-item" title="Delete">
            <i class="fas fa-trash"></i>
        </button>
    `);
}

/**
 * Save item (create or update)
 */
function saveItem(row) {
    const itemId = row.data('item-id');
    const isNew = !itemId;
    
    // Collect data
    const data = {
        budget_category_id: currentChildCategory,
        description: row.find('.description-input').val(),
        stock_code: row.find('.stock-code-input').val(),
        budget_code: row.find('.budget-code-input').val(),
        product_line: row.find('.product-line-input').val(),
        cost_center: row.find('.cost-center-input').val(),
        beg_balance: row.find('.beg-balance-input').val(),
        cons_rate: row.find('.cons-rate-input').val(),
        unit: row.find('.unit-input').val(),
        total: row.find('.total-input').val(),
        activity_jan: row.find('.activity-jan').is(':checked') ? 1 : 0,
        activity_feb: row.find('.activity-feb').is(':checked') ? 1 : 0,
        activity_mar: row.find('.activity-mar').is(':checked') ? 1 : 0,
        activity_apr: row.find('.activity-apr').is(':checked') ? 1 : 0,
        activity_may: row.find('.activity-may').is(':checked') ? 1 : 0,
        activity_jun: row.find('.activity-jun').is(':checked') ? 1 : 0,
        activity_jul: row.find('.activity-jul').is(':checked') ? 1 : 0,
        activity_aug: row.find('.activity-aug').is(':checked') ? 1 : 0,
        activity_sep: row.find('.activity-sep').is(':checked') ? 1 : 0,
        activity_oct: row.find('.activity-oct').is(':checked') ? 1 : 0,
        activity_nov: row.find('.activity-nov').is(':checked') ? 1 : 0,
        activity_dec: row.find('.activity-dec').is(':checked') ? 1 : 0,
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
                // Reload items
                loadItems(currentChildCategory);
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
 * Delete item
 */
function deleteItem(row) {
    const itemId = row.data('item-id');
    
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
                        loadItems(currentChildCategory);
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
 * Calculate row total based on beg_balance and cons_rate
 */
function calculateRowTotal(row) {
    const begBalance = parseFloat(row.find('.beg-balance-input').val()) || 0;
    const consRate = parseFloat(row.find('.cons-rate-input').val()) || 0;
    const total = begBalance * consRate;
    row.find('.total-input').val(total.toFixed(2));
}

/**
 * Get icon for category
 */
function getCategoryIcon(code) {
    const icons = {
        '1': 'fas fa-box',
        '2': 'fas fa-warehouse',
        '3': 'fas fa-tools',
        '4': 'fas fa-sync-alt',
        '5': 'fas fa-chart-line',
        '6': 'fas fa-forward',
        '7': 'fas fa-calendar-alt'
    };
    return icons[code] || 'fas fa-folder';
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
