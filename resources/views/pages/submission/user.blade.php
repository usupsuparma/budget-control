@extends('layouts.master')

@section('title', 'Pengajuan Anggaran | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Daftar Pengajuan Anggaran')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection

@section('content')
<div id="layout-wrapper">

    <div class="container-fluid">

        <div class="d-flex align-items-center mt-2 mb-2">
            <h6 class="mb-0 flex-grow-1">List Pengajuan</h6>
            <div class="flex-shrink-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Anggaran</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pengajuan</li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- === SUMMARY CARD === --}}
        <div class="row mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="fw-bold">42</h2>
                        <h6 class="fw-medium text-muted">Total Pengajuan</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-primary">18</h2>
                        <h6 class="fw-medium text-muted">Menunggu Persetujuan</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-success">15</h2>
                        <h6 class="fw-medium text-muted">Disetujui</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-danger">9</h2>
                        <h6 class="fw-medium text-muted">Ditolak / Dibatalkan</h6>
                    </div>
                </div>
            </div>
        </div>

        {{-- === MAIN TABLE === --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-semibold">Daftar Pengajuan Anggaran</h6>
                <div class="d-flex align-items-center gap-3">
                    <input type="text" class="form-control form-control-sm w-auto" placeholder="Cari...">
                    <a href="{{ route('userSubmission.create') }}">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle-dotted me-1"></i>Tambah Pengajuan
                        </button>
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-box table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th>No</th>
                                <th>ID Pengajuan</th>
                                <th>Tanggal</th>
                                <th>Pemohon</th>
                                <th>Uraian Pengadaan</th>
                                <th>Qty</th>
                                <th>Harga Satuan (Rp)</th>
                                <th>Total (Rp)</th>
                                <th>Kategori</th>
                                <th>COA / Beban</th>
                                <th>Status</th>
                                <th>PIC / Approver</th>
                                <th>Lampiran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><strong>OP-2025-001</strong></td>
                                <td>02 Jan 2025</td>
                                <td>Divisi IT</td>
                                <td>Pengadaan Laptop Dell Latitude 5540</td>
                                <td>5</td>
                                <td>18.500.000</td>
                                <td>92.500.000</td>
                                <td><span class="badge bg-info-subtle text-info">Investasi</span></td>
                                <td>7102.05.01</td>
                                <td><span class="badge bg-warning-subtle text-warning">Menunggu Review</span></td>
                                <td>Bagus Chandara</td>
                                <td>
                                    <a href="#"><i class="bi bi-paperclip"></i></a>
                                </td>
                                <td>
                                    <div class="hstack gap-1 justify-content-center">
                                        <button class="btn btn-light-success icon-btn-sm" title="Detail"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-light-primary icon-btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-light-danger icon-btn-sm" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td><strong>OP-2025-002</strong></td>
                                <td>05 Jan 2025</td>
                                <td>Divisi Keuangan</td>
                                <td>Jasa Pemeliharaan AC Gedung Kantor</td>
                                <td>1 paket</td>
                                <td>35.000.000</td>
                                <td>35.000.000</td>
                                <td><span class="badge bg-secondary-subtle text-secondary">Operasional</span></td>
                                <td>6201.02.02</td>
                                <td><span class="badge bg-success-subtle text-success">Disetujui</span></td>
                                <td>Yuniarti</td>
                                <td><a href="#"><i class="bi bi-paperclip"></i></a></td>
                                <td>
                                    <div class="hstack gap-1 justify-content-center">
                                        <button class="btn btn-light-success icon-btn-sm"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-light-primary icon-btn-sm"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-light-danger icon-btn-sm"><i class="ri-delete-bin-line"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td><strong>OP-2025-003</strong></td>
                                <td>10 Jan 2025</td>
                                <td>Divisi Layanan Umum</td>
                                <td>Pengadaan Kursi Rapat 12 Unit</td>
                                <td>12</td>
                                <td>1.250.000</td>
                                <td>15.000.000</td>
                                <td><span class="badge bg-info-subtle text-info">Pengadaan</span></td>
                                <td>6301.01.03</td>
                                <td><span class="badge bg-danger-subtle text-danger">Ditolak</span></td>
                                <td>Rizal R.</td>
                                <td><a href="#"><i class="bi bi-paperclip"></i></a></td>
                                <td>
                                    <div class="hstack gap-1 justify-content-center">
                                        <button class="btn btn-light-success icon-btn-sm"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-light-primary icon-btn-sm"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-light-danger icon-btn-sm"><i class="ri-delete-bin-line"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex flex-wrap gap-4 align-items-center m-4">
                    <div class="fw-medium">Showing 1 - 3 of 3 Entries</div>
                    <div class="ms-auto">
                        <ul class="pagination pagination-sm pagination-primary mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#"><i class="ri-arrow-left-s-line"></i></a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#"><i class="ri-arrow-right-s-line"></i></a></li>
                        </ul>
                    </div>
                </div>
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