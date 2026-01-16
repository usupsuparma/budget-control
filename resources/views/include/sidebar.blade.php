<aside class="pe-app-sidebar" id="sidebar">
    <div class="pe-app-sidebar-logo px-6 d-flex align-items-center position-relative">
        <a href="index" class="fs-18 fw-semibold">
            <img height="80" class="pe-app-sidebar-logo-default d-none" alt="Logo" src="{{ asset('assets/images/logo-dark.png') }}">
            <img height="30" class="pe-app-sidebar-logo-light d-none" alt="Logo" src="{{ asset('assets/images/logo-light.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize d-none" alt="Logo" src="{{ asset('assets/images/logo-md.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize-light d-none" alt="Logo" src="{{ asset('assets/images/logo-md-light.png') }}">
        </a>
    </div>

    <nav class="pe-app-sidebar-menu nav nav-pills" data-simplebar id="sidebar-simplebar">
        <ul class="pe-main-menu list-unstyled">

            {{-- DASHBOARD --}}
            @can('dashboard.view')
            <li class="pe-slide pe-has-sub">
                <a href="{{ route('dash.executive') }}" class="pe-nav-link {{ Request::is('dashboard/dash') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 pe-nav-icon"></i>
                    <span class="pe-nav-content">Dashboards</span>
                </a>
            </li>
            @endcan


            {{-- COMPANY POLICY --}}
            @can('companypolicy.view')
            <li class="pe-slide pe-has-sub">
                <a href="{{ route('company-policy.index') }}" class="pe-nav-link {{ Request::is('company-policy*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text pe-nav-icon"></i>
                    <span class="pe-nav-content">Company Policy</span>
                </a>
            </li>
            @endcan


            {{-- KPI --}}
            @canany(['kpi.view','kpi.sasaranstrategis.view','kpi.approval'])
            <li class="pe-slide pe-has-sub">
                <a href="#collapsePages"
                    class="pe-nav-link {{ Request::is('sasaran-strategis*') || Request::is('kpi*') ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-airplane pe-nav-icon"></i>
                    <span class="pe-nav-content">KPI</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ Request::is('sasaran-strategis*') || Request::is('kpi*') ? 'show' : '' }}" id="collapsePages">

                    {{-- @can('kpi.kpidivision.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('kpidivision.index') }}" class="pe-nav-link {{ Request::is('kpidivision*') ? 'active' : '' }}">
                            <i class="bi bi-check2-square"></i>
                            Division
                        </a>
                    </li>
                    {{-- @endcan --}}

                    {{-- @can('kpi.department.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('kpidepartment.index') }}" class="pe-nav-link {{ Request::is('kpidepartment*') ? 'active' : '' }}">
                            <i class="bi bi-check2-square"></i>
                            Departement
                        </a>
                    </li>
                    {{-- @endcan --}}

                    {{-- @can('kpi.section.view') --}}
                    <li class="pe-slide-item">
                        <a href="{{ route('kpisection.index') }}" class="pe-nav-link {{ Request::is('kpisection*') ? 'active' : '' }}">
                            <i class="bi bi-check2-square"></i>
                            Section
                        </a>
                    </li>
                    {{-- @endcan --}}

                </ul>
            </li>
            @endcanany

            @canany(['production.view','marketing.view'])
            <li class="pe-slide pe-has-sub">
                <a href="#collapseSalesPlan"
                    class="pe-nav-link {{ Request::is('production*') || Request::is('marketing*') ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-calculator pe-nav-icon"></i>
                    <span class="pe-nav-content">Sales Plan</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse {{ Request::is('production*') || Request::is('marketing*') ? 'show' : '' }}" id="collapseSalesPlan">

                    <li class="pe-slide pe-has-sub">
                        <a href="{{ route('production.index') }}" class="pe-nav-link {{ Request::is('production*') ? 'active' : '' }}">
                            <i class="bi bi-minecart-loaded pe-nav-icon"></i>
                            <span class="pe-nav-content">Productions Plan</span>
                        </a>
                    </li>

                    <li class="pe-slide pe-has-sub">
                        <a href="{{ route('marketing.index') }}" class="pe-nav-link {{ Request::is('marketing*') ? 'active' : '' }}">
                            <i class="bi bi-megaphone pe-nav-icon"></i>
                            <span class="pe-nav-content">Marketing Plan</span>
                        </a>
                    </li>

                </ul>
            </li>
            @endcanany

            {{-- BUDGET CONTROL --}}
            @can('budget.view')
            <li class="pe-slide pe-has-sub">
                <a href="#collapseInvoices"
                    class="pe-nav-link {{ Request::is('workplan*') || Request::is('anggaran*') || Request::is('budget-admin*') || Request::is('pengajuan.anggaran*') ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-currency-exchange pe-nav-icon"></i>
                    <span class="pe-nav-content">Budget Control</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ Request::is('workplan*') || Request::is('anggaran*') || Request::is('budget-admin*') || Request::is('budget-user*') || Request::is('pengajuan.anggaran*') ? 'show' : '' }}" id="collapseInvoices">
                    <li class="pe-slide-item">
                        <a href="{{ route('workplan.index') }}" class="pe-nav-link {{ Request::is('workplan*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check"></i> Work Plan
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('budget-user.index') }}" class="pe-nav-link {{ Request::is('budget-user*') ? 'active' : '' }}">
                            <i class="bi bi-person-check"></i> Budget User
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('budget-admin.index') }}" class="pe-nav-link {{ Request::is('budget-admin*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart-line"></i> Budget Admin
                        </a>
                    </li>


                    <li class="pe-slide-item">
                        <a href="{{ route('budget-resume.index') }}" class="pe-nav-link {{ Request::is('budget-resume*') ? 'active' : '' }}">
                            <i class="bi bi-wallet2"></i> Budget Resume
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('budget.submission.index') }}" class="pe-nav-link {{ Request::is('budget-submission*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i> Budget Submission
                        </a>
                    </li>
                </ul>
            </li>
            @endcan


            {{-- TRANSACTIONS --}}
            @canany(['transaction.user.view','transaction.admin.view'])
            <li class="pe-slide pe-has-sub">
                <a href="#collapseSubmission"
                    class="pe-nav-link {{ Request::is('admission/*') ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-receipt pe-nav-icon"></i>
                    <span class="pe-nav-content">Transactions</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ Request::is('admission/*') ? 'show' : '' }}" id="collapseSubmission">

                    @can('transaction.user.view')
                    <li class="pe-slide-item">
                        <a href="{{ route('userSubmission.index') }}" class="pe-nav-link {{ Request::is('admission/user*') ? 'active' : '' }}">
                            User Submission
                        </a>
                    </li>
                    @endcan

                    @can('transaction.admin.view')
                    <li class="pe-slide-item">
                        <a href="{{ route('adminSubmission.index') }}" class="pe-nav-link {{ Request::is('admission/admin*') ? 'active' : '' }}">
                            Approval Submission
                        </a>
                    </li>
                    @endcan

        </ul>
        </li>
        @endcanany

        {{-- FINANCE --}}
        {{-- @canany(['finance.user.view','transaction.admin.view']) --}}
        <li class="pe-slide pe-has-sub">
            <a href="#collapseFinance"
                class="pe-nav-link {{ Request::is('finance/*') ? 'active' : '' }}"
                data-bs-toggle="collapse">

                <i class="bi bi-clipboard2-data-fill"></i>
                <span class="pe-nav-content">Finance</span>
                <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
            </a>

            <ul class="pe-slide-menu collapse {{ Request::is('finance/*') ? 'show' : '' }}" id="collapseFinance">

                {{-- @can('transaction.user.view') --}}
                <li class="pe-slide-item">
                    <a href="{{ route('userSubmission.index') }}" class="pe-nav-link {{ Request::is('admission/user*') ? 'active' : '' }}">
                        Budget Assumption
                    </a>
                </li>
                {{-- @endcan --}}

                {{-- @can('transaction.user.view') --}}
                <li class="pe-slide-item">
                    <a href="{{ route('userSubmission.index') }}" class="pe-nav-link {{ Request::is('admission/user*') ? 'active' : '' }}">
                        Budget Accrual
                    </a>
                </li>
                {{-- @endcan --}}

                {{-- @can('transaction.admin.view') --}}
                <li class="pe-slide-item">
                    <a href="{{ route('adminSubmission.index') }}" class="pe-nav-link {{ Request::is('admission/admin*') ? 'active' : '' }}">
                        Master Price
                    </a>
                </li>
                {{-- @endcan --}}


            </ul>
        </li>
        {{-- @endcanany --}}

        {{-- FINANCE --}}
        {{-- @canany(['finance.user.view','transaction.admin.view']) --}}
        <li class="pe-slide pe-has-sub">
            <a href="#collapseReport"
                class="pe-nav-link {{ Request::is('report/*') ? 'active' : '' }}"
                data-bs-toggle="collapse">

                <i class="bi bi-journal-bookmark-fill"></i>
                <span class="pe-nav-content">Report</span>
                <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
            </a>

            <ul class="pe-slide-menu collapse {{ Request::is('report/*') ? 'show' : '' }}" id="collapseReport">
                {{-- @can('expenditure.view') --}}
                <li class="pe-slide-item">
                    <a href="{{ route('adminSubmission.index') }}" class="pe-nav-link {{ Request::is('report/expenditure*') ? 'active' : '' }}">
                        Expenditure
                    </a>
                </li>
                {{-- @endcan --}}

                {{-- @can('costCenter.view') --}}
                <li class="pe-slide-item">
                    <a href="{{ route('adminSubmission.index') }}" class="pe-nav-link {{ Request::is('report/costCenter*') ? 'active' : '' }}">
                        Cost Center
                    </a>
                </li>
                {{-- @endcan --}}

                {{-- @can('costAllocation.view') --}}
                <li class="pe-slide-item">
                    <a href="{{ route('adminSubmission.index') }}" class="pe-nav-link {{ Request::is('report/costAllocation*') ? 'active' : '' }}">
                        Cost Allocation
                    </a>
                </li>
                {{-- @endcan --}}
                {{-- @can('cogs.view') --}}
                <li class="pe-slide-item">
                    <a href="{{ route('adminSubmission.index') }}" class="pe-nav-link {{ Request::is('report/cogs*') ? 'active' : '' }}">
                        COGS
                    </a>
                </li>
                {{-- @endcan --}}
                {{-- @can('incomeStatement.view') --}}
                <li class="pe-slide-item">
                    <a href="{{ route('adminSubmission.index') }}" class="pe-nav-link {{ Request::is('report/incomeStatement*') ? 'active' : '' }}">
                        Income Statement
                    </a>
                </li>
                {{-- @endcan --}}
                {{-- @can('cashflow.view') --}}
                <li class="pe-slide-item">
                    <a href="{{ route('adminSubmission.index') }}" class="pe-nav-link {{ Request::is('report/cashflow*') ? 'active' : '' }}">
                        Cash Flows
                    </a>
                </li>
                {{-- @endcan --}}
                {{-- @can('financialPosition.view') --}}
                <li class="pe-slide-item">
                    <a href="{{ route('adminSubmission.index') }}" class="pe-nav-link {{ Request::is('report/financialPosition*') ? 'active' : '' }}">
                        Financial Position
                    </a>
                </li>
                {{-- @endcan --}}
            </ul>
        </li>
        {{-- @endcanany --}}

        {{-- SETTINGS --}}
        @canany([
        'setting.master.view',
        'setting.users.view',
        'setting.code.view',
        'setting.production.view',
        'approval.view',
        'setting.history.view'
        ])
        <li class="pe-slide pe-has-sub">
            <a href="#collapseSetting"
                class="pe-nav-link {{ Request::is('master*') || Request::is('user*') || Request::is('code*') || Request::is('setting.production*') || Request::is('approval*') || Request::is('auth.roles*') || Request::is('history*') ? 'active' : '' }}"
                data-bs-toggle="collapse">

                <i class="bi bi-gear pe-nav-icon"></i>
                <span class="pe-nav-content">Settings</span>
                <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
            </a>

            <ul class="pe-slide-menu collapse {{ Request::is('master*') || Request::is('code*') || Request::is('setting.production*') || Request::is('user*') || Request::is('approval*') || Request::is('auth.roles*') || Request::is('history*') ? 'show' : '' }}" id="collapseSetting">

                @can('setting.master.view')
                <li class="pe-slide-item">
                    <a href="{{ route('master') }}" class="pe-nav-link {{ Request::is('master*') ? 'active' : '' }}">
                        Employment
                    </a>
                </li>
                @endcan

                @can('setting.code.view')
                <a href="{{ route('code.index') }}" class="pe-nav-link {{ Request::is('code*') ? 'active' : '' }}">
                    Code
                </a>
                @endcan

                @can('setting.production.view')
                <a href="{{ route('setting.production.index') }}" class="pe-nav-link {{ Request::is('setting.production*') ? 'active' : '' }}">
                    Production
                </a>
                @endcan

                @can('setting.users.view')
                <a href="{{ route('users.index') }}" class="pe-nav-link {{ Request::is('user*') ? 'active' : '' }}">
                    Users
                </a>
                @endcan

                @can('approval.view')
                <a href="{{ route('approval') }}" class="pe-nav-link {{ Request::is('approval*') ? 'active' : '' }}">
                    Approval
                </a>
                @endcan



                @can('setting.history.view')
                <a href="{{ route('history') }}" class="pe-nav-link {{ Request::is('history*') ? 'active' : '' }}">
                    History
                </a>
                @endcan

            </ul>
        </li>
        @endcanany

        </ul>
    </nav>
</aside>