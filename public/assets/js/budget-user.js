/**
 * Budget User JavaScript
 * Manages budget user interface with all budget items in one table
 */

let selectedDivisionId = null;
let selectedYear = null;
let budgetCodesData = [];
let allWorkplans = [];
let programIdChoices = null;

$(document).ready(function() {
    initializeEventListeners();
    checkFilterValues(); // Check on page load
    
    // Auto-load if URL parameters exist
    if (typeof paramDivisionId !== 'undefined' && paramDivisionId && 
        typeof paramYear !== 'undefined' && paramYear) {
        // If workplan_id exists, use it for pre-selection
        if (typeof paramWorkplanId !== 'undefined' && paramWorkplanId) {
            autoLoadFromWorkplan(paramDivisionId, paramYear, paramWorkplanId);
        } else {
            // Load without workplan_id (from budget-admin)
            autoLoadFromWorkplan(paramDivisionId, paramYear, null);
        }
    }
});

/**
 * Initialize all event listeners
 */
function initializeEventListeners() {
    // Filter change handlers
    $('#divisionFilter, #yearFilter').on('change', function() {
        checkFilterValues();
    });

    // Load budget button
    $('#loadBudgetBtn').on('click', function() {
        loadAllBudgetItems();
    });

    // Add data button
    $('#addDataBtn').on('click', function() {
        openAddItemModal();
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
    
    // Auto-calculate total when monthly activities or price estimation change
    $(document).on('input', '.monthly-activity, #priceEstimation', function() {
        calculateTotal();
    });
}

/**
 * Calculate total from monthly activities × price estimation
 */
function calculateTotal() {
    let sumMonths = 0;
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    
    months.forEach(month => {
        const value = parseInt($(`input[name="activity_${month}"]`).val()) || 0;
        sumMonths += value;
    });
    
    // Total = sum of months × price estimation
    const priceEstimation = parseFloat($('#priceEstimation').val()) || 0;
    const total = sumMonths * priceEstimation;
    
    $('#total').val(total);
}

/**
 * Check if both filters have values and enable/disable button
 */
function checkFilterValues() {
    const divisionId = $('#divisionFilter').val();
    const year = $('#yearFilter').val();
    
    if (divisionId && year) {
        $('#loadBudgetBtn').prop('disabled', false);
    } else {
        $('#loadBudgetBtn').prop('disabled', true);
        $('#dataInfoSection').hide();
        $('#budgetItemsSection').hide();
    }
}

/**
 * Load all budget items based on division and year
 */
function loadAllBudgetItems() {
    selectedDivisionId = $('#divisionFilter').val();
    selectedYear = $('#yearFilter').val();

    if (!selectedDivisionId || !selectedYear) {
        showToast('Please select Division and Year', 'error');
        return;
    }

    showLoading();

    $.ajax({
        url: '/budget-user/items/all',
        method: 'GET',
        data: {
            division_id: selectedDivisionId,
            year: selectedYear
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                allWorkplans = response.workplans || [];
                budgetCodesData = response.budgetCodes || [];
                
                // Update info section
                const divisionName = $('#divisionFilter option:selected').text();
                $('#selectedDivisionName').text(divisionName);
                $('#selectedYear').text(selectedYear);
                $('#totalWorkplans').text(response.totalWorkplans || 0);
                $('#totalItems').text(response.data.length);
                
                // Show sections
                $('#dataInfoSection').show();
                $('#budgetItemsSection').show();
                
                // Render items
                renderAllItems(response.data);
                
                showToast('Budget data loaded successfully', 'success');
            } else {
                showToast(response.message || 'Failed to load budget data', 'error');
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('Error loading budget data', 'error');
            console.error('Error:', xhr.responseText);
        }
    });
}

/**
 * Render all items in table
 */
function renderAllItems(items) {
    const tbody = $('#budgetItemsTableBody');
    tbody.empty();

    if (items.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="27" class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">No budget items found. Click "Add Data" to create one.</p>
                </td>
            </tr>
        `);
        return;
    }

    items.forEach((item, index) => {
        tbody.append(renderItemRowForTable(item));
    });
}

/**
 * Render single item row for main table
 */
function renderItemRowForTable(item) {
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    const isApproved = item.status === 'approved';
    const categoryColors = {
        'Routine': 'bg-secondary',
        'Turn Around': 'bg-info',
        'Carry Over': 'bg-warning',
        'Multi Year': 'bg-primary'
    };
    const categoryColor = categoryColors[item.category_type] || 'bg-secondary';
    
    let html = `<tr data-item-id="${item.id}">`;
    
    // Action column
    html += `<td class="text-center action-column">`;
    if (isApproved) {
        html += `
            <button type="button" class="btn btn-sm btn-success btn-action-item" title="Approved">
                <i class="bi bi-check-circle"></i>
            </button>
        `;
    } else {
        html += `
            <button type="button" class="btn btn-sm btn-primary btn-action-item" onclick="editItem(${item.id})" title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger btn-action-item" onclick="deleteItem(${item.id})" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
            <button type="button" class="btn btn-sm btn-warning btn-action-item" title="Approve">
                <i class="bi bi-check"></i>
            </button>
        `;
    }
    html += `</td>`;
    
    // Category
    html += `<td><span class="badge ${categoryColor}">${item.category_type || '-'}</span></td>`;
    
    // Other fields
    html += `
        <td style="font-size: 10px;">${item.description || '-'}</td>
        <td style="font-size: 10px;">${item.workplan?.activity || '-'}</td>
        <td style="font-size: 10px;">${item.stock_code || '-'}</td>
        <td style="font-size: 10px;">${item.budget_code || '-'}</td>
        <td style="font-size: 10px;">${item.product_line || '-'}</td>
        <td style="font-size: 10px;">${item.cost_center || '-'}</td>
        <td style="font-size: 10px;">${item.beg_balance || '-'}</td>
        <td style="font-size: 10px;">${item.supplier_name || '-'}</td>
        <td style="font-size: 10px;">${item.cons_rate || '-'}</td>
        <td style="font-size: 10px;">${item.unit_name || '-'}</td>
    `;
    
    // Monthly quantities
    months.forEach(month => {
        const qty = item[`activity_${month}`] || 0;
        const qtyClass = qty > 0 ? 'bg-light' : '';
        html += `<td class="text-center ${qtyClass}" style="font-size: 10px;">${qty}</td>`;
    });
    
    
    // Price estimation
    html += `
        <td class="text-end" style="font-size: 10px;">${item.price_estimation ? formatCurrency(item.price_estimation) : '-'}</td>
        <td style="font-size: 10px;">${item.price_estimation_description || '-'}</td>
    `;
    
    // Calculate total = sum of months × price estimation
    const sumMonths = months.reduce((sum, month) => sum + (item[`activity_${month}`] || 0), 0);
    const priceEstimation = parseFloat(item.price_estimation) || 0;
    const total = sumMonths * priceEstimation;
    html += `<td class="text-end" style="font-size: 10px;"><strong>${formatCurrency(total)}</strong></td>`;
    html += `</tr>`;
    
    return html;
}

/**
 * Auto-load from workplan parameters
 */
function autoLoadFromWorkplan(divisionId, year, workplanId) {
    // Set filter values
    $('#divisionFilter').val(divisionId);
    $('#yearFilter').val(year);
    
    selectedDivisionId = divisionId;
    selectedYear = year;
    
    // Enable load button
    $('#loadBudgetBtn').prop('disabled', false);
    
    // Load budget items
    showLoading();
    
    $.ajax({
        url: '/budget-user/items/all',
        method: 'GET',
        data: {
            division_id: divisionId,
            year: year
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                // Update info section
                const divisionName = $('#divisionFilter option:selected').text();
                $('#selectedDivisionName').text(divisionName);
                $('#selectedYear').text(year);
                $('#totalWorkplans').text(response.totalWorkplans || 0);
                $('#totalItems').text(response.data.length);
                
                // Show sections
                $('#dataInfoSection').show();
                $('#budgetItemsSection').show();
                
                // Store budget codes
                budgetCodesData = response.budgetCodes || [];
                
                // Render items
                renderAllItems(response.data || []);
                
                showToast('Budget data loaded successfully', 'success');
            } else {
                showToast(response.message || 'Failed to load items', 'error');
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('Error loading budget items', 'error');
        }
    });
}

/**
 * Open add item modal with pre-selected workplan
 */
function openAddItemModalWithWorkplan(workplanId) {
    resetItemForm();
    $('#itemModalLabel').html('<i class="bi bi-plus-circle me-2"></i>Add Budget Item');
    $('#itemId').val('');
    
    // Load all dropdown data
    loadBudgetCategories();
    loadBudgetCodes();
    loadCostCenters();
    loadSuppliers();
    loadUnits();
    
    // Load workplans and set the selected one
    loadWorkplansForDropdownWithSelection(workplanId);
    
    $('#itemModal').modal('show');
}

/**
 * Open add item modal
 */
function openAddItemModal() {
    resetItemForm();
    $('#itemModalLabel').html('<i class="bi bi-plus-circle me-2"></i>Add Budget Item');
    $('#itemId').val('');
    
    // Load all dropdown data
    loadBudgetCategories();
    loadBudgetCodes();
    loadCostCenters();
    loadSuppliers();
    loadUnits();
    
    // Check if there's a workplan_id from URL parameter
    if (typeof paramWorkplanId !== 'undefined' && paramWorkplanId) {
        // Load workplans with pre-selection
        loadWorkplansForDropdownWithSelection(paramWorkplanId);
    } else {
        // Load workplans without pre-selection
        loadWorkplansForDropdown();
    }
    
    $('#itemModal').modal('show');
}

/**
 * Edit item from workplan (with pre-loaded data)
 */
function editItemFromWorkplan(itemId, workplanId) {
    showLoading();

    // Find item from loaded data
    $.ajax({
        url: '/budget-user/items/all',
        method: 'GET',
        data: {
            division_id: selectedDivisionId,
            year: selectedYear
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                budgetCodesData = response.budgetCodes || [];
                
                const item = response.data.find(i => i.id === itemId);
                if (item) {
                    $('#itemModalLabel').html('<i class="bi bi-pencil me-2"></i>Edit Budget Item');
                    
                    // Load all dropdown data
                    loadBudgetCategories();
                    loadBudgetCodes();
                    loadCostCenters();
                    loadSuppliers();
                    loadUnits();
                    
                    // Load workplans with the current workplan selected
                    loadWorkplansForDropdownWithSelection(workplanId);
                    
                    // Wait a bit for data to load before populating
                    setTimeout(() => {
                        populateItemForm(item);
                    }, 800);
                    
                    $('#itemModal').modal('show');
                } else {
                    showToast('Item not found', 'error');
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
 * Edit item
 */
function editItem(itemId) {
    showLoading();

    // Find item from loaded data
    $.ajax({
        url: '/budget-user/items/all',
        method: 'GET',
        data: {
            division_id: selectedDivisionId,
            year: selectedYear
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                budgetCodesData = response.budgetCodes || [];
                
                const item = response.data.find(i => i.id === itemId);
                if (item) {
                    $('#itemModalLabel').html('<i class="bi bi-pencil me-2"></i>Edit Budget Item');
                    
                    // Load all dropdown data
                    loadBudgetCategories();
                    loadBudgetCodes();
                    loadCostCenters();
                    loadSuppliers();
                    loadUnits();
                    loadWorkplansForDropdown();
                    
                    // Wait a bit for data to load before populating
                    setTimeout(() => {
                        populateItemForm(item);
                    }, 800);
                    
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
    $('#description').val(item.description);
    $('#stockCode').val(item.stock_code);
    
    // Set category_type radio button
    if (item.category_type) {
        $('input[name="category_type"][value="' + item.category_type + '"]').prop('checked', true);
    }
    
    // Set Program ID using Choices
    if (programIdChoices && item.kpi_workplan_id) {
        programIdChoices.setChoiceByValue(item.kpi_workplan_id.toString());
    }
    
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
    $('#supplier').val(item.supplier_id);
    $('#consRate').val(item.cons_rate);
    $('#unit').val(item.unit_id);
    $('#total').val(item.total);
    $('#priceEstimation').val(item.price_estimation);
    $('#priceEstimationDescription').val(item.price_estimation_description);

    // Monthly activities
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    months.forEach(month => {
        $(`input[name="activity_${month}"]`).val(item[`activity_${month}`] || 0);
    });
}

/**
 * Reset item form
 */
function resetItemForm() {
    // Reset radio buttons
    $('input[name="category_type"]').prop('checked', false);
    $('#itemForm')[0].reset();
    $('#itemId').val('');
    
    // Reset monthly activity inputs to 0
    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    months.forEach(month => {
        $(`input[name="activity_${month}"]`).val(0);
    });
    
    // Reset total
    $('#total').val(0);
    
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
    
    if (programIdChoices) {
        programIdChoices.destroy();
        programIdChoices = null;
    }
}

/**
 * Load budget categories (parent only)
 */
function loadBudgetCategories() {
    $.ajax({
        url: '/budget-user/budget-categories',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const select = $('#budgetCategoryId');
                select.empty();
                select.append('<option value="">Select Budget Category...</option>');
                
                response.data.forEach(category => {
                    select.append(`<option value="${category.id}">${category.name}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading budget categories:', xhr.responseJSON);
            showToast('Failed to load budget categories', 'error');
        }
    });
}

/**
 * Load cost centers from budget codes
 */
function loadCostCenters() {
    $.ajax({
        url: '/budget-user/cost-centers',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const select = document.getElementById('costCenter');
                select.innerHTML = '<option value="">Select Cost Center</option>';
                
                response.data.forEach(center => {
                    const option = document.createElement('option');
                    option.value = center;
                    option.textContent = center;
                    select.appendChild(option);
                });
                
                // Initialize Choices.js
                if (select.choicesInstance) {
                    select.choicesInstance.destroy();
                }
                
                const choices = new Choices(select, {
                    searchEnabled: true,
                    searchPlaceholderValue: 'Search cost center...',
                    itemSelectText: 'Click to select',
                    shouldSort: false,
                });
                
                select.choicesInstance = choices;
            }
        },
        error: function(xhr) {
            console.error('Error loading cost centers:', xhr.responseJSON);
        }
    });
}

/**
 * Load suppliers
 */
function loadSuppliers() {
    $.ajax({
        url: '/budget-user/suppliers',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const select = $('#supplier');
                select.empty();
                select.append('<option value="" data-id="">Select Supplier</option>');
                
                response.data.forEach(supplier => {
                    select.append(`<option value="${supplier.id}" data-name="${supplier.supplier}">${supplier.supplier}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading suppliers:', xhr.responseJSON);
        }
    });
}

/**
 * Load units
 */
function loadUnits() {
    $.ajax({
        url: '/budget-user/units',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const select = $('#unit');
                select.empty();
                select.append('<option value="" data-name="">Select Unit</option>');
                
                response.data.forEach(unit => {
                    select.append(`<option value="${unit.id}" data-name="${unit.unit}">${unit.unit}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading units:', xhr.responseJSON);
        }
    });
}

/**
 * Load workplans for dropdown with pre-selection
 */
function loadWorkplansForDropdownWithSelection(selectedWorkplanId) {
    if (!selectedDivisionId || !selectedYear) {
        showToast('Please select division and year first', 'warning');
        return;
    }

    $.ajax({
        url: '/budget-user/workplans/dropdown',
        method: 'GET',
        data: {
            division_id: selectedDivisionId,
            year: selectedYear
        },
        success: function(response) {
            if (response.success) {
                const select = document.getElementById('programId');
                
                // Destroy existing Choices instance if it exists
                if (programIdChoices) {
                    programIdChoices.destroy();
                }
                
                // Initialize Choices.js for searchable dropdown
                programIdChoices = new Choices(select, {
                    searchEnabled: true,
                    searchPlaceholderValue: 'Search work plan...',
                    itemSelectText: 'Click to select',
                    shouldSort: false,
                    removeItemButton: false,
                    placeholder: true,
                    placeholderValue: 'Select work plan'
                });
                
                // Clear existing choices
                programIdChoices.clearChoices();
                
                // Add default option
                programIdChoices.setChoices([
                    { value: '', label: 'Select Work Plan...', selected: true, disabled: false }
                ], 'value', 'label', true);
                
                if (response.data && response.data.length > 0) {
                    // Add workplan options with consistent formatting
                    const choices = response.data.map(workplan => {
                        const typeLabel = workplan.kpi_type === 'department' ? 'Department' : 'Section';
                        const typeBadge = workplan.kpi_type === 'department' ? '🏢' : '📋';
                        return {
                            value: workplan.id.toString(),
                            label: `${typeBadge} [${typeLabel}] ${workplan.activity}`,
                            customProperties: {
                                kpi_type: workplan.kpi_type
                            }
                        };
                    });
                    
                    programIdChoices.setChoices(choices, 'value', 'label', false);
                    
                    // Set selected workplan
                    if (selectedWorkplanId) {
                        programIdChoices.setChoiceByValue(selectedWorkplanId.toString());
                    }
                } else {
                    showToast('No workplans found for this division and year', 'info');
                }
            } else {
                showToast(response.message || 'Failed to load workplans', 'error');
            }
        },
        error: function(xhr) {
            showToast('Error loading workplans', 'error');
        }
    });
}

/**
 * Load workplans for dropdown (department and section only)
 */
function loadWorkplansForDropdown() {
    if (!selectedDivisionId || !selectedYear) {
        return;
    }

    $.ajax({
        url: '/budget-user/workplans/dropdown',
        method: 'GET',
        data: {
            division_id: selectedDivisionId,
            year: selectedYear
        },
        success: function(response) {
            if (response.success) {
                const select = document.getElementById('programId');
                
                // Destroy existing Choices instance if it exists
                if (programIdChoices) {
                    programIdChoices.destroy();
                }
                
                // Initialize Choices.js for searchable dropdown
                programIdChoices = new Choices(select, {
                    searchEnabled: true,
                    searchPlaceholderValue: 'Search work plan...',
                    itemSelectText: 'Click to select',
                    shouldSort: false,
                    removeItemButton: false,
                    placeholder: true,
                    placeholderValue: 'Select work plan'
                });
                
                // Clear existing choices
                programIdChoices.clearChoices();
                
                // Add default option
                programIdChoices.setChoices([
                    { value: '', label: 'Select Work Plan...', selected: true, disabled: false }
                ], 'value', 'label', true);
                
                // Add workplan options
                const choices = response.data.map(workplan => {
                    const typeLabel = workplan.kpi_type === 'department' ? 'Department' : 'Section';
                    const typeBadge = workplan.kpi_type === 'department' ? '🏢' : '📋';
                    return {
                        value: workplan.id.toString(),
                        label: `${typeBadge} [${typeLabel}] ${workplan.activity}`,
                        customProperties: {
                            kpi_type: workplan.kpi_type
                        }
                    };
                });
                
                programIdChoices.setChoices(choices, 'value', 'label', false);
            }
        },
        error: function(xhr) {
            console.error('Error loading workplans:', xhr.responseJSON);
            showToast('Failed to load work plans', 'error');
        }
    });
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
    // Validate category_type
    if (!$('input[name="category_type"]:checked').val()) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Please select a category type',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    const itemId = $('#itemId').val();
    const isEdit = itemId !== '';
    
    // Validate required fields
    const programId = $('#programId').val();
    const budgetCategoryId = $('#budgetCategoryId').val();
    const description = $('#description').val();
    
    if (!programId) {
        showToast('Please select a work plan', 'error');
        return;
    }
    
    if (!budgetCategoryId) {
        showToast('Please select a budget category', 'error');
        return;
    }
    
    if (!description) {
        showToast('Please enter a description', 'error');
        return;
    }

    // Get supplier data
    const supplierId = $('#supplier').val();
    const supplierName = supplierId ? $('#supplier option:selected').data('name') : null;
    
    // Get unit data
    const unitId = $('#unit').val();
    const unitName = unitId ? $('#unit option:selected').data('name') : null;
    
    const formData = {
        kpi_workplan_id: programId,
        budget_category_id: budgetCategoryId,
        category_type: $('input[name="category_type"]:checked').val(),
        description: description,
        stock_code: $('#stockCode').val(),
        budget_code: $('#budgetCode').val(),
        product_line: $('#productLine').val(),
        cost_center: $('#costCenter').val(),
        beg_balance: $('#begBalance').val(),
        supplier_id: supplierId,
        supplier_name: supplierName,
        cons_rate: $('#consRate').val(),
        unit_id: unitId,
        unit_name: unitName,
        total: $('#total').val(),
        price_estimation: $('#priceEstimation').val(),
        price_estimation_description: $('#priceEstimationDescription').val(),
        activity_jan: $('input[name="activity_jan"]').val() || 0,
        activity_feb: $('input[name="activity_feb"]').val() || 0,
        activity_mar: $('input[name="activity_mar"]').val() || 0,
        activity_apr: $('input[name="activity_apr"]').val() || 0,
        activity_may: $('input[name="activity_may"]').val() || 0,
        activity_jun: $('input[name="activity_jun"]').val() || 0,
        activity_jul: $('input[name="activity_jul"]').val() || 0,
        activity_aug: $('input[name="activity_aug"]').val() || 0,
        activity_sep: $('input[name="activity_sep"]').val() || 0,
        activity_oct: $('input[name="activity_oct"]').val() || 0,
        activity_nov: $('input[name="activity_nov"]').val() || 0,
        activity_dec: $('input[name="activity_dec"]').val() || 0,
        notes: $('textarea[name="notes"]').val(),
        year: selectedYear,
        division_id: selectedDivisionId
    };

    showLoading();

    const url = isEdit ? `/budget-user/items/${itemId}` : '/budget-user/items';
    const method = isEdit ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showToast(response.message, 'success');
                $('#itemModal').modal('hide');
                loadAllBudgetItems();
            }
        },
        error: function(xhr) {
            hideLoading();
            const message = xhr.responseJSON?.message || 'Failed to save item';
            showToast(message, 'error');
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
                url: `/budget-user/items/${itemId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        showToast(response.message || 'Item deleted successfully', 'success');
                        loadAllBudgetItems(); // Reload all data
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
