@extends('layouts.master')

@section('title', 'Resume Anggaran Keuangan | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Resume Anggaran Divisi Keuangan')

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
                        <select id="status-choice" class="form-select">
                            <option value="2026">2026</option>
                            <option value="2025" selected>2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-xl-3 col-xxl-2">
                        <select id="jenis-choice" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="Procurement">Procurement</option>
                            <option value="Investasi">Investasi</option>
                            <option value="Kinerja">Kinerja</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-xl-6 col-xxl-8 text-end">
                        <a href="{{ route('anggaran.index') }}">
                            <button class="btn btn-outline-primary"><i class="bi bi-arrow-left-circle me-2"></i>Kembali ke Daftar Anggaran</button>
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-box table-responsive">
                        <table class="table text-nowrap align-middle mb-0">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>No</th>
                                    <th>Jenis</th>
                                    <th>Beban Anggaran (COA)</th>
                                    <th>Uraian Beban</th>
                                    <th>Nilai Anggaran (Rp)</th>
                                    <th colspan="12">Realisasi Bulanan (%)</th>
                                    <th>Realisasi Total</th>
                                    <th>Actions</th>
                                </tr>
                                <tr class="small text-muted text-center">
                                    <th colspan="6"></th>
                                    <th>Jan</th>
                                    <th>Feb</th>
                                    <th>Mar</th>
                                    <th>Apr</th>
                                    <th>Mei</th>
                                    <th>Jun</th>
                                    <th>Jul</th>
                                    <th>Agu</th>
                                    <th>Sep</th>
                                    <th>Okt</th>
                                    <th>Nov</th>
                                    <th>Des</th>
                                    <th colspan="2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- ==================== BEBAN PAJAK ==================== --}}
                                <tr class="table-secondary fw-bold">
                                    <td>1</td>
                                    <td><span class="badge bg-primary">Investasi</span></td>
                                    <td colspan="4">BEBAN PAJAK</td>
                                    <td colspan="14"></td>
                                </tr>
                                <tr>
                                    <td>1.1</td>
                                    <td></td>
                                    <td>6101.01</td>
                                    <td>Beban PPN</td>
                                    <td>80.000.000</td>
                                    <td>8%</td>
                                    <td>10%</td>
                                    <td>15%</td>
                                    <td>20%</td>
                                    <td>30%</td>
                                    <td>45%</td>
                                    <td>55%</td>
                                    <td>65%</td>
                                    <td>70%</td>
                                    <td>85%</td>
                                    <td>90%</td>
                                    <td>100%</td>
                                    <td><span class="badge bg-success-subtle text-success">100%</span></td>
                                    <td>
                                        <button class="btn btn-light-primary icon-btn-sm"><i class="bi bi-eye"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>1.2</td>
                                    <td></td>
                                    <td>6101.02</td>
                                    <td>Beban PPh (21, 23, 25)</td>
                                    <td>100.000.000</td>
                                    <td>10%</td>
                                    <td>15%</td>
                                    <td>20%</td>
                                    <td>30%</td>
                                    <td>40%</td>
                                    <td>55%</td>
                                    <td>70%</td>
                                    <td>80%</td>
                                    <td>90%</td>
                                    <td>95%</td>
                                    <td>100%</td>
                                    <td>100%</td>
                                    <td><span class="badge bg-success-subtle text-success">100%</span></td>
                                    <td>
                                        <button class="btn btn-light-primary icon-btn-sm"><i class="bi bi-eye"></i></button>
                                    </td>
                                </tr>

                                {{-- ==================== BEBAN PEMELIHARAAN ==================== --}}
                                <tr class="table-secondary fw-bold">
                                    <td>2</td>
                                    <td><span class="badge bg-success">Procurement</span></td>
                                    <td colspan="4">BEBAN PEMELIHARAAN</td>
                                    <td colspan="14"></td>
                                </tr>
                                <tr>
                                    <td>2.1</td>
                                    <td></td>
                                    <td>6202.01</td>
                                    <td>Pemeliharaan Kendaraan Operasional</td>
                                    <td>75.000.000</td>
                                    <td>5%</td>
                                    <td>10%</td>
                                    <td>15%</td>
                                    <td>25%</td>
                                    <td>35%</td>
                                    <td>45%</td>
                                    <td>55%</td>
                                    <td>65%</td>
                                    <td>75%</td>
                                    <td>85%</td>
                                    <td>90%</td>
                                    <td>100%</td>
                                    <td><span class="badge bg-warning-subtle text-warning">90%</span></td>
                                    <td>
                                        <button class="btn btn-light-primary icon-btn-sm"><i class="bi bi-eye"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2.2</td>
                                    <td></td>
                                    <td>6202.02</td>
                                    <td>Pemeliharaan Gedung & Fasilitas</td>
                                    <td>125.000.000</td>
                                    <td>10%</td>
                                    <td>15%</td>
                                    <td>25%</td>
                                    <td>35%</td>
                                    <td>50%</td>
                                    <td>60%</td>
                                    <td>70%</td>
                                    <td>80%</td>
                                    <td>90%</td>
                                    <td>95%</td>
                                    <td>100%</td>
                                    <td>100%</td>
                                    <td><span class="badge bg-success-subtle text-success">100%</span></td>
                                    <td>
                                        <button class="btn btn-light-primary icon-btn-sm"><i class="bi bi-eye"></i></button>
                                    </td>
                                </tr>

                                {{-- ==================== BEBAN ADMINISTRASI KEUANGAN ==================== --}}
                                <tr class="table-secondary fw-bold">
                                    <td>3</td>
                                    <td><span class="badge bg-info">Kinerja</span></td>
                                    <td colspan="4">BEBAN ADMINISTRASI KEUANGAN</td>
                                    <td colspan="14"></td>
                                </tr>
                                <tr>
                                    <td>3.1</td>
                                    <td></td>
                                    <td>6303.01</td>
                                    <td>Beban Biaya Bank & Transaksi</td>
                                    <td>50.000.000</td>
                                    <td>5%</td>
                                    <td>10%</td>
                                    <td>15%</td>
                                    <td>25%</td>
                                    <td>35%</td>
                                    <td>45%</td>
                                    <td>60%</td>
                                    <td>70%</td>
                                    <td>80%</td>
                                    <td>85%</td>
                                    <td>90%</td>
                                    <td>100%</td>
                                    <td><span class="badge bg-success-subtle text-success">100%</span></td>
                                    <td>
                                        <button class="btn btn-light-primary icon-btn-sm"><i class="bi bi-eye"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3.2</td>
                                    <td></td>
                                    <td>6303.02</td>
                                    <td>Beban Audit dan Pelaporan Keuangan</td>
                                    <td>200.000.000</td>
                                    <td>10%</td>
                                    <td>15%</td>
                                    <td>25%</td>
                                    <td>40%</td>
                                    <td>60%</td>
                                    <td>70%</td>
                                    <td>75%</td>
                                    <td>85%</td>
                                    <td>90%</td>
                                    <td>95%</td>
                                    <td>100%</td>
                                    <td>100%</td>
                                    <td><span class="badge bg-success-subtle text-success">100%</span></td>
                                    <td>
                                        <button class="btn btn-light-primary icon-btn-sm"><i class="bi bi-eye"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-4 m-5">
                        <div class="fw-medium"> Showing 1 - 3 of 3 Beban Utama</div>
                        <div class="ms-auto">
                            <nav aria-label="Page navigation example">
                                <ul class="pagination pagination-primary mb-0">
                                    <li class="page-item disabled"><a class="page-link" href="#"><i class="ri-arrow-left-s-line fw-semibold"></i></a></li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#"><i class="ri-arrow-right-s-line fw-semibold"></i></a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div> <!-- end card-body -->
            </div>
        </div>
    </div>
</div>
</main>
@endsection

@section('js')
<script type="module" src="{{ asset('assets/js/app.js') }}"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/app/project-list.init.js') }}"></script>
@endsection