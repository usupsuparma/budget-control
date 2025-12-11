@extends('layouts.master')

@section('title', 'Budget Admin | Budget Control')

@section('title-sub', 'Budget Admin')
@section('pagetitle', 'Budget Administration')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        .table-budget-admin {
            font-size: 13px;
        }
        
        .table-budget-admin tbody tr.level-0 td {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        
        .table-budget-admin tbody tr.level-1 td {
            padding-left: 30px;
            background-color: #fefefe;
        }
        
        .table-budget-admin tbody tr.level-2 td {
            padding-left: 60px;
        }
        
        .table-budget-admin tbody tr.level-3 td {
            padding-left: 90px;
        }

        .table-budget-admin tbody tr.level-4 td {
            padding-left: 120px;
        }
        
        .table-budget-admin tbody tr:not(.has-kpi) {
            opacity: 0.6;
        }
        
        .btn-view-link {
            padding: 2px 8px;
            font-size: 11px;
        }

        .filter-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        {{-- Filter Section --}}
        <div class="row">
            <div class="col-xl-12">
                <div class="filter-section">
                    <h6 class="mb-3">Filter Budget</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Year</label>
                            <select id="filter_year" class="form-select">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button id="btnLoadBudget" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i> Load Budget
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Budget Admin Table --}}
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Budget Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="budgetAdminTable" class="table table-bordered table-hover table-budget-admin" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th style="width: 250px;">Structure</th>
                                        <th style="width: 100px;">Company Policy</th>
                                        <th style="width: 100px;">KPI</th>
                                        <th style="width: 100px;">Work Plan</th>
                                        <th style="width: 100px;">Plan Budget</th>
                                        <th style="width: 100px;">Budget</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
            });
        </script>
    @endif
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        let budgetTable;

        $(document).ready(function() {
            // Initialize DataTable
            budgetTable = $('#budgetAdminTable').DataTable({
                processing: true,
                serverSide: false,
                paging: true,
                searching: true,
                ordering: false,
                info: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                pageLength: 25,
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading...',
                    emptyTable: "No data available",
                    zeroRecords: "No matching records found"
                },
                columns: [
                    { data: 'no' },
                    { data: 'structure' },
                    { data: 'company_policy' },
                    { data: 'kpi' },
                    { data: 'workplan' },
                    { data: 'plan_budget' },
                    { data: 'budget' }
                ],
                data: [] // Start with empty data
            });

            // Load initial data
            loadBudgetData();

            // Load budget button
            $('#btnLoadBudget').on('click', function() {
                loadBudgetData();
            });
        });

        function loadBudgetData() {
            const year = $('#filter_year').val();

            $('#btnLoadBudget').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Loading...');

            $.ajax({
                url: "{{ route('budget.admin.data') }}",
                type: 'GET',
                data: { year: year },
                success: function(response) {
                    if (response.success) {
                        renderBudgetTable(response.data);
                        
                        // Swal.fire({
                        //     icon: 'success',
                        //     title: 'Success!',
                        //     text: 'Budget data loaded successfully',
                        //     timer: 1500,
                        //     showConfirmButton: false
                        // });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load budget data'
                    });
                },
                complete: function() {
                    $('#btnLoadBudget').prop('disabled', false).html('<i class="fas fa-search me-1"></i> Load Budget');
                }
            });
        }

        function renderBudgetTable(data) {
            // Clear existing data
            budgetTable.clear().draw();

            // Add new rows
            data.forEach(function(row) {
                const hasKpiClass = row.has_kpi ? 'has-kpi' : '';
                const levelClass = 'level-' + row.level;

                const structureCell = `<div style="padding-left: ${row.level * 30}px;">${row.structure}</div>`;
                
                const companyPolicyCell = row.company_policy_url 
                    ? `<a href="${row.company_policy_url}" class="btn btn-sm btn-outline-primary btn-view-link">View</a>` 
                    : '<span class="text-muted">-</span>';
                
                const kpiCell = row.kpi_url 
                    ? `<a href="${row.kpi_url}" class="btn btn-sm btn-outline-success btn-view-link">View</a>` 
                    : '<span class="text-muted">-</span>';
                
                const workplanCell = row.workplan_url 
                    ? `<a href="${row.workplan_url}" class="btn btn-sm btn-outline-info btn-view-link">View</a>` 
                    : '<span class="text-muted">-</span>';
                
                const planBudgetCell = row.plan_budget_url 
                    ? `<a href="${row.plan_budget_url}" class="btn btn-sm btn-outline-warning btn-view-link">View</a>` 
                    : '<span class="text-muted">-</span>';
                
                const budgetCell = row.budget_url 
                    ? `<a href="${row.budget_url}" class="btn btn-sm btn-outline-danger btn-view-link">View</a>` 
                    : '<span class="text-muted">-</span>';

                // Add row and get the node
                const rowNode = budgetTable.row.add({
                    no: row.no,
                    structure: structureCell,
                    company_policy: companyPolicyCell,
                    kpi: kpiCell,
                    workplan: workplanCell,
                    plan_budget: planBudgetCell,
                    budget: budgetCell
                }).draw(false).node();

                // Add classes to the row
                $(rowNode).addClass(levelClass + ' ' + hasKpiClass);
            });

            // Redraw the table
            budgetTable.draw();
        }
    </script>
@endsection
