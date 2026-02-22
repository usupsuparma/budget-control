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
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
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

        .level-1 {
            background: #d1e7dd;
            color: #0f5132;
        }

        .level-2 {
            background: #cff4fc;
            color: #055160;
        }

        .level-3 {
            background: #fff3cd;
            color: #664d03;
        }

        .level-4 {
            background: #f8d7da;
            color: #842029;
        }

        .level-5 {
            background: #d3d3d4;
            color: #41464b;
        }

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
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr("href");

                if (target === '#modules') {
                    loadModules();
                } else if (target === '#templates') {
                    loadTemplates();
                    loadModulesForDropdown();
                } else if (target === '#flowdetails') {
                    loadAllTemplatesWithFlowDetails();
                    loadEmployments();
                    loadLpjApprovers();
                }
            });

            // NOTE: Template dropdown removed - now using accordion direct view

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

            $('#lpjApproverForm').on('submit', function(e) {
                e.preventDefault();
                saveLpjApprover();
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
                url: '{{ route('approval.modules.data') }}',
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
                <td>${item.condition_field ? '<code>' + item.condition_field + '</code>' : '<span class="text-muted">-</span>'}</td>
                <td>
                    <span class="badge bg-${item.is_active ? 'success' : 'secondary'}">
                        ${item.is_active ? 'Aktif' : 'Nonaktif'}
                    </span>
                </td>
                <td>
                    
                </td>
            </tr>
        `);

                /**@argument
                 * disable action buttons for modules
                 * <button class="btn btn-sm btn-outline-primary me-1" onclick="editModule(${item.id})">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteModule(${item.id})">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                 * **/
            });
        }

        function loadAvailableTables(excludeId = null, currentTableName = null) {
            let url = '{{ route('approval.modules.tables') }}';
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
                            select.append(
                                `<option value="${currentTableName}" selected>${response.data[currentTableName]}</option>`
                            );
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
                            showAlert(
                                'Semua tabel sudah memiliki module. Tidak ada tabel yang tersedia untuk membuat module baru.',
                                'warning');
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
                url: '{{ route('approval.modules.data') }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const item = response.data.find(m => m.id === id);
                        if (item) {
                            isEditMode = true;
                            $('#moduleModalTitle').text('Edit Module');
                            $('#module-id').val(item.id);
                            $('#module_name').val(item.module_name);
                            $('#condition_field').val(item.condition_field || '');
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
                condition_field: $('#condition_field').val(),
                is_active: $('#module_is_active').is(':checked') ? 1 : 0
            };

            const moduleId = $('#module-id').val();
            let url = '{{ route('approval.modules.store') }}';
            let method = 'POST';

            if (isEditMode && moduleId) {
                url = `{{ url('approval/modules/update') }}/${moduleId}`;
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
                        url: `{{ url('approval/modules/delete') }}/${id}`,
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

        function loadModulesForDropdown(callback) {
            const excludeTemplateId = isEditMode ? $('#template-id').val() : null;

            $.ajax({
                url: '{{ route('approval.templates.modules') }}',
                type: 'GET',
                data: {
                    exclude_template_id: excludeTemplateId
                },
                success: function(response) {
                    if (response.success) {
                        const select = $('#template_module_id');
                        select.empty().append('<option value="">Pilih Module</option>');
                        response.data.forEach(item => {
                            select.append(
                                `<option value="${item.id}" data-condition-field="${item.condition_field || ''}">${item.module_name}</option>`
                            );
                        });

                        // Update condition_field display when module changes
                        select.off('change').on('change', function() {
                            const selectedOption = $(this).find('option:selected');
                            const conditionField = selectedOption.data('condition-field');
                            const displayField = $('#display_condition_field');

                            if (conditionField) {
                                displayField.html(`<code>${conditionField}</code>`);
                            } else {
                                displayField.html(
                                    '<span class="text-muted">Tidak ada condition field</span>');
                            }
                        });

                        // Execute callback if provided
                        if (typeof callback === 'function') {
                            callback();
                        }
                    }
                }
            });
        }

        function loadTemplates() {
            $.ajax({
                url: '{{ route('approval.templates.data') }}',
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

            // Enable module select for new template
            $('#template_module_id').prop('disabled', false);
            $('#display_condition_field').html('<span class="text-muted">Pilih module terlebih dahulu</span>');

            // Hide uppline config section for new template
            $('#upplineConfigSection').hide();
            $('#upplineConfigsTableBody').html(`
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        <small>No configurations yet. Click "Add Level Configuration" to start.</small>
                    </td>
                </tr>
            `);

            loadModulesForDropdown();
            $('#templateModal').modal('show');
        }

        function editTemplate(id) {
            $.ajax({
                url: '{{ route('approval.templates.data') }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const item = response.data.find(t => t.id === id);
                        if (item) {
                            isEditMode = true;

                            // Populate form first
                            $('#templateModalTitle').text('Edit Template');
                            $('#template-id').val(item.id);
                            $('#template_name').val(item.template_name);
                            $('#template_priority').val(item.priority);
                            $('#use_uppline_chain').prop('checked', item.use_uppline_chain);
                            $('#use_threshold').prop('checked', item.use_threshold);
                            $('#template_is_active').prop('checked', item.is_active);

                            // Show/hide uppline config section and load configs if needed
                            if (item.use_uppline_chain) {
                                $('#upplineConfigSection').show();
                                currentEditingTemplateId = item.id;
                                loadUpplineConfigs(item.id);
                            } else {
                                $('#upplineConfigSection').hide();
                            }

                            // Load modules dropdown, then set selected module
                            loadModulesForDropdown(() => {
                                const select = $('#template_module_id');

                                // If module not in dropdown (because already used), add it manually
                                if (select.find(`option[value="${item.module_id}"]`).length === 0) {
                                    select.append(
                                        `<option value="${item.module_id}" data-condition-field="${item.condition_field || ''}">${item.module ? item.module.module_name : 'Module'}</option>`
                                    );
                                }

                                // Set selected value
                                select.val(item.module_id).trigger('change');

                                // Display condition field
                                const displayField = $('#display_condition_field');
                                if (item.condition_field) {
                                    displayField.html(`<code>${item.condition_field}</code>`);
                                } else {
                                    displayField.html(
                                        '<span class="text-muted">Tidak ada condition field</span>');
                                }

                                // Disable module select in edit mode (after setting value)
                                select.prop('disabled', true);

                                $('#templateModal').modal('show');
                            });
                        }
                    }
                }
            });
        }

        function saveTemplate() {
            const templateId = $('#template-id').val();
            const data = {
                _token: '{{ csrf_token() }}',
                template_name: $('#template_name').val(),
                use_uppline_chain: $('#use_uppline_chain').is(':checked') ? 1 : 0,
                use_threshold: $('#use_threshold').is(':checked') ? 1 : 0,
                priority: $('#template_priority').val() || 1,
                is_active: $('#template_is_active').is(':checked') ? 1 : 0
            };

            let url = '{{ route('approval.templates.store') }}';
            let method = 'POST';

            if (isEditMode && templateId) {
                // EDIT MODE: Don't send module_id (module cannot be changed)
                url = `{{ url('approval/templates/update') }}/${templateId}`;
                method = 'POST';
                data._method = 'PUT'; // Laravel method spoofing
            } else {
                // CREATE MODE: Send module_id
                data.module_id = $('#template_module_id').val();
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
                        url: `{{ url('approval/templates/delete') }}/${id}`,
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

        function loadAllTemplatesWithFlowDetails() {
            $.ajax({
                url: '{{ url('approval/templates/with-flow-details') }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        if (response.data.length === 0) {
                            $('#templatesAccordion').hide();
                            $('#noTemplatesPlaceholder').show();
                        } else {
                            $('#templatesAccordion').show();
                            $('#noTemplatesPlaceholder').hide();
                            renderTemplatesAccordion(response.data);
                        }
                    } else {
                        showAlert(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    showAlert('Gagal memuat data templates', 'error');
                }
            });
        }

        function renderTemplatesAccordion(templates) {
            const accordion = $('#templatesAccordion');
            accordion.empty();

            templates.forEach(template => {
                const accordionId = `template-${template.id}`;
                const hasApprovers = template.approvers.length > 0;

                const accordionItem = `
                    <div class="accordion-item mb-2 border rounded">
                        <h2 class="accordion-header" id="heading-${template.id}">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#${accordionId}"
                                    aria-expanded="false"
                                    aria-controls="${accordionId}">
                                <div class="d-flex align-items-center w-100 justify-content-between pe-3">
                                    <div>
                                        <strong>${template.template_name}</strong>
                                        <span class="badge bg-info ms-2">${template.module_name}</span>
                                        <span class="badge bg-${hasApprovers ? 'success' : 'secondary'} ms-1">
                                            ${template.approvers_count} Approver(s)
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="${accordionId}" class="accordion-collapse collapse" 
                             aria-labelledby="heading-${template.id}"
                             data-bs-parent="#templatesAccordion">
                            <div class="accordion-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted small">
                                        <i class="ri-information-line"></i> 
                                        Daftar approver untuk template "${template.template_name}"
                                    </span>
                                    <button class="btn btn-sm btn-primary" 
                                            onclick="showAddFlowDetailModal(${template.id})">
                                        <i class="ri-add-line me-1"></i> Tambah Approver
                                    </button>
                                </div>

                                ${hasApprovers ? renderApproversTable(template.id, template.approvers) : renderNoApprovers()}
                            </div>
                        </div>
                    </div>
                `;

                accordion.append(accordionItem);
            });
        }

        function renderApproversTable(templateId, approvers) {
            let rows = '';
            approvers.forEach((approver, index) => {
                rows += `
                    <tr>
                        <td>${index + 1}</td>
                        <td><span class="level-badge level-${approver.level_sequence}">${approver.level_sequence}</span></td>
                        <td><strong>${approver.employee_name}</strong></td>
                        <td>${formatCurrency(approver.threshold_amount)}</td>
                        <td>
                            <span class="badge bg-${approver.is_required ? 'primary' : 'secondary'}">
                                ${approver.is_required ? 'Required' : 'Optional'}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1" 
                                    onclick="editFlowDetail(${approver.id}, ${templateId})">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteFlowDetail(${approver.id}, ${templateId})">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            return `
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Level</th>
                                <th>Employee (Approver)</th>
                                <th>Threshold Amount</th>
                                <th width="12%">Required</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }

        function renderNoApprovers() {
            return `
                <div class="text-center text-muted py-4">
                    <i class="ri-user-unfollow-line" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    <p class="mt-2 mb-0">Belum ada approver. Klik "Tambah Approver" untuk menambahkan.</p>
                </div>
            `;
        }

        function loadEmployments() {
            $.ajax({
                url: '{{ route('approval.employments.data') }}',
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
                url: `{{ url('approval/flow-details/data') }}/${templateId}`,
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
                const employeeName = item.employment?.employee ?
                    `${item.employment.employee.first_name} ${item.employment.employee.last_name || ''}` :
                    'N/A';

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

        function showAddFlowDetailModal(templateId) {
            if (!templateId) {
                showAlert('Template ID tidak valid', 'error');
                return;
            }

            isEditMode = false;
            selectedTemplateId = templateId;
            $('#flowDetailModalTitle').text('Tambah Approver');
            $('#flowDetailForm')[0].reset();
            $('#flowdetail-id').val('');
            $('#flowdetail_template_id').val(templateId);
            $('#level_sequence').val(1);
            $('#is_required').prop('checked', true);
            $('#flowDetailModal').modal('show');
        }

        function editFlowDetail(id, templateId) {
            selectedTemplateId = templateId;
            $.ajax({
                url: `{{ url('approval/flow-details/data') }}/${templateId}`,
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
                is_required: $('#is_required').is(':checked') ? 1 : 0
            };

            const detailId = $('#flowdetail-id').val();
            let url = '{{ route('approval.flowdetails.store') }}';
            let method = 'POST';

            if (isEditMode && detailId) {
                url = `{{ url('approval/flow-details/update') }}/${detailId}`;
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
                        $('#flowDetailModal').modal('hide');
                        loadAllTemplatesWithFlowDetails();
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
                        url: `{{ url('approval/flow-details/delete') }}/${id}`,
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
                                loadAllTemplatesWithFlowDetails();
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

        // ========== UPPLINE CONFIGS FUNCTIONS ==========

        let divisionsData = [];
        let currentEditingTemplateId = null;

        // Toggle uppline config section based on checkbox
        $('#use_uppline_chain').on('change', function() {
            if ($(this).is(':checked')) {
                $('#upplineConfigSection').slideDown();

                // Load uppline configs if editing existing template
                const templateId = $('#template-id').val();
                if (templateId) {
                    currentEditingTemplateId = templateId;
                    loadUpplineConfigs(templateId);
                }
            } else {
                $('#upplineConfigSection').slideUp();
                $('#upplineConfigsTableBody').html(`
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            <small>No configurations yet. Click "Add Level Configuration" to start.</small>
                        </td>
                    </tr>
                `);
            }
        });

        // Handle uppline config form submission
        $('#upplineConfigForm').on('submit', function(e) {
            e.preventDefault();
            saveUpplineConfig();
        });

        function loadDivisions() {
            $.ajax({
                url: '{{ route('approval.divisions.data') }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        divisionsData = response.data;
                        populateDivisionDropdown();
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load divisions:', xhr);
                }
            });
        }

        function populateDivisionDropdown() {
            const select = $('#upplineconfig_division_id');
            select.find('option:not(:first)').remove(); // Keep "Default" option

            divisionsData.forEach(division => {
                select.append(`<option value="${division.id}">${division.division_name}</option>`);
            });
        }

        function loadUpplineConfigs(templateId) {
            $.ajax({
                url: `{{ url('approval/uppline-configs/data') }}/${templateId}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderUpplineConfigsTable(response.data);
                    }
                },
                error: function(xhr) {
                    showAlert('Failed to load uppline configs', 'error');
                }
            });
        }

        function renderUpplineConfigsTable(data) {
            const tbody = $('#upplineConfigsTableBody');
            tbody.empty();

            if (data.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <small>No configurations yet. Click "Add Level Configuration" to start.</small>
                        </td>
                    </tr>
                `);
                return;
            }

            data.forEach(config => {
                const divisionBadge = config.division_id ?
                    `<span class="badge bg-primary">${config.division_name}</span>` :
                    `<span class="badge bg-secondary">Default (All Divisions)</span>`;
                
                const thresholdDisplay = config.threshold_amount && config.threshold_amount > 0 ?
                    `<span class="badge bg-info">Rp ${formatThousand(config.threshold_amount)}</span>` :
                    `<span class="text-muted">-</span>`;

                tbody.append(`
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-info">${config.step_sequence}</span>
                        </td>
                        <td>${divisionBadge}</td>
                        <td><strong>${config.job_level_name}</strong></td>
                        <td>${thresholdDisplay}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning me-1" onclick="editUpplineConfig(${config.id})">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteUpplineConfig(${config.id})">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }

        function showAddUpplineConfigModal() {
            const templateId = $('#template-id').val();

            if (!templateId) {
                showAlert('Please save the template first before adding uppline configs', 'warning');
                return;
            }

            isEditMode = false;
            $('#upplineConfigModalTitle').text('Add Level Configuration');
            $('#upplineConfigForm')[0].reset();
            $('#upplineconfig-id').val('');
            $('#upplineconfig-template-id').val(templateId);
            $('#upplineconfig_step_sequence').val(1);
            $('#upplineconfig_threshold_amount').val('');

            loadDivisions();
            $('#upplineConfigModal').modal('show');
        }

        function editUpplineConfig(id) {
            const templateId = $('#template-id').val();

            $.ajax({
                url: `{{ url('approval/uppline-configs/data') }}/${templateId}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const config = response.data.find(c => c.id === id);
                        if (config) {
                            isEditMode = true;
                            $('#upplineConfigModalTitle').text('Edit Level Configuration');
                            $('#upplineconfig-id').val(config.id);
                            $('#upplineconfig-template-id').val(config.template_id);
                            $('#upplineconfig_division_id').val(config.division_id || '');
                            $('#upplineconfig_step_sequence').val(config.step_sequence);
                            $('#upplineconfig_job_level_name').val(config.job_level_name);
                            
                            // Populate threshold_amount with formatted value
                            if (config.threshold_amount && config.threshold_amount > 0) {
                                $('#upplineconfig_threshold_amount').val(formatThousand(config.threshold_amount));
                            } else {
                                $('#upplineconfig_threshold_amount').val('');
                            }

                            loadDivisions();

                            setTimeout(() => {
                                $('#upplineconfig_division_id').val(config.division_id || '');
                                $('#upplineConfigModal').modal('show');
                            }, 200);
                        }
                    }
                }
            });
        }

        function saveUpplineConfig() {
            const data = {
                _token: '{{ csrf_token() }}',
                template_id: $('#upplineconfig-template-id').val(),
                division_id: $('#upplineconfig_division_id').val() || null,
                step_sequence: $('#upplineconfig_step_sequence').val(),
                job_level_name: $('#upplineconfig_job_level_name').val(),
                threshold_amount: parseThousand($('#upplineconfig_threshold_amount').val()) || 0,
            };

            const configId = $('#upplineconfig-id').val();
            let url = '{{ route('approval.upplineconfigs.store') }}';
            let method = 'POST';

            if (isEditMode && configId) {
                url = `{{ url('approval/uppline-configs/update') }}/${configId}`;
                method = 'POST'; // Use POST with _method for Laravel
                data._method = 'PUT'; // Laravel method spoofing
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
                        $('#upplineConfigModal').modal('hide');
                        loadUpplineConfigs(data.template_id);
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

        function deleteUpplineConfig(id) {
            Swal.fire({
                title: 'Hapus Konfigurasi?',
                text: 'Level konfigurasi ini akan dihapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('approval/uppline-configs/delete') }}/${id}`,
                        type: 'POST', // Use POST with _method for Laravel
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE' // Laravel method spoofing
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                const templateId = $('#template-id').val();
                                loadUpplineConfigs(templateId);
                            }
                        },
                        error: function(xhr) {
                            showAlert(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                        }
                    });
                }
            });
        }

        // ========== LPJ APPROVAL MASTER FUNCTIONS ==========

        let lpjApproversData = [];
        let isEditLpjMode = false;

        /**
         * Load all LPJ approvers
         */
        function loadLpjApprovers() {
            $.ajax({
                url: '{{ route('lpjApprovalMaster.data') }}',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        lpjApproversData = response.data;
                        renderLpjApproversTable(response.data);
                    }
                },
                error: function(xhr) {
                    showAlert('Gagal memuat data LPJ approvers', 'error');
                }
            });
        }

        /**
         * Render LPJ approvers table
         */
        function renderLpjApproversTable(data) {
            const tbody = $('#lpjApproversTableBody');
            tbody.empty();

            if (data.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="ri-inbox-line" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p class="mt-2 mb-0">Belum ada approver LPJ</p>
                        </td>
                    </tr>
                `);
                return;
            }

            data.forEach((item, index) => {
                const statusBadge = item.is_active
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';

                tbody.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <span class="level-badge level-${Math.min(item.approval_sequence, 5)}">
                                ${item.approval_sequence}
                            </span>
                        </td>
                        <td>${item.employee_name}</td>
                        <td>${item.job_position || '-'}</td>
                        <td>${statusBadge}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" onclick="editLpjApprover(${item.id})" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-danger ms-1" onclick="deleteLpjApprover(${item.id})" title="Hapus">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }

        /**
         * Load available employees for LPJ dropdown
         */
        function loadLpjAvailableEmployees() {
            $.ajax({
                url: '{{ route('lpjApprovalMaster.availableEmployees') }}',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const select = $('#lpj_employment_id');
                        select.empty();
                        select.append('<option value="">Pilih Employee</option>');
                        
                        response.data.forEach(emp => {
                            select.append(`<option value="${emp.id}">${emp.name} - ${emp.job_position}</option>`);
                        });
                    }
                },
                error: function(xhr) {
                    showAlert('Gagal memuat data employees', 'error');
                }
            });
        }

        /**
         * Show add LPJ approver modal
         */
        function showAddLpjApproverModal() {
            isEditLpjMode = false;
            $('#lpjApproverModalTitle').text('Tambah LPJ Approver');
            $('#lpjApproverForm')[0].reset();
            $('#lpjapprover-id').val('');
            $('#lpj_approval_sequence').val(lpjApproversData.length + 1);
            $('#lpj_is_active').prop('checked', true);
            $('#lpjEmploymentSelectDiv').show();
            loadLpjAvailableEmployees();
            $('#lpjApproverModal').modal('show');
        }

        /**
         * Edit LPJ approver
         */
        function editLpjApprover(id) {
            const approver = lpjApproversData.find(a => a.id === id);
            if (!approver) {
                showAlert('Data tidak ditemukan', 'error');
                return;
            }

            isEditLpjMode = true;
            $('#lpjApproverModalTitle').text('Edit LPJ Approver');
            $('#lpjapprover-id').val(approver.id);
            $('#lpj_approval_sequence').val(approver.approval_sequence);
            $('#lpj_is_active').prop('checked', approver.is_active);
            
            // For edit mode, we don't change employment, so hide the select
            $('#lpjEmploymentSelectDiv').hide();
            
            $('#lpjApproverModal').modal('show');
        }

        /**
         * Save LPJ approver (create or update)
         */
        function saveLpjApprover() {
            const id = $('#lpjapprover-id').val();
            const data = {
                approval_sequence: $('#lpj_approval_sequence').val(),
                is_active: $('#lpj_is_active').is(':checked') ? 1 : 0,
                _token: '{{ csrf_token() }}'
            };

            // Only include employment_id when creating new
            if (!isEditLpjMode) {
                data.employment_id = $('#lpj_employment_id').val();
                
                if (!data.employment_id) {
                    showAlert('Pilih employee terlebih dahulu', 'warning');
                    return;
                }
            }

            let url = '{{ route('lpjApprovalMaster.store') }}';
            let method = 'POST';

            if (isEditLpjMode && id) {
                url = '{{ route('lpjApprovalMaster.update', ':id') }}'.replace(':id', id);
                method = 'POST';
                data._method = 'PUT';
            }

            $.ajax({
                url: url,
                method: method,
                data: data,
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        $('#lpjApproverModal').modal('hide');
                        loadLpjApprovers();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Terjadi kesalahan';
                    showAlert(message, 'error');
                }
            });
        }

        /**
         * Delete LPJ approver
         */
        function deleteLpjApprover(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus approver ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('lpjApprovalMaster.destroy', ':id') }}'.replace(':id', id),
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                loadLpjApprovers();
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
