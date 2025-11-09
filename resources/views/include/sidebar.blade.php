<aside class="pe-app-sidebar" id="sidebar">
    <div class="pe-app-sidebar-logo px-6 d-flex align-items-center position-relative">
        <!--begin::Brand Image-->
        <a href="index" class="fs-18 fw-semibold">
            <img height="80" class="pe-app-sidebar-logo-default d-none" alt="Logo" src="{{ asset('assets/images/logo-dark.png') }}">
            <img height="30" class="pe-app-sidebar-logo-light d-none" alt="Logo" src="{{ asset('assets/images/logo-light.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize d-none" alt="Logo" src="{{ asset('assets/images/logo-md.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize-light d-none" alt="Logo" src="{{ asset('assets/images/logo-md-light.png') }}">
            <!-- FabKin -->
        </a>
        <!--end::Brand Image-->
    </div>
    <nav class="pe-app-sidebar-menu nav nav-pills" data-simplebar id="sidebar-simplebar">
        <ul class="pe-main-menu list-unstyled">

            <!-- DASHBOARD -->
            <li class="pe-slide pe-has-sub">
                <a href="{{ route('dash.executive') }}" class="pe-nav-link {{ Request::is('dashboard/dash') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 pe-nav-icon"></i>
                    <span class="pe-nav-content">Dashboards</span>
                </a>

            </li>
            <!-- <li class="pe-slide pe-has-sub">
                <a href="{{ url('/') }}" class="pe-nav-link {{ Request::is('/') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 pe-nav-icon"></i>
                    <span class="pe-nav-content">Dashboards Div</span>
                </a>
            </li>
            <li class="pe-slide pe-has-sub">
                <a href="{{ url('/') }}" class="pe-nav-link {{ Request::is('/') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 pe-nav-icon"></i>
                    <span class="pe-nav-content">Dashboards Dev</span>
                </a>
            </li> -->


            <!-- COMPANY POLICY -->
            <li class="pe-slide pe-has-sub">
                <a href="{{ route('company-policy.index') }}" class="pe-nav-link {{ Request::is('company-policy*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text pe-nav-icon"></i>
                    <span class="pe-nav-content">Company Policy</span>
                </a>
            </li>

            @php
            $menu_kpi = ['sasaran-strategis', 'sasaran-strategis/*', 'kpi', 'kpi/*'];
            @endphp
            <li class="pe-slide pe-has-sub">
                <a href="#collapsePages" class="pe-nav-link {{ in_array(true, array_map(fn($p) => Request::is($p), $menu_kpi)) ? 'active' : '' }}" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapsePages">
                    <i class="bi bi-airplane pe-nav-icon"></i>
                    <span class="pe-nav-content">KPI</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse {{ in_array(true, array_map(fn($p) => Request::is($p), $menu_kpi)) ? 'show' : '' }}" id="collapsePages">
                    @php
                    $menu_sasaranstrategis = ['sasaran-strategis', 'sasaran-strategis/*'];
                    @endphp
                    <li class="pe-slide-item">
                        <a href="{{ route('sasaran-strategis.index') }}" class="pe-nav-link {{ in_array(true, array_map(fn($p) => Request::is($p), $menu_sasaranstrategis)) ? 'active' : '' }}">
                            Strategic Goals <!-- Sasaran Strategis -->
                        </a>
                    </li>
                    @php
                    $menu_kpi_detail = ['kpi', 'kpi/*'];
                    @endphp
                    <li class="pe-slide-item">
                        <a href="{{ route('kpi.index') }}" class="pe-nav-link  {{ in_array(true, array_map(fn($p) => Request::is($p), $menu_kpi_detail)) ? 'active' : '' }}">
                            KPI & Work Program <!-- KPI & Program Kerja -->
                        </a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="pages-faqs" class="pe-nav-link">
                            Approvals
                        </a>
                    </li>

                </ul>
            </li>
            @php
            $menu_anggaran = ['anggaran*', 'realisasi*', 'resume*'];
            @endphp

            <li class="pe-slide pe-has-sub">
                <a href="#collapseInvoices"
                    class="pe-nav-link {{ Request::is($menu_anggaran) ? 'active' : '' }}"
                    data-bs-toggle="collapse"
                    aria-expanded="{{ Request::is($menu_anggaran) ? 'true' : 'false' }}"
                    aria-controls="collapseInvoices">
                    <i class="bi bi-receipt pe-nav-icon"></i>
                    <span class="pe-nav-content">Budget Control</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ Request::is($menu_anggaran) ? 'show' : '' }}" id="collapseInvoices">
                    <li class="pe-slide-item">
                        <a href="{{ route('anggaran.index') }}"
                            class="pe-nav-link {{ Request::is('anggaran*') ? 'active' : '' }}">
                            Budgets
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('resume-anggaran.index') }}"
                            class="pe-nav-link {{ Request::is('resume*') ? 'active' : '' }}">
                            Resume Budgets
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('realisasi.index') }}"
                            class="pe-nav-link {{ Request::is('realisasi*') ? 'active' : '' }}">
                            Realization
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="apps-invoice-create" class="pe-nav-link">
                            Amendment
                        </a>
                    </li>
                </ul>
            </li>

            @php
            $menu_transaction = ['admission/user*', 'admission/admin*'];
            @endphp

            <li class="pe-slide pe-has-sub">
                <a href="#collapseSubmission"
                    class="pe-nav-link {{ in_array(true, array_map(fn($p) => Request::is($p), $menu_transaction)) ? 'active' : '' }}"
                    data-bs-toggle="collapse"
                    aria-expanded="{{ in_array(true, array_map(fn($p) => Request::is($p), $menu_transaction)) ? 'true' : 'false' }}"
                    aria-controls="collapseSubmission">
                    <i class="bi bi-receipt pe-nav-icon"></i>
                    <span class="pe-nav-content">Transactions</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse {{ in_array(true, array_map(fn($p) => Request::is($p), $menu_transaction)) ? 'show' : '' }}"
                    id="collapseSubmission">

                    <li class="pe-slide-item">
                        <a href="{{ route('userSubmission.index') }}"
                            class="pe-nav-link {{ Request::is('admission/user*') ? 'active' : '' }}">
                            User Submission
                        </a>
                    </li>

                    <li class="pe-slide-item">
                        <a href="{{ route('adminSubmission.index') }}"
                            class="pe-nav-link {{ Request::is('admission/admin*') ? 'active' : '' }}">
                            Admin Submission
                        </a>
                    </li>
                </ul>
            </li>


            <li class="pe-slide pe-has-sub">
                <a href="#collapseCMS" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseCMS">
                    <i class="bi bi-book pe-nav-icon"></i>
                    <span class="pe-nav-content">Setting</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseCMS">

                    <li class="pe-slide-item">
                        <a href="{{ route('master') }}" class="pe-nav-link">
                            Master
                        </a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="apps-cms-add-content" class="pe-nav-link">
                            COA
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</aside>