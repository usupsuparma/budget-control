@extends('layouts.master')

@section('title', 'Dashboard | Budget Control System')
@section('title-sub', 'Dashboard')
@section('pagetitle', 'Dashboard')

@section('content')
    <div id="layout-wrapper">
        <div class="row">
            <!-- === COMPANY POLICY 2026 (5 COLUMNS) === -->
            <div class="col-12">
                <div class="card bg-secondary-subtle border-0">
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-12 position-relative">

                                <!-- TITLE (CENTER PERFECT) -->
                                <h5 class="fw-bold text-center mb-0" id="companyPolicyTitle">
                                    Company Policy {{ date('Y') }} (Strategic Goals)
                                </h5>

                                <!-- YEAR SELECT (RIGHT) -->
                                <div class="position-absolute top-50 end-0 translate-middle-y">
                                    <select class="form-select form-select-sm w-auto" id="form-select-01" name="tahun">
                                        <option value="">Select</option>
                                        @for ($year = 2023; $year <= date('Y') + 1; $year++)
                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless align-middle text-center mb-0">
                                <tr id="strategicGoalsRow">
                                    @foreach ($policies as $policy)
                                        @if ($policy->tahun == date('Y'))
                                            @foreach ($policy->details as $detail)
                                                @if ($detail->strategic_goal_id)
                                                    <td>
                                                        <div
                                                            class="d-flex flex-column align-items-center justify-content-center">
                                                            <div
                                                                class="h-50px w-50px d-flex justify-content-center align-items-center bg-secondary text-white fs-4 rounded-pill mb-3">
                                                                <i class="bi bi-mortarboard"></i>
                                                            </div>
                                                            <h6 class="fw-semibold fs-14 mb-1">{!! $detail->strategic_goal_id !!}</h6>
                                                            <p class="text-muted fs-13 mb-0">{!! $detail->description_id !!}</p>
                                                        </div>
                                                    </td>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- === NOTIFICATIONS === -->
            @if(!empty($notifications) && count($notifications) > 0)
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-bell me-2"></i>Recent Notifications</h6>
                        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notif)
                            <div class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-bottom-0">
                                <div class="flex-shrink-0">
                                    @php
                                        $bgColor = 'bg-primary';
                                        $icon = 'bi-info-circle';
                                        if($notif->category) {
                                            switch(strtolower($notif->category->name)) {
                                                case 'approval': $bgColor = 'bg-warning'; $icon = 'bi-check2-circle'; break;
                                                case 'system': $bgColor = 'bg-danger'; $icon = 'bi-cpu'; break;
                                                case 'info': $bgColor = 'bg-info'; $icon = 'bi-info-lg'; break;
                                            }
                                        }
                                    @endphp
                                    <div class="h-40px w-40px d-flex justify-content-center align-items-center rounded-circle {{ $bgColor }} text-white">
                                        <i class="bi {{ $icon }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1 {{ !$notif->is_read ? 'fw-bold' : 'text-muted' }}">{{ $notif->title }}</h6>
                                        <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 text-muted fs-13 text-truncate" style="max-width: 700px;">{{ $notif->details }}</p>
                                </div>
                                @if(!$notif->is_read)
                                <div class="flex-shrink-0">
                                    <span class="badge bg-primary rounded-pill p-1"><span class="visually-hidden">New</span></span>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- === SUMMARY CARDS === -->
            <div class="col-xxl-12 mb-4">
                <div class="row g-3">
                    <!-- Total Anggaran -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-10">
                                    <div>
                                        <p class="text-muted mb-2">Budget Total</p>
                                        <h3 class="fw-medium mb-0" id="budgetTotalValue">Rp -</h3>
                                    </div>
                                    <div
                                        class="h-50px w-50px position-relative d-flex justify-content-center align-items-center bg-info text-white fs-4 rounded-pill">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                </div>
                                <p class="text-success mb-0 fs-13" id="budgetTotalNote">
                                    <i class="bi bi-arrow-up-short"></i> -
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Realisasi Anggaran -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-10">
                                    <div>
                                        <p class="text-muted mb-2">Budget Realization</p>
                                        <h3 class="fw-medium mb-0">Rp 98,2 M</h3>
                                    </div>
                                    <div
                                        class="h-50px w-50px position-relative d-flex justify-content-center align-items-center bg-warning text-white fs-4 rounded-pill">
                                        <i class="bi bi-graph-up-arrow"></i>
                                    </div>
                                </div>
                                <p class="text-success mb-0 fs-13"><i class="bi bi-arrow-up-short"></i> 78.1% dari total
                                    anggaran</p>
                            </div>
                        </div>
                    </div>
                    <!-- Outstanding Budget -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-10">
                                    <div>
                                        <p class="text-muted mb-2">Budget Balance</p>
                                        <h3 class="fw-medium mb-0">Rp 27,6 M</h3>
                                    </div>
                                    <div
                                        class="h-50px w-50px position-relative d-flex justify-content-center align-items-center bg-success text-white fs-4 rounded-pill">
                                        <i class="bi bi-pie-chart"></i>
                                    </div>
                                </div>
                                <p class="text-warning mb-0 fs-13"><i class="bi bi-arrow-right-short"></i> 21.9% tersisa</p>
                            </div>
                        </div>
                    </div>
                    <!-- KPI Achievement -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-10">
                                    <div>
                                        <p class="text-muted mb-2">KPI Achievement</p>
                                        <h3 class="fw-medium mb-0">89%</h3>
                                    </div>
                                    <div
                                        class="h-50px w-50px position-relative d-flex justify-content-center align-items-center bg-primary text-white fs-4 rounded-pill">
                                        <i class="bi bi-activity"></i>
                                    </div>
                                </div>
                                <p class="text-success mb-0 fs-13"><i class="bi bi-arrow-up-short"></i> Meningkat 5% bulan
                                    ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- === GRAFIK REALISASI & PENGAJUAN === -->

            <div class="col-xxl-12 col-md-12 mb-4">
                <div class="row g-3">
                    <div class="col-xl-6 col-md-6 p-1">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Division Budget Realization</h6>
                                <button class="btn btn-outline-light text-muted btn-sm">See All<i
                                        class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-box table-responsive">
                                    <table class="table text-nowrap align-middle">
                                        <thead>
                                            <tr>
                                                <th scope="col">Divisi</th>
                                                <th scope="col">Realisasi</th>
                                                <th scope="col">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div class="form-check form-check-primary">
                                                            <label>
                                                                <h6 class="mb-1">PLANT</h6>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="fs-12 fw-semibold">50%</span>
                                                    <div class="progress progress-xs" role="progressbar" aria-valuenow="50"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                        <div class="progress-bar" style="width: 50%"></div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-info-subtle text-info">On Budget</span></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div class="form-check form-check-primary">
                                                            <label>
                                                                <h6 class="mb-1">PRODUCTION</h6>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="fs-12 fw-semibold">75%</span>
                                                    <div class="progress progress-xs" role="progressbar" aria-valuenow="75"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                        <div class="progress-bar" style="width: 75%"></div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-info-subtle text-info">On Budget</span></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div class="form-check form-check-primary">
                                                            <label>
                                                                <h6 class="mb-1">MARKETING</h6>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="fs-12 fw-semibold">100%</span>
                                                    <div class="progress progress-xs" role="progressbar"
                                                        aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                                        <div class="progress-bar" style="width: 100%"></div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-info-subtle text-info">On Budget</span></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div class="form-check form-check-primary">
                                                            <label>
                                                                <h6 class="mb-1">FINANCE</h6>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="fs-12 fw-semibold">64%</span>
                                                    <div class="progress progress-xs" role="progressbar"
                                                        aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">
                                                        <div class="progress-bar" style="width: 64%"></div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-info-subtle text-info">On Budget</span></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div class="form-check form-check-primary">
                                                            <label>
                                                                <h6 class="mb-1">HR, GA & PROCUREMENT</h6>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="fs-12 fw-semibold">25%</span>
                                                    <div class="progress progress-xs" role="progressbar"
                                                        aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                        <div class="progress-bar" style="width: 25%"></div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-info-subtle text-info">On Budget</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-6 p-1">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Today Activities</h6>
                            </div>
                            <div class="card-body">
                                <section data-simplebar class="px-5 mx-n5" style="max-height: 340px;">
                                    <div class="timeline2">
                                        <ul>
                                            <li class="card border-0 box">
                                                <span></span>
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div
                                                            class="h-40px w-40px d-flex justify-content-center align-items-center bg-light-subtle text-muted rounded-pill">
                                                            <i class="bi bi-cup-hot fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Pengajuan Transaksi</h6>
                                                            <p class="fs-12 text-muted mb-0">- Sarah
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="text-muted mb-1">10:00 AM</div>
                                                        <div class="avatar-group">
                                                            <a href="javascript:voide(0)" class="avatar-item">
                                                                <img class="img-fluid avatar-sm"
                                                                    src="{{ asset('assets/images/avatar/avatar-1.jpg') }}"
                                                                    alt="avatar image">
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>

                                            </li>
                                            <li class="card border-0 box">
                                                <span></span>
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div
                                                            class="h-40px w-40px d-flex justify-content-center align-items-center bg-light-subtle text-muted rounded-pill">
                                                            <i class="bi bi-gem fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Pengajuan ReClass Anggaran</h6>
                                                            <p class="fs-12 text-muted mb-0">- Anthony
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="text-muted mb-1">10:00 AM</div>
                                                        <div class="avatar-group">
                                                            <a href="javascript:voide(0)" class="avatar-item">
                                                                <img class="img-fluid avatar-sm"
                                                                    src="{{ asset('assets/images/avatar/avatar-2.jpg') }}"
                                                                    alt="avatar image">
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="card border-0 box">
                                                <span></span>
                                                <div class="d-flex justify-content-between align-items-start mb-5">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div
                                                            class="h-40px w-40px d-flex justify-content-center align-items-center bg-light-subtle text-muted rounded-pill">
                                                            <i class="bi bi-gem fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 max-w-200px text-truncate"> Approval Anggaran
                                                            </h6>
                                                            <p class="fs-12 text-muted mb-0">- Andrew
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="text-muted mb-1">11:30 AM</div>
                                                        <div class="avatar-group">
                                                            <a href="javascript:voide(0)" class="avatar-item">
                                                                <img class="img-fluid avatar-sm"
                                                                    src="{{ asset('assets/images/avatar/avatar-3.jpg') }}"
                                                                    alt="avatar image">
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="card border-0 box">
                                                <span></span>
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div
                                                            class="h-40px w-40px d-flex justify-content-center align-items-center bg-light-subtle text-muted rounded-pill">
                                                            <i class="bi bi-clock-history fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Pencairan Pengajuan </h6>
                                                            <p class="text-muted mb-0">- Andrew</p>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="text-muted mb-1">5:15 AM</div>
                                                        <div class="avatar-group">

                                                            <a href="javascript:voide(0)" class="avatar-item">
                                                                <img class="img-fluid avatar-sm"
                                                                    src="{{ asset('assets/images/avatar/avatar-3.jpg') }}"
                                                                    alt="avatar image">
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="p-3 border border-dashed rounded cursor-pointer">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <div class="d-flex align-items-center gap-2">

                                                                <div>

                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- === BUDGET VS REALIZATION === -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Budget VS Realisasi</h6>
                        <button class="btn btn-outline-light text-muted btn-sm"><i
                                class="bi bi-arrow-right ms-1"></i></button>
                    </div>
                    <div class="card-body">
                        <div id="basic_column_chart" class="apexcharts-container"></div>
                    </div>
                </div>
            </div>




            <!-- === TABEL PENGAJUAN === -->
            <div class="col-xl-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Budget Submission List</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Unit</th>
                                        <th>Program Kerja</th>
                                        <th>Nilai</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Divisi Operasional</td>
                                        <td>Perawatan Aset Fasilitas</td>
                                        <td>Rp 520.000.000</td>
                                        <td><span class="badge bg-info-subtle text-info">Menunggu</span></td>
                                        <td>01 Nov 2025</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Divisi HR & GA</td>
                                        <td>Pelatihan SDM Tahap II</td>
                                        <td>Rp 245.000.000</td>
                                        <td><span class="badge bg-warning-subtle text-warning">Proses Review</span></td>
                                        <td>03 Nov 2025</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Divisi Keuangan</td>
                                        <td>Upgrade Sistem ERP</td>
                                        <td>Rp 360.000.000</td>
                                        <td><span class="badge bg-success-subtle text-success">Disetujui</span></td>
                                        <td>05 Nov 2025</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelect = document.getElementById('form-select-01');
            const titleEl = document.getElementById('companyPolicyTitle');

            function updateTitle() {
                const year = yearSelect.value || '{{ date('Y') }}';
                titleEl.textContent = `Company Policy ${year} (Strategic Goals)`;
            }

            // initial load
            updateTitle();

            // when year changes
            yearSelect.addEventListener('change', updateTitle);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelect = document.getElementById('form-select-01');
            const titleEl = document.getElementById('companyPolicyTitle');
            const rowEl = document.getElementById('strategicGoalsRow');

            const ajaxUrl = "{{ route('dash.executive.policies') }}";

            function updateTitle() {
                const year = yearSelect.value || "{{ date('Y') }}";
                titleEl.textContent = `Company Policy ${year} (Strategic Goals)`;
            }

            async function loadPoliciesByYear(year) {
                if (!year) return;

                rowEl.innerHTML = `<td class="text-muted py-4">Loading...</td>`;

                try {
                    const res = await fetch(`${ajaxUrl}?year=${encodeURIComponent(year)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const json = await res.json();

                    if (!res.ok || json.status !== 'success') {
                        rowEl.innerHTML = `<td class="text-danger py-4">Failed to load data.</td>`;
                        return;
                    }

                    rowEl.innerHTML = json.html;

                } catch (e) {
                    rowEl.innerHTML = `<td class="text-danger py-4">Error loading data.</td>`;
                }
            }

            // init title + load initial
            updateTitle();
            loadPoliciesByYear(yearSelect.value || "{{ date('Y') }}");

            yearSelect.addEventListener('change', function() {
                updateTitle();
                loadPoliciesByYear(this.value);
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelect = document.getElementById('form-select-01');
            const budgetEl = document.getElementById('budgetTotalValue');

            const ajaxUrl = "{{ route('budget.summary.year') }}";

            function formatRupiahCompact(number) {
                const n = Number(number || 0);

                // contoh hasil: Rp 125,8 M / Rp 2,3 T / Rp 950,2 Jt
                const abs = Math.abs(n);
                let value = n;
                let suffix = '';

                if (abs >= 1_000_000_000_000) {
                    value = n / 1_000_000_000_000;
                    suffix = ' T';
                } else if (abs >= 1_000_000_000) {
                    value = n / 1_000_000_000;
                    suffix = ' M';
                } else if (abs >= 1_000_000) {
                    value = n / 1_000_000;
                    suffix = ' Jt';
                } else if (abs >= 1_000) {
                    value = n / 1_000;
                    suffix = ' Rb';
                }

                // pakai format Indonesia (koma desimal)
                return 'Rp ' + value.toLocaleString('id-ID', {
                    maximumFractionDigits: 1
                }) + suffix;
            }

            async function loadBudgetTotal(year) {
                if (!year) {
                    budgetEl.textContent = 'Rp -';
                    return;
                }

                budgetEl.textContent = 'Loading...';

                try {
                    const res = await fetch(`${ajaxUrl}?year=${encodeURIComponent(year)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await res.json();

                    if (!res.ok || json.status !== 'success') {
                        budgetEl.textContent = 'Rp -';
                        return;
                    }

                    budgetEl.textContent = formatRupiahCompact(json.total_sum);
                } catch (err) {
                    budgetEl.textContent = 'Rp -';
                }
            }

            // initial load
            loadBudgetTotal(yearSelect.value);

            // on change year
            yearSelect.addEventListener('change', function() {
                loadBudgetTotal(this.value);
            });
        });
    </script>


    <script>
        // === Realisasi Anggaran per Divisi ===
        var optionsRealisasi = {
            series: [{
                name: 'Realisasi',
                data: [92, 85, 80, 78, 73]
            }],
            chart: {
                type: 'bar',
                height: 300
            },
            colors: ['#0d6efd'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    horizontal: true
                }
            },
            xaxis: {
                categories: ['Operasional', 'Keuangan', 'Asset Mgmt', 'Bisnis', 'HR & GA']
            }
        };
        new ApexCharts(document.querySelector("#chart_realisasi_divisi"), optionsRealisasi).render();

        // === Top 5 Divisi ===
        var optionsTop5 = {
            series: [{
                name: 'Realisasi (%)',
                data: [98, 95, 90, 88, 86]
            }],
            chart: {
                type: 'bar',
                height: 280
            },
            colors: ['#198754'],
            plotOptions: {
                bar: {
                    borderRadius: 5,
                    horizontal: false,
                    columnWidth: '45%'
                }
            },
            xaxis: {
                categories: ['Operasional', 'Keuangan', 'Asset Mgmt', 'Bisnis', 'HR & GA']
            },
            dataLabels: {
                enabled: true
            },
        };
        new ApexCharts(document.querySelector("#chart_top5_divisi"), optionsTop5).render();
    </script>
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            var budget_realization_chart = {
                series: [{
                    name: 'Anggaran',
                    data: [1250, 980, 1500, 1750, 900, 1100] // contoh nilai dalam juta
                }, {
                    name: 'Realisasi',
                    data: [1180, 870, 1400, 1600, 880, 950] // contoh nilai dalam juta
                }],
                chart: {
                    type: 'bar',
                    height: 380,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '45%',
                        borderRadius: 6,
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 3,
                    colors: ['transparent']
                },
                colors: ["#2CBCAD", "#4E73DF"], // Warna: hijau kebiruan & biru MUJ
                xaxis: {
                    categories: ['PLANT', 'PRODUCTION', 'MARKETING', 'FINANCE', 'HR', 'GA & PROCUREMENT'],
                    labels: {
                        style: {
                            colors: '#6c757d',
                            fontSize: '13px',
                            fontWeight: 500
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return 'Rp ' + val + ' jt';
                        },
                        style: {
                            colors: '#6c757d'
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: "dark",
                    y: {
                        formatter: function(val) {
                            return "Rp " + val + " juta";
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    fontSize: '13px',
                    labels: {
                        colors: '#333'
                    },
                    markers: {
                        radius: 6
                    }
                },
                fill: {
                    opacity: 1,
                    type: 'solid'
                }
            };

            // Render chart ke elemen dengan id basic_column_chart
            var chart = new ApexCharts(document.querySelector("#basic_column_chart"), budget_realization_chart);
            chart.render();
        });
    </script>

@endsection
