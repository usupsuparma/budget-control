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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">KPI Division</h6>
                            <button id="btnAddRow" role="button" type="button" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Add New KPI Division
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">
                                <div class="col-xl-12">
                                    <div class="p-3">
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
                                                        <tr data-id="{{ $kpidiv->id }}">
                                                            <td>{{ $i++ }}</td> {{-- No (tidak editable) --}}

                                                            <td class="editable" data-field="year">
                                                                {{ $kpidiv->year }}
                                                            </td>

                                                            <td>
                                                                {{ $kpidiv->companyPolicy->strategic_goal }}
                                                            </td>

                                                            <td>
                                                                {{ optional($kpidiv->division)->name ?? 'Division #' . $kpidiv->division_id }}
                                                            </td>

                                                            <td class="editable" data-field="division_goals">
                                                                {{ $kpidiv->division_goals }}
                                                            </td>

                                                            <td class="editable" data-field="target_division">
                                                                {{ $kpidiv->target_division }}
                                                            </td>

                                                            <td class="editable" data-field="duration_days">
                                                                {{ $kpidiv->duration_days }}
                                                            </td>

                                                            <td class="editable" data-field="schedule_start">
                                                                {{ date("Y-m-d", strtotime($kpidiv->schedule_start)) }}
                                                            </td>

                                                            <td class="editable" data-field="schedule_end">
                                                                {{ date("Y-m-d", strtotime($kpidiv->schedule_end)) }}
                                                            </td>

                                                            {{-- contoh bulan, ditampilkan Yes/No tapi disimpan boolean di DB --}}
                                                            <td style="{{ $kpidiv->jan ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="jan">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" {{ $kpidiv->jan ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->feb ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="feb">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->feb ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->mar ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="mar">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->mar ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->apr ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="apr">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->apr ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->may ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="may">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->may ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->jun ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="jun">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->jun ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->jul ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="jul">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->jul ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->aug ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="aug">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->aug ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->sep ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="sep">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->sep ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->okt ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="oct">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->oct ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->nov ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="nov">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->nov ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td style="{{ $kpidiv->dec ? 'background-color:limegreen' : '' }} ;"
                                                                class="editable text-center" data-field="dec">
                                                                <input class="form-check-input month-checkbox" type="checkbox"
                                                                            value="" id="flexCheckLarge" {{ $kpidiv->dec ? 'checked' : '' }} disabled>
                                                            </td>

                                                            <td class="editable" data-field="revenue_cost">
                                                                {{ $kpidiv->revenue_cost }}
                                                            </td>

                                                            <td class="editable" data-field="pic">
                                                                {{ $kpidiv->pic }}
                                                            </td>

                                                            <td class="editable" data-field="description">
                                                                {{ $kpidiv->description }}
                                                            </td>

                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger btn-delete">
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
                    '<input type="checkbox" class="form-check-input new-jan">',
                    '<input type="checkbox" class="form-check-input new-feb">',
                    '<input type="checkbox" class="form-check-input new-mar">',
                    '<input type="checkbox" class="form-check-input new-apr">',
                    '<input type="checkbox" class="form-check-input new-may">',
                    '<input type="checkbox" class="form-check-input new-jun">',
                    '<input type="checkbox" class="form-check-input new-jul">',
                    '<input type="checkbox" class="form-check-input new-aug">',
                    '<input type="checkbox" class="form-check-input new-sep">',
                    '<input type="checkbox" class="form-check-input new-oct">',
                    '<input type="checkbox" class="form-check-input new-nov">',
                    '<input type="checkbox" class="form-check-input new-dec">',
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

                var jan = $row.find('.new-jan').is(':checked') ? 1 : 0;
                var janText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (jan ? 'checked' : '') + '>';
                var feb = $row.find('.new-feb').is(':checked') ? 1 : 0;
                var febText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (feb ? 'checked' : '') + '>';
                var mar = $row.find('.new-mar').is(':checked') ? 1 : 0;
                var marText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (mar ? 'checked' : '') + '>';
                var apr = $row.find('.new-apr').is(':checked') ? 1 : 0;
                var aprText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (apr ? 'checked' : '') + '>';
                var may = $row.find('.new-may').is(':checked') ? 1 : 0;
                var mayText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (may ? 'checked' : '') + '>';
                var jun = $row.find('.new-jun').is(':checked') ? 1 : 0;
                var junText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (jun ? 'checked' : '') + '>';
                var jul = $row.find('.new-jul').is(':checked') ? 1 : 0;
                var julText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (jul ? 'checked' : '') + '>';
                var aug = $row.find('.new-aug').is(':checked') ? 1 : 0;
                var augText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (aug ? 'checked' : '') + '>';
                var sep = $row.find('.new-sep').is(':checked') ? 1 : 0;
                var sepText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (sep ? 'checked' : '') + '>';
                var oct = $row.find('.new-oct').is(':checked') ? 1 : 0;
                var octText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (oct ? 'checked' : '') + '>';
                var nov = $row.find('.new-nov').is(':checked') ? 1 : 0;
                var novText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (nov ? 'checked' : '') + '>';
                var dec = $row.find('.new-dec').is(':checked') ? 1 : 0;
                var decText = '<input type="checkbox" class="form-check-input month-checkbox" ' + (dec ? 'checked' : '') + '>';

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
                let id = $tr.data('id'); // mengambil ID dari database

                if (!id) {
                    // data baru yang belum tersimpan
                    row.remove().draw(false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: 'Baris baru dibatalkan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    return;
                }

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

                        $.ajax({
                            url: "{{ url('/kpidivision') }}/" + id,
                            method: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                if (res.status === 'success') {
                                    row.remove().draw(false);

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Terhapus',
                                        text: res.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: res.message,
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message ||
                                        'Gagal menghapus data.',
                                });
                            }
                        });

                    }
                });
            });

            const inlineUrl = "{{ route('kpidivision.inline', ['id' => '__ID__']) }}";

            $('#kpi_division_table tbody').on('dblclick', 'td.editable', function() {
                var cell = table.cell(this);
                var $td = $(this);
                var $tr = $td.closest('tr');

                var id = $tr.data('id');
                var field = $td.data('field');

                if (!id || !field) return;

                // kalau sudah ada input di sel ini, jangan buat lagi
                if ($td.find('input,select,textarea').length > 0) return;

                var originalDisplay = cell.data(); // text yang terlihat sekarang
                var originalText = $('<div>').html(originalDisplay).text().trim();

                // buat input (default text)
                var $input = $('<input type="text" class="form-control form-control-sm">')
                    .val(originalText);

                // untuk field Yes/No bulan, boleh diganti dropdown
                if (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec']
                    .includes(field)) {
                    $input = $('<select class="form-select form-select-sm">' +
                        '<option value="0">No</option>' +
                        '<option value="1">Yes</option>' +
                        '</select>');

                    if (originalText.toLowerCase() === 'yes') {
                        $input.val('1');
                    } else {
                        $input.val('0');
                    }
                }

                // tanggal bisa pakai input type date
                if (['schedule_start', 'schedule_end'].includes(field)) {
                    $input = $('<input type="date" class="form-control form-control-sm">')
                        .val(originalText);
                }

                // isi sel dengan input
                $td.empty().append($input);
                $input.focus().select();

                // fungsi untuk submit perubahan
                function saveInline() {
                    var newValue = $input.val();

                    // kalau tidak berubah, kembalikan saja
                    if (newValue == originalText) {
                        cell.data(originalDisplay).draw(false);
                        return;
                    }

                    $.ajax({
                        url: inlineUrl.replace('__ID__', id),
                        method: 'PATCH',
                        dataType: 'json',
                        data: {
                            _token: csrfToken,
                            field: field,
                            value: newValue
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                // gunakan display_value dari server (misal Yes/No)
                                cell.data(res.display_value).draw(false);

                                // update data-* di tr kalau perlu
                                $tr.attr('data-' + field, res.value);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Tersimpan',
                                    text: 'Kolom berhasil diperbarui.',
                                    timer: 1200,
                                    showConfirmButton: false
                                });
                            } else {
                                cell.data(originalDisplay).draw(false);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message || 'Gagal menyimpan data.'
                                });
                            }
                        },
                        error: function(xhr) {
                            cell.data(originalDisplay).draw(false);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                            });
                        }
                    });
                }

                // Enter = simpan, Esc = batal
                $input.on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        saveInline();
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        cell.data(originalDisplay).draw(false);
                    }
                });

                // blur = simpan juga (boleh kamu matikan kalau mau hanya Enter)
                $input.on('blur', function() {
                    saveInline();
                });
            });

        });
    </script>
@endsection
