@php
$dashboardRoutes = ['dashboard', 'dashboard.*', 'dash.executive', 'dash.executive.*'];
$companyPolicyRoutes = ['company-policy.*'];
$kpiRoutes = ['sasaran-strategis.*', 'kpidivision.*', 'KPIDepartment.*', 'kpisection.*'];
$salesPlanRoutes = ['production.*', 'marketing.*'];
$budgetRoutes = [
'workplan.*',
'anggaran.*',
'budget-admin.*',
'budget.admin.*',
'budget-user.*',
'pengajuan.anggaran.*',
'budget-resume.*',
'userSubmission.dueDate',
'userSubmission.dueDateData',
'budget.submission.*',
];
$transactionActive = request()->routeIs('approvalSubmission.*')
|| (
request()->routeIs('userSubmission.*')
&& ! request()->routeIs('userSubmission.dueDate', 'userSubmission.dueDateData')
);
$settingRoutes = [
'master',
'master.*',
'users.*',
'code.*',
'setting.production.*',
'approval.*',
'auth.roles*',
'history',
'settingPriceVerificator.*',
];
@endphp

<aside class="pe-app-sidebar" id="sidebar">
    <div class="pe-app-sidebar-logo px-6 d-flex align-items-center position-relative">
        <a href="{{ route('dash.executive') }}" class="fs-18 fw-semibold">
            <img height="80" class="pe-app-sidebar-logo-default d-none" alt="Logo"
                src="{{ asset('assets/images/logo-dark.png') }}">
            <img height="30" class="pe-app-sidebar-logo-light d-none" alt="Logo"
                src="{{ asset('assets/images/logo-light.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize d-none" alt="Logo"
                src="{{ asset('assets/images/logo-md.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize-light d-none" alt="Logo"
                src="{{ asset('assets/images/logo-md-light.png') }}">
        </a>
    </div>

    <nav class="pe-app-sidebar-menu nav nav-pills" data-simplebar id="sidebar-simplebar">
        <ul class="pe-main-menu list-unstyled">

            {{-- DASHBOARD --}}
            @can('dashboard.view')
            <li class="pe-slide pe-has-sub">
                <a href="{{ route('dash.executive') }}"
                    class="pe-nav-link {{ request()->routeIs(...$dashboardRoutes) ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 pe-nav-icon"></i>
                    <span class="pe-nav-content">Dashboards</span>
                </a>
            </li>
            @endcan


            {{-- COMPANY POLICY --}}
            @can('companypolicy.view')
            <li class="pe-slide pe-has-sub">
                <a href="{{ route('company-policy.index') }}"
                    class="pe-nav-link {{ request()->routeIs(...$companyPolicyRoutes) ? 'active' : '' }}">
                    <i class="bi bi-journal-text pe-nav-icon"></i>
                    <span class="pe-nav-content">Company Policy</span>
                </a>
            </li>
            @endcan


            {{-- KPI --}}
            @canany(['kpi.view', 'kpi.sasaranstrategis.view', 'kpi.approval'])
            <li class="pe-slide pe-has-sub">
                <a href="#collapsePages"
                    class="pe-nav-link {{ request()->routeIs(...$kpiRoutes) ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-airplane pe-nav-icon"></i>
                    <span class="pe-nav-content">KPI</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ request()->routeIs(...$kpiRoutes) ? 'show' : '' }}"
                    id="collapsePages" data-bs-parent="#sidebar">

                    {{-- @can('kpi.kpidivision.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('kpidivision.index') }}"
                            class="pe-nav-link {{ request()->routeIs('kpidivision.*') ? 'active' : '' }}">
                            <i class="bi bi-check2-square"></i>
                            Division
                        </a>
                    </li>
                    {{-- @endcan --}}

                    {{-- @can('kpi.department.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('KPIDepartment.index') }}"
                            class="pe-nav-link {{ request()->routeIs('KPIDepartment.*') ? 'active' : '' }}">
                            <i class="bi bi-check2-square"></i>
                            Departement
                        </a>
                    </li>
                    {{-- @endcan --}}

                    {{-- @can('kpi.section.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('kpisection.index') }}"
                            class="pe-nav-link {{ request()->routeIs('kpisection.*') ? 'active' : '' }}">
                            <i class="bi bi-check2-square"></i>
                            Section
                        </a>
                    </li>
                    {{-- @endcan --}}

                </ul>
            </li>
            @endcanany



            {{-- BUDGET CONTROL --}}
            @can('budget.view')
            <li class="pe-slide pe-has-sub">
                <a href="#collapseInvoices"
                    class="pe-nav-link {{ request()->routeIs(...$budgetRoutes) ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-currency-exchange pe-nav-icon"></i>
                    <span class="pe-nav-content">Budget</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ request()->routeIs(...$budgetRoutes) ? 'show' : '' }}"
                    id="collapseInvoices" data-bs-parent="#sidebar">
                    <li class="pe-slide-item">
                        <a href="{{ route('workplan.index') }}"
                            class="pe-nav-link {{ request()->routeIs('workplan.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check"></i> Work Plan
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('budget-user.index') }}"
                            class="pe-nav-link {{ request()->routeIs('budget-user.*') ? 'active' : '' }}">
                            <i class="bi bi-person-check"></i> Budget User
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('budget-admin.index') }}"
                            class="pe-nav-link {{ request()->routeIs('budget-admin.*', 'budget.admin.*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart-line"></i> Budget Admin
                        </a>
                    </li>


                    <li class="pe-slide-item">
                        <a href="{{ route('budget-resume.index') }}"
                            class="pe-nav-link {{ request()->routeIs('budget-resume.*') ? 'active' : '' }}">
                            <i class="bi bi-wallet2"></i> Budget Control
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('userSubmission.dueDate') }}"
                            class="pe-nav-link {{ request()->routeIs('userSubmission.dueDate', 'userSubmission.dueDateData') ? 'active' : '' }}">
                            <i class="bi bi-calendar-x"></i> Budget Due Date
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('budget.submission.index') }}"
                            class="pe-nav-link {{ request()->routeIs('budget.submission.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i> Budget Movement
                        </a>
                    </li>
                </ul>
            </li>
            @endcan


            {{-- TRANSACTIONS --}}
            {{--
                Authorization Directive Comparison:
                
                @canany(['permission1', 'permission2'])
                - Checks if the user has ANY of the listed permissions
                - Returns true if user has at least ONE of the specified permissions
                - Useful when multiple permissions can grant access to the same resource
                - Example: User needs either 'transaction.user.view' OR 'transaction.approval.view'
                
                @can('permission')
                - Checks if the user has a SPECIFIC single permission
                - Returns true only if user has that exact permission
                - Useful when only one specific permission is required
                - Example: User must have 'transaction.user.view'
                
                In this context:
                - @canany allows users with either viewing permission to access the sidebar
                - @can would only allow users with one specific permission
            --}}
            @canany(['transaction.user.view', 'transaction.approval.view'])
            <li class="pe-slide pe-has-sub">
                <a href="#collapseSubmission" class="pe-nav-link {{ $transactionActive ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-receipt pe-nav-icon"></i>
                    <span class="pe-nav-content">Transactions</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ $transactionActive ? 'show' : '' }}"
                    id="collapseSubmission" data-bs-parent="#sidebar">

                    @can('transaction.user.view')
                    <li class="pe-slide-item">
                        <a href="{{ route('userSubmission.index') }}"
                            class="pe-nav-link {{ request()->routeIs('userSubmission.*') && ! request()->routeIs('userSubmission.dueDate', 'userSubmission.dueDateData') ? 'active' : '' }}">
                            User Submission
                        </a>
                    </li>
                    @endcan

                    @can('transaction.approval.view')
                    <li class="pe-slide-item">
                        <a href="{{ route('approvalSubmission.index') }}"
                            class="pe-nav-link {{ request()->routeIs('approvalSubmission.*') ? 'active' : '' }}">
                            Approval Submission
                        </a>
                    </li>
                    @endcan

                </ul>
            </li>
            @endcanany

            {{-- FINANCE --}}
            {{-- @canany(['finance.user.view', 'transaction.approval.view']) --}}
            <li class="pe-slide pe-has-sub">
                <a href="#collapseFinance" class="pe-nav-link"
                    data-bs-toggle="collapse">

                    <i class="bi bi-clipboard2-data-fill"></i>
                    <span class="pe-nav-content">Finance</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse" id="collapseFinance"
                    data-bs-parent="#sidebar">

                    {{-- @can('transaction.user.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('userSubmission.index') }}"
                            class="pe-nav-link">
                            Budget Assumption
                        </a>
                    </li>
                    {{-- @endcan --}}

                    {{-- @can('transaction.user.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('userSubmission.index') }}"
                            class="pe-nav-link">
                            Budget Accrual
                        </a>
                    </li>
                    {{-- @endcan --}}

                    {{-- @can('transaction.approval.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('approvalSubmission.index') }}"
                            class="pe-nav-link">
                            Master Price
                        </a>
                    </li>
                    {{-- @endcan --}}


                </ul>
            </li>
            {{-- @endcanany --}}

            {{-- FINANCE --}}
            {{-- @canany(['finance.user.view', 'transaction.approval.view']) --}}
            <li class="pe-slide pe-has-sub">
                <a href="#collapseReport" class="pe-nav-link"
                    data-bs-toggle="collapse">

                    <i class="bi bi-journal-bookmark-fill"></i>
                    <span class="pe-nav-content">Report</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse" id="collapseReport">
                    {{-- @can('expenditure.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('approvalSubmission.index') }}"
                            class="pe-nav-link">
                            Expenditure
                        </a>
                    </li>
                    {{-- @endcan --}}

                    {{-- @can('costCenter.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('approvalSubmission.index') }}"
                            class="pe-nav-link">
                            Cost Center
                        </a>
                    </li>
                    {{-- @endcan --}}

                    {{-- @can('costAllocation.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('approvalSubmission.index') }}"
                            class="pe-nav-link">
                            Cost Allocation
                        </a>
                    </li>
                    {{-- @endcan --}}
                    {{-- @can('cogs.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('approvalSubmission.index') }}"
                            class="pe-nav-link">
                            COGS
                        </a>
                    </li>
                    {{-- @endcan --}}
                    {{-- @can('incomeStatement.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('approvalSubmission.index') }}"
                            class="pe-nav-link">
                            Income Statement
                        </a>
                    </li>
                    {{-- @endcan --}}
                    {{-- @can('cashflow.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('approvalSubmission.index') }}"
                            class="pe-nav-link">
                            Cash Flows
                        </a>
                    </li>
                    {{-- @endcan --}}
                    {{-- @can('financialPosition.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('approvalSubmission.index') }}"
                            class="pe-nav-link">
                            Financial Position
                        </a>
                    </li>
                    {{-- @endcan --}}
                </ul>
            </li>
            {{-- @endcanany --}}

            @canany(['production.view', 'marketing.view'])
            <li class="pe-slide pe-has-sub">
                <a href="#collapseSalesPlan"
                    class="pe-nav-link {{ request()->routeIs(...$salesPlanRoutes) ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-calculator pe-nav-icon"></i>
                    <span class="pe-nav-content">Sales Plan</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse {{ request()->routeIs(...$salesPlanRoutes) ? 'show' : '' }}"
                    id="collapseSalesPlan" data-bs-parent="#sidebar">

                    <li class="pe-slide pe-has-sub">
                        <a href="{{ route('production.index') }}"
                            class="pe-nav-link {{ request()->routeIs('production.*') ? 'active' : '' }}">
                            <i class="bi bi-minecart-loaded pe-nav-icon"></i>
                            <span class="pe-nav-content">Productions Plan</span>
                        </a>
                    </li>

                    <li class="pe-slide pe-has-sub">
                        <a href="{{ route('marketing.index') }}"
                            class="pe-nav-link {{ request()->routeIs('marketing.*') ? 'active' : '' }}">
                            <i class="bi bi-megaphone pe-nav-icon"></i>
                            <span class="pe-nav-content">Marketing Plan</span>
                        </a>
                    </li>

                </ul>
            </li>
            @endcanany

            {{-- SETTINGS --}}
            @canany(['setting.master.view', 'setting.users.view', 'setting.code.view', 'setting.price.view',
            'setting.production.view', 'approval.view', 'setting.history.view'])
            <li class="pe-slide pe-has-sub">
                <a href="#collapseSetting"
                    class="pe-nav-link {{ request()->routeIs(...$settingRoutes) ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-gear pe-nav-icon"></i>
                    <span class="pe-nav-content">Settings</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ request()->routeIs(...$settingRoutes) ? 'show' : '' }}"
                    id="collapseSetting">

                    @can('setting.master.view')
                    <li class="pe-slide-item">
                        <a href="{{ route('master') }}"
                            class="pe-nav-link {{ request()->routeIs('master', 'master.*') ? 'active' : '' }}">
                            Employment
                        </a>
                    </li>
                    @endcan

                    @can('setting.code.view')
                    <a href="{{ route('code.index') }}"
                        class="pe-nav-link {{ request()->routeIs('code.*') ? 'active' : '' }}">
                        Code
                    </a>
                    @endcan

                    @can('setting.price.view')
                    <a href="{{ route('settingPriceVerificator.index') }}"
                        class="pe-nav-link {{ request()->routeIs('settingPriceVerificator.*') ? 'active' : '' }}">
                        Price
                    </a>
                    @endcan

                    @can('setting.production.view')
                    <a href="{{ route('setting.production.index') }}"
                        class="pe-nav-link {{ request()->routeIs('setting.production.*') ? 'active' : '' }}">
                        Production
                    </a>
                    @endcan

                    @can('setting.users.view')
                    <a href="{{ route('users.index') }}"
                        class="pe-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        Users
                    </a>
                    @endcan

                    @can('approval.view')
                    <a href="{{ route('approval.index') }}"
                        class="pe-nav-link {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                        Approval
                    </a>
                    @endcan



                    @can('setting.history.view')
                    <a href="{{ route('history') }}"
                        class="pe-nav-link {{ request()->routeIs('history') ? 'active' : '' }}">
                        History
                    </a>
                    @endcan

                </ul>
            </li>
            @endcanany

        </ul>
    </nav>
</aside>