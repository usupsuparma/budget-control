@extends('layouts.master')

@section('title', 'Company Policy | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Company Policy')
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
                            {{-- <select id="status-choice">
                            <option value="all">All Years</option>
                            <option value="uk1">2025</option>
                            <option value="uk2">2024</option>
                            <option value="uk3">2023</option>
                        </select> --}}
                        </div>
                        <div class="col-md-4 col-xl-3 col-xxl-2">
                            {{-- <select id="priority-choice">
                                <option value="javascript">High</option>
                                <option value="python">Medium</option>
                                <option value="java">Low</option>
                            </select> --}}
                        </div>
                        <div class="col-md-4 col-xl-6 col-xxl-8 text-end">
                            <a href="{{ route('company-policy.create') }}">
                                <button class="btn btn-primary"><i class="bi bi-plus-circle-dotted me-2"></i>Add Company
                                    Policy</button>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-10">
                        <div class="table-box table-responsive">
                            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
                                rel="stylesheet">

                            <style>
                                /* Tambahan kecil agar panah animasi saat dibuka */
                                .accordion-toggle i.bi-chevron-down {
                                    transition: transform 0.2s ease-in-out;
                                }

                                .accordion-toggle[aria-expanded="true"] i.bi-chevron-down {
                                    transform: rotate(180deg);
                                }

                                .child-wrapper {
                                    background: #f8f9fa;
                                    padding: 10px 15px;
                                    border-radius: 4px;
                                }

                                .child-wrapper table {
                                    margin-bottom: 0;
                                }
                            </style>

                            <table id="companyPolicyTable" class="display table table-striped table-bordered"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tahun</th>
                                        <th>Nama Dokumen</th>
                                        <th>Jumlah Strategic Goals</th>
                                        <th>File</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($policies as $index => $policy)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $policy->tahun }}</td>
                                            <td>{{ $policy->nama_dokumen }}</td>
                                            <td>{{ $policy->details_count }}</td>
                                            <td>
                                                @if ($policy->file_path)
                                                    <a href="{{ asset('storage/' . $policy->file_path) }}" target="_blank"
                                                        class="btn btn-link btn-sm">
                                                        Lihat / Unduh
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm btn-detail"
                                                    data-id="{{ $policy->id }}">
                                                    Detail
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                    data-id="{{ $policy->id }}">
                                                    Delete
                                                </button>

                                                <form id="delete-form-{{ $policy->id }}"
                                                    action="{{ route('company-policy.destroy', $policy->id) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            {{-- HIDDEN TEMPLATE DETAIL (tabel) --}}
                            <div id="hidden-details" style="display:none;">
                                @foreach ($policies as $policy)
                                    <div id="detail-{{ $policy->id }}">
                                        @if ($policy->details->isEmpty())
                                            <div class="alert alert-secondary mb-0">
                                                Belum ada Strategic Goals untuk dokumen ini.
                                            </div>
                                        @else
                                            <div class="child-wrapper">
                                                <strong>Strategic Goals – {{ $policy->nama_dokumen }}
                                                    ({{ $policy->tahun }})</strong>
                                                <table class="table table-sm table-bordered mt-2">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 40px;">No</th>
                                                            <th>Strategic Goal</th>
                                                            <th>Deskripsi</th>
                                                            <th>Target</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($policy->details as $i => $detail)
                                                            <tr>
                                                                <td>{{ $i + 1 }}</td>
                                                                <td>{{ $detail->strategic_goal }}</td>
                                                                <td>{!! nl2br(e($detail->description)) !!}</td>
                                                                <td>{!! nl2br(e($detail->target)) !!}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: `
        <ul style='text-align:left;'>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    `
            });
        </script>
    @endif
@endsection

@section('js')

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <script src="{{ asset('assets/js/app/project-list.init.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#companyPolicyTable').DataTable({
                language: {
                    paginate: {
                        first: "&laquo;&laquo;",
                        previous: "&laquo;",
                        next: "&raquo;",
                        last: "&raquo;&raquo;"
                    }
                }
            });

            // Klik tombol Detail → tampilkan / sembunyikan child row berisi tabel detail
            $('#companyPolicyTable tbody').on('click', '.btn-detail', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var id = $(this).data('id');
                var html = $('#detail-' + id).html();

                if (row.child.isShown()) {
                    // Kalau sudah terbuka → tutup
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // (opsional) tutup child lain biar rapi
                    table.rows().every(function() {
                        if (this.child.isShown()) {
                            this.child.hide();
                            $(this.node()).removeClass('shown');
                        }
                    });

                    row.child(html).show();
                    tr.addClass('shown');
                }
            });

            // Klik tombol Delete → SweetAlert konfirmasi → submit form delete
            $('#companyPolicyTable tbody').on('click', '.btn-delete', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Hapus dokumen ini?',
                    text: "Dokumen dan semua strategic goals akan dihapus permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#delete-form-' + id).submit();
                    }
                });
            });

        });
    </script>
@endsection
