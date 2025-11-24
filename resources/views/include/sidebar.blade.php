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

                    @can('kpi.sasaranstrategis.view')
                    <li class="pe-slide-item">
                        <a href="{{ route('sasaran-strategis.index') }}" class="pe-nav-link {{ Request::is('sasaran-strategis*') ? 'active' : '' }}">
                            Strategic Goals
                        </a>
                    </li>
                    @endcan

                    @can('kpi.view')
                    <li class="pe-slide-item">
                        <a href="{{ route('kpi.index') }}" class="pe-nav-link {{ Request::is('kpi*') ? 'active' : '' }}">
                            KPI & Work Program
                        </a>
                    </li>
                    @endcan

                    @can('kpi.approval')
                    <li class="pe-slide-item">
                        <a href="pages-faqs" class="pe-nav-link">Approvals</a>
                    </li>
                    @endcan

                </ul>
            </li>
            @endcanany


            {{-- BUDGET CONTROL --}}
            @can('budget.view')
            <li class="pe-slide pe-has-sub">
                <a href="#collapseInvoices"
                    class="pe-nav-link {{ Request::is('anggaran*') || Request::is('resume*') ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-receipt pe-nav-icon"></i>
                    <span class="pe-nav-content">Budget Control</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ Request::is('anggaran*') || Request::is('resume*') ? 'show' : '' }}" id="collapseInvoices">
                    <li class="pe-slide-item">
                        <a href="{{ route('anggaran.index') }}" class="pe-nav-link {{ Request::is('anggaran*') ? 'active' : '' }}">
                            Budgets
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('resume-anggaran.index') }}" class="pe-nav-link {{ Request::is('resume*') ? 'active' : '' }}">
                            Resume Budgets
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('realisasi.index') }}" class="pe-nav-link {{ Request::is('realisasi*') ? 'active' : '' }}">
                            Realization
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
                            Admin Submission
                        </a>
                    </li>
                    @endcan

                </ul>
            </li>
            @endcanany


            {{-- SETTINGS --}}
            @canany([
            'setting.master.view',
            'setting.users.view',
            'setting.coa.view',
            'approval.view',
            'setting.authorization.view',
            'setting.history.view'
            ])
            <li class="pe-slide pe-has-sub">
                <a href="#collapseSetting"
                    class="pe-nav-link {{ Request::is('master*') || Request::is('user*') || Request::is('coa*') || Request::is('approval*') || Request::is('auth.roles*') || Request::is('history*') ? 'active' : '' }}"
                    data-bs-toggle="collapse">

                    <i class="bi bi-gear pe-nav-icon"></i>
                    <span class="pe-nav-content">Settings</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ Request::is('master*') || Request::is('user*') || Request::is('coa*') || Request::is('approval*') || Request::is('auth.roles*') || Request::is('history*') ? 'show' : '' }}" id="collapseSetting">

                    @can('setting.master.view')
                    <li class="pe-slide-item">
                        <a href="{{ route('master') }}" class="pe-nav-link {{ Request::is('master*') ? 'active' : '' }}">
                            Master
                        </a>
                    </li>
                    @endcan

                    @can('setting.users.view')
                    <a href="{{ route('user') }}" class="pe-nav-link {{ Request::is('user*') ? 'active' : '' }}">
                        Users
                    </a>
                    @endcan

                    @can('setting.coa.view')
                    <a href="{{ route('coa') }}" class="pe-nav-link {{ Request::is('coa*') ? 'active' : '' }}">
                        COA
                    </a>
                    @endcan

                    @can('approval.view')
                    <a href="{{ route('approval') }}" class="pe-nav-link {{ Request::is('approval*') ? 'active' : '' }}">
                        Approval
                    </a>
                    @endcan

                    @can('setting.authorization.view')
                    <a href="{{ route('auth.roles') }}" class="pe-nav-link {{ Request::is('auth.roles*') ? 'active' : '' }}">
                        Authorization
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