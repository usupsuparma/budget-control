@extends('layouts.master')

@section('title', 'Dashboard | Budget Control System')
@section('title-sub', 'Dashboard')
@section('pagetitle', 'Dashboard')

@section('content')

<div id="layout-wrapper">

    {{-- ── Notifications ─────────────────────────────────────── --}}
    @if(!empty($notifications) && count($notifications) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-bell me-2"></i>Notifications</h6>
                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notif)
                        <div class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <div class="flex-shrink-0">
                                @php
                                $bgColor = 'bg-primary';
                                $icon = 'bi-info-circle';
                                if ($notif->category) {
                                switch (strtolower($notif->category->name)) {
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
                                <p class="mb-0 text-muted fs-13 text-truncate" style="max-width: 500px;">{{ $notif->details }}</p>
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
    </div>
    @endif

    {{-- ── Year Filter + Loading Skeleton ─────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 fw-semibold">Budget Overview</h5>
        <div class="d-flex align-items-center gap-2">
            <label for="dashboard-year-filter" class="text-muted mb-0 me-1"><i class="bi bi-calendar3"></i></label>
            <select id="dashboard-year-filter" class="form-select form-select-sm" style="width:110px;">
                @for($y = now()->year; $y >= now()->year - 4; $y--)
                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- ── Row 1: 4 KPI Cards ──────────────────────────────────── --}}
    <div class="row g-3 mb-4" id="kpi-cards-row">

        {{-- Budget Total --}}
        <div class="col-xxl-3 col-xl-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="h-70px w-70px bg-primary bg-opacity-10 position-absolute top-0 end-0 blur-md rounded-circle" style="transform:translate(20px,-20px);"></div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 fs-13">Budget Total</p>
                            <h4 class="mb-0 fw-bold" id="stat-total-budget">
                                <span class="placeholder col-8 rounded"></span>
                            </h4>
                        </div>
                        <div class="h-50px w-50px d-flex justify-content-center align-items-center bg-primary text-white fs-4 rounded-pill flex-shrink-0">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                    <p class="text-muted fs-13 mb-0">Seluruh divisi &bull; tahun berjalan</p>
                </div>
            </div>
        </div>

        {{-- Budget Realisasi --}}
        <div class="col-xxl-3 col-xl-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="h-70px w-70px bg-warning bg-opacity-10 position-absolute top-0 end-0 blur-md rounded-circle" style="transform:translate(20px,-20px);"></div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 fs-13">Budget Realisasi</p>
                            <h4 class="mb-0 fw-bold" id="stat-total-realization">
                                <span class="placeholder col-8 rounded"></span>
                            </h4>
                        </div>
                        <div class="h-50px w-50px d-flex justify-content-center align-items-center bg-warning text-white fs-4 rounded-pill flex-shrink-0">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    <p class="text-muted fs-13 mb-0">Total pencairan & LPJ reimburse</p>
                </div>
            </div>
        </div>

        {{-- Budget Balance --}}
        <div class="col-xxl-3 col-xl-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="h-70px w-70px bg-success bg-opacity-10 position-absolute top-0 end-0 blur-md rounded-circle" style="transform:translate(20px,-20px);"></div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 fs-13">Budget Balance</p>
                            <h4 class="mb-0 fw-bold" id="stat-balance">
                                <span class="placeholder col-8 rounded"></span>
                            </h4>
                        </div>
                        <div class="h-50px w-50px d-flex justify-content-center align-items-center bg-success text-white fs-4 rounded-pill flex-shrink-0">
                            <i class="bi bi-piggy-bank"></i>
                        </div>
                    </div>
                    <p class="text-muted fs-13 mb-0">Total &minus; Realisasi</p>
                </div>
            </div>
        </div>

        {{-- KPI Achievement --}}
        <div class="col-xxl-3 col-xl-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="h-70px w-70px bg-info bg-opacity-10 position-absolute top-0 end-0 blur-md rounded-circle" style="transform:translate(20px,-20px);"></div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 fs-13">KPI Achievement</p>
                            <h4 class="mb-0 fw-bold" id="stat-kpi">
                                <span class="placeholder col-4 rounded"></span>
                            </h4>
                        </div>
                        <div class="h-50px w-50px d-flex justify-content-center align-items-center bg-info text-white fs-4 rounded-pill flex-shrink-0">
                            <i class="bi bi-bar-chart-line"></i>
                        </div>
                    </div>
                    <p class="text-muted fs-13 mb-0">Pencapaian KPI organisasi</p>
                </div>
            </div>
        </div>

    </div>{{-- /.row kpi cards --}}

    {{-- ── Row 2: Chart (Budget vs Realisasi) + Total Activities ── --}}
    <div class="row g-3 mb-4">

        {{-- Budget vs Realisasi Chart --}}
        <div class="col-xl-8 col-xxl-9">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                    <h6 class="mb-0 fw-semibold">Budget vs Realisasi</h6>
                    <span class="badge bg-light text-muted" id="chart-year-badge">{{ now()->year }}</span>
                </div>
                <div class="card-body pt-0">
                    <div id="budget-vs-realization-chart" style="min-height:280px;"></div>
                </div>
            </div>
        </div>

        {{-- Total Activities --}}
        <div class="col-xl-4 col-xxl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-semibold mb-3">Total Activities</h6>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="h-55px w-55px d-flex justify-content-center align-items-center bg-warning-subtle text-warning rounded-circle fs-3">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 fw-bold" id="stat-pending-activities">
                                    <span class="placeholder col-5 rounded"></span>
                                </h3>
                                <p class="text-muted mb-0 fs-13">Transaksi menunggu approval</p>
                            </div>
                        </div>
                        <div class="p-3 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-25">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning text-dark fs-13 px-3 py-2">Pending</span>
                                <span class="text-muted fs-13">Perlu tindakan atasan</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <p class="text-muted fs-12 mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Berdasarkan transaksi dengan status <code>pending</code> approval.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /.row chart + activities --}}

    {{-- ── Row 3: Division Budget Realization Table ────────────── --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                    <h6 class="mb-0 fw-semibold">Division Budget Realization</h6>
                    <span class="text-muted fs-13" id="division-table-year">Tahun {{ now()->year }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="division-realization-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width:40px;">#</th>
                                    <th>Divisi</th>
                                    <th class="text-end">Budget</th>
                                    <th class="text-end">Realisasi</th>
                                    <th style="min-width:180px;">Persentase (%)</th>
                                </tr>
                            </thead>
                            <tbody id="division-realization-tbody">
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="d-flex justify-content-center align-items-center gap-2 text-muted">
                                            <div class="spinner-border spinner-border-sm" role="status"></div>
                                            <span>Memuat data...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot id="division-realization-tfoot" class="d-none">
                                <tr class="table-secondary fw-semibold">
                                    <td class="ps-4" colspan="2">TOTAL</td>
                                    <td class="text-end" id="tfoot-budget">—</td>
                                    <td class="text-end" id="tfoot-realization">—</td>
                                    <td id="tfoot-pct">—</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>{{-- /.row division table --}}

</div>{{-- /#layout-wrapper --}}

@endsection

@section('js')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    (function() {
        'use strict';

        // ── Config ────────────────────────────────────────────────────────────
        const URLS = {
            stats: "{{ route('dash.executive.stats') }}",
            division: "{{ route('dash.executive.division.realization') }}",
            monthly: "{{ route('dash.executive.monthly.chart') }}",
        };

        let budgetChart = null;
        let currentYear = {
            {
                now() - > year
            }
        };

        // ── Helpers ───────────────────────────────────────────────────────────
        function formatRupiah(value) {
            if (value === null || value === undefined) return '—';
            const num = parseFloat(value);
            if (isNaN(num)) return '—';
            return 'Rp ' + num.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function formatPct(value) {
            if (value === null || value === undefined) return '0%';
            return parseFloat(value).toFixed(1) + '%';
        }

        function pctBadgeClass(pct) {
            if (pct >= 90) return 'bg-danger';
            if (pct >= 70) return 'bg-warning';
            if (pct >= 40) return 'bg-info';
            return 'bg-success';
        }

        // ── KPI Cards ─────────────────────────────────────────────────────────
        function loadStats(year) {
            ['stat-total-budget', 'stat-total-realization', 'stat-balance', 'stat-kpi', 'stat-pending-activities'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = '<span class="placeholder col-8 rounded"></span>';
            });

            $.get(URLS.stats, {
                year
            }, function(res) {
                if (!res.success) return;
                const d = res.data;

                document.getElementById('stat-total-budget').textContent = formatRupiah(d.total_budget);
                document.getElementById('stat-total-realization').textContent = formatRupiah(d.total_realization);
                document.getElementById('stat-balance').textContent = formatRupiah(d.balance);
                document.getElementById('stat-kpi').textContent = formatPct(d.kpi_achievement);
                document.getElementById('stat-pending-activities').textContent = d.pending_activities;
            });
        }

        // ── Monthly Chart ─────────────────────────────────────────────────────
        function initChart(labels, budgetSeries, realizationSeries) {
            const opts = {
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit',
                },
                series: [{
                        name: 'Budget',
                        type: 'bar',
                        data: budgetSeries
                    },
                    {
                        name: 'Realisasi',
                        type: 'line',
                        data: realizationSeries
                    },
                ],
                colors: ['#5b73e8', '#f5b225'],
                stroke: {
                    width: [0, 3],
                    curve: 'smooth'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '50%'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: labels,
                    labels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: v => 'Rp ' + (v / 1e6).toFixed(1) + 'M',
                        style: {
                            fontSize: '11px'
                        },
                    },
                },
                tooltip: {
                    y: {
                        formatter: v => formatRupiah(v)
                    },
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '13px',
                },
                grid: {
                    borderColor: '#e9ecef',
                    strokeDashArray: 4
                },
            };

            if (budgetChart) {
                budgetChart.updateOptions({
                    xaxis: {
                        categories: labels
                    }
                });
                budgetChart.updateSeries([{
                        name: 'Budget',
                        data: budgetSeries
                    },
                    {
                        name: 'Realisasi',
                        data: realizationSeries
                    },
                ]);
            } else {
                budgetChart = new ApexCharts(document.getElementById('budget-vs-realization-chart'), opts);
                budgetChart.render();
            }
        }

        function loadMonthlyChart(year) {
            $.get(URLS.monthly, {
                year
            }, function(res) {
                if (!res.success) return;
                const d = res.data;
                initChart(d.labels, d.budget, d.realization);
            });
        }

        // ── Division Table ────────────────────────────────────────────────────
        function loadDivisionTable(year) {
            const tbody = document.getElementById('division-realization-tbody');
            const tfoot = document.getElementById('division-realization-tfoot');
            tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="d-flex justify-content-center align-items-center gap-2 text-muted">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <span>Memuat data...</span>
                    </div>
                </td>
            </tr>`;
            tfoot.classList.add('d-none');

            $.get(URLS.division, {
                year
            }, function(res) {
                if (!res.success) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Gagal memuat data.</td></tr>';
                    return;
                }

                const rows = res.data;

                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data untuk tahun ini.</td></tr>';
                    return;
                }

                let html = '';
                let totalBudget = 0;
                let totalReal = 0;

                rows.forEach((row, idx) => {
                    const pct = parseFloat(row.percentage);
                    const barCls = pctBadgeClass(pct);
                    totalBudget += parseFloat(row.budget);
                    totalReal += parseFloat(row.realization);

                    html += `
                <tr>
                    <td class="ps-4 text-muted">${idx + 1}</td>
                    <td class="fw-medium">${row.division_name}</td>
                    <td class="text-end">${formatRupiah(row.budget)}</td>
                    <td class="text-end">${formatRupiah(row.realization)}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;">
                                <div class="progress-bar ${barCls}" style="width:${Math.min(pct,100)}%;"></div>
                            </div>
                            <span class="text-muted fs-13 text-end" style="min-width:46px;">${formatPct(pct)}</span>
                        </div>
                    </td>
                </tr>`;
                });

                tbody.innerHTML = html;

                // Footer totals
                const grandPct = totalBudget > 0 ? ((totalReal / totalBudget) * 100) : 0;
                document.getElementById('tfoot-budget').textContent = formatRupiah(totalBudget);
                document.getElementById('tfoot-realization').textContent = formatRupiah(totalReal);
                document.getElementById('tfoot-pct').innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height:8px;">
                        <div class="progress-bar ${pctBadgeClass(grandPct)}" style="width:${Math.min(grandPct,100)}%;"></div>
                    </div>
                    <span class="text-muted fs-13" style="min-width:46px;">${formatPct(grandPct)}</span>
                </div>`;
                tfoot.classList.remove('d-none');
            });
        }

        // ── Bootstrap All ─────────────────────────────────────────────────────
        function loadAll(year) {
            document.getElementById('chart-year-badge').textContent = year;
            document.getElementById('division-table-year').textContent = 'Tahun ' + year;
            loadStats(year);
            loadMonthlyChart(year);
            loadDivisionTable(year);
        }

        $(document).ready(function() {
            loadAll(currentYear);

            $('#dashboard-year-filter').on('change', function() {
                currentYear = parseInt($(this).val());
                loadAll(currentYear);
            });
        });

    })();
</script>
@endsection