@extends('layouts.master')

@section('title', 'KPI Department | Budget Control')
@section('title-sub', 'KPI Department')
@section('pagetitle', 'Add Data')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.css">
    <style>
        #kpi_department_table thead th {
            font-size: 11px;
            white-space: nowrap;
            text-align: center;
        }

        #kpi_department_table tbody td {
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

        .month-cell {
            padding: 4px 6px;
            border-radius: 4px;
            display: inline-block;
            width: 40px;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        {{-- Tabel KPI Department --}}
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">KPI Department</h6>
                        <button id="btnAddRow" type="button" class="btn btn-primary  btn-sm">
                            <i class="bi bi-plus-circle"></i> Add New KPI Department
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="overflow-x:auto;">
                            <table id="kpi_department_table" class="display" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>No</th>
                                        <th>Year</th>
                                        <th>KPI Division</th>
                                        <th>Department</th>
                                        <th>KPI Department</th>
                                        <th>Department Activities</th>
                                        <th>Target Department</th>
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

                        </div> <!-- table-responsive -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal KPI Department -->
    <div class="modal fade" id="kpiDepartmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kpiDepartmentModalLabel">Add KPI Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="kpiDepartmentForm">
                        @csrf
                        <input type="hidden" id="kpi_department_id" name="kpi_department_id">

                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Year</label>
                                <select name="year" class="form-select">
                                    <option value="">Select Year</option>
                                    @php $years = range(date('Y') - 1, date('Y') + 5); @endphp
                                    @foreach ($years as $y)
                                        <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">KPI Division</label>
                                <select name="kpi_division_id" class="form-select">
                                    <option value="">Select KPI Division</option>
                                    @foreach ($kpiDivisions as $div)
                                        <option value="{{ $div->id }}">
                                            [{{ $div->year }}] {{ $div->division_goals }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-select">
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">KPI Department</label>
                                <textarea name="department_goals" class="form-control" rows="1" required></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Department Activities</label>
                                <textarea name="department_activities" class="form-control" rows="1" required></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Target Department</label>
                                <input type="text" name="target_department" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Duration (Days)</label>
                                <input type="number" name="duration_days" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Schedule Start</label>
                                <input type="date" name="schedule_start" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Schedule End</label>
                                <input type="date" name="schedule_end" class="form-control" readonly required>
                            </div>

                            {{-- Checkbox bulan, mirip KPI Division --}}
                            <div class="col-md-12">
                                <label class="form-label d-block">Planning Months</label>
                                <div class="d-flex flex-wrap gap-2 month-container">
                                    <div class="row g-2">
                                        @php
                                            $months = [
                                                'jan' => 'January',
                                                'feb' => 'February',
                                                'mar' => 'March',
                                                'apr' => 'April',
                                                'may' => 'May',
                                                'jun' => 'June',
                                                'jul' => 'July',
                                                'aug' => 'August',
                                                'sep' => 'September',
                                                'oct' => 'October',
                                                'nov' => 'November',
                                                'dec' => 'December',
                                            ];
                                        @endphp
                                        @foreach ($months as $key => $label)
                                            <div class="col-md-2 col-sm-4 col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="plan_{{ $key }}"
                                                        name="{{ $key }}">
                                                    <label class="form-check-label"
                                                        for="plan_{{ $key }}"><i class="bi bi-calendar3"></i> {{ $label }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Revenue/Cost</label>
                                <input type="text" name="revenue_cost" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">PIC</label>
                                <input type="text" name="pic" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="1"></textarea>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn-save-kpi-dept">Save</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            const yearSelect = document.querySelector('select[name="year"]');

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
                minDate = new Date(selectedYear, 00, 01);
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
        const datatableUrl = "{{ route('kpidepartment.datatable') }}";
        const storeUrl = "{{ route('kpidepartment.store') }}";
        const showUrlTpl = "{{ url('kpidepartment') }}/:id/show";
        const updateUrlTpl = "{{ url('kpidepartment') }}/:id/update";
        const deleteUrlTpl = "{{ url('kpidepartment') }}/:id/destroy";
        const csrfToken = "{{ csrf_token() }}";

        let mode = 'create'; // 'create' | 'edit'

        $(document).ready(function() {
            const table = $('#kpi_department_table').DataTable({
                scrollX: true,
                scrollCollapse: true,
                autoWidth: true,
                processing: true,
                ajax: {
                    url: datatableUrl,
                    type: 'GET',
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
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
                    {
                        data: 'no',
                        className: 'text-center'
                    }, // No
                    {
                        data: 'year',
                        className: 'text-center'
                    }, // Year
                    {
                        data: 'kpi_division'
                    }, // KPI Division (text)
                    {
                        data: 'department'
                    }, // Department (text)
                    {
                        data: 'department_goals'
                    }, // KPI Department
                    {
                        data: 'department_activities'
                    }, // Activities
                    {
                        data: 'target_department'
                    }, // Target Dept
                    {
                        data: 'duration_days',
                        className: 'text-center'
                    },
                    {
                        data: 'schedule_start',
                        className: 'text-center'
                    },
                    {
                        data: 'schedule_end',
                        className: 'text-center'
                    },
                    {
                        data: 'jan',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "jan");
                        }
                    },
                    {
                        data: 'feb',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "feb");
                        }
                    },
                    {
                        data: 'mar',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "mar");
                        }
                    },
                    {
                        data: 'apr',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "apr");
                        }
                    },
                    {
                        data: 'may',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "may");
                        }
                    },
                    {
                        data: 'jun',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "jun");
                        }
                    },
                    {
                        data: 'jul',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "jul");
                        }
                    },
                    {
                        data: 'aug',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "aug");
                        }
                    },
                    {
                        data: 'sep',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "sep");
                        }
                    },
                    {
                        data: 'oct',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "oct");
                        }
                    },
                    {
                        data: 'nov',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "nov");
                        }
                    },
                    {
                        data: 'dec',
                        className: 'text-center',
                        render: function(data) {
                            return renderMonthCell(data, "&nbsp;", "dec");
                        }
                    },
                    {
                        data: 'revenue_cost'
                    },
                    {
                        data: 'pic'
                    },
                    {
                        data: 'description'
                    },
                ],
                createdRow: function(row, data) {
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
            });

            function renderMonthCell(value, monthText, fieldName) {
                var color = value == 1 ? "background-color: limegreen;" : "background-color: grey;";
                return `<span class="month-cell" style="${color}" data-field="${fieldName}">${monthText}</span>`;
            }

            function refreshTable() {
                table.ajax.reload(null, false);
            }

            function formatToYMD(dateString) {
                if (!dateString) return "";

                const d = new Date(dateString);
                if (isNaN(d.getTime())) return dateString; // kalau gagal parse, biarkan original

                let y = d.getFullYear();
                let m = String(d.getMonth() + 1).padStart(2, '0');
                let da = String(d.getDate()).padStart(2, '0');

                return `${y}-${m}-${da}`;
            }

            // Tombol Add New KPI Department
            $('#btnAddRow').on('click', function() {
                mode = 'create';
                $('#kpiDepartmentModalLabel').text('Add KPI Department');
                $('#btn-save-kpi-dept').text('Save');
                $('#kpi_department_id').val('');
                $('#kpiDepartmentForm')[0].reset();
                $('#kpiDepartmentModal').modal('show');
            });

            // Klik Edit
            $('#kpi_department_table tbody').on('click', '.btn-edit', function() {
                const id = $(this).closest('tr').data('id');
                if (!id) return;

                mode = 'edit';
                $('#kpiDepartmentModalLabel').text('Edit KPI Department');
                $('#btn-save-kpi-dept').text('Update');
                $('#kpi_department_id').val(id);

                const showUrl = showUrlTpl.replace(':id', id);

                $.get(showUrl, function(res) {
                    const d = res.data;
                    const form = $('#kpiDepartmentForm');

                    form.find('[name="year"]').val(d.year);
                    form.find('[name="kpi_division_id"]').val(d.kpi_division_id);
                    form.find('[name="department_id"]').val(d.department_id);

                    form.find('[name="department_goals"]').val(d.department_goals);
                    form.find('[name="department_activities"]').val(d.department_activities);
                    form.find('[name="target_department"]').val(d.target_department);
                    form.find('[name="duration_days"]').val(d.duration_days);
                    form.find('[name="schedule_start"]').val(formatToYMD(d.schedule_start));
                    form.find('[name="schedule_end"]').val(formatToYMD(d.schedule_end));

                    const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep',
                        'oct', 'nov', 'dec'
                    ];
                    months.forEach(function(m) {
                        form.find('#plan_' + m).prop('checked', !!d[m]);
                    });

                    form.find('[name="revenue_cost"]').val(d.revenue_cost);
                    form.find('[name="pic"]').val(d.pic);
                    form.find('[name="description"]').val(d.description);

                    if (window.applyKpiDateFromDb) {
                        window.applyKpiDateFromDb(
                            d.year,
                            formatToYMD(d.schedule_start),
                            d.duration_days,
                            formatToYMD(d.schedule_end)
                        );
                    }

                    $('#kpiDepartmentModal').modal('show');
                });
            });

            // Save (Create / Update)
            $('#btn-save-kpi-dept').on('click', function() {
                const form = $('#kpiDepartmentForm');
                const id = $('#kpi_department_id').val();

                const year = form.find('[name="year"]').val();
                const kpiDivisionId = form.find('[name="kpi_division_id"]').val();
                const departmentId = form.find('[name="department_id"]').val();
                const deptGoals = form.find('[name="department_goals"]').val().trim();

                if (!year || !kpiDivisionId || !departmentId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: 'Year, KPI Division, and Department are required.'
                    });
                    return;
                }

                if (!deptGoals) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: 'KPI Department must not be empty.'
                    });
                    return;
                }

                const payload = {
                    _token: csrfToken,
                    year: year,
                    kpi_division_id: kpiDivisionId,
                    department_id: departmentId,
                    department_goals: deptGoals,
                    department_activities: form.find('[name="department_activities"]').val().trim(),
                    target_department: form.find('[name="target_department"]').val().trim(),
                    duration_days: form.find('[name="duration_days"]').val(),
                    schedule_start: form.find('[name="schedule_start"]').val(),
                    schedule_end: form.find('[name="schedule_end"]').val(),
                    revenue_cost: form.find('[name="revenue_cost"]').val().trim(),
                    pic: form.find('[name="pic"]').val().trim(),
                    description: form.find('[name="description"]').val().trim(),
                };

                const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov',
                    'dec'
                ];
                months.forEach(function(m) {
                    payload[m] = form.find('#plan_' + m).is(':checked') ? 1 : 0;
                });

                let url = storeUrl;
                if (mode === 'edit' && id) {
                    url = updateUrlTpl.replace(':id', id);
                    payload._method = 'PUT';
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json',
                    data: payload,
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: (mode === 'edit' ? 'Updated' : 'Saved'),
                            text: res.message || 'Success',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        $('#kpiDepartmentModal').modal('hide');
                        form[0].reset();
                        refreshTable();
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat menyimpan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: msg
                        });
                    }
                });
            });

            // Delete
            $('#kpi_department_table tbody').on('click', '.btn-delete', function() {
                const id = $(this).closest('tr').data('id');
                if (!id) return;

                Swal.fire({
                    title: 'Delete this KPI Department?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    const url = deleteUrlTpl.replace(':id', id);

                    $.ajax({
                        url: url,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            _token: csrfToken,
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: res.message || 'Deleted successfully',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            refreshTable();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: xhr.responseJSON?.message ||
                                    'Error deleting data'
                            });
                        }
                    });
                });
            });

        });
    </script>
@endsection
