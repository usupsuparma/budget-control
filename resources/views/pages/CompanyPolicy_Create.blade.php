@extends('layouts.master')

@section('title', 'Company Policy | Budget Control')

@section('title-sub', 'Company Policy')
@section('pagetitle', 'Add Data')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection
@section('content')

    <!-- Begin page -->
    <div id="layout-wrapper">
        <form action="{{ route('company-policy.store') }}" method="POST" enctype="multipart/form-data" id="dokumenForm">
            @csrf
            <div class="row">

                <div class="col-xl-12">
                    <div class="card card-h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Add Document</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">
                                <div class="col-12">
                                    <div class="">
                                        <div class="tab-content" id="default-select">
                                            <div class="tab-pane fade show active" id="html-default-select" role="tabpanel"
                                                aria-labelledby="html-default-select-tab" tabindex="0">
                                                <label class="form-label" for="product-size-add">Year</label>
                                                <select class="form-select" id="form-select-01" name="tahun"
                                                    aria-label="Default select example">
                                                    <option selected>Select</option>
                                                    @for ($year = 2023; $year <= date('Y'); $year++)
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <label class="form-label" for="product-cost-add">Upload Document Company Policy</label>
                                    <input class="form-control" type="file" name="file_dokumen" id="formFile">
                                </div>

                            </div>
                        </div>
                    </div>
                </div><!--End col-->

            </div><!--End row-->
            <div class="row">

                <div class="col-xl-12">
                    <div class="card card-h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Add Detail Document</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">
                                <div class="col-xl-12">
                                    <div class="p-3">
                                        <div style="margin-bottom: 10px;">
                                            <button id="btnAddRow" role="button" type="button" class="btn btn-primary"><i
                                                    class="bi bi-plus-circle"></i> Add New Details</button>
                                        </div>
                                        <h6 class="fw-bold mb-2">Detail Company Policy (2025)</h6>
                                        <table id="company_policy_table" class="display" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Strategic Goals</th>
                                                    <th>Descriptions</th>
                                                    <th>Target</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            {{-- <tbody>
                                            <tr>
                                                <td class="editable">Andi</td>
                                                <td class="editable">Staff</td>
                                                <td class="editable">Jakarta</td>
                                                <td>
                                                    <a href="#" role="button" class="btn btn-small btn-danger"><i class="bi bi-trash"></i></a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="editable">Budi</td>
                                                <td class="editable">Manager</td>
                                                <td class="editable">Bandung</td>
                                                <td>
                                                    <a href="#" role="button" class="btn btn-small btn-danger"><i class="bi bi-trash"></i></a>
                                                </td>
                                            </tr>
                                        </tbody> --}}
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--End col-->

            </div><!--End row-->
            <div class="row">

                <div class="col-xl-12">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-xl-12">
                                    <div class="text-end">
                                        <a href="{{ route('company-policy.index') }}" role="button"
                                            class="btn btn-light-primary">Cancel</a>
                                        <button type="submit" role="button" class="btn btn-primary" id="btnSubmit">
                                            Submit
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div><!--End col-->

            </div><!--End row-->
        </form>
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
            // Inisialisasi DataTable
            var table = $('#company_policy_table').DataTable({
                columnDefs: [{
                        orderable: false,
                        searchable: false,
                        targets: -1
                    } // kolom aksi
                ],
                language: {
                    paginate: {
                        first: "&laquo;&laquo;", // <<
                        previous: "&laquo;", // <
                        next: "&raquo;", // >
                        last: "&raquo;&raquo;" // >>
                    }
                }
            });

            // Fungsi HTML tombol delete
            function deleteButtonHtml() {
                return '<button role="button" class="btn btn-danger btn-delete"><i class="bi bi-trash"></i> Delete</button>';
            }

            // Pastikan 3 kolom pertama baris awal editable
            $('#company_policy_table tbody tr').each(function() {
                $(this).find('td').eq(0).addClass('editable');
                $(this).find('td').eq(1).addClass('editable');
                $(this).find('td').eq(2).addClass('editable');
            });

            // Tambah baris baru di dalam tabel
            $('#btnAddRow').on('click', function() {
                // Cegah kalau sudah ada baris yang sedang ditambah (mode input)
                if ($('#company_policy_table tbody tr.adding').length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: "Selesaikan dulu baris yang sedang ditambah.",
                    });
                    return;
                }

                var newRowNode = table.row.add([
                    '<textarea name="goal[]" class="edit-input new-input new-name form-control" rows="3" placeholder="Strategic Goals" required></textarea>',
                    '<textarea name="deskripsi[]" class="edit-input new-input new-pos form-control" rows="3" placeholder="Descriptions" required></textarea>',
                    '<textarea name="target[]" class="edit-input new-input new-addr form-control" rows="3" placeholder="Target" required></textarea>',
                    '<button role="button" type="button" class="btn btn-info btn-save-new"><i class="bi bi-floppy"></i> Save</button>' +
                    '<button role="button" type="button" class="btn btn-warning btn-cancel-new"><i class="bi bi-x-square"></i> Cancel</button>'
                ]).draw(false).node();

                $(newRowNode).addClass('adding');
                // Focus ke kolom pertama
                $(newRowNode).find('.new-name').focus();
            });

            // Save baris baru
            $('#company_policy_table tbody').on('click', '.btn-save-new', function() {
                var $row = $(this).closest('tr');
                var row = table.row($row);

                var name = $row.find('.new-name').val().trim();
                var pos = $row.find('.new-pos').val().trim();
                var addr = $row.find('.new-addr').val().trim();

                if (!name || !pos || !addr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: "Strategic Goals, Descriptions, Target harus diisi",
                    });
                    return;
                }

                // Ubah menjadi baris normal
                row.data([
                    name,
                    pos,
                    addr,
                    deleteButtonHtml()
                ]).draw(false);

                var node = row.node();
                $(node).removeClass('adding');
                $(node).find('td').eq(0).addClass('editable');
                $(node).find('td').eq(1).addClass('editable');
                $(node).find('td').eq(2).addClass('editable');
            });

            // Cancel baris baru
            $('#company_policy_table tbody').on('click', '.btn-cancel-new', function() {
                var $row = $(this).closest('tr');
                table.row($row).remove().draw(false);
            });

            // Enter / Esc di input baris baru
            $('#company_policy_table tbody').on('keydown', 'input.new-input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $(this).closest('tr').find('.btn-save-new').click();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    $(this).closest('tr').find('.btn-cancel-new').click();
                }
            });

            // Delete baris
            $('#company_policy_table tbody').on('click', '.btn-delete', function() {
                let row = table.row($(this).closest('tr'));

                Swal.fire({
                    title: 'Yakin hapus?',
                    text: "Data ini tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        row.remove().draw(false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: 'Baris data berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });


            // Double klik untuk edit sel (baris yang sudah jadi)
            $('#company_policy_table tbody').on('dblclick', 'td.editable', function() {
                var cell = table.cell(this);
                var originalValue = cell.data();

                // Kalau sudah ada input di sel ini, jangan buat lagi
                if ($(this).find('input.edit-input').length > 0) return;

                // Ganti isi sel jadi input
                $(this).html(
                    '<input type="text" class="edit-input" value="' +
                    $('<div>').text(originalValue).html() +
                    '">'
                );

                var input = $(this).find('input.edit-input');
                input.focus();
                input.select();

                // Enter = save, Esc = batal (kembali ke nilai lama)
                input.on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        var newValue = $(this).val();
                        cell.data(newValue).draw(false);
                    } else if (e.key === 'Escape') {
                        cell.data(originalValue).draw(false);
                    }
                });

                // (opsional) blur = save juga
                input.on('blur', function() {
                    var newValue = $(this).val();
                    cell.data(newValue).draw(false);
                });
            });

            // Konfirmasi sebelum submit form
            $('#dokumenForm').on('submit', function(e) {
                var form = this;
                // Cek minimal 1 baris detail
                if (table.rows().count() === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Detail kosong',
                        text: 'Tambahkan minimal satu Strategic Goal sebelum menyimpan.'
                    });
                    return;
                }

                e.preventDefault(); // tahan dulu

                // bersihkan hidden input lama (kalau ada)
                $(form).find('input[name="goal[]"], input[name="deskripsi[]"]').remove();

                // ambil SEMUA baris (termasuk yang tidak terlihat kalau paging on)
                table.rows().every(function () {
                    var data = this.data();

                    var goalText = $('<div>').html(data[0]).text();      // kolom 0
                    var deskText = $('<div>').html(data[1]).text();      // kolom 1
                    var targText = $('<div>').html(data[2]).text();      // kolom 2

                    // buat hidden input untuk dikirim ke Laravel
                    $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'goal[]')
                        .val(goalText)
                        .appendTo(form);

                    $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'deskripsi[]')
                        .val(deskText)
                        .appendTo(form);

                    $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'target[]')
                        .val(targText)
                        .appendTo(form);
                });

                Swal.fire({
                    title: 'Simpan data?',
                    text: 'Tahun, Dokumen, dan Semua detail akan disimpan ke database.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        e.target.submit(); // submit form beneran
                    }
                });
            });

        });
    </script>
@endsection
