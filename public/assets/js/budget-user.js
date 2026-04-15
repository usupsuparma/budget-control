/**
 * Budget User JavaScript
 * Manages budget user interface with all budget items in one table
 */

let selectedDivisionId = null;
let selectedYear = null;
// Server-side search caches — replaces full 20k-item arrays
const _budgetCodeCache = new Map(); // budget_code → {budget_code, name, inchargeCode}
const _stockCodeCache = new Map(); // stock_code  → {stock_code, name, unit, budget_code, product_line}
let _budgetCodeSearchTimer = null;
let _stockCodeSearchTimer = null;
let suppliersData = [];
let unitsData = [];
let allWorkplans = [];
let programIdChoices = null;
let currentEmploymentId = null; // Current user's employment ID for approval authorization
let allItemsData = []; // Store all items data for detail lookup

$(document).ready(function () {
    initializeEventListeners();
    checkFilterValues(); // Check on page load

    // Auto-load if URL parameters exist
    if (
        typeof paramDivisionId !== "undefined" &&
        paramDivisionId &&
        typeof paramYear !== "undefined" &&
        paramYear
    ) {
        // If workplan_id exists, use it for pre-selection
        if (typeof paramWorkplanId !== "undefined" && paramWorkplanId) {
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
    $("#divisionFilter, #yearFilter").on("change", function () {
        checkFilterValues();
    });

    // Load budget button
    $("#loadBudgetBtn").on("click", function () {
        loadAllBudgetItems();
    });

    // Add data button
    $("#addDataBtn").on("click", function () {
        openAddItemModal();
    });

    // Save item button
    $("#saveItemBtn").on("click", function () {
        saveItem();
    });

    // Reset item button
    $("#resetItemBtn").on("click", function () {
        resetItemForm();
    });

    // Modal hidden event
    $("#itemModal").on("hidden.bs.modal", function () {
        resetItemForm();
    });

    // Auto-calculate total when monthly activities change
    $(document).on("input", ".monthly-activity", function () {
        calculateTotal();
    });

    // Format price estimation with thousand separator on input (real-time)
    let priceEstimationTimeout;
    $(document).on("input", "#priceEstimation", function () {
        clearTimeout(priceEstimationTimeout);
        const input = $(this);
        const cursorPosition = this.selectionStart;
        const oldValue = input.val();
        const oldLength = oldValue.length;

        priceEstimationTimeout = setTimeout(() => {
            const value = parseFormattedNumber(oldValue);
            const formattedValue =
                value > 0 ? formatNumberWithSeparator(value) : "0";
            input.val(formattedValue);

            // Restore cursor position
            const newLength = formattedValue.length;
            const newPosition = cursorPosition + (newLength - oldLength);
            this.setSelectionRange(newPosition, newPosition);

            calculateTotal();
        }, 500); // 500ms debounce
    });

    // Format on blur to ensure proper formatting
    $(document).on("blur", "#priceEstimation", function () {
        const value = parseFormattedNumber($(this).val());
        const formattedValue =
            value > 0 ? formatNumberWithSeparator(value) : "0";
        $(this).val(formattedValue);
        calculateTotal();
    });
}

/**
 * Calculate total from monthly activities × price estimation
 */
function calculateTotal() {
    let sumMonths = 0;
    const months = [
        "jan",
        "feb",
        "mar",
        "apr",
        "may",
        "jun",
        "jul",
        "aug",
        "sep",
        "oct",
        "nov",
        "dec",
    ];

    months.forEach((month) => {
        const value = parseInt($(`input[name="activity_${month}"]`).val()) || 0;
        sumMonths += value;
    });

    // Total = sum of months × price estimation (parse formatted number)
    const priceEstimation = parseFormattedNumber($("#priceEstimation").val());
    const total = sumMonths * priceEstimation;

    // Format total with thousand separator
    $("#total").val(formatNumberWithSeparator(total));
}

/**
 * Check if both filters have values and enable/disable button
 */
function checkFilterValues() {
    const divisionId = $("#divisionFilter").val();
    const year = $("#yearFilter").val();

    if (divisionId && year) {
        $("#loadBudgetBtn").prop("disabled", false);
    } else {
        $("#loadBudgetBtn").prop("disabled", true);
        $("#dataInfoSection").hide();
        $("#budgetItemsSection").hide();
    }
}

/**
 * Refresh budget items with current selected division and year
 */
function refreshBudgetItems() {
    if (!selectedDivisionId || !selectedYear) {
        showToast("Please select Division and Year first", "warning");
        return;
    }

    showLoading();

    $.ajax({
        url: "/budget-user/items/all",
        method: "GET",
        data: {
            division_id: selectedDivisionId,
            year: selectedYear,
        },
        success: function (response) {
            hideLoading();
            if (response.success) {
                console.log(response, "refreshed data");

                allWorkplans = response.workplans || [];
                allItemsData = response.data || [];
                currentEmploymentId = response.currentEmploymentId;

                // Update counts
                $("#totalWorkplans").text(response.totalWorkplans || 0);
                $("#totalItems").text(response.data.length);

                // Render items
                renderAllItems(response.data);

                showToast("Budget items refreshed successfully", "success");
            } else {
                showToast(
                    response.message || "Failed to refresh budget items",
                    "error",
                );
            }
        },
        error: function (xhr) {
            hideLoading();
            showToast("Error refreshing budget items", "error");
            console.error("Error:", xhr.responseText);
        },
    });
}

/**
 * Load all budget items based on division and year
 */
function loadAllBudgetItems() {
    selectedDivisionId = $("#divisionFilter").val();
    selectedYear = $("#yearFilter").val();

    if (!selectedDivisionId || !selectedYear) {
        showToast("Please select Division and Year", "error");
        return;
    }

    showLoading();

    $.ajax({
        url: "/budget-user/items/all",
        method: "GET",
        data: {
            division_id: selectedDivisionId,
            year: selectedYear,
        },
        success: function (response) {
            hideLoading();
            if (response.success) {
                allWorkplans = response.workplans || [];
                allItemsData = response.data || []; // Store for detail lookup
                currentEmploymentId = response.currentEmploymentId; // Store for authorization

                // Update info section
                const divisionName = $(
                    "#divisionFilter option:selected",
                ).text();
                $("#selectedDivisionName").text(divisionName);
                $("#selectedYear").text(selectedYear);
                $("#totalWorkplans").text(response.totalWorkplans || 0);
                $("#totalItems").text(response.data.length);

                // Show sections
                $("#dataInfoSection").show();
                $("#budgetItemsSection").show();

                // Render items
                renderAllItems(response.data);

                showToast("Budget data loaded successfully", "success");
            } else {
                showToast(
                    response.message || "Failed to load budget data",
                    "error",
                );
            }
        },
        error: function (xhr) {
            hideLoading();
            showToast("Error loading budget data", "error");
            console.error("Error:", xhr.responseText);
        },
    });
}

/**
 * Render all items in table
 */
function renderAllItems(items) {
    const tbody = $("#budgetItemsTableBody");
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
    const months = [
        "jan",
        "feb",
        "mar",
        "apr",
        "may",
        "jun",
        "jul",
        "aug",
        "sep",
        "oct",
        "nov",
        "dec",
    ];

    const categoryColors = {
        Routine: "bg-secondary",
        "Turn Around": "bg-info",
        "Carry Over": "bg-warning",
        "Multi Year": "bg-primary",
    };
    const categoryColor = categoryColors[item.category_type] || "bg-secondary";

    // Determine which price to use and calculate total
    const isVerified =
        item.verification_status === "verified" &&
        item.price_final &&
        parseFloat(item.price_final) > 0;
    const unitPrice = isVerified
        ? parseFloat(item.price_final)
        : parseFloat(item.price_estimation || 0);
    const totalQty = months.reduce(
        (sum, m) => sum + (parseInt(item[`activity_${m}`]) || 0),
        0,
    );
    const totalBudget = unitPrice * totalQty;

    let html = `<tr data-item-id="${item.id}">`;

    // Action column - based on status
    html += `<td class="text-center action-column">`;
    html += renderActionButtons(item);
    html += `</td>`;

    // Status column - combined verification & approval status
    html += `<td class="text-center">${renderStatusBadges(item)}</td>`;

    // Category
    html += `<td><span class="badge ${categoryColor}">${
        item.category_type || "-"
    }</span></td>`;

    // Other fields
    html += `
        <td style="font-size: 10px;">${item.description || "-"}</td>
        <td style="font-size: 10px;">${item.workplan?.activity || "-"}</td>
        <td style="font-size: 10px;">${item.stock_code || "-"}</td>
        <td style="font-size: 10px;">${item.budget_code || "-"}</td>
        <td style="font-size: 10px;">${item.product_line || "-"}</td>
        <td style="font-size: 10px;">${item.cost_center || "-"}</td>
        <td style="font-size: 10px;">${item.beg_balance || "-"}</td>
        <td style="font-size: 10px;">${item.supplier_name || "-"}</td>
        <td style="font-size: 10px; display:none;">${item.cons_rate || "-"}</td>
        <td style="font-size: 10px;">${item.unit_name || "-"}</td>
    `;

    // Monthly quantities
    months.forEach((month) => {
        const qty = item[`activity_${month}`] || 0;
        const qtyClass = qty > 0 ? "bg-light" : "";
        html += `<td class="text-center ${qtyClass}" style="font-size: 10px;">${qty}</td>`;
    });

    // Unit Price - show verified or estimated with indicator
    html += `<td class="text-end" style="font-size: 10px;">`;
    html += `<div class="d-flex flex-column align-items-end">`;
    html += `<span class="fw-bold">${formatCurrency(unitPrice)}</span>`;
    if (isVerified) {
        html += `<small class="text-success"><i class="bi bi-check-circle-fill"></i> Verified</small>`;
    } else {
        html += `<small class="text-muted"><i class="bi bi-hourglass-split"></i> Estimated</small>`;
    }
    html += `</div>`;
    html += `</td>`;

    // Price Status - detailed verification info
    html += `<td style="font-size: 10px;">`;
    if (isVerified) {
        html += `<span class="badge bg-success">Verified</span>`;
        if (
            item.price_estimation &&
            parseFloat(item.price_estimation) !== unitPrice
        ) {
            const diff = unitPrice - parseFloat(item.price_estimation);
            const diffPercent = (
                (diff / parseFloat(item.price_estimation)) *
                100
            ).toFixed(1);
            const diffClass = diff > 0 ? "text-danger" : "text-success";
            html += `<br><small class="${diffClass}">${diff > 0 ? "+" : ""}${formatCurrency(diff)}</small>`;
            html += `<br><small class="${diffClass}">(${diff > 0 ? "+" : ""}${diffPercent}%)</small>`;
        }
    } else if (item.verification_status === "pending") {
        html += `<span class="badge bg-warning text-dark">Pending Verification</span>`;
        html += `<br><small class="text-muted">Est: ${formatCurrency(item.price_estimation || 0)}</small>`;
    } else if (item.verification_status === "rejected") {
        html += `<span class="badge bg-danger">Rejected</span>`;
        html += `<br><small class="text-muted">Est: ${formatCurrency(item.price_estimation || 0)}</small>`;
    } else {
        html += `<span class="badge bg-secondary">Not Verified</span>`;
        html += `<br><small class="text-muted">Using estimation</small>`;
    }
    if (item.price_estimation_description) {
        html += `<br><small class="text-muted fst-italic">${item.price_estimation_description}</small>`;
    }
    html += `</td>`;

    // Total Budget - calculated from current price × qty
    html += `<td class="text-end" style="font-size: 10px;">`;
    html += `<div class="d-flex flex-column align-items-end">`;
    html += `<span class="fw-bold fs-6">${formatCurrency(totalBudget)}</span>`;
    html += `<small class="text-muted">${totalQty} × ${formatCurrency(unitPrice)}</small>`;
    html += `</div>`;
    html += `</td>`;
    html += `</tr>`;

    return html;
}

/**
 * Auto-load from workplan parameters
 */
function autoLoadFromWorkplan(divisionId, year, workplanId) {
    // Set filter values
    $("#divisionFilter").val(divisionId);
    $("#yearFilter").val(year);

    selectedDivisionId = divisionId;
    selectedYear = year;

    // Enable load button
    $("#loadBudgetBtn").prop("disabled", false);

    // Load budget items
    showLoading();

    $.ajax({
        url: "/budget-user/items/all",
        method: "GET",
        data: {
            division_id: divisionId,
            year: year,
        },
        success: function (response) {
            hideLoading();

            if (response.success) {
                // Update info section
                const divisionName = $(
                    "#divisionFilter option:selected",
                ).text();
                $("#selectedDivisionName").text(divisionName);
                $("#selectedYear").text(year);
                $("#totalWorkplans").text(response.totalWorkplans || 0);
                $("#totalItems").text(response.data.length);

                // Show sections
                $("#dataInfoSection").show();
                $("#budgetItemsSection").show();

                // Render items
                renderAllItems(response.data || []);

                showToast("Budget data loaded successfully", "success");
            } else {
                showToast(response.message || "Failed to load items", "error");
            }
        },
        error: function (xhr) {
            hideLoading();
            showToast("Error loading budget items", "error");
        },
    });
}

/**
 * Open add item modal with pre-selected workplan
 */
function openAddItemModalWithWorkplan(workplanId) {
    resetItemForm();
    $("#itemModalLabel").html(
        '<i class="bi bi-plus-circle me-2"></i>Add Budget Item',
    );
    $("#itemId").val("");

    Swal.fire({
        title: "Memuat data...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    Promise.all([
        loadBudgetCategoriesAsync(),
        loadBudgetCodesAsync(),
        loadStockCodesAsync(),
        loadCostCentersAsync(),
        loadSuppliersAsync(),
        loadUnitsAsync(),
        loadWorkplansForDropdownWithSelectionAsync(workplanId),
    ])
        .then(() => {
            Swal.close();
            $("#itemModal").modal("show");
        })
        .catch(() => {
            Swal.close();
            $("#itemModal").modal("show");
        });
}

/**
 * Open add item modal
 */
function openAddItemModal() {
    resetItemForm();
    $("#itemModalLabel").html(
        '<i class="bi bi-plus-circle me-2"></i>Add Budget Item',
    );
    $("#itemId").val("");

    Swal.fire({
        title: "Memuat data...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    const workplanLoader =
        typeof paramWorkplanId !== "undefined" && paramWorkplanId
            ? loadWorkplansForDropdownWithSelectionAsync(paramWorkplanId)
            : loadWorkplansForDropdownAsync();

    Promise.all([
        loadBudgetCategoriesAsync(),
        loadBudgetCodesAsync(),
        loadStockCodesAsync(),
        loadCostCentersAsync(),
        loadSuppliersAsync(),
        loadUnitsAsync(),
        workplanLoader,
    ])
        .then(() => {
            Swal.close();
            $("#itemModal").modal("show");
        })
        .catch(() => {
            Swal.close();
            $("#itemModal").modal("show");
        });
}

/**
 * Edit item from workplan (with pre-loaded data)
 */
function editItemFromWorkplan(itemId, workplanId) {
    const item = allItemsData.find((i) => i.id === itemId);

    if (!item) {
        showToast("Item not found", "error");
        return;
    }

    $("#itemModalLabel").html(
        '<i class="bi bi-pencil me-2"></i>Edit Budget Item',
    );

    Swal.fire({
        title: "Memuat data...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    Promise.all([
        loadBudgetCategoriesAsync(),
        loadBudgetCodesAsync(),
        loadStockCodesAsync(),
        loadCostCentersAsync(),
        loadSuppliersAsync(),
        loadUnitsAsync(),
        loadWorkplansForDropdownWithSelectionAsync(workplanId),
    ])
        .then(() => {
            populateItemForm(item);
            Swal.close();
            $("#itemModal").modal("show");
        })
        .catch(() => {
            populateItemForm(item);
            Swal.close();
            $("#itemModal").modal("show");
        });
}

/**
 * Edit item
 */
function editItem(itemId) {
    const item = allItemsData.find((i) => i.id === itemId);

    if (!item) {
        showToast("Item not found", "error");
        return;
    }

    $("#itemModalLabel").html(
        '<i class="bi bi-pencil me-2"></i>Edit Budget Item',
    );

    Swal.fire({
        title: "Memuat data...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    Promise.all([
        loadBudgetCategoriesAsync(),
        loadBudgetCodesAsync(),
        loadStockCodesAsync(),
        loadCostCentersAsync(),
        loadSuppliersAsync(),
        loadUnitsAsync(),
        loadWorkplansForDropdownWithSelectionAsync(item.kpi_workplan_id),
    ])
        .then(() => {
            populateItemForm(item);
            Swal.close();
            $("#itemModal").modal("show");
        })
        .catch((error) => {
            console.error("Error loading dropdown data:", error);
            populateItemForm(item);
            Swal.close();
            $("#itemModal").modal("show");
        });
}

/**
 * Populate item form with data
 */
function populateItemForm(item) {
    $("#itemId").val(item.id);
    $("#budgetCategoryId").val(item.budget_category_id);
    $("#description").val(item.description);

    // Re-init Stock Code dropdown with preselected value (server-side search, edit mode)
    if (item.stock_code) {
        const scRelation = item.stock_code_relation;
        const scLabel = scRelation
            ? item.stock_code + " - " + scRelation.name
            : item.stock_code;
        _initStockCodeSearchDropdown(item.stock_code, scLabel);
        // Prime cache so related-field auto-fill works without an extra request
        if (scRelation) {
            _stockCodeCache.set(item.stock_code, {
                stock_code: item.stock_code,
                name: scRelation.name,
                budget_code: item.budget_code || null,
                product_line: item.product_line || null,
                unit: scRelation.unit || null,
            });
        }
    } else {
        _initStockCodeSearchDropdown(null, null);
    }

    // Set category_type radio button
    if (item.category_type) {
        $(
            'input[name="category_type"][value="' + item.category_type + '"]',
        ).prop("checked", true);
    }

    // Set Program ID using Choices
    if (programIdChoices && item.kpi_workplan_id) {
        programIdChoices.setChoiceByValue(item.kpi_workplan_id.toString());
    }

    // Re-init Budget Code dropdown with preselected value (server-side search, edit mode)
    if (item.budget_code) {
        const bcRelation = item.budget_code_relation;
        const bcLabel = bcRelation
            ? item.budget_code + " - " + bcRelation.name
            : item.budget_code;
        _initBudgetCodeSearchDropdown(item.budget_code, bcLabel);
        if (bcRelation) {
            _budgetCodeCache.set(item.budget_code, bcRelation);
        }
    } else {
        _initBudgetCodeSearchDropdown(null, null);
    }

    $("#productLine").val(item.product_line);

    // Set Cost Center using Choices
    const costCenterSelect = document.getElementById("costCenter");
    if (costCenterSelect.choicesInstance && item.cost_center) {
        costCenterSelect.choicesInstance.setChoiceByValue(item.cost_center);
    }

    $("#begBalance").val(item.beg_balance);
    $("#supplier").val(item.supplier_id);
    $("#consRate").val(item.cons_rate);
    $("#unit").val(item.unit_id);

    // Format price estimation and total with separator
    if (item.price_estimation) {
        $("#priceEstimation").val(
            formatNumberWithSeparator(item.price_estimation),
        );
    }
    if (item.total) {
        $("#total").val(formatNumberWithSeparator(item.total));
    }

    $("#priceEstimationDescription").val(item.price_estimation_description);

    // Monthly activities
    const months = [
        "jan",
        "feb",
        "mar",
        "apr",
        "may",
        "jun",
        "jul",
        "aug",
        "sep",
        "oct",
        "nov",
        "dec",
    ];
    months.forEach((month) => {
        $(`input[name="activity_${month}"]`).val(
            item[`activity_${month}`] || 0,
        );
    });
}

/**
 * Reset item form
 */
function resetItemForm() {
    // Reset radio buttons
    $('input[name="category_type"]').prop("checked", false);
    $("#itemForm")[0].reset();
    $("#itemId").val("");

    // Reset monthly activity inputs to 0
    const months = [
        "jan",
        "feb",
        "mar",
        "apr",
        "may",
        "jun",
        "jul",
        "aug",
        "sep",
        "oct",
        "nov",
        "dec",
    ];
    months.forEach((month) => {
        $(`input[name="activity_${month}"]`).val(0);
    });

    // Reset total
    $("#total").val("0");
    $("#priceEstimation").val("0");

    // Destroy Choices instances and remove tracked event listeners to prevent accumulation
    const budgetCodeSelect = document.getElementById("budgetCode");
    if (budgetCodeSelect) {
        if (budgetCodeSelect.choicesInstance) {
            budgetCodeSelect.choicesInstance.destroy();
            budgetCodeSelect.choicesInstance = null;
        }
        if (budgetCodeSelect._choicesSearchHandler) {
            budgetCodeSelect.removeEventListener(
                "search",
                budgetCodeSelect._choicesSearchHandler,
            );
            budgetCodeSelect._choicesSearchHandler = null;
        }
        if (budgetCodeSelect._choicesChangeHandler) {
            budgetCodeSelect.removeEventListener(
                "change",
                budgetCodeSelect._choicesChangeHandler,
            );
            budgetCodeSelect._choicesChangeHandler = null;
        }
    }

    const stockCodeSelect = document.getElementById("stockCode");
    if (stockCodeSelect) {
        if (stockCodeSelect.choicesInstance) {
            stockCodeSelect.choicesInstance.destroy();
            stockCodeSelect.choicesInstance = null;
        }
        if (stockCodeSelect._choicesSearchHandler) {
            stockCodeSelect.removeEventListener(
                "search",
                stockCodeSelect._choicesSearchHandler,
            );
            stockCodeSelect._choicesSearchHandler = null;
        }
        if (stockCodeSelect._choicesChangeHandler) {
            stockCodeSelect.removeEventListener(
                "change",
                stockCodeSelect._choicesChangeHandler,
            );
            stockCodeSelect._choicesChangeHandler = null;
        }
    }

    const costCenterSelect = document.getElementById("costCenter");
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
        url: "/budget-user/budget-categories",
        method: "GET",
        success: function (response) {
            if (response.success) {
                const select = $("#budgetCategoryId");
                select.empty();
                select.append(
                    '<option value="">Select Budget Category...</option>',
                );

                response.data.forEach((category) => {
                    select.append(
                        `<option value="${category.id}">${category.name}</option>`,
                    );
                });
            }
        },
        error: function (xhr) {
            console.error("Error loading budget categories:", xhr.responseJSON);
            showToast("Failed to load budget categories", "error");
        },
    });
}

/**
 * Load cost centers from budget codes
 */
function loadCostCenters() {
    $.ajax({
        url: "/budget-user/cost-centers",
        method: "GET",
        success: function (response) {
            if (response.success) {
                const select = document.getElementById("costCenter");
                select.innerHTML =
                    '<option value="">Select Cost Center</option>';

                response.data.forEach((center) => {
                    const option = document.createElement("option");
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
                    searchPlaceholderValue: "Search cost center...",
                    itemSelectText: "Click to select",
                    shouldSort: false,
                });

                select.choicesInstance = choices;
            }
        },
        error: function (xhr) {
            console.error("Error loading cost centers:", xhr.responseJSON);
        },
    });
}

/**
 * Load suppliers
 */
function loadSuppliers() {
    $.ajax({
        url: "/budget-user/suppliers",
        method: "GET",
        success: function (response) {
            if (response.success) {
                suppliersData = response.data || [];
                const select = $("#supplier");
                select.empty();
                select.append('<option value="">Select Supplier</option>');

                suppliersData.forEach((supplier) => {
                    select.append(
                        `<option value="${supplier.id}">${supplier.supplier}</option>`,
                    );
                });
            }
        },
        error: function (xhr) {
            console.error("Error loading suppliers:", xhr.responseJSON);
        },
    });
}

/**
 * Load units
 */
function loadUnits() {
    $.ajax({
        url: "/budget-user/units",
        method: "GET",
        success: function (response) {
            if (response.success) {
                unitsData = response.data || [];
                const select = $("#unit");
                select.empty();
                select.append('<option value="">Select Unit</option>');

                unitsData.forEach((unit) => {
                    select.append(
                        `<option value="${unit.id}">${unit.unit}</option>`,
                    );
                });
            }
        },
        error: function (xhr) {
            console.error("Error loading units:", xhr.responseJSON);
        },
    });
}

/**
 * Load workplans for dropdown with pre-selection
 */
function loadWorkplansForDropdownWithSelection(selectedWorkplanId) {
    if (!selectedDivisionId || !selectedYear) {
        showToast("Please select division and year first", "warning");
        return;
    }

    $.ajax({
        url: "/budget-user/workplans/dropdown",
        method: "GET",
        data: {
            division_id: selectedDivisionId,
            year: selectedYear,
        },
        success: function (response) {
            if (response.success) {
                const select = document.getElementById("programId");

                // Destroy existing Choices instance if it exists
                if (programIdChoices) {
                    programIdChoices.destroy();
                }

                // Initialize Choices.js for searchable dropdown
                programIdChoices = new Choices(select, {
                    searchEnabled: true,
                    searchPlaceholderValue: "Search work plan...",
                    itemSelectText: "Click to select",
                    shouldSort: false,
                    removeItemButton: false,
                    placeholder: true,
                    placeholderValue: "Select work plan",
                });

                // Clear existing choices
                programIdChoices.clearChoices();

                // Add default option
                programIdChoices.setChoices(
                    [
                        {
                            value: "",
                            label: "Select Work Plan...",
                            selected: true,
                            disabled: false,
                        },
                    ],
                    "value",
                    "label",
                    true,
                );

                if (response.data && response.data.length > 0) {
                    // Add workplan options with consistent formatting
                    const choices = response.data.map((workplan) => {
                        const typeLabel =
                            workplan.kpi_type === "department"
                                ? "Department"
                                : "Section";
                        const typeBadge =
                            workplan.kpi_type === "department" ? "🏢" : "📋";
                        return {
                            value: workplan.id.toString(),
                            label: `${typeBadge} [${typeLabel}] ${workplan.activity}`,
                            customProperties: {
                                kpi_type: workplan.kpi_type,
                            },
                        };
                    });

                    programIdChoices.setChoices(
                        choices,
                        "value",
                        "label",
                        false,
                    );

                    // Set selected workplan
                    if (selectedWorkplanId) {
                        programIdChoices.setChoiceByValue(
                            selectedWorkplanId.toString(),
                        );
                    }
                } else {
                    showToast(
                        "No workplans found for this division and year",
                        "info",
                    );
                }
            } else {
                showToast(
                    response.message || "Failed to load workplans",
                    "error",
                );
            }
        },
        error: function (xhr) {
            showToast("Error loading workplans", "error");
        },
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
        url: "/budget-user/workplans/dropdown",
        method: "GET",
        data: {
            division_id: selectedDivisionId,
            year: selectedYear,
        },
        success: function (response) {
            if (response.success) {
                const select = document.getElementById("programId");

                // Destroy existing Choices instance if it exists
                if (programIdChoices) {
                    programIdChoices.destroy();
                }

                // Initialize Choices.js for searchable dropdown
                programIdChoices = new Choices(select, {
                    searchEnabled: true,
                    searchPlaceholderValue: "Search work plan...",
                    itemSelectText: "Click to select",
                    shouldSort: false,
                    removeItemButton: false,
                    placeholder: true,
                    placeholderValue: "Select work plan",
                });

                // Clear existing choices
                programIdChoices.clearChoices();

                // Add default option
                programIdChoices.setChoices(
                    [
                        {
                            value: "",
                            label: "Select Work Plan...",
                            selected: true,
                            disabled: false,
                        },
                    ],
                    "value",
                    "label",
                    true,
                );

                // Add workplan options
                const choices = response.data.map((workplan) => {
                    const typeLabel =
                        workplan.kpi_type === "department"
                            ? "Department"
                            : "Section";
                    const typeBadge =
                        workplan.kpi_type === "department" ? "🏢" : "📋";
                    return {
                        value: workplan.id.toString(),
                        label: `${typeBadge} [${typeLabel}] ${workplan.activity}`,
                        customProperties: {
                            kpi_type: workplan.kpi_type,
                        },
                    };
                });

                programIdChoices.setChoices(choices, "value", "label", false);
            }
        },
        error: function (xhr) {
            console.error("Error loading workplans:", xhr.responseJSON);
            showToast("Failed to load work plans", "error");
        },
    });
}

/**
 * Async version of loadWorkplansForDropdown (no pre-selection) - returns a Promise
 */
function loadWorkplansForDropdownAsync() {
    return new Promise((resolve, reject) => {
        if (!selectedDivisionId || !selectedYear) {
            resolve();
            return;
        }

        $.ajax({
            url: "/budget-user/workplans/dropdown",
            method: "GET",
            data: {
                division_id: selectedDivisionId,
                year: selectedYear,
            },
            success: function (response) {
                if (response.success) {
                    const select = document.getElementById("programId");

                    if (programIdChoices) {
                        programIdChoices.destroy();
                    }

                    programIdChoices = new Choices(select, {
                        searchEnabled: true,
                        searchPlaceholderValue: "Search work plan...",
                        itemSelectText: "Click to select",
                        shouldSort: false,
                        removeItemButton: false,
                        placeholder: true,
                        placeholderValue: "Select work plan",
                    });

                    programIdChoices.clearChoices();
                    programIdChoices.setChoices(
                        [
                            {
                                value: "",
                                label: "Select Work Plan...",
                                selected: true,
                                disabled: false,
                            },
                        ],
                        "value",
                        "label",
                        true,
                    );

                    if (response.data && response.data.length > 0) {
                        const choices = response.data.map((workplan) => {
                            const typeLabel =
                                workplan.kpi_type === "department"
                                    ? "Department"
                                    : "Section";
                            const typeBadge =
                                workplan.kpi_type === "department"
                                    ? "🏢"
                                    : "📋";
                            return {
                                value: workplan.id.toString(),
                                label: `${typeBadge} [${typeLabel}] ${workplan.activity}`,
                                customProperties: {
                                    kpi_type: workplan.kpi_type,
                                },
                            };
                        });
                        programIdChoices.setChoices(
                            choices,
                            "value",
                            "label",
                            false,
                        );
                    }
                }
                resolve();
            },
            error: function (xhr) {
                console.error("Error loading workplans:", xhr.responseJSON);
                reject(xhr);
            },
        });
    });
}

/**
 * Initialize Budget Code dropdown with server-side AJAX search.
 * Eliminates 20k DOM nodes — only loads up to 50 matching results per query.
 *
 * @param {string|null} preselectedCode  - Code value to pre-select (edit mode)
 * @param {string|null} preselectedLabel - Display label for the pre-selected code
 */
function _initBudgetCodeSearchDropdown(preselectedCode, preselectedLabel) {
    const select = document.getElementById("budgetCode");

    if (select.choicesInstance) {
        select.choicesInstance.destroy();
        select.choicesInstance = null;
    }

    // Remove old event listeners before re-adding to prevent accumulation
    if (select._choicesSearchHandler) {
        select.removeEventListener("search", select._choicesSearchHandler);
        select._choicesSearchHandler = null;
    }
    if (select._choicesChangeHandler) {
        select.removeEventListener("change", select._choicesChangeHandler);
        select._choicesChangeHandler = null;
    }

    // Start with minimal DOM — no 20k <option> nodes
    select.innerHTML =
        '<option value="">Type to search budget code...</option>';

    if (preselectedCode) {
        const opt = document.createElement("option");
        opt.value = preselectedCode;
        opt.textContent = preselectedLabel || preselectedCode;
        opt.selected = true;
        select.appendChild(opt);
        if (!_budgetCodeCache.has(preselectedCode)) {
            _budgetCodeCache.set(preselectedCode, {
                budget_code: preselectedCode,
                name: preselectedLabel || preselectedCode,
                inchargeCode: "",
            });
        }
    }

    const choices = new Choices(select, {
        searchEnabled: true,
        searchChoices: false, // disable client-side filtering — server does it
        searchFloor: 2,
        searchResultLimit: 50,
        searchPlaceholderValue: "Type at least 2 characters...",
        itemSelectText: "",
        noResultsText: "No results. Keep typing...",
        noChoicesText: "Type to search budget codes",
        shouldSort: false,
        removeItemButton: false,
    });

    select.choicesInstance = choices;

    // Fire AJAX search on Choices.js search event (debounced 300ms)
    const _budgetCodeSearchHandler = function (e) {
        const query = e.detail.value;
        clearTimeout(_budgetCodeSearchTimer);
        if (!query || query.length < 2) return;

        _budgetCodeSearchTimer = setTimeout(function () {
            $.ajax({
                url: "/budget-user/budget-codes/search",
                method: "GET",
                data: { q: query, limit: 50 },
                success: function (response) {
                    if (!response.success || !select.choicesInstance) return;

                    const results = response.data || [];
                    results.forEach(function (item) {
                        _budgetCodeCache.set(item.budget_code, item);
                    });

                    const newChoices = results.map(function (code) {
                        return {
                            value: code.budget_code,
                            label: code.budget_code + " - " + code.name,
                        };
                    });

                    select.choicesInstance.setChoices(
                        newChoices,
                        "value",
                        "label",
                        true,
                    );
                },
            });
        }, 300);
    };
    select._choicesSearchHandler = _budgetCodeSearchHandler;
    select.addEventListener("search", _budgetCodeSearchHandler);

    // Cost center auto-fill when budget code is selected
    const _budgetCodeChangeHandler = function (e) {
        // Use detail.value from Choices.js CustomEvent when available, fall back to element value
        const val =
            e && e.detail && e.detail.value !== undefined
                ? e.detail.value
                : this.value;
        if (!val) return;
        const cached = _budgetCodeCache.get(val);
        const inchargeCode = cached ? cached.inchargeCode || "" : "";

        $("#costCenter").val(inchargeCode);
        const costCenterEl = document.getElementById("costCenter");
        if (costCenterEl && costCenterEl.choicesInstance) {
            costCenterEl.choicesInstance.setChoiceByValue(inchargeCode);
        }
    };
    select._choicesChangeHandler = _budgetCodeChangeHandler;
    select.addEventListener("change", _budgetCodeChangeHandler);
}

/**
 * Load budget codes (now initialises AJAX search dropdown — no 20k preload)
 */
function loadBudgetCodes() {
    _initBudgetCodeSearchDropdown(null, null);
}

/**
 * Initialize Stock Code dropdown with server-side AJAX search + infinite scroll.
 * Loads 10 items on open, appends more on scroll, filters on type.
 *
 * @param {string|null} preselectedCode  - Code value to pre-select (edit mode)
 * @param {string|null} preselectedLabel - Display label for the pre-selected code
 */
function _initStockCodeSearchDropdown(preselectedCode, preselectedLabel) {
    const select = document.getElementById("stockCode");

    // ── Cleanup previous instance ──────────────────────────────────────────
    if (select.choicesInstance) {
        select.choicesInstance.destroy();
        select.choicesInstance = null;
    }
    [
        "_choicesSearchHandler",
        "_choicesChangeHandler",
        "_showDropdownHandler",
    ].forEach(function (key) {
        if (select[key]) {
            select.removeEventListener(
                key
                    .replace("Handler", "")
                    .replace("_choices", "")
                    .replace("_show", "show"),
                select[key],
            );
            select[key] = null;
        }
    });

    select.innerHTML = '<option value="">Select stock code...</option>';

    if (preselectedCode) {
        const opt = document.createElement("option");
        opt.value = preselectedCode;
        opt.textContent = preselectedLabel || preselectedCode;
        opt.selected = true;
        select.appendChild(opt);
        if (!_stockCodeCache.has(preselectedCode)) {
            _stockCodeCache.set(preselectedCode, {
                stock_code: preselectedCode,
                name: preselectedLabel || preselectedCode,
                budget_code: null,
                product_line: null,
                unit: null,
            });
        }
    }

    const choices = new Choices(select, {
        searchEnabled: true,
        searchChoices: false, // server-side only
        searchFloor: 1,
        searchResultLimit: 10,
        searchPlaceholderValue: "Search stock code...",
        itemSelectText: "",
        noResultsText: "No results found.",
        noChoicesText: "Loading...",
        shouldSort: false,
        removeItemButton: false,
    });

    select.choicesInstance = choices;

    // ── Infinite scroll state ──────────────────────────────────────────────
    let _scQuery = "";
    let _scPage = 1;
    let _scLoading = false;
    let _scHasMore = true;
    let _scScrollBound = false;

    // ── Core fetch function ────────────────────────────────────────────────
    function fetchStockCodes(query, page, replace) {
        if (_scLoading) return;
        if (!replace && !_scHasMore) return;

        _scLoading = true;

        $.ajax({
            url: "/budget-user/stock-codes/search",
            method: "GET",
            data: { q: query, limit: 10, page: page },
            success: function (response) {
                _scLoading = false;
                if (!response.success || !select.choicesInstance) return;

                _scHasMore = response.has_more || false;
                const results = response.data || [];

                results.forEach(function (item) {
                    _stockCodeCache.set(item.stock_code, item);
                });

                const newChoices = results.map(function (code) {
                    return {
                        value: code.stock_code,
                        label: code.stock_code + " - " + code.name,
                    };
                });

                // replace=true clears list; replace=false appends
                select.choicesInstance.setChoices(
                    newChoices,
                    "value",
                    "label",
                    replace,
                );
            },
            error: function () {
                _scLoading = false;
            },
        });
    }

    // ── Bind scroll listener on the Choices inner list (once per instance) ─
    function bindScrollListener() {
        if (_scScrollBound) return;
        _scScrollBound = true;

        const listEl = choices.choiceList && choices.choiceList.element;
        if (!listEl) return;

        listEl.addEventListener("scroll", function () {
            const threshold = 60;
            if (
                listEl.scrollTop + listEl.clientHeight >=
                listEl.scrollHeight - threshold
            ) {
                if (!_scLoading && _scHasMore) {
                    _scPage++;
                    fetchStockCodes(_scQuery, _scPage, false);
                }
            }
        });
    }

    // ── showDropdown: load first page when dropdown opens ─────────────────
    const _showDropdownHandler = function () {
        _scPage = 1;
        _scHasMore = true;
        fetchStockCodes(_scQuery, 1, true);
        bindScrollListener();
    };
    select._showDropdownHandler = _showDropdownHandler;
    select.addEventListener("showDropdown", _showDropdownHandler);

    // ── search event: user types — reset and load page 1 ─────────────────
    const _stockCodeSearchHandler = function (e) {
        const query = e.detail.value;
        clearTimeout(_stockCodeSearchTimer);

        _stockCodeSearchTimer = setTimeout(function () {
            _scQuery = query || "";
            _scPage = 1;
            _scHasMore = true;
            fetchStockCodes(_scQuery, 1, true);
        }, 300);
    };
    select._choicesSearchHandler = _stockCodeSearchHandler;
    select.addEventListener("search", _stockCodeSearchHandler);

    // ── Detect clear (user deletes all text) → reload page 1 ─────────────
    setTimeout(function () {
        const inputEl = choices.input && choices.input.element;
        if (!inputEl) return;
        inputEl.addEventListener("input", function () {
            if (this.value === "") {
                clearTimeout(_stockCodeSearchTimer);
                _scQuery = "";
                _scPage = 1;
                _scHasMore = true;
                fetchStockCodes("", 1, true);
            }
        });
    }, 0);

    // ── change: auto-fill related fields when a stock code is selected ────
    const _stockCodeChangeHandler = function (e) {
        const val =
            e && e.detail && e.detail.value !== undefined
                ? e.detail.value
                : this.value;
        if (!val) return;
        const cached = _stockCodeCache.get(val);
        if (!cached) return;

        if (cached.product_line) {
            $("#productLine").val(cached.product_line);
        }

        if (cached.unit && unitsData.length > 0) {
            const unitMatch = unitsData.find(function (u) {
                return u.unit.toLowerCase() === cached.unit.toLowerCase();
            });
            if (unitMatch) $("#unit").val(unitMatch.id);
        }

        // NOTE: Budget Code is intentionally NOT auto-filled here.
        // User must select Budget Code manually to avoid unintended data entry.
    };
    select._choicesChangeHandler = _stockCodeChangeHandler;
    select.addEventListener("change", _stockCodeChangeHandler);
}

/**
 * Load stock codes (now initialises AJAX search dropdown — no 20k preload)
 */
function loadStockCodes() {
    _initStockCodeSearchDropdown(null, null);
}

/**
 * Async version of loadBudgetCategories - returns a Promise
 */
function loadBudgetCategoriesAsync() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "/budget-user/budget-categories",
            method: "GET",
            success: function (response) {
                if (response.success) {
                    const select = $("#budgetCategoryId");
                    select.empty();
                    select.append(
                        '<option value="">Select Budget Category...</option>',
                    );

                    response.data.forEach((category) => {
                        select.append(
                            `<option value="${category.id}">${category.name}</option>`,
                        );
                    });
                }
                resolve();
            },
            error: function (xhr) {
                console.error(
                    "Error loading budget categories:",
                    xhr.responseJSON,
                );
                reject(xhr);
            },
        });
    });
}

/**
 * Async version of loadBudgetCodes — resolves instantly (no 20k preload).
 * Initialises the AJAX-search dropdown immediately.
 */
function loadBudgetCodesAsync() {
    _initBudgetCodeSearchDropdown(null, null);
    return Promise.resolve();
}

/**
 * Async version of loadStockCodes — resolves instantly (no 20k preload).
 * Initialises the AJAX-search dropdown immediately.
 */
function loadStockCodesAsync() {
    _initStockCodeSearchDropdown(null, null);
    return Promise.resolve();
}

/**
 * Async version of loadCostCenters - returns a Promise
 */
function loadCostCentersAsync() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "/budget-user/cost-centers",
            method: "GET",
            success: function (response) {
                if (response.success) {
                    const select = document.getElementById("costCenter");
                    select.innerHTML =
                        '<option value="">Select Cost Center</option>';

                    response.data.forEach((center) => {
                        const option = document.createElement("option");
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
                        searchPlaceholderValue: "Search cost center...",
                        itemSelectText: "Click to select",
                        shouldSort: false,
                    });

                    select.choicesInstance = choices;
                }
                resolve();
            },
            error: function (xhr) {
                console.error("Error loading cost centers:", xhr.responseJSON);
                reject(xhr);
            },
        });
    });
}

/**
 * Async version of loadSuppliers - returns a Promise
 */
function loadSuppliersAsync() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "/budget-user/suppliers",
            method: "GET",
            success: function (response) {
                if (response.success) {
                    suppliersData = response.data || [];
                    const select = $("#supplier");
                    select.empty();
                    select.append('<option value="">Select Supplier</option>');

                    suppliersData.forEach((supplier) => {
                        select.append(
                            `<option value="${supplier.id}">${supplier.supplier}</option>`,
                        );
                    });
                }
                resolve();
            },
            error: function (xhr) {
                console.error("Error loading suppliers:", xhr.responseJSON);
                reject(xhr);
            },
        });
    });
}

/**
 * Async version of loadUnits - returns a Promise
 */
function loadUnitsAsync() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "/budget-user/units",
            method: "GET",
            success: function (response) {
                if (response.success) {
                    unitsData = response.data || [];
                    const select = $("#unit");
                    select.empty();
                    select.append('<option value="">Select Unit</option>');

                    unitsData.forEach((unit) => {
                        select.append(
                            `<option value="${unit.id}">${unit.unit}</option>`,
                        );
                    });
                }
                resolve();
            },
            error: function (xhr) {
                console.error("Error loading units:", xhr.responseJSON);
                reject(xhr);
            },
        });
    });
}

/**
 * Async version of loadWorkplansForDropdownWithSelection - returns a Promise
 */
function loadWorkplansForDropdownWithSelectionAsync(selectedWorkplanId) {
    return new Promise((resolve, reject) => {
        if (!selectedDivisionId || !selectedYear) {
            resolve();
            return;
        }

        $.ajax({
            url: "/budget-user/workplans/dropdown",
            method: "GET",
            data: {
                division_id: selectedDivisionId,
                year: selectedYear,
            },
            success: function (response) {
                if (response.success) {
                    const select = document.getElementById("programId");

                    // Destroy existing Choices instance if it exists
                    if (programIdChoices) {
                        programIdChoices.destroy();
                    }

                    // Initialize Choices.js for searchable dropdown
                    programIdChoices = new Choices(select, {
                        searchEnabled: true,
                        searchPlaceholderValue: "Search work plan...",
                        itemSelectText: "Click to select",
                        shouldSort: false,
                        removeItemButton: false,
                        placeholder: true,
                        placeholderValue: "Select work plan",
                    });

                    // Clear existing choices
                    programIdChoices.clearChoices();

                    // Add default option
                    programIdChoices.setChoices(
                        [
                            {
                                value: "",
                                label: "Select Work Plan...",
                                selected: true,
                                disabled: false,
                            },
                        ],
                        "value",
                        "label",
                        true,
                    );

                    if (response.data && response.data.length > 0) {
                        // Add workplan options with consistent formatting
                        const choices = response.data.map((workplan) => {
                            const typeLabel =
                                workplan.kpi_type === "department"
                                    ? "Department"
                                    : "Section";
                            const typeBadge =
                                workplan.kpi_type === "department"
                                    ? "🏢"
                                    : "📋";
                            return {
                                value: workplan.id.toString(),
                                label: `${typeBadge} [${typeLabel}] ${workplan.activity}`,
                                customProperties: {
                                    kpi_type: workplan.kpi_type,
                                },
                            };
                        });

                        programIdChoices.setChoices(
                            choices,
                            "value",
                            "label",
                            false,
                        );

                        // Set selected workplan
                        if (selectedWorkplanId) {
                            programIdChoices.setChoiceByValue(
                                selectedWorkplanId.toString(),
                            );
                        }
                    }
                }
                resolve();
            },
            error: function (xhr) {
                console.error("Error loading workplans:", xhr.responseJSON);
                reject(xhr);
            },
        });
    });
}

/**
 * Save item (create or update)
 */
function saveItem() {
    // Parse formatted numbers before sending
    const priceEstimationValue = parseFormattedNumber(
        $("#priceEstimation").val(),
    );
    const totalValue = parseFormattedNumber($("#total").val());

    // Temporarily set pure numbers for form submission
    $("#priceEstimation").val(priceEstimationValue);
    $("#total").val(totalValue);
    // Validate category_type
    if (!$('input[name="category_type"]:checked').val()) {
        Swal.fire({
            icon: "warning",
            title: "Validation Error",
            text: "Please select a category type",
            confirmButtonColor: "#3085d6",
        });
        return;
    }
    const itemId = $("#itemId").val();
    const isEdit = itemId !== "";

    // Validate required fields
    const programId = $("#programId").val();
    const budgetCategoryId = $("#budgetCategoryId").val();
    const description = $("#description").val();

    if (!programId) {
        showToast("Please select a work plan", "error");
        return;
    }

    if (!budgetCategoryId) {
        showToast("Please select a budget category", "error");
        return;
    }

    if (!description) {
        showToast("Please enter a description", "error");
        return;
    }

    // Get supplier data
    const supplierId = $("#supplier").val();
    const selectedSupplier = suppliersData.find(
        (s) => s.id.toString() === supplierId.toString(),
    );
    const supplierName = selectedSupplier ? selectedSupplier.supplier : null;

    // Get unit data
    const unitId = $("#unit").val();
    const selectedUnit = unitsData.find(
        (u) => u.id.toString() === unitId.toString(),
    );
    const unitName = selectedUnit ? selectedUnit.unit : null;

    const formData = {
        kpi_workplan_id: programId,
        budget_category_id: budgetCategoryId,
        category_type: $('input[name="category_type"]:checked').val(),
        description: description,
        stock_code: $("#stockCode").val(),
        budget_code: $("#budgetCode").val(),
        product_line: $("#productLine").val(),
        cost_center: $("#costCenter").val(),
        beg_balance: $("#begBalance").val(),
        supplier_id: supplierId,
        supplier_name: supplierName,
        cons_rate: $("#consRate").val(),
        unit_id: unitId,
        unit_name: unitName,
        price_estimation: $("#priceEstimation").val(),
        price_estimation_description: $("#priceEstimationDescription").val(),
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
        division_id: selectedDivisionId,
    };

    showLoading();

    const url = isEdit ? `/budget-user/items/${itemId}` : "/budget-user/items";
    const method = isEdit ? "PUT" : "POST";

    $.ajax({
        url: url,
        method: method,
        data: formData,
        headers: {
            "X-CSRF-TOKEN": CSRF_TOKEN,
        },
        success: function (response) {
            hideLoading();
            if (response.success) {
                showToast(response.message, "success");
                $("#itemModal").modal("hide");
                loadAllBudgetItems();
            }
        },
        error: function (xhr) {
            hideLoading();
            const message = xhr.responseJSON?.message || "Failed to save item";
            showToast(message, "error");
        },
    });
}

/**
 * Delete item
 */
function deleteItem(itemId) {
    Swal.fire({
        title: "Delete Item?",
        text: "This action cannot be undone!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();

            $.ajax({
                url: `/budget-user/items/${itemId}`,
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": CSRF_TOKEN,
                },
                success: function (response) {
                    hideLoading();
                    if (response.success) {
                        showToast(
                            response.message || "Item deleted successfully",
                            "success",
                        );
                        loadAllBudgetItems(); // Reload all data
                    } else {
                        showToast(
                            response.message || "Failed to delete item",
                            "error",
                        );
                    }
                },
                error: function (xhr) {
                    hideLoading();
                    const response = xhr.responseJSON;
                    const message = response?.message || "Error deleting item";
                    showToast(message, "error");
                },
            });
        }
    });
}

/**
 * Show loading overlay
 */
function showLoading() {
    $("#loadingOverlay").addClass("show");
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    $("#loadingOverlay").removeClass("show");
}

/**
 * Show toast notification
 */
function showToast(message, type = "info") {
    const iconMap = {
        success: "success",
        error: "error",
        warning: "warning",
        info: "info",
    };

    Swal.fire({
        toast: true,
        position: "top-end",
        icon: iconMap[type] || "info",
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}

/**
 * Format currency
 */
function formatCurrency(value) {
    if (!value) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}

/**
 * Format number with thousand separator (without currency symbol)
 */
function formatNumberWithSeparator(value) {
    if (!value || value === 0) return "0";
    // Remove non-numeric characters except decimal point
    let numValue = value.toString().replace(/[^0-9.]/g, "");
    // Parse to float
    numValue = parseFloat(numValue);
    if (isNaN(numValue)) return "0";
    // Format with thousand separator using Indonesian locale
    return new Intl.NumberFormat("id-ID", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(numValue);
}

/**
 * Remove thousand separator and return pure number
 */
function parseFormattedNumber(value) {
    if (!value) return 0;
    // Remove all dots (thousand separator) and replace comma with dot (decimal separator)
    const cleaned = value.toString().replace(/\./g, "").replace(",", ".");
    const parsed = parseFloat(cleaned);
    return isNaN(parsed) ? 0 : parsed;
}

// ==================== APPROVAL FUNCTIONS ====================

/**
 * Render action buttons based on item status, verification status, and approval request
 */
function renderActionButtons(item) {
    console.log(item);

    const status = item.status || "draft";
    const verificationStatus = item.verification_status || "unverified";
    const approvalRequest = item.approval_request;
    let html = "";

    // Handle verification status first (for draft items)
    if (status === "draft") {
        switch (verificationStatus) {
            case "pending":
                // Waiting for verification
                html = `
                    <span class="badge bg-info"><i class="bi bi-hourglass-split me-1"></i>Waiting Verification</span>
                    <button type="button" class="btn btn-sm btn-secondary btn-action-item ms-1" onclick="showVerificationStatus(${item.id})" title="View Verification Status">
                        <i class="bi bi-eye"></i>
                    </button>
                `;
                return html;

            case "rejected":
                // Verification rejected - can edit and re-submit
                html = `
                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Verification Rejected</span>
                    <button type="button" class="btn btn-sm btn-primary btn-action-item ms-1" onclick="editItem(${item.id})" title="Edit & Re-submit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary btn-action-item ms-1" onclick="showVerificationStatus(${item.id})" title="View Rejection Reason">
                        <i class="bi bi-eye"></i>
                    </button>
                `;
                return html;

            case "verified":
                // This shouldn't happen normally because after verification, auto-submit should change status to pending
                // But handle it just in case
                html = `
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Verified</span>
                    <button type="button" class="btn btn-sm btn-warning btn-action-item ms-1" onclick="submitForApproval(${item.id})" title="Submit for Approval">
                        <i class="bi bi-send"></i>
                    </button>
                `;
                return html;

            case "unverified":
            default:
                // Draft & unverified - show edit, delete, submit for verification
                html = `
                    <button type="button" class="btn btn-sm btn-primary btn-action-item" onclick="editItem(${item.id})" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger btn-action-item" onclick="deleteItem(${item.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-warning btn-action-item" onclick="submitForVerification(${item.id})" title="Submit for Verification">
                        <i class="bi bi-clipboard-check"></i>
                    </button>
                `;
                return html;
        }
    }

    // Handle other statuses (pending approval, approved, rejected)
    switch (status) {
        case "approved":
            html = `
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                <button type="button" class="btn btn-sm btn-info btn-action-item ms-1" onclick="showApprovalTimeline(${item.id})" title="View Timeline">
                    <i class="bi bi-clock-history"></i>
                </button>
            `;
            break;

        case "rejected":
            html = `
                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                <button type="button" class="btn btn-sm btn-info btn-action-item ms-1" onclick="showApprovalTimeline(${item.id})" title="View Timeline">
                    <i class="bi bi-clock-history"></i>
                </button>
                <button type="button" class="btn btn-sm btn-primary btn-action-item ms-1" onclick="editItem(${item.id})" title="Edit & Re-submit">
                    <i class="bi bi-pencil"></i>
                </button>
            `;
            break;

        case "pending":
        case "in_progress":
            html = `
                <button type="button" class="btn btn-sm btn-info btn-action-item" onclick="showApprovalTimeline(${item.id})" title="View Timeline">
                    <i class="bi bi-clock-history"></i>
                </button>
            `;
            // Check if current user can approve
            if (approvalRequest && approvalRequest.details) {
                const pendingDetail = approvalRequest.details.find(
                    (d) =>
                        d.status === "pending" &&
                        d.employment_id === currentEmploymentId,
                );
                // Also check if it's the next in sequence
                const nextPending = approvalRequest.details
                    .filter((d) => d.status === "pending")
                    .sort((a, b) => a.level_sequence - b.level_sequence)[0];

                if (
                    pendingDetail &&
                    nextPending &&
                    pendingDetail.id === nextPending.id
                ) {
                    html += `
                        <button type="button" class="btn btn-sm btn-success btn-action-item ms-1" onclick="approveItem(${pendingDetail.id}, ${item.id})" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger btn-action-item ms-1" onclick="rejectItem(${pendingDetail.id})" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    `;
                }
            }
            break;

        default:
            // Fallback for any other status
            html = `
                <button type="button" class="btn btn-sm btn-primary btn-action-item" onclick="editItem(${item.id})" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
            `;
            break;
    }

    return html;
}

/**
 * Show approval timeline modal
 */
function showApprovalTimeline(itemId) {
    console.log(itemId);
    console.log(allItemsData);

    const item = allItemsData.find((i) => i.id === itemId);
    if (!item) {
        showToast("Item not found", "error");
        return;
    }

    // Populate item details
    const detailsHtml = `
        <div class="row">
            <div class="col-md-6">
                <strong>Description:</strong> ${item.description || "-"}<br>
                <strong>Category:</strong> ${item.category_type || "-"}<br>
                <strong>Status:</strong> <span class="badge bg-${getStatusBadgeClass(
                    item.status,
                )}">${item.status}</span>
            </div>
            <div class="col-md-6">
                <strong>Total:</strong> ${formatCurrency(item.total || 0)}<br>
                <strong>Workplan:</strong> ${item.workplan?.activity || "-"}
            </div>
        </div>
    `;
    $("#approvalItemDetails").html(detailsHtml);

    // Populate timeline
    let timelineHtml = "";
    const approvalRequest = item.approval_request;

    if (
        !approvalRequest ||
        !approvalRequest.details ||
        approvalRequest.details.length === 0
    ) {
        timelineHtml = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-info-circle fs-3"></i>
                <p class="mt-2">No approval request found. Submit this item for approval first.</p>
            </div>
        `;
    } else {
        const sortedDetails = [...approvalRequest.details].sort(
            (a, b) => a.level_sequence - b.level_sequence,
        );
        const nextPending = sortedDetails.find((d) => d.status === "pending");

        sortedDetails.forEach((detail, index) => {
            const isCurrentPending =
                nextPending && nextPending.id === detail.id;
            const statusClass = getTimelineStatusClass(
                detail.status,
                isCurrentPending,
            );
            const contentClass = isCurrentPending ? "current" : "";

            timelineHtml += `
                <div class="timeline-item ${statusClass} ${
                    isCurrentPending ? "current" : ""
                }">
                    <div class="timeline-content ${contentClass}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>Level ${detail.level_sequence}</strong>
                            <span class="badge bg-${getStatusBadgeClass(
                                detail.status,
                            )}">${capitalizeFirst(detail.status)}</span>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-person me-1"></i>${
                                detail.employment_name || "Unknown Approver"
                            }
                        </div>
                        ${
                            detail.approved_at
                                ? `
                            <div class="text-muted small mt-1">
                                <i class="bi bi-calendar me-1"></i>${formatDate(
                                    detail.approved_at,
                                )}
                            </div>
                        `
                                : ""
                        }
                    </div>
                </div>
            `;
        });
    }
    $("#approvalTimelineContent").html(timelineHtml);

    // Show/hide footer action buttons
    let footerHtml = `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`;

    if (approvalRequest && approvalRequest.details) {
        const myPendingDetail = approvalRequest.details.find(
            (d) =>
                d.status === "pending" &&
                d.employment_id === currentEmploymentId,
        );
        const nextPending = approvalRequest.details
            .filter((d) => d.status === "pending")
            .sort((a, b) => a.level_sequence - b.level_sequence)[0];

        if (
            myPendingDetail &&
            nextPending &&
            myPendingDetail.id === nextPending.id
        ) {
            footerHtml = `
                <button type="button" class="btn btn-danger" onclick="rejectItem(${myPendingDetail.id})">
                    <i class="bi bi-x-lg me-1"></i>Reject
                </button>
                <button type="button" class="btn btn-success" onclick="approveItem(${myPendingDetail.id}, ${itemId})">
                    <i class="bi bi-check-lg me-1"></i>Approve
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            `;
        }
    }
    $("#approvalTimelineFooter").html(footerHtml);

    $("#approvalTimelineModal").modal("show");
}

/**
 * Submit item for approval
 */
function submitForApproval(itemId) {
    Swal.fire({
        title: "Submit for Approval?",
        text: "This item will be submitted for approval. You cannot edit it until it is approved or rejected.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#ffc107",
        confirmButtonText: "Yes, Submit",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();

            $.ajax({
                url: `/workplan-budget-item-approval/${itemId}/submit`,
                method: "POST",
                data: { _token: CSRF_TOKEN },
                success: function (response) {
                    hideLoading();
                    if (response.success) {
                        showToast(
                            response.message || "Item submitted for approval",
                            "success",
                        );
                        loadAllBudgetItems(); // Refresh data
                    } else {
                        showToast(
                            response.message || "Failed to submit for approval",
                            "error",
                        );
                    }
                },
                error: function (xhr) {
                    hideLoading();
                    const msg =
                        xhr.responseJSON?.message ||
                        "Error submitting for approval";
                    showToast(msg, "error");
                },
            });
        }
    });
}

/**
 * Approve an item
 */
function approveItem(detailId, itemId) {
    Swal.fire({
        title: "Approve this item?",
        text: "Are you sure you want to approve this budget item?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#198754",
        confirmButtonText: "Yes, Approve",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();
            $.ajax({
                url: `/workplan-budget-item-approval/detail/${detailId}/approve`,
                method: "POST",
                data: { _token: CSRF_TOKEN },
                success: function (response) {
                    hideLoading();
                    if (response.success) {
                        showToast(
                            response.message || "Item approved successfully",
                            "success",
                        );
                        $("#approvalTimelineModal").modal("hide");
                        // Refresh approval tab if open
                        if (typeof loadPendingApprovalItems === "function") {
                            loadPendingApprovalItems();
                        }
                        // Refresh budget items if loaded
                        if (selectedDivisionId && selectedYear) {
                            loadAllBudgetItems();
                        }
                    } else {
                        showToast(
                            response.message || "Failed to approve",
                            "error",
                        );
                    }
                },
                error: function (xhr) {
                    hideLoading();
                    const msg =
                        xhr.responseJSON?.message || "Error approving item";
                    showToast(msg, "error");
                },
            });
        }
    });
}

/**
 * Reject an item - open comment modal
 */
function rejectItem(detailId) {
    $("#rejectDetailId").val(detailId);
    $("#rejectComments").val("");
    $("#approvalTimelineModal").modal("hide");
    $("#rejectCommentModal").modal("show");
}

/**
 * Confirm rejection with comments
 */
function confirmReject() {
    const detailId = $("#rejectDetailId").val();
    const comments = $("#rejectComments").val().trim();

    if (!comments) {
        showToast("Please provide a reason for rejection", "warning");
        return;
    }

    showLoading();
    $.ajax({
        url: `/workplan-budget-item-approval/detail/${detailId}/reject`,
        method: "POST",
        data: {
            _token: CSRF_TOKEN,
            comments: comments,
        },
        success: function (response) {
            hideLoading();
            if (response.success) {
                showToast(response.message || "Item rejected", "success");
                $("#rejectCommentModal").modal("hide");
                // Refresh approval tab if rejection came from there
                if (
                    window._rejectFromApprovalTab &&
                    typeof loadPendingApprovalItems === "function"
                ) {
                    loadPendingApprovalItems();
                    window._rejectFromApprovalTab = false;
                }
                // Also refresh budget items if loaded
                if (selectedDivisionId && selectedYear) {
                    loadAllBudgetItems();
                }
            } else {
                showToast(response.message || "Failed to reject", "error");
            }
        },
        error: function (xhr) {
            hideLoading();
            const msg = xhr.responseJSON?.message || "Error rejecting item";
            showToast(msg, "error");
        },
    });
}

// Helper functions
function getStatusBadgeClass(status) {
    const classes = {
        draft: "secondary",
        pending: "warning",
        in_progress: "info",
        approved: "success",
        rejected: "danger",
        skipped: "secondary",
        cancelled: "secondary",
    };
    return classes[status] || "secondary";
}

function getTimelineStatusClass(status, isCurrentPending) {
    if (isCurrentPending) return "pending";
    const classes = {
        approved: "completed",
        rejected: "rejected",
        skipped: "skipped",
        pending: "",
    };
    return classes[status] || "";
}

function capitalizeFirst(str) {
    if (!str) return "";
    return str.charAt(0).toUpperCase() + str.slice(1).replace("_", " ");
}

function formatDate(dateStr) {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

// ==================== VERIFICATION FUNCTIONS ====================

/**
 * Submit item for verification
 */
function submitForVerification(itemId) {
    const item = allItemsData.find((i) => i.id === itemId);

    if (!item) {
        showToast("Item not found", "error");
        return;
    }

    // Check if cost_center is filled
    if (!item.cost_center) {
        showToast(
            "Cost center is required for verification. Please edit the item first.",
            "warning",
        );
        return;
    }

    Swal.fire({
        title: "Submit for Verification?",
        html: `
            <p>This item will be submitted for price verification.</p>
            <p class="text-muted">Verifier will validate the price estimation before approval process.</p>
            <hr>
            <small><strong>Cost Center:</strong> ${item.cost_center}</small><br>
            <small><strong>Price Estimation:</strong> ${formatCurrency(item.price_estimation || 0)}</small>
        `,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#ffc107",
        confirmButtonText: "Yes, Submit for Verification",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();

            $.ajax({
                url: `/budget-verification/${itemId}/submit`,
                method: "POST",
                data: { _token: CSRF_TOKEN },
                success: function (response) {
                    hideLoading();
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Submitted!",
                            html: `
                                <p>${response.message}</p>
                                <p class="text-muted">Verifier count: ${response.data?.verifier_count || 0}</p>
                            `,
                            timer: 3000,
                            showConfirmButton: false,
                        });
                        loadAllBudgetItems(); // Refresh data
                    } else {
                        showToast(
                            response.message ||
                                "Failed to submit for verification",
                            "error",
                        );
                    }
                },
                error: function (xhr) {
                    hideLoading();
                    const msg =
                        xhr.responseJSON?.message ||
                        "Error submitting for verification";
                    showToast(msg, "error");
                },
            });
        }
    });
}

/**
 * Show verification status modal
 */
function showVerificationStatus(itemId) {
    showLoading();

    $.ajax({
        url: `/budget-verification/${itemId}/status`,
        method: "GET",
        success: function (response) {
            hideLoading();

            if (response.success) {
                const data = response.data;

                let candidatesHtml = "";
                if (data.candidates && data.candidates.length > 0) {
                    candidatesHtml = data.candidates
                        .map(
                            (c) => `
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <span>${c.verifier_name || c.verifier_id}</span>
                            ${c.is_executor ? '<span class="badge bg-success">Executor</span>' : '<span class="badge bg-secondary">Candidate</span>'}
                        </div>
                    `,
                        )
                        .join("");
                } else {
                    candidatesHtml =
                        '<p class="text-muted">No candidates found</p>';
                }

                let historyHtml = "";
                if (data.history && data.history.length > 0) {
                    historyHtml = data.history
                        .map(
                            (h) => `
                        <div class="border-bottom py-2">
                            <div class="d-flex justify-content-between">
                                <strong>${h.verifier_name || h.verifier_id}</strong>
                                <small class="text-muted">${formatDate(h.created_at)}</small>
                            </div>
                            <div class="small">
                                <span class="text-muted">Submitted:</span> ${formatCurrency(h.submitted_price)}<br>
                                <span class="text-muted">Verified:</span> ${formatCurrency(h.verified_price)}
                            </div>
                            ${h.notes ? `<div class="small mt-1"><span class="text-muted">Notes:</span> ${h.notes}</div>` : ""}
                        </div>
                    `,
                        )
                        .join("");
                } else {
                    historyHtml =
                        '<p class="text-muted">No verification history</p>';
                }

                const statusBadge = getVerificationStatusBadge(
                    data.verification_status,
                );

                Swal.fire({
                    title: "Verification Status",
                    html: `
                        <div class="text-start">
                            <div class="mb-3">
                                <strong>Status:</strong> ${statusBadge}
                            </div>
                            <div class="mb-3">
                                <strong>Price Estimation:</strong> ${formatCurrency(data.price_estimation || 0)}
                            </div>
                            <div class="mb-3">
                                <strong>Verified Price:</strong> ${formatCurrency(data.total || 0)}
                            </div>
                            <hr>
                            <h6>Verification Candidates</h6>
                            <div class="mb-3">${candidatesHtml}</div>
                            <hr>
                            <h6>Verification History</h6>
                            <div>${historyHtml}</div>
                        </div>
                    `,
                    width: "600px",
                    showConfirmButton: true,
                    confirmButtonText: "Close",
                });
            } else {
                showToast(
                    response.message || "Failed to load verification status",
                    "error",
                );
            }
        },
        error: function (xhr) {
            hideLoading();
            const msg =
                xhr.responseJSON?.message ||
                "Error loading verification status";
            showToast(msg, "error");
        },
    });
}

/**
 * Get verification status badge HTML
 */
function getVerificationStatusBadge(status) {
    const badges = {
        unverified: '<span class="badge bg-secondary">Unverified</span>',
        pending:
            '<span class="badge bg-warning text-dark">Pending Verification</span>',
        verified: '<span class="badge bg-success">Verified</span>',
        rejected: '<span class="badge bg-danger">Rejected</span>',
    };
    return badges[status] || badges["unverified"];
}

/**
 * Render status badges for table (verification + approval status)
 */
function renderStatusBadges(item) {
    const status = item.status || "draft";
    const verificationStatus = item.verification_status || "unverified";
    let html = "";

    // Verification status badge
    const verificationBadges = {
        unverified:
            '<span class="badge bg-light text-dark" style="font-size:10px;">Unverified</span>',
        pending:
            '<span class="badge bg-info" style="font-size:10px;">Verifying</span>',
        verified:
            '<span class="badge bg-success" style="font-size:10px;">Verified</span>',
        rejected:
            '<span class="badge bg-danger" style="font-size:10px;">V.Rejected</span>',
    };

    // Approval status badge
    const statusBadges = {
        draft: '<span class="badge bg-secondary" style="font-size:10px;">Draft</span>',
        pending:
            '<span class="badge bg-warning text-dark" style="font-size:10px;">Pending</span>',
        in_progress:
            '<span class="badge bg-info" style="font-size:10px;">In Progress</span>',
        approved:
            '<span class="badge bg-success" style="font-size:10px;">Approved</span>',
        rejected:
            '<span class="badge bg-danger" style="font-size:10px;">Rejected</span>',
    };

    // Show verification badge first if not verified yet (only for draft items)
    if (status === "draft" && verificationStatus !== "verified") {
        html +=
            verificationBadges[verificationStatus] ||
            verificationBadges["unverified"];
    } else {
        // Show approval status
        html += statusBadges[status] || statusBadges["draft"];
    }

    return html;
}
