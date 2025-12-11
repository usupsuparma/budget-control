@extends('layouts.master')

@section('title', 'KPI Division | Budget Control')

@section('title-sub', 'KPI Division')
@section('pagetitle', 'Add Data')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.css">

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

        .month-container .form-check {
            padding: 8px 12px;
            background: white;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .month-container .form-check:hover {
            background: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .month-container .form-check-input {
            width: 20px;
            height: 20px;
            margin-top: 0;
            cursor: pointer;
            border: 2px solid #6c757d;
            margin-left: 0px !important;
        }

        .month-container .form-check-input:checked {
            background-color: #ff6900;
            border-color: #ff6900;
        }

        .month-container .form-check-label {
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            margin-left: 8px;
            user-select: none;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        {{-- form tanpa action; hanya untuk grouping, tidak digunakan submit --}}
        {{-- <form id="kpiForm"> --}}
        {{-- @csrf --}}

        {{-- CARD: Detail KPI Division --}}
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">KPI Division</h6>
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-kpi" data-bs-toggle="modal"
                            data-bs-target="#extraLargeModel">
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
                                                    <th>Action</th>
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
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
        {{-- </form> --}}
    </div>

    <!-- start:: Extra Large Modal Size -->
    <div class="modal fade" id="extraLargeModel" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="extraLargeModelLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="extraLargeModelLabel">Add New KPI Division</h5>
                    <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ri-close-large-line fw-semibold"></i>
                    </button>

                </div>
                <form id="kpiForm">
                    @csrf
                    <input type="hidden" name="kpi_id" id="kpi_id">
                    <div class="modal-body">
                        <div class="row g-3">

                            {{-- Year --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Year</label>
                                <select class="form-select" id="form-select-01" name="tahun" required>
                                    <option value="">Select</option>
                                    @for ($year = 2023; $year <= date('Y') + 1; $year++)
                                        <option value="{{ $year }}"
                                            {{ $year == date('Y') + 1 ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            {{-- Company Policy --}}
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Company Policy</label>
                                <select name="company_policy_id" class="form-select" required>
                                    <option value="">Select Company Policy</option>
                                    @foreach ($companyPolicies as $cp)
                                        <option value="{{ $cp->id }}">
                                            {{ Str::limit(strip_tags($cp->strategic_goal), 60) }}
                                            {{ isset($cp->dokumen->tahun) ? $cp->dokumen->tahun : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Division --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Division</label>
                                <select name="division_id" class="form-select" required>
                                    <option value="">Select Division</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Division Goals --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Division Goals</label>
                                <textarea name="division_goals" class="form-control" rows="1" placeholder="Describe division goals" required></textarea>
                            </div>

                            {{-- Target Division --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Target Division</label>
                                <input type="text" name="target_division" class="form-control"
                                    placeholder="e.g. 95% KPI" required>
                            </div>

                            {{-- Duration (Days) --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Duration (Days)</label>
                                <input type="number" min="1" name="duration_days" class="form-control"
                                    placeholder="e.g. 30" required>
                            </div>

                            {{-- Schedule Start --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Schedule Start</label>
                                <input type="date" name="schedule_start" class="form-control" required>
                            </div>

                            {{-- Schedule End --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Schedule End</label>
                                <input type="date" name="schedule_end" class="form-control" readonly required>
                            </div>

                        </div> {{-- row --}}

                        <div class="row mt-1 g-3">
                            <div class="col-md-12">
                                <div class="month-section-header">
                                    <label class="form-label fw-semibold">Planning Schedule (Select Months)</label>
                                </div>
                                <div class="month-container">
                                    <div class="row g-2">
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_jan"
                                                    name="plan_jan">
                                                <label class="form-check-label" for="plan_jan">
                                                    <i class="bi bi-calendar3"></i> January
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_feb"
                                                    name="plan_feb">
                                                <label class="form-check-label" for="plan_feb">
                                                    <i class="bi bi-calendar3"></i> February
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_mar"
                                                    name="plan_mar">
                                                <label class="form-check-label" for="plan_mar">
                                                    <i class="bi bi-calendar3"></i> March
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_apr"
                                                    name="plan_apr">
                                                <label class="form-check-label" for="plan_apr">
                                                    <i class="bi bi-calendar3"></i> April
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_may"
                                                    name="plan_may">
                                                <label class="form-check-label" for="plan_may">
                                                    <i class="bi bi-calendar3"></i> May
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_jun"
                                                    name="plan_jun">
                                                <label class="form-check-label" for="plan_jun">
                                                    <i class="bi bi-calendar3"></i> June
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_jul"
                                                    name="plan_jul">
                                                <label class="form-check-label" for="plan_jul">
                                                    <i class="bi bi-calendar3"></i> July
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_aug"
                                                    name="plan_aug">
                                                <label class="form-check-label" for="plan_aug">
                                                    <i class="bi bi-calendar3"></i> August
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_sep"
                                                    name="plan_sep">
                                                <label class="form-check-label" for="plan_sep">
                                                    <i class="bi bi-calendar3"></i> September
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_oct"
                                                    name="plan_oct">
                                                <label class="form-check-label" for="plan_oct">
                                                    <i class="bi bi-calendar3"></i> October
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_nov"
                                                    name="plan_nov">
                                                <label class="form-check-label" for="plan_nov">
                                                    <i class="bi bi-calendar3"></i> November
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="plan_dec"
                                                    name="plan_dec">
                                                <label class="form-check-label" for="plan_dec">
                                                    <i class="bi bi-calendar3"></i> December
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-1 g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Revenue/Cost</label>
                                <select class="form-select new-revenue">
                                    <option value="">Select</option>
                                    <option value="Revenue">Revenue</option>
                                    <option value="Cost">Cost</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">PIC</label>
                                <select class="form-select new-pic">
                                    <option value="">Select</option>
                                    <option value="Andi">Andi</option>
                                    <option value="Budi">Budi</option>
                                    <option value="Cici">Cici</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control new-desc" rows="1" placeholder="Description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="btn-save-kpi">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- end:: Extra Large Modal Size -->

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.js"></script>

    @php
        $years = range(2023, date('Y') + 5);
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const durationInput = document.querySelector('input[name="duration_days"]');
            const startInput = document.querySelector('input[name="schedule_start"]');
            const endInput = document.querySelector('input[name="schedule_end"]');

            function updateEndDate() {
                const duration = parseInt(durationInput.value, 10);
                const startVal = startInput.value;

                if (!duration || !startVal) return;

                const startDate = new Date(startVal);
                if (isNaN(startDate.getTime())) return;

                const endDate = new Date(startDate);
                // Jika ingin end = start + duration LANGSUNG, gunakan baris di bawah:
                endDate.setDate(endDate.getDate() + duration);
                // Jika ingin end = start + duration - 1 (durasi inklusif), ubah ke: 
                // endDate.setDate(endDate.getDate() + duration - 1);

                // Format ke yyyy-mm-dd untuk input[type="date"]
                endInput.value = endDate.toISOString().slice(0, 10);
            }

            durationInput.addEventListener('input', updateEndDate);
            startInput.addEventListener('change', updateEndDate);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const durationInput = document.querySelector('input[name="duration_days"]');
            const startInput = document.querySelector('input[name="schedule_start"]');
            const endInput = document.querySelector('input[name="schedule_end"]');
            const yearSelect = document.querySelector('select[name="tahun"]');

            // Checkbox bulan
            const monthMap = {
                0: document.getElementById('plan_jan'),
                1: document.getElementById('plan_feb'),
                2: document.getElementById('plan_mar'),
                3: document.getElementById('plan_apr'),
                4: document.getElementById('plan_may'),
                5: document.getElementById('plan_jun'),
                6: document.getElementById('plan_jul'),
                7: document.getElementById('plan_aug'),
                8: document.getElementById('plan_sep'),
                9: document.getElementById('plan_oct'),
                10: document.getElementById('plan_nov'),
                11: document.getElementById('plan_dec'),
            };

            function resetMonths() {
                Object.values(monthMap).forEach(cb => {
                    if (!cb) return;
                    cb.checked = false;
                    cb.disabled = true;
                });
            }

            function getMonthsInRange(startDate, endDate) {
                const months = new Set();
                const current = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
                const last = new Date(endDate.getFullYear(), endDate.getMonth(), 1);

                while (current <= last) {
                    months.add(current.getMonth());
                    current.setMonth(current.getMonth() + 1);
                }
                return months;
            }

            function updateMonthCheckboxes(startDate, endDate) {
                resetMonths();
                if (!startDate || !endDate) return;

                const activeMonths = getMonthsInRange(startDate, endDate);

                Object.entries(monthMap).forEach(([monthIndex, checkbox]) => {
                    if (!checkbox) return;
                    const idx = parseInt(monthIndex, 10);
                    if (activeMonths.has(idx)) {
                        checkbox.disabled = false;
                        checkbox.checked = true;
                    }
                });
            }

            // Format helper yyyy-mm-dd
            function formatDate(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            // ===============================
            //  BATAS MIN/MAX & DEFAULT START DATE BERDASARKAN TAHUN
            // ===============================
            function updateStartDateByYear() {
                const selectedYear = parseInt(yearSelect.value, 10);
                if (!selectedYear) return;

                const today = new Date();
                const currentYear = today.getFullYear();

                let minDate, maxDate, defaultStart;
                minDate = new Date(selectedYear, 01, 01);
                maxDate = new Date(selectedYear, 11, 31);

                if (selectedYear === currentYear) {
                    defaultStart = today;
                } else {
                    defaultStart = new Date(selectedYear, 0, 1);
                }

                // Set batas date picker
                startInput.min = formatDate(minDate);
                startInput.max = formatDate(maxDate);

                // Kalau start date kosong atau tahunnya beda, set default
                if (!startInput.value) {
                    startInput.value = formatDate(defaultStart);
                } else {
                    const currentStart = new Date(startInput.value);
                    if (isNaN(currentStart.getTime()) ||
                        currentStart.getFullYear() !== selectedYear ||
                        currentStart < minDate || currentStart > maxDate) {
                        startInput.value = formatDate(defaultStart);
                    }
                }
            }

            // ===========================================
            //  UPDATE END DATE DENGAN PEMBATASAN TAHUN
            // ===========================================
            function updateEndDate() {
                resetMonths();

                const duration = parseInt(durationInput.value, 10);
                const startVal = startInput.value;
                const selectedYear = parseInt(yearSelect.value, 10);

                if (!duration || !startVal || !selectedYear) {
                    endInput.value = '';
                    return;
                }

                const startDate = new Date(startVal);
                if (isNaN(startDate.getTime())) {
                    endInput.value = '';
                    return;
                }

                // Hitung end date sementara
                let endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + duration);

                const yearEndDate = new Date(selectedYear, 11, 31); // 31 Desember tahun dipilih

                if (endDate > yearEndDate) {
                    // Paksa end date jadi 31 Desember
                    endDate = yearEndDate;

                    // Hitung ulang durasi hari (selisih dalam hari)
                    const diffTime = endDate - startDate;
                    const newDuration = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    durationInput.value = newDuration;
                }

                // Pastikan start juga tidak keluar dari tahun yang dipilih
                if (startDate.getFullYear() !== selectedYear) {
                    // Sesuaikan start minimal 1 Jan tahun terpilih
                    const fixedStart = new Date(selectedYear, 0, 1);
                    startInput.value = formatDate(fixedStart);
                    // Rekalkulasi berdasarkan start baru
                    return updateEndDate();
                }

                endInput.value = formatDate(endDate);

                // Update bulan sesuai range
                updateMonthCheckboxes(startDate, endDate);
            }

            // ===============================
            // EVENT LISTENERS
            // ===============================
            durationInput.addEventListener('input', function() {
                updateStartDateByYear(); // jaga2 kalau tahun baru dipilih
                updateEndDate();
            });

            startInput.addEventListener('change', function() {
                updateStartDateByYear(); // jaga min/max saat user ubah manual
                updateEndDate();
            });

            yearSelect.addEventListener('change', function() {
                updateStartDateByYear();
                updateEndDate();
            });

            window.applyKpiDateFromDb = function(year, start, duration, end) {
                if (year) {
                    yearSelect.value = year;
                }

                if (start) {
                    startInput.value = start; // YYYY-MM-DD dari DB
                }

                if (duration) {
                    durationInput.value = duration;
                }

                if (end) {
                    endInput.value = end;
                }

                // Sesuaikan min/max berdasarkan tahun,
                // tapi karena start sudah di-set dan masih dalam tahun yg sama,
                // updateStartDateByYear TIDAK akan mereset tanggal (lihat logikanya).
                updateStartDateByYear();

                // Hitung ulang end date (kalau perlu) dan update checkbox bulan
                updateEndDate();
            };

            // Inisialisasi awal
            updateStartDateByYear();
            updateEndDate();
        });
    </script>

    <script>
        $(document).ready(function() {
            var table = $('#kpi_division_table').DataTable({
                scrollX: true,
                scrollCollapse: true,
                autoWidth: true,
                processing: true,
                ajax: {
                    url: "{{ route('kpidivision.datatable') }}",
                    type: "GET"
                },
                columns: [
                    // ACTION
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return `
                                <button type="button" class="btn btn-sm btn-warning btn-edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            `;
                        }
                    },
                    // No (index baris)
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: 'year',            className: 'editable text-center' },
                    { data: 'company_policy' },
                    { data: 'division' },
                    { data: 'division_goals',  className: 'editable' },
                    { data: 'target_division', className: 'editable' },
                    { data: 'duration_days',   className: 'editable text-center' },
                    { data: 'schedule_start',  className: 'editable text-center', },
                    { data: 'schedule_end',    className: 'editable text-center', },

                    // Bulan – kita isi teks, warna bisa tetap pakai fungsi colorMonths()
                    {
                        data: 'jan',
                        className: 'editable text-center',
                        render: function (data, type, row, meta) {
                            return `<span data-field="jan">Jan</span>`;
                        }
                    },
                    {
                        data: 'feb',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="feb">Feb</span>`; }
                    },
                    {
                        data: 'mar',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="mar">Mar</span>`; }
                    },
                    {
                        data: 'apr',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="apr">Apr</span>`; }
                    },
                    {
                        data: 'may',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="may">May</span>`; }
                    },
                    {
                        data: 'jun',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="jun">Jun</span>`; }
                    },
                    {
                        data: 'jul',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="jul">Jul</span>`; }
                    },
                    {
                        data: 'aug',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="aug">Aug</span>`; }
                    },
                    {
                        data: 'sep',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="sep">Sep</span>`; }
                    },
                    {
                        data: 'oct',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="oct">Oct</span>`; }
                    },
                    {
                        data: 'nov',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="nov">Nov</span>`; }
                    },
                    {
                        data: 'dec',
                        className: 'editable text-center',
                        render: function () { return `<span data-field="dec">Dec</span>`; }
                    },

                    { data: 'revenue_cost', className: 'editable text-center' },
                    { data: 'pic',          className: 'editable text-center' },
                    { data: 'description',  className: 'editable' }
                ],
                columnDefs: [
                    {
                        targets: [0, 1, 2, 7, 8, 9, 22, 23],
                        className: 'text-center'
                    }
                ],
                createdRow: function (row, data, dataIndex) {
                    // supaya event edit/delete masih bisa pakai data-id
                    $(row).attr('data-id', data.id);
                },
                language: {
                    paginate: {
                        first: "&laquo;&laquo;",
                        previous: "&laquo;",
                        next: "&raquo;",
                        last: "&raquo;&raquo;"
                    }
                },
                drawCallback: function () {
                    // kalau kamu punya fungsi colorMonths() untuk kasih warna hijau/abu
                    if (typeof colorMonths === 'function') {
                        colorMonths();
                    }
                }
            });

            function refreshTable() {
                table.ajax.reload(null, false); // false = tetap di page sekarang
            }


            function deleteButtonHtml() {
                return '<button role="button" class="btn btn-danger btn-delete"><i class="bi bi-trash"></i></button>';
            }

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
                                    refreshTable();
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
        });
    </script>

    <script>
        $(document).ready(function() {
            const storeUrl = "{{ route('kpidivision.store') }}";
            const csrfToken = "{{ csrf_token() }}";
            const showUrlTemplate = "{{ url('kpidivision') }}/:id/show";
            const updateUrlTemplate = "{{ url('kpidivision') }}/:id/update";

            // MODE ADD
            $('#btn-add-kpi').on('click', function() {
                $('#kpiForm')[0].reset();
                $('#kpi_id').val('');
                $('#extraLargeModelLabel').text('Add New KPI Division');
                $('#btn-save-kpi').text('Save').data('mode', 'create');
            });

            $("#btn-save-kpi").on("click", function() {

                var form = $("#kpiForm");
                var mode = $(this).data('mode') || 'create';
                var id = $('#kpi_id').val();

                // ambil semua value (year, policyId, dst...) — BIARKAN seperti kode kamu yang lama
                var year = form.find("select[name='tahun']").val() || "";
                var policyId = form.find("select[name='company_policy_id']").val() || "";
                var divisionId = form.find("select[name='division_id']").val() || "";
                var divisionGoals = (form.find("textarea[name='division_goals']").val() || "").trim();
                var target = (form.find("input[name='target_division']").val() || "").trim();
                var duration = form.find("input[name='duration_days']").val() || "";
                var start = form.find("input[name='schedule_start']").val() || "";
                var end = form.find("input[name='schedule_end']").val() || "";

                var jan = form.find("#plan_jan").is(':checked') ? 1 : 0;
                var feb = form.find("#plan_feb").is(':checked') ? 1 : 0;
                var mar = form.find("#plan_mar").is(':checked') ? 1 : 0;
                var apr = form.find("#plan_apr").is(':checked') ? 1 : 0;
                var may = form.find("#plan_may").is(':checked') ? 1 : 0;
                var jun = form.find("#plan_jun").is(':checked') ? 1 : 0;
                var jul = form.find("#plan_jul").is(':checked') ? 1 : 0;
                var aug = form.find("#plan_aug").is(':checked') ? 1 : 0;
                var sep = form.find("#plan_sep").is(':checked') ? 1 : 0;
                var oct = form.find("#plan_oct").is(':checked') ? 1 : 0;
                var nov = form.find("#plan_nov").is(':checked') ? 1 : 0;
                var dec = form.find("#plan_dec").is(':checked') ? 1 : 0;

                var revenueCost = form.find(".new-revenue").val() || "";
                var pic = form.find(".new-pic").val() || "";
                var desc = (form.find(".new-desc").val() || "").trim();

                // VALIDASI (biarkan seperti sebelumnya)
                if (!year || !policyId || !divisionId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: "Year, Company Policy, and Division are required to be selected",
                    });
                    return;
                }

                if (!divisionGoals) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: "Division Goals must fill.",
                    });
                    return;
                }

                // tentukan URL & method
                let url = storeUrl;
                let extra = {};

                if (mode === 'edit' && id) {
                    url = updateUrlTemplate.replace(':id', id);
                    extra._method = 'PUT'; // spoofing method untuk Laravel
                }

                $.ajax({
                    url: url,
                    method: "POST", // tetap POST, method asli via _method
                    dataType: "json",
                    data: Object.assign({
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
                        description: desc,
                    }, extra),

                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: (mode === 'edit') ? 'Updated' : 'Saved',
                            text: (mode === 'edit') ?
                                'KPI Division successfully updated.' :
                                'KPI Division successfully saved.',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        $("#extraLargeModel").modal('hide');
                        $("#kpiForm")[0].reset();

                        if (typeof refreshTable === "function") {
                            refreshTable();
                        } else {
                            // atau kalau tidak ada refreshTable, bisa location.reload();
                            // location.reload();
                        }
                    },

                    error: function(xhr) {
                        let msg = "Terjadi kesalahan saat menyimpan.";
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

            // MODE EDIT
            $('#kpi_division_table tbody').on('click', '.btn-edit', function() {
                const $tr = $(this).closest('tr');
                const id = $tr.data('id');

                if (!id) return;

                $('#kpi_id').val(id);
                $('#extraLargeModelLabel').text('Edit KPI Division');
                $('#btn-save-kpi').text('Update').data('mode', 'edit');

                const showUrl = showUrlTemplate.replace(':id', id);

                // ambil data detail dari backend
                $.get(showUrl, function(res) {
                    const data = res.data || res; // sesuaikan dengan format response

                    const form = $('#kpiForm');

                    // basic field
                    form.find("select[name='tahun']").val(data.year);
                    form.find("select[name='company_policy_id']")
                        .val(data.company_policy_detail_id).trigger('change');
                    form.find("select[name='division_id']")
                        .val(data.division_id).trigger('change');

                    form.find("textarea[name='division_goals']").val(data.division_goals);
                    form.find("input[name='target_division']").val(data.target_division);

                    form.find("input[name='duration_days']").val(data.duration_days);
                    form.find("input[name='schedule_start']").val(data.schedule_start);
                    form.find("input[name='schedule_end']").val(data.schedule_end);

                    // bulan (boolean 0/1 di DB)
                    form.find('#plan_jan').prop('checked', !!data.jan);
                    form.find('#plan_feb').prop('checked', !!data.feb);
                    form.find('#plan_mar').prop('checked', !!data.mar);
                    form.find('#plan_apr').prop('checked', !!data.apr);
                    form.find('#plan_may').prop('checked', !!data.may);
                    form.find('#plan_jun').prop('checked', !!data.jun);
                    form.find('#plan_jul').prop('checked', !!data.jul);
                    form.find('#plan_aug').prop('checked', !!data.aug);
                    form.find('#plan_sep').prop('checked', !!data.sep);
                    form.find('#plan_oct').prop('checked', !!data.oct);
                    form.find('#plan_nov').prop('checked', !!data.nov);
                    form.find('#plan_dec').prop('checked', !!data.dec);

                    // revenue / PIC / description
                    form.find(".new-revenue").val(data.revenue_cost);
                    form.find(".new-pic").val(data.pic);
                    form.find(".new-desc").val(data.description);

                    if (window.applyKpiDateFromDb) {
                        window.applyKpiDateFromDb(
                            data.year,
                            data.schedule_start,
                            data.duration_days,
                            data.schedule_end
                        );
                    }

                    // buka modal
                    $('#extraLargeModel').modal('show');
                });
            });

        });
    </script>

@endsection
