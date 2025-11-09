@extends('layouts.master')

@section('title', 'Anggaran | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Anggaran')
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
                        <a href="{{ route('anggaran.create') }}">
                            <button class="btn btn-primary"><i class="bi bi-plus-circle-dotted me-2"></i>Input Anggaran</button>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-box table-responsive">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle text-center" style="font-size: 12px;">
                                <thead>
                                    <tr class="table-warning">
                                        <th rowspan="2" style="vertical-align: middle;">DESCRIPTION<br><small>(1)</small></th>
                                        <th rowspan="2" style="vertical-align: middle;">Program ID<br><small>(2)</small></th>
                                        <th rowspan="2" style="vertical-align: middle;">STOCK CODE<br><small>(3)</small></th>
                                        <th rowspan="2" style="vertical-align: middle;">BUDGET CODE<br><small>(4)</small></th>
                                        <th rowspan="2" style="vertical-align: middle;">Product Line<br><small>(5)</small></th>
                                        <th rowspan="2" style="vertical-align: middle;">Cost Centre<br><small>(6)</small></th>
                                        <th rowspan="2" style="vertical-align: middle;">BEG BALANCE<br>(PROGNOSIS)<br><small>(7)</small></th>
                                        <th rowspan="2" style="vertical-align: middle;">SUPPLIER<br><small>(8)</small></th>
                                        <th rowspan="2" style="vertical-align: middle;">UNIT<br><small>(9)</small></th>
                                        <th colspan="13" style="background-color: #FFF9C4;">2025</th>
                                        <th colspan="2" class="table-info">Price Estimation</th>
                                    </tr>
                                    <tr class="table-warning">
                                        <th>JAN<br><small>(10)</small></th>
                                        <th>FEB<br><small>(11)</small></th>
                                        <th>MAR<br><small>(12)</small></th>
                                        <th>APR<br><small>(13)</small></th>
                                        <th>MAY<br><small>(14)</small></th>
                                        <th>JUN<br><small>(15)</small></th>
                                        <th>JUL<br><small>(16)</small></th>
                                        <th>AUG<br><small>(17)</small></th>
                                        <th>SEP<br><small>(18)</small></th>
                                        <th>OCT<br><small>(19)</small></th>
                                        <th>NOV<br><small>(20)</small></th>
                                        <th>DEC<br><small>(21)</small></th>
                                        <th>TOTAL<br><small>(22)</small></th>
                                        <th class="table-info">Estimation<br><small>(23)</small></th>
                                        <th class="table-info">Estimation Description<br><small>(24)</small></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td class="text-start">Biaya Pemeliharaan Sistem ERP</td>
                                        <td>PRG-001</td>
                                        <td>STK-1001</td>
                                        <td>BDG-110</td>
                                        <td>IT Digitalization</td>
                                        <td>Finance</td>
                                        <td>Rp 50.000.000</td>
                                        <td>PT Aplikasi Jaya</td>
                                        <td>Paket</td>
                                        <td>5</td>
                                        <td>5</td>
                                        <td>10</td>
                                        <td>5</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>10</td>
                                        <td>10</td>
                                        <td>5</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>

                                        <td>Rp 200.000.000</td>
                                        <td>Langganan sistem & maintenance ERP Budget Control</td>
                                    </tr>

                                    <tr>
                                        <td class="text-start">Audit Keuangan Eksternal</td>
                                        <td>PRG-002</td>
                                        <td>STK-1102</td>
                                        <td>BDG-210</td>
                                        <td>Financial Governance</td>
                                        <td>Finance</td>
                                        <td>Rp 25.000.000</td>
                                        <td>KAP ABC</td>
                                        <td>Paket</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>10</td>
                                        <td>10</td>
                                        <td>10</td>
                                        <td>5</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>35</td>
                                        <td>Rp 150.000.000</td>
                                        <td>Jasa audit laporan keuangan tahunan oleh auditor eksternal</td>
                                    </tr>

                                    <tr>
                                        <td class="text-start">Pelatihan Pajak & Akuntansi</td>
                                        <td>PRG-003</td>
                                        <td>STK-2010</td>
                                        <td>BDG-310</td>
                                        <td>Training</td>
                                        <td>HR</td>
                                        <td>Rp 10.000.000</td>
                                        <td>TaxEdu Indonesia</td>
                                        <td>Orang</td>
                                        <td>0</td>
                                        <td>5</td>
                                        <td>5</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>10</td>
                                        <td>5</td>
                                        <td>0</td>
                                        <td>5</td>
                                        <td>5</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>35</td>
                                        <td>Rp 75.000.000</td>
                                        <td>Pelatihan kompetensi staf keuangan bidang perpajakan & akuntansi</td>
                                    </tr>

                                    <tr>
                                        <td class="text-start">Pemeliharaan Database Keuangan</td>
                                        <td>PRG-004</td>
                                        <td>STK-2201</td>
                                        <td>BDG-410</td>
                                        <td>System</td>
                                        <td>IT</td>
                                        <td>Rp 15.000.000</td>
                                        <td>PT DataTech</td>
                                        <td>Paket</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>5</td>
                                        <td>5</td>
                                        <td>5</td>
                                        <td>5</td>
                                        <td>10</td>
                                        <td>10</td>
                                        <td>10</td>
                                        <td>5</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>55</td>
                                        <td>Rp 120.000.000</td>
                                        <td>Pemeliharaan database & backup server keuangan setiap semester</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

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