@extends('layouts.master')

@section('title', 'KPI Division | Budget Control')

@section('title-sub', 'KPI Division')
@section('pagetitle', 'Add Data')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <style>
        #kpi_division_table thead th {
            font-size: 11px;
            white-space: nowrap;
            text-align: center;
        }

        #kpi_division_table tbody td {
            font-size: 11px;
            vertical-align: middle;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        {{-- form tanpa action; hanya untuk grouping, tidak digunakan submit --}}
        <form id="kpiForm">
            @csrf

            {{-- CARD: Detail KPI Division --}}
            <div class="row">
                <div class="col-xl-12">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <div class="row g-5">
                                <div class="col-xl-12">
                                    <div class="p-3">
                                        <div class="mb-3">
                                            <button id="btnAddRow" role="button" type="button" class="btn btn-primary">
                                                <i class="bi bi-plus-circle"></i> Add New KPI Division
                                            </button>
                                        </div>

                                        <h6 class="fw-bold mb-2">KPI Division</h6>

                                        <div class="table-responsive" style="overflow-x: auto;">
                                            <table id="kpi_division_table" class="display" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Year</th>
                                                        <th>Company Policy</th>
                                                        <th>Division</th>
                                                        <th>Division Goals</th>
                                                        <th>Target Division</th>
                                                        <th>Duration (Days)</th>
                                                        <th>Schedule Start</th>
                                                        <th>Schedule End</th>
                                                        <th>Jan</th>
                                                        <th>Feb</th>
                                                        <th>Mar</th>
                                                        <th>Apr</th>
                                                        <th>May</th>
                                                        <th>Jun</th>
                                                        <th>Jul</th>
                                                        <th>Aug</th>
                                                        <th>Sep</th>
                                                        <th>Oct</th>
                                                        <th>Nov</th>
                                                        <th>Dec</th>
                                                        <th>Revenue/Cost</th>
                                                        <th>PIC</th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $i = 1; @endphp
                                                    @foreach ($kpidivisions as $kpidiv)
                                                        <tr data-id="{{ $kpidiv->id }}" data-year="{{ $kpidiv->year }}"
                                                            data-company_policy_id="{{ $kpidiv->company_policy_id }}"
                                                            data-division_id="{{ $kpidiv->division_id }}"
                                                            data-jan="{{ (int) $kpidiv->jan }}"
                                                            data-feb="{{ (int) $kpidiv->feb }}"
                                                            data-mar="{{ (int) $kpidiv->mar }}"
                                                            data-apr="{{ (int) $kpidiv->apr }}"
                                                            data-may="{{ (int) $kpidiv->may }}"
                                                            data-jun="{{ (int) $kpidiv->jun }}"
                                                            data-jul="{{ (int) $kpidiv->jul }}"
                                                            data-aug="{{ (int) $kpidiv->aug }}"
                                                            data-sep="{{ (int) $kpidiv->sep }}"
                                                            data-oct="{{ (int) $kpidiv->oct }}"
                                                            data-nov="{{ (int) $kpidiv->nov }}"
                                                            data-dec="{{ (int) $kpidiv->dec }}"
                                                            data-revenue_cost="{{ $kpidiv->revenue_cost }}"
                                                            data-pic="{{ $kpidiv->pic }}">
                                                            {{-- 0. No --}}
                                                            <td>{{ $i++ }}</td>

                                                            {{-- 1. Year --}}
                                                            <td>{{ $kpidiv->year }}</td>

                                                            {{-- 2. Company Policy (ambil dari relasi) --}}
                                                            <td>{{ $kpidiv->companyPolicy->strategic_goal }}
                                                            </td>

                                                            {{-- 3. Division --}}
                                                            <td>{{ optional($kpidiv->division)->name ?? 'Division #' . $kpidiv->division_id }}
                                                            </td>

                                                            {{-- 4. Division Goals --}}
                                                            <td>{{ $kpidiv->division_goals }}</td>

                                                            {{-- 5. Target Division --}}
                                                            <td>{{ $kpidiv->target_division }}</td>

                                                            {{-- 6. Duration (Days) --}}
                                                            <td>{{ $kpidiv->duration_days }}</td>

                                                            {{-- 7–8. Schedule --}}
                                                            <td>{{ date("Y-m-d", strtotime($kpidiv->schedule_start)) }}</td>
                                                            <td>{{ date("Y-m-d", strtotime($kpidiv->schedule_end)) }}</td>

                                                            {{-- 9–20. Jan–Dec (Yes/No) --}}
                                                            <td>{{ $kpidiv->jan ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->feb ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->mar ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->apr ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->may ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->jun ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->jul ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->aug ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->sep ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->oct ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->nov ? 'Yes' : 'No' }}</td>
                                                            <td>{{ $kpidiv->dec ? 'Yes' : 'No' }}</td>

                                                            {{-- 21. Revenue/Cost --}}
                                                            <td>{{ $kpidiv->revenue_cost }}</td>

                                                            {{-- 22. PIC --}}
                                                            <td>{{ $kpidiv->pic }}</td>

                                                            {{-- 23. Description --}}
                                                            <td>{{ $kpidiv->description }}</td>

                                                            {{-- 24. Action --}}
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-danger btn-delete">
                                                                    <i class="bi bi-trash"></i> Delete
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>

                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--End col-->
            </div><!--End row-->

            {{-- tidak ada tombol submit semua --}}
        </form>
    </div>

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
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    @php
        $years = range(2023, date('Y') + 5);
    @endphp

    <script>
        const yearOptionsHtml = `
            <option value="">Select Year</option>
            @foreach ($years as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        `;

        const companyPolicyOptionsHtml = `
            <option value="">Select Policy</option>
            @foreach ($companyPolicies ?? [] as $policy)
                <option value="{{ $policy->id }}">
                    {{ $policy->name ?? $policy->strategic_goal }}
                </option>
            @endforeach
        `;

        const divisionOptionsHtml = `
            <option value="">Select Division</option>
            @foreach ($divisions ?? [] as $division)
                <option value="{{ $division->id }}">{{ $division->name }}</option>
            @endforeach
        `;

        const yesNoOptionsHtml = `
            <option value="0">No</option>
            <option value="1">Yes</option>
        `;

        const revenueCostOptionsHtml = `
            <option value="">Select</option>
            <option value="Revenue">Revenue</option>
            <option value="Cost">Cost</option>
        `;

        const picOptionsHtml = `
            <option value="">Select</option>
            <option value="Andi">Andi</option>
            <option value="Budi">Budi</option>
            <option value="Cici">Cici</option>
        `;
    </script>

    <script>
        $(document).ready(function() {
            const storeUrl = "{{ route('kpidivision.store') }}";
            const csrfToken = "{{ csrf_token() }}";

            var table = $('#kpi_division_table').DataTable({
                scrollX: true,
                scrollCollapse: true,
                autoWidth: false,
                columnDefs: [{
                    orderable: false,
                    searchable: false,
                    targets: -1
                }],
                language: {
                    paginate: {
                        first: "&laquo;&laquo;",
                        previous: "&laquo;",
                        next: "&raquo;",
                        last: "&raquo;&raquo;"
                    }
                }
            });

            function deleteButtonHtml() {
                return '<button role="button" class="btn btn-danger btn-delete"><i class="bi bi-trash"></i> Delete</button>';
            }

            // Tambah baris KPI (mode input)
            $('#btnAddRow').on('click', function() {
                if ($('#kpi_division_table tbody tr.adding').length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: "Selesaikan dulu baris yang sedang ditambah.",
                    });
                    return;
                }

                var no = table.rows().count() + 1;

                var newRowNode = table.row.add([
                    no,
                    '<select class="form-select new-year" style="width: 100px !important;">' +
                    yearOptionsHtml + '</select>',
                    '<select class="form-select new-policy" style="width: 150px !important;">' +
                    companyPolicyOptionsHtml + '</select>',
                    '<select class="form-select new-division" style="width: 150px !important;">' +
                    divisionOptionsHtml + '</select>',
                    '<textarea class="form-control new-division-goals" rows="2" placeholder="Division Goals" style="width: 150px !important;"></textarea>',
                    '<input type="text" class="form-control new-target" placeholder="Target Division" style="width: 150px !important;">',
                    '<input type="number" class="form-control new-duration" min="0" placeholder="Days">',
                    '<input type="date" class="form-control new-start">',
                    '<input type="date" class="form-control new-end">',
                    '<select class="form-select new-jan" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-feb" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-mar" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-apr" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-may" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-jun" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-jul" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-aug" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-sep" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-oct" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-nov" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-dec" style="width: 80px !important;">' +
                    yesNoOptionsHtml + '</select>',
                    '<select class="form-select new-revenue" style="width: 150px !important;">' +
                    revenueCostOptionsHtml + '</select>',
                    '<select class="form-select new-pic" style="width: 150px !important;">' +
                    picOptionsHtml + '</select>',
                    '<textarea class="form-control new-desc" rows="2" placeholder="Description" style="width: 150px !important;"></textarea>',
                    '<button role="button" type="button" class="btn btn-info btn-save-new"><i class="bi bi-floppy"></i> Save</button>' +
                    ' <button role="button" type="button" class="btn btn-warning btn-cancel-new"><i class="bi bi-x-square"></i> Cancel</button>'
                ]).draw(false).node();

                $(newRowNode).addClass('adding');
            });

            // SAVE baris -> AJAX ke backend
            $('#kpi_division_table tbody').on('click', '.btn-save-new', function() {
                var $row = $(this).closest('tr');
                var row = table.row($row);

                var no = $row.find('td').eq(0).text().trim();

                var year = $row.find('.new-year').val();
                var yearText = $row.find('.new-year option:selected').text();

                var policyId = $row.find('.new-policy').val();
                var policyText = $row.find('.new-policy option:selected').text();

                var divisionId = $row.find('.new-division').val();
                var divisionText = $row.find('.new-division option:selected').text();

                var divisionGoals = $row.find('.new-division-goals').val().trim();
                var target = $row.find('.new-target').val().trim();
                var duration = $row.find('.new-duration').val();
                var start = $row.find('.new-start').val();
                var end = $row.find('.new-end').val();

                var jan = $row.find('.new-jan').val();
                var janText = $row.find('.new-jan option:selected').text();
                var feb = $row.find('.new-feb').val();
                var febText = $row.find('.new-feb option:selected').text();
                var mar = $row.find('.new-mar').val();
                var marText = $row.find('.new-mar option:selected').text();
                var apr = $row.find('.new-apr').val();
                var aprText = $row.find('.new-apr option:selected').text();
                var may = $row.find('.new-may').val();
                var mayText = $row.find('.new-may option:selected').text();
                var jun = $row.find('.new-jun').val();
                var junText = $row.find('.new-jun option:selected').text();
                var jul = $row.find('.new-jul').val();
                var julText = $row.find('.new-jul option:selected').text();
                var aug = $row.find('.new-aug').val();
                var augText = $row.find('.new-aug option:selected').text();
                var sep = $row.find('.new-sep').val();
                var sepText = $row.find('.new-sep option:selected').text();
                var oct = $row.find('.new-oct').val();
                var octText = $row.find('.new-oct option:selected').text();
                var nov = $row.find('.new-nov').val();
                var novText = $row.find('.new-nov option:selected').text();
                var dec = $row.find('.new-dec').val();
                var decText = $row.find('.new-dec option:selected').text();

                var revenueCost = $row.find('.new-revenue').val();
                var revenueCostText = $row.find('.new-revenue option:selected').text();

                var pic = $row.find('.new-pic').val();
                var picText = $row.find('.new-pic option:selected').text();

                var desc = $row.find('.new-desc').val().trim();

                if (!year || !policyId || !divisionId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: "Year, Company Policy, dan Division wajib dipilih.",
                    });
                    return;
                }

                if (!divisionGoals) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: "Division Goals harus diisi.",
                    });
                    return;
                }

                // Kirim ke server (per baris)
                $.ajax({
                    url: storeUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        _token: csrfToken,
                        year: year,
                        company_policy_detail_id: policyId,
                        division_id: divisionId,
                        division_goals: divisionGoals,
                        target_division: target,
                        duration_days: duration,
                        schedule_start: start,
                        schedule_end: end,
                        jan: jan,
                        feb: feb,
                        mar: mar,
                        apr: apr,
                        may: may,
                        jun: jun,
                        jul: jul,
                        aug: aug,
                        sep: sep,
                        oct: oct,
                        nov: nov,
                        dec: dec,
                        revenue_cost: revenueCost,
                        pic: pic,
                        description: desc
                    },
                    success: function(response) {
                        // ubah tampilan baris jadi text biasa
                        row.data([
                            no,
                            yearText,
                            policyText,
                            divisionText,
                            divisionGoals,
                            target,
                            duration,
                            start,
                            end,
                            janText,
                            febText,
                            marText,
                            aprText,
                            mayText,
                            junText,
                            julText,
                            augText,
                            sepText,
                            octText,
                            novText,
                            decText,
                            revenueCostText,
                            picText,
                            desc,
                            deleteButtonHtml()
                        ]).draw(false);

                        var node = row.node();
                        $(node).removeClass('adding').addClass('saved-row');
                        $(node).attr('data-id', response
                        .id); // simpan id untuk update/delete nanti

                        Swal.fire({
                            icon: 'success',
                            title: 'Saved',
                            text: 'KPI Division berhasil disimpan.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat menyimpan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: msg,
                        });
                    }
                });
            });

            // Cancel baris yang belum disimpan
            $('#kpi_division_table tbody').on('click', '.btn-cancel-new', function() {
                var $row = $(this).closest('tr');
                table.row($row).remove().draw(false);
            });

            // Delete row di front-end (kalau mau sekalian delete di DB, bisa tambah AJAX lagi)
            $('#kpi_division_table tbody').on('click', '.btn-delete', function() {
                let $tr = $(this).closest('tr');
                let row = table.row($tr);
                let id = $tr.data('id'); // ID dari DB

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
                        // kalau mau, di sini panggil AJAX DELETE ke server pakai id
                        row.remove().draw(false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: 'Baris data berhasil dihapus (front-end).',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });

        });
    </script>
@endsection
