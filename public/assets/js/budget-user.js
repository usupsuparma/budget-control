/**
 * Budget User JavaScript
 * Manages budget user interface with workplan selection and budget items
 */

let selectedWorkplanId = null;
let currentCategory = null;
let budgetCodesData = [];

$(document).ready(function() {
    initializeEventListeners();
    checkFilterValues(); // Check on page load
});

/**
 * Initialize all event listeners
 */
function initializeEventListeners() {
    // Filter change handlers
    $('#divisionFilter, #yearFilter').on('change', function() {
        const division = $('#divisionFilter').val();
        const year = $('#yearFilter').val();
        $('#selectWorkplanBtn').prop('disabled', !division || !year);
    });

    // Select workplan button
    $('#selectWorkplanBtn').on('click', function() {
        loadWorkplans();
    });

    // Change workplan button
    $('#changeWorkplanBtn').on('click', function() {
        loadWorkplans();
    });

    // Confirm workplan selection
    $('#confirmWorkplanBtn').on('click', function() {
        confirmWorkplanSelection();
    });

    // Save item button
    $('#saveItemBtn').on('click', function() {
        saveItem();
    });

    // Reset item button
    $('#resetItemBtn').on('click', function() {
        resetItemForm();
    });

    // Modal hidden event
    $('#itemModal').on('hidden.bs.modal', function() {
        resetItemForm();
    });
}

/**
 * Check if both filters have values and enable/disable button
 */
function checkFilterValues() {
    const divisionId = $('#divisionFilter').val();
    const year = $('#yearFilter').val();
    
    if (divisionId && year) {
        $('#selectWorkplanBtn').prop('disabled', false);
    } else {
        $('#selectWorkplanBtn').prop('disabled', true);
    }
}

/**
 * Load workplans based on division and year
 */
function loadWorkplans() {
    const divisionId = $('#divisionFilter').val();
    const year = $('#yearFilter').val();

    if (!divisionId || !year) {
        showToast('Please select Division and Year', 'error');
        return;
    }

    showLoading();

    $.ajax({
        url: '/budget-user/workplans',
        method: 'GET',
        data: {
            division_id: divisionId,
            year: year
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                renderWorkplans(response.data);
                $('#workplanModal').modal('show');
            } else {
                showToast(response.message || 'Failed to load workplans', 'error');
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('Error loading workplans', 'error');
            console.error('Error:', xhr.responseText);
        }
    });
}

/**
 * Render workplans in modal
 */
function renderWorkplans(workplans) {
    const container = $('#workplanList');
    container.empty();

    if (workplans.length === 0) {
        $('#noWorkplanData').show();
        container.hide();
        return;
    }

    $('#noWorkplanData').hide();
    container.show();

    workplans.forEach(workplan => {
        const department = workplan.kpi_department?.department?.name || '-';
        const section = workplan.kpi_section?.section?.name || '-';
        const budget = workplan.budget ? formatCurrency(workplan.budget) : '-';
        
        const card = `
            <div class="col-md-6">
                <div class="card workplan-card" data-workplan-id="${workplan.id}">
                    <div class="card-body">
                        <h6 class="card-title">${workplan.activity}</h6>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="bi bi-building me-1"></i>${department}<br>
                                <i class="bi bi-diagram-3 me-1"></i>${section}<br>
                                <i class="bi bi-currency-dollar me-1"></i>Budget: ${budget}
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        `;
        container.append(card);
    });

    // Handle workplan card click
    $('.workplan-card').on('click', function() {
        $('.workplan-card').removeClass('selected');
        $(this).addClass('selected');
        selectedWorkplanId = $(this).data('workplan-id');
        $('#confirmWorkplanBtn').prop('disabled', false);
    });
}

/**
 * Confirm workplan selection
 */
function confirmWorkplanSelection() {
    if (!selectedWorkplanId) {
        showToast('Please select a work plan', 'warning');
        return;
    }

    const selectedCard = $(`.workplan-card[data-workplan-id="${selectedWorkplanId}"]`);
    const activity = selectedCard.find('.card-title').text();
    const details = selectedCard.find('.card-text small').html().replace(/<br>/g, ' | ');

    // Update UI
    $('#selectedWorkplanActivity').text(activity);
    $('#selectedWorkplanDetails').html(details);
    
    $('#workplanPlaceholder').hide();
    $('#workplanInfoSection').show();
    $('#budgetItemsSection').show();

    // Close modal
    $('#workplanModal').modal('hide');

    // Load categories
    loadCategories();

    showToast('Work plan selected successfully', 'success');
}

/**
 * Load categories for selected workplan
 */
function loadCategories() {
    if (!selectedWorkplanId) return;

    showLoading();

    $.ajax({
        url: `/budget-user/${selectedWorkplanId}/categories`,
        method: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                renderParentCategories(response.data);
            } else {
                showToast(response.message || 'Failed to load categories', 'error');
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('Error loading categories', 'error');
            console.error('Error:', xhr.responseText);
        }
    });
}

/**
 * Render parent categories
 */
function renderParentCategories(categories) {
    const container = $('#parentCategoryTabs');
    container.empty();

    categories.forEach((category, index) => {
        const active = index === 0 ? 'active' : '';
        container.append(`
            <button class="nav-link ${active}" 
                    data-category-id="${category.id}" 
                    onclick="selectCategory(${category.id})">
                ${category.name}
            </button>
        `);
    });

    // Load first category by default
    if (categories.length > 0) {
        selectCategory(categories[0].id);
    }
}

/**
 * Select category and load items
 */
function selectCategory(categoryId) {
    currentCategory = categoryId;
    $('.category-tabs .nav-link').removeClass('active');
    $(`.category-tabs .nav-link[data-category-id="${categoryId}"]`).addClass('active');
    loadItems(categoryId);
}

/**
 * Load items for category
 */
function loadItems(categoryId) {
    if (!selectedWorkplanId) return;

    showLoading();

    $.ajax({
        url: `/budget-user/${selectedWorkplanId}/items`,
        method: 'GET',
        data: {
            category_id: categoryId
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                budgetCodesData = response.budgetCodes || [];
                renderItemsTable(response.data, categoryId);
            } else {
                showToast(response.message || 'Failed to load items', 'error');
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('Error loading items', 'error');
            console.error('Error:', xhr.responseText);
        }
    });
}

/**
 * Render items table
 */
function renderItemsTable(items, categoryId) {
    const container = $('#categoryContent');
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    
    let html = `
        <div class="mb-3">
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddItemModal(${categoryId})">
                <i class="bi bi-plus-circle me-2"></i>Add Item
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered items-table">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Actions</th>
                        <th rowspan="2">Type</th>
                        <th rowspan="2">Description</th>
                        <th rowspan="2">Stock Code</th>
                        <th rowspan="2">Budget Code</th>
                        <th rowspan="2">Product Line</th>
                        <th rowspan="2">Cost Center</th>
                        <th rowspan="2">Beg. Balance</th>
                        <th rowspan="2">Cons. Rate</th>
                        <th rowspan="2">Unit</th>
                        <th rowspan="2">Total</th>
                        <th colspan="12" class="month-header">Monthly Activities</th>
                        <th rowspan="2">Price Est.</th>
                        <th rowspan="2">Price Est. Desc.</th>
                    </tr>
                    <tr>
                        ${months.map(m => `<th class="month-header">${m.toUpperCase()}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
    `;

    if (items.length === 0) {
        html += `
            <tr>
                <td colspan="27" class="no-data">
                    <i class="bi bi-inbox fs-3 text-muted"></i>
                    <p class="mt-2">No budget items yet. Click "Add Item" to create one.</p>
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
}

/**
 * Render single item row
 */
function renderItemRow(item, rowNumber) {
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    const isApproved = item.status === 'approved';
    const rowClass = isApproved ? 'table-success' : '';
    
    let html = `
        <tr data-item-id="${item.id}" class="${rowClass}">
            <td class="text-center">${rowNumber}</td>
            <td class="text-center action-column">
    `;

    if (isApproved) {
        html += `<span class="badge bg-success status-badge">Approved</span>`;
    } else {
        html += `
            <button type="button" class="btn btn-sm btn-primary btn-action-item" onclick="editItem(${item.id})" title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger btn-action-item" onclick="deleteItem(${item.id})" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        `;
    }

    html += `
            </td>
            <td style="font-size: 10px;">${item.category_type || '-'}</td>
            <td style="font-size: 10px;">${item.description || '-'}</td>
            <td style="font-size: 10px;">${item.stock_code || '-'}</td>
            <td style="font-size: 10px;">${item.budget_code || '-'}</td>
            <td style="font-size: 10px;">${item.product_line || '-'}</td>
            <td style="font-size: 10px;">${item.cost_center || '-'}</td>
            <td style="font-size: 10px;">${item.beg_balance || '-'}</td>
            <td style="font-size: 10px;">${item.cons_rate || '-'}</td>
            <td style="font-size: 10px;">${item.unit || '-'}</td>
            <td class="text-end" style="font-size: 10px;">${item.total || '-'}</td>
    `;

    months.forEach(month => {
        const qty = item[`activity_${month}`] || 0;
        const qtyClass = qty > 0 ? 'bg-light' : '';
        html += `<td class="text-center ${qtyClass}">${qty}</td>`;
    });

    html += `
            <td class="text-end" style="font-size: 10px;">${item.price_estimation ? formatCurrency(item.price_estimation) : '-'}</td>
            <td style="font-size: 10px;">${item.price_estimation_description || '-'}</td>
        </tr>
    `;

    return html;
}

/**
 * Open add item modal
 */
function openAddItemModal(categoryId) {
    resetItemForm();
    $('#itemModalLabel').html('<i class="bi bi-plus-circle me-2"></i>Add Budget Item');
    $('#budgetCategoryId').val(categoryId);
    loadBudgetCodes();
    initializeCostCenterDropdown();
    $('#itemModal').modal('show');
}

/**
 * Edit item
 */
function editItem(itemId) {
    showLoading();

    $.ajax({
        url: `/budget-user/${selectedWorkplanId}/items`,
        method: 'GET',
        data: {
            category_id: currentCategory
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                // Store budget codes data for use in populateItemForm
                budgetCodesData = response.budgetCodes || [];
                
                const item = response.data.find(i => i.id === itemId);
                if (item) {
                    $('#itemModalLabel').html('<i class="bi bi-pencil me-2"></i>Edit Budget Item');
                    loadBudgetCodes();
                    initializeCostCenterDropdown();
                    populateItemForm(item);
                    $('#itemModal').modal('show');
                }
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('Error loading item data', 'error');
        }
    });
}

/**
 * Populate item form with data
 */
function populateItemForm(item) {
    $('#itemId').val(item.id);
    $('#budgetCategoryId').val(item.budget_category_id);
    $('#categoryType').val(item.category_type);
    $('#description').val(item.description);
    $('#stockCode').val(item.stock_code);
    
    // Set Budget Code using Choices
    const budgetCodeSelect = document.getElementById('budgetCode');
    if (budgetCodeSelect.choicesInstance && item.budget_code) {
        budgetCodeSelect.choicesInstance.setChoiceByValue(item.budget_code);
    }
    
    $('#productLine').val(item.product_line);
    
    // Set Cost Center using Choices
    const costCenterSelect = document.getElementById('costCenter');
    if (costCenterSelect.choicesInstance && item.cost_center) {
        costCenterSelect.choicesInstance.setChoiceByValue(item.cost_center);
    }
    
    $('#begBalance').val(item.beg_balance);
    $('#consRate').val(item.cons_rate);
    $('#unit').val(item.unit);
    $('#total').val(item.total);
    $('#priceEstimation').val(item.price_estimation);
    $('#priceEstimationDescription').val(item.price_estimation_description);

    // Monthly activities
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    months.forEach(month => {
        $(`input[name="activity_${month}"]`).val(item[`activity_${month}`] || 0);
    });

    $('textarea[name="notes"]').val(item.notes);
}

/**
 * Reset item form
 */
function resetItemForm() {
    $('#itemForm')[0].reset();
    $('#itemId').val('');
    $('#budgetCategoryId').val('');
    
    // Destroy Choices instances if they exist
    const budgetCodeSelect = document.getElementById('budgetCode');
    if (budgetCodeSelect && budgetCodeSelect.choicesInstance) {
        budgetCodeSelect.choicesInstance.destroy();
        budgetCodeSelect.choicesInstance = null;
    }
    
    const costCenterSelect = document.getElementById('costCenter');
    if (costCenterSelect && costCenterSelect.choicesInstance) {
        costCenterSelect.choicesInstance.destroy();
        costCenterSelect.choicesInstance = null;
    }
}

/**
 * Initialize Cost Center dropdown with Choices.js
 */
function initializeCostCenterDropdown() {
    const select = document.getElementById('costCenter');
    
    // Destroy existing Choices instance if it exists
    if (select.choicesInstance) {
        select.choicesInstance.destroy();
    }
    
    // Initialize Choices.js for searchable dropdown
    const choices = new Choices(select, {
        searchEnabled: true,
        searchChoices: true,
        searchPlaceholderValue: 'Search cost center...',
        itemSelectText: 'Click to select',
        noResultsText: 'No cost centers found',
        shouldSort: false,
        removeItemButton: false,
    });
    
    select.choicesInstance = choices;
}

/**
 * Load budget codes
 */
function loadBudgetCodes() {
    const select = document.getElementById('budgetCode');
    
    // Destroy existing Choices instance if it exists
    if (select.choicesInstance) {
        select.choicesInstance.destroy();
    }
    
    // Clear and populate options
    select.innerHTML = '<option value="">Select Budget Code</option>';
    budgetCodesData.forEach(code => {
        const option = document.createElement('option');
        option.value = code.stock_code;
        option.textContent = `${code.stock_code} - ${code.name}`;
        option.setAttribute('data-incharge', code.inchargeCode || '');
        select.appendChild(option);
    });
    
    // Initialize Choices.js for searchable dropdown
    const choices = new Choices(select, {
        searchEnabled: true,
        searchChoices: true,
        searchPlaceholderValue: 'Search budget code...',
        itemSelectText: 'Click to select',
        noResultsText: 'No budget codes found',
        shouldSort: false,
        removeItemButton: false,
    });
    
    select.choicesInstance = choices;
    
    // Bind change event to populate cost_center
    $(select).off('change').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const inchargeCode = selectedOption.data('incharge');
        $('#costCenter').val(inchargeCode || '');
        
        // Update Cost Center Choices if it exists
        const costCenterSelect = document.getElementById('costCenter');
        if (costCenterSelect.choicesInstance) {
            costCenterSelect.choicesInstance.setChoiceByValue(inchargeCode || '');
        }
    });
}

/**
 * Save item (create or update)
 */
function saveItem() {
    const itemId = $('#itemId').val();
    const formData = $('#itemForm').serializeArray();
    const data = {};
    
    formData.forEach(field => {
        data[field.name] = field.value;
    });

    // Validation
    if (!data.budget_category_id) {
        showToast('Budget category is required', 'warning');
        return;
    }

    if (!data.category_type) {
        showToast('Category type is required', 'warning');
        return;
    }

    if (!data.description) {
        showToast('Description is required', 'warning');
        return;
    }

    showLoading();

    const url = itemId 
        ? `/budget-user/${selectedWorkplanId}/items/${itemId}`
        : `/budget-user/${selectedWorkplanId}/items`;
    
    const method = itemId ? 'PUT' : 'POST';

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
                showToast(response.message || 'Item saved successfully', 'success');
                $('#itemModal').modal('hide');
                loadItems(currentCategory);
            } else {
                showToast(response.message || 'Failed to save item', 'error');
            }
        },
        error: function(xhr) {
            hideLoading();
            const response = xhr.responseJSON;
            const message = response?.message || 'Error saving item';
            showToast(message, 'error');
            console.error('Error:', xhr.responseText);
        }
    });
}

/**
 * Delete item
 */
function deleteItem(itemId) {
    Swal.fire({
        title: 'Delete Item?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();

            $.ajax({
                url: `/budget-user/${selectedWorkplanId}/items/${itemId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        showToast(response.message || 'Item deleted successfully', 'success');
                        loadItems(currentCategory);
                    } else {
                        showToast(response.message || 'Failed to delete item', 'error');
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    const response = xhr.responseJSON;
                    const message = response?.message || 'Error deleting item';
                    showToast(message, 'error');
                }
            });
        }
    });
}

/**
 * Show loading overlay
 */
function showLoading() {
    $('#loadingOverlay').addClass('show');
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    $('#loadingOverlay').removeClass('show');
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const iconMap = {
        success: 'success',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: iconMap[type] || 'info',
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

/**
 * Format currency
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}
