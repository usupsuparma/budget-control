@extends('layouts.master')

@section('title', 'KPI | Budget Control')

@section('title-sub', 'KPI')
@section('pagetitle', 'KPI & Program Kerja')
@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection
@section('content')

<!-- Begin page -->
<div id="layout-wrapper">

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-4 col-xl-3 col-xxl-2 me-2">
                        <select id="status-choice">
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-xl-3 col-xxl-2">
                        {{-- <select id="priority-choice">
                                <option value="javascript">High</option>
                                <option value="python">Medium</option>
                                <option value="java">Low</option>
                            </select> --}}
                    </div>
                    <div class="col-md-4 col-xl-6 col-xxl-8 text-end">
                        <a href="{{ route('kpi.create') }}">
                            <button class="btn btn-primary"><i class="bi bi-plus-circle-dotted me-2"></i>Input KPI & Program Kerja</button>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-box table-responsive">
                        <table class="table table-bordered align-middle text-nowrap">
                            <thead class="table-primary text-center align-middle">
                                <tr>
                                    <th style="width: 3%;">No</th>
                                    <th style="width: 8%;">Strategic Goals</th>
                                    <th style="width: 8%;">Division Goals / KPI</th>
                                    <th style="width: 6%;">Target</th>
                                    <th style="width: 6%;">Duration (Days)</th>
                                    <th style="width: 6%;">Schedule<br>(Jan–Dec)</th>
                                    <th style="width: 3%;">Jan</th>
                                    <th style="width: 3%;">Feb</th>
                                    <th style="width: 3%;">Mar</th>
                                    <th style="width: 3%;">Apr</th>
                                    <th style="width: 3%;">May</th>
                                    <th style="width: 3%;">Jun</th>
                                    <th style="width: 3%;">Jul</th>
                                    <th style="width: 3%;">Aug</th>
                                    <th style="width: 3%;">Sep</th>
                                    <th style="width: 3%;">Oct</th>
                                    <th style="width: 3%;">Nov</th>
                                    <th style="width: 3%;">Dec</th>
                                    <th style="width: 7%;">Revenue/Cost</th>
                                    <th style="width: 8%;">PIC</th>
                                    <th style="width: 15%;">Description</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- 1 -->
                                <tr>
                                    <td>1</td>
                                    <td>Keselamatan Aktivitas & Kestabilan Operasional</td>
                                    <td class="small text-muted">Menjamin akurasi & kepatuhan laporan keuangan serta pajak sesuai regulasi</td>
                                    <td>100%</td>
                                    <td>365</td>
                                    <td class="text-center fw-semibold">Jan–Dec</td>
                                    @for($i=1;$i<=12;$i++)
                                        <td><span class="badge bg-success-subtle text-success">&nbsp;</span></td>
                                        @endfor
                                        <td>Compliance</td>
                                        <td>Finance Manager</td>
                                        <td>Monitoring laporan keuangan, audit internal, dan kepatuhan pajak</td>
                                </tr>

                                <!-- 2 -->
                                <tr>
                                    <td>2</td>
                                    <td>Peningkatan Penjualan & Daya Saing Produk</td>
                                    <td class="small text-muted">Mempercepat arus kas & efektivitas proses anggaran</td>
                                    <td>85%</td>
                                    <td>365</td>
                                    <td class="text-center fw-semibold">Jan–Dec</td>
                                    @for($i=1;$i<=12;$i++)
                                        <td><span class="badge bg-info-subtle text-info">&nbsp;</span></td>
                                        @endfor
                                        <td>Revenue</td>
                                        <td>Treasury & Budget</td>
                                        <td>Penguatan sistem penagihan dan percepatan proses pengajuan</td>
                                </tr>

                                <!-- 3 -->
                                <tr>
                                    <td>3</td>
                                    <td>Penguatan Tata Kelola & Digitalisasi Sistem</td>
                                    <td class="small text-muted">Implementasi penuh sistem ERP & otomatisasi laporan keuangan</td>
                                    <td>70%</td>
                                    <td>180</td>
                                    <td class="text-center fw-semibold">Jan–Jun</td>
                                    @for($i=1;$i<=6;$i++)
                                        <td><span class="badge bg-warning-subtle text-warning">&nbsp;</span></td>
                                        @endfor
                                        @for($i=7;$i<=12;$i++)
                                            <td>
                                            </td>
                                            @endfor
                                            <td>Cost</td>
                                            <td>IT & Finance</td>
                                            <td>Integrasi ERP (Budget Control, Cashflow, Reporting)</td>
                                </tr>

                                <!-- 4 -->
                                <tr>
                                    <td>4</td>
                                    <td>Pengembangan Sumber Daya Manusia (SDM)</td>
                                    <td class="small text-muted">Peningkatan kompetensi staf keuangan melalui pelatihan & sertifikasi</td>
                                    <td>90%</td>
                                    <td>365</td>
                                    <td class="text-center fw-semibold">Feb–Nov</td>
                                    @for($i=2;$i<=12;$i++)
                                        <td><span class="badge bg-success-subtle text-success">&nbsp;</span></td>
                                        @endfor
                                        <td></td>
                                        <td>Cost</td>
                                        <td>HR & Finance</td>
                                        <td>Pelatihan perpajakan, akuntansi, ERP, & audit</td>
                                </tr>

                                <!-- 5 -->
                                <tr>
                                    <td>5</td>
                                    <td>Optimalisasi Keuangan & Efisiensi Operasional</td>
                                    <td class="small text-muted">Efisiensi biaya & pengendalian realisasi anggaran</td>
                                    <td>95%</td>
                                    <td>365</td>
                                    <td class="text-center fw-semibold">Jan–Dec</td>
                                    @for($i=1;$i<=12;$i++)
                                        <td><span class="badge bg-primary-subtle text-primary">&nbsp;</span></td>
                                        @endfor
                                        <td>Cost Saving</td>
                                        <td>Finance & Procurement</td>
                                        <td>Optimalisasi budget control dan efisiensi biaya operasional</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-4 m-5">
                        <div class="fw-medium"> Showing 1 - 10 of 18 Entries</div>
                        <div class="ms-auto">
                            <nav aria-label="Page navigation example">
                                <ul class="pagination pagination-primary mb-0">
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)">
                                            <i class="ri-arrow-left-s-line fw-semibold"></i>
                                        </a>
                                    </li>
                                    <li class="page-item"><a class="page-link" href="javascript:void(0)">1</a></li>
                                    <li class="page-item active"><a class="page-link" href="javascript:void(0)">2</a>
                                    </li>
                                    <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                    <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                    <li class="page-item"><a class="page-link" href="javascript:void(0)">5</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)">
                                            <i class="ri-arrow-right-s-line fw-semibold"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
@endsection

@section('js')

<!-- App js -->
<script type="module" src="{{ asset('assets/js/app.js') }}"></script>

<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

<script src="{{ asset('assets/js/app/project-list.init.js') }}"></script>
@endsection