@extends('layouts.master')

@section('title', 'Sasaran Strategis | Budget Control')

@section('title-sub', 'KPI')
@section('pagetitle', 'Sasaran Strategis')
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
                        <a href="{{ route('sasaran-strategis.create') }}">
                            <button class="btn btn-primary"><i class="bi bi-plus-circle-dotted me-2"></i>Input Sasaran Strategis</button>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-box table-responsive">
                        <div class="table-responsive" style="overflow-x: hidden;">
                            <table class="table align-middle table-bordered"
                                style="table-layout: fixed; width: 100%; word-wrap: break-word; white-space: normal;">
                                <thead class="table-primary text-center align-middle">
                                    <tr>
                                        <th style="width: 23%;">Sasaran Strategis</th>
                                        <th style="width: 37%;">Deskripsi</th>
                                        <th style="width: 10%;">Target</th>
                                        <th style="width: 10%;">Satuan</th>
                                        <th style="width: 10%;">Catatan</th>
                                        <th style="width: 10%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="align-top">

                                    <!-- 1 -->
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-primary">
                                                <input class="form-check-input" type="checkbox" id="goal1">
                                                <label class="form-check-label ms-2" for="goal1">
                                                    <strong>Keselamatan Aktivitas & Kestabilan Operasional</strong><br>
                                                    <span class="text-muted small">Target: Zero Accident</span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            Menargetkan <strong>Zero Accident</strong> dan maksimal 3 gangguan internal pabrik.
                                            Menjaga keselamatan kerja, kestabilan operasi, serta kepatuhan terhadap regulasi
                                            untuk mencapai target produksi dan zero keluhan pelanggan.
                                        </td>
                                        <td>
                                            <span class="fw-semibold small">100%</span>
                                            <div class="progress progress-xs mt-1">
                                                <div class="progress-bar bg-success" style="width:100%"></div>
                                            </div>
                                        </td>
                                        <td>Compliance</td>
                                        <td><span class="badge bg-success-subtle text-success">Ongoing</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('sasaran-strategis.edit',1) }}" class="btn btn-light-primary btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button class="btn btn-light-danger btn-sm">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- 2 -->
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-primary">
                                                <input class="form-check-input" type="checkbox" id="goal2">
                                                <label class="form-check-label ms-2" for="goal2">
                                                    <strong>Peningkatan Penjualan & Daya Saing Produk</strong><br>
                                                    <span class="text-muted small">Target: Diversifikasi Produk</span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            Meningkatkan penjualan melalui diversifikasi produk bernilai tambah, perluasan pasar
                                            domestik dan ekspor, serta efisiensi distribusi dan strategi pemasaran digital.
                                        </td>
                                        <td>
                                            <span class="fw-semibold small">85%</span>
                                            <div class="progress progress-xs mt-1">
                                                <div class="progress-bar bg-info" style="width:85%"></div>
                                            </div>
                                        </td>
                                        <td>Volume Sales</td>
                                        <td><span class="badge bg-info-subtle text-info">In Progress</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('sasaran-strategis.edit',2) }}" class="btn btn-light-primary btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button class="btn btn-light-danger btn-sm">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- 3 -->
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-primary">
                                                <input class="form-check-input" type="checkbox" id="goal3">
                                                <label class="form-check-label ms-2" for="goal3">
                                                    <strong>Penguatan Tata Kelola & Digitalisasi Sistem</strong><br>
                                                    <span class="text-muted small">Target: Implementasi Sistem Digital</span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            Memperkuat tata kelola dan mempercepat transformasi digital dengan penyusunan
                                            <em>blueprint</em> sistem TI terintegrasi untuk efisiensi dan transparansi proses bisnis.
                                        </td>
                                        <td>
                                            <span class="fw-semibold small">70%</span>
                                            <div class="progress progress-xs mt-1">
                                                <div class="progress-bar bg-warning" style="width:70%"></div>
                                            </div>
                                        </td>
                                        <td>Implementasi</td>
                                        <td><span class="badge bg-warning-subtle text-warning">In Progress</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('sasaran-strategis.edit',3) }}" class="btn btn-light-primary btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button class="btn btn-light-danger btn-sm">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- 4 -->
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-primary">
                                                <input class="form-check-input" type="checkbox" id="goal4">
                                                <label class="form-check-label ms-2" for="goal4">
                                                    <strong>Pengembangan Sumber Daya Manusia (SDM)</strong><br>
                                                    <span class="text-muted small">Target: Kompetensi & Produktivitas</span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            Fokus pada pembentukan karakter, mindset, budaya kerja produktif, serta peningkatan
                                            kompetensi teknis dan non-teknis untuk mendukung kinerja organisasi.
                                        </td>
                                        <td>
                                            <span class="fw-semibold small">90%</span>
                                            <div class="progress progress-xs mt-1">
                                                <div class="progress-bar bg-success" style="width:90%"></div>
                                            </div>
                                        </td>
                                        <td>Training KPI</td>
                                        <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('sasaran-strategis.edit',4) }}" class="btn btn-light-primary btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button class="btn btn-light-danger btn-sm">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- 5 -->
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-primary">
                                                <input class="form-check-input" type="checkbox" id="goal5">
                                                <label class="form-check-label ms-2" for="goal5">
                                                    <strong>Optimalisasi Keuangan & Efisiensi Operasional</strong><br>
                                                    <span class="text-muted small">Target: EBITDA ≥0.2 BJPY, ROE ≥9%, ROIC ≥10%</span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            Meningkatkan efisiensi keuangan dan transparansi dengan pengelolaan anggaran,
                                            monitoring kinerja keuangan, serta optimalisasi biaya operasional.
                                        </td>
                                        <td>
                                            <span class="fw-semibold small">95%</span>
                                            <div class="progress progress-xs mt-1">
                                                <div class="progress-bar bg-primary" style="width:95%"></div>
                                            </div>
                                        </td>
                                        <td>Finance Ratio</td>
                                        <td><span class="badge bg-primary-subtle text-primary">Stable</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('sasaran-strategis.edit',5) }}" class="btn btn-light-primary btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button class="btn btn-light-danger btn-sm">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
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