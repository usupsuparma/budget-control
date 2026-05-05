@extends('layouts.master')

@section('title', 'KPI Section | Budget Control')
@section('title-sub', 'KPI Section')
@section('pagetitle', 'Add Data')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/quill/quill.bubble.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/quill/quill.snow.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.css">
<style>
    #kpi_section_table {
        font-size: 0.875rem;
        /* 14px */
    }

    #kpi_section_table thead th,
    #kpi_section_table tbody td {
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
        background-color: #0099d6;
        border-color: #0099d6;
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

    .ql-editor table {
        width: 100%;
        border-collapse: collapse;
    }

    .ql-editor th,
    .ql-editor td {
        border: 1px solid #ddd;
        padding: 6px;
    }

    .ql-editor .table-wrap {
        overflow-x: auto;
    }

    /* Styling untuk Tab Navigation */
    .nav-pills .nav-link {
        background-color: #e9ecef;
        color: #495057;
        border-radius: 6px;
        margin-right: 8px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .nav-pills .nav-link:hover {
        background-color: #0099d6;
        color: white;
    }

    .nav-pills .nav-link.active {
        background-color: #0099d6;
        color: white;
        box-shadow: 0 2px 8px rgba(0, 153, 214, 0.3);
    }

    .nav-pills .nav-link i {
        margin-right: 6px;
    }

    /* FORCE warna tab */
    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        background-color: #0099d6 !important;
        color: #ffffff !important;
    }

    /* Optional: biar lebih solid */
    .nav-pills .nav-link {
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div id="layout-wrapper">

    {{-- Table KPI Section --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <!-- Nav tabs -->
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#demo-tab-5_home" role="tab"
                                aria-selected="true">
                                <span><i class="fas fa-home"></i></span>
                                <span>Company Policy by Section</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#demo-tab-5_profile" role="tab"
                                aria-selected="false" tabindex="-1">
                                <span><i class="far fa-user"></i></span>
                                <span>KPI Section</span>
                            </a>
                        </li>
                    </ul>
                    <div>
                        <button type="button" class="btn btn-workplan btn-sm mb-3 d-none" id="btnAddCpSection"
                            data-bs-toggle="modal" data-bs-target="#cpSectionModal">
                            <i class="bi bi-plus-circle"></i> Add Company Policy By Section
                        </button>

                        <button id="btnAddRow" type="button" class="btn btn-workplan btn-sm">
                            <i class="bi bi-plus-circle"></i> Add New KPI Section
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane" id="demo-tab-5_home" role="tabpanel">
                            <div class="col-xl-12">
                                <div class="card card-h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Company Policy by KPI Section</h6>
                                        <div class="ms-auto d-flex gap-2">

                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-5">
                                            <div class="col-xl-12">
                                                <div class="p-3">
                                                    <div class="table-responsive" style="overflow-x:auto;">
                                                        <table id="cp_kpisection_table" class="display table table-striped table-bordered table-compact"
                                                            style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th width="10%">Action</th>
                                                                    <th width="10%">No</th>
                                                                    <th width="35%">Year</th>
                                                                    <th width="33%">File</th>
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
                        </div>
                        <div class="tab-pane active show" id="demo-tab-5_profile" role="tabpanel">
                            <div class="col-xl-12">
                                <div class="card card-h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <h6 class="mb-0">KPI Section</h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <label for="kpi_section_year_filter" class="mb-0 fw-semibold"></label>
                                                <select id="kpi_section_year_filter" class="form-select form-select-sm">
                                                    @foreach ($kpiYears as $year)
                                                    <option value="{{ $year }}" {{ (int) $year === (int) $currentYear ? 'selected' : '' }}>
                                                        {{ $year }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="ms-auto d-flex gap-2">

                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="kpi-section-config"
                                            data-current-year="{{ $currentYear }}"
                                            data-urls='@json($kpiSectionUrls)'>
                                        </div>
                                        <div class="row g-5">
                                            <div class="col-xl-12">
                                                <div class="p-3">
                                                    <div class="table-responsive" style="overflow-x:auto;">
                                                        <table id="kpi_section_table" class="display table table-striped table-bordered table-compact"
                                                            style="width:100%;">
                                                            <thead>
                                                                <tr>
                                                                    <th>Action</th>
                                                                    <th>No</th>
                                                                    <th>Year</th>
                                                                    <th>KPI Department</th>
                                                                    <th>Section</th>
                                                                    <th>KPI Section</th>
                                                                    <th>Section Activities</th>
                                                                    <th>Target Section</th>
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
                                                                    <th>Unit ID</th>
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
                            </div><!--End col-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cpSectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="cpSectionForm">
                @csrf
                <input type="hidden" id="cp_section_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="cpSectionModalLabel">Add Company Policy By Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Year</label>
                            <select class="form-select" id="cp_tahun" name="tahun" required>
                                <option value="">Select</option>
                                @for ($year = 2023; $year <= date('Y') + 1; $year++)
                                    <option value="{{ $year }}" {{ $year == date('Y') + 1 ? 'selected' : '' }}>
                                    {{ $year }}
                                    </option>
                                    @endfor
                            </select>
                        </div>

                        <!-- HEADER -->
                        <input type="hidden" id="cp_header_input" name="header">
                        <div class="col-12">
                            <label class="form-label">Header</label>
                            <div id="cp_header_editor" style="min-height:120px;"></div>
                        </div>

                        <!-- CONTENTS EN -->
                        <input type="hidden" id="cp_contents_en_input" name="contents_en">
                        <div class="col-md-6">
                            <label class="form-label">Contents (EN)</label>
                            <div id="cp_contents_en_editor" style="min-height:160px;"></div>
                        </div>

                        <!-- CONTENTS ID -->
                        <input type="hidden" id="cp_contents_id_input" name="contents_id">
                        <div class="col-md-6">
                            <label class="form-label">Contents (ID)</label>
                            <div id="cp_contents_id_editor" style="min-height:160px;"></div>
                        </div>

                        <!-- PROLOGUE -->
                        <input type="hidden" id="cp_prologue_en_input" name="prologue_en">
                        <div class="col-md-6">
                            <label class="form-label">Prologue (EN)</label>
                            <div id="cp_prologue_en_editor" style="min-height:140px;"></div>
                        </div>

                        <input type="hidden" id="cp_prologue_id_input" name="prologue_id">
                        <div class="col-md-6">
                            <label class="form-label">Prologue (ID)</label>
                            <div id="cp_prologue_id_editor" style="min-height:140px;"></div>
                        </div>

                        <!-- CLOSING -->
                        <input type="hidden" id="cp_closing_en_input" name="closing_en">
                        <div class="col-md-6">
                            <label class="form-label">Closing (EN)</label>
                            <div id="cp_closing_en_editor" style="min-height:140px;"></div>
                        </div>

                        <input type="hidden" id="cp_closing_id_input" name="closing_id">
                        <div class="col-md-6">
                            <label class="form-label">Closing (ID)</label>
                            <div id="cp_closing_id_editor" style="min-height:140px;"></div>
                        </div>

                        <!-- SIGNATURE -->
                        <input type="hidden" id="cp_signature_input" name="signature">
                        <div class="col-12">
                            <label class="form-label">Signature</label>
                            <div id="cp_signature_editor" style="min-height:140px;"></div>
                        </div>

                        <div class="alert alert-danger d-none" id="cpDeptFormError"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCpSection">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal KPI Section -->
<div class="modal fade" id="modalKPISection" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalKPISectionLabel">Add KPI Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="kpiSectionForm">
                    @csrf
                    <input type="hidden" name="kpi_section_id" id="kpi_section_id">

                    <div class="row g-3">

                        <div class="col-md-2">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
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
                            <label class="form-label">KPI Department <span class="text-danger">*</span></label>
                            <select name="kpi_department_id" class="form-select">
                                <option value="">Select KPI Department</option>
                                @foreach ($kpiDepartments as $kd)
                                <option value="{{ $kd->id }}">
                                    [{{ $kd->year }}]
                                    {{ \Illuminate\Support\Str::limit($kd->department_goals, 80) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select name="section_id" class="form-select">
                                <option value="">Select Section</option>
                                @foreach ($sections as $sec)
                                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">KPI Section <span class="text-danger">*</span></label>
                            <textarea name="section_goals" class="form-control" rows="1" placeholder="Input KPI Section..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Section Activities</label>
                            <textarea name="activities" class="form-control" rows="1" placeholder="Input Activities..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Target Section</label>
                            <input type="text" name="target_section" class="form-control"
                                placeholder="e.g. 100% / 1x / etc">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Duration (Days)</label>
                            <input type="number" name="duration_days" class="form-control" min="1"
                                placeholder="e.g. 10">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Schedule Start</label>
                            <input type="date" name="schedule_start" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Schedule End</label>
                            <input type="date" name="schedule_end" class="form-control">
                        </div>

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
                                            <input class="form-check-input" type="checkbox"
                                                id="plan_{{ $key }}" name="{{ $key }}">
                                            <label class="form-check-label" for="plan_{{ $key }}"><i
                                                    class="bi bi-calendar3"></i> {{ $label }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Revenue/Cost</label>
                            <select name="revenue_cost" class="form-select">
                                <option value="">Select</option>
                                <option value="Revenue">Revenue</option>
                                <option value="Cost">Cost</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Unit ID</label>
                            <input type="text" name="unit_id" class="form-control" placeholder="Unit ID">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="1" placeholder="Description..."></textarea>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-save-kpi-section"
                    data-mode="create">Save</button>
            </div>

        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
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
    const kpiSectionConfigEl = document.getElementById('kpi-section-config');
    const kpiSectionUrls = kpiSectionConfigEl ? $(kpiSectionConfigEl).data('urls') : {};
    const currentSectionYear = kpiSectionConfigEl ? parseInt(kpiSectionConfigEl.dataset.currentYear, 10) : null;
    const yearSectionFilter = $('#kpi_section_year_filter');
    const ajaxUrl = kpiSectionUrls.datatable;
    const storeUrl = kpiSectionUrls.store;
    const showUrlTemplate = kpiSectionUrls.show;
    const updateUrlTemplate = kpiSectionUrls.update;
    const deleteUrlTemplate = kpiSectionUrls.destroy;
    const csrfToken = "{{ csrf_token() }}";


    function buildInlineUrl(id) {
        if (!id) return null;
        return inlineUrlTpl.replace('__ID__', id);
    }

    function buildDeleteUrl(id) {
        if (!id) return null;
        return deleteUrlTpl.replace('__ID__', id);
    }

    $(document).ready(function() {
        function resolveUrl(template, id) {
            return template
                .replace(':id', id)
                .replace('%3Aid', id);
        }

        if (currentSectionYear && (!yearSectionFilter.val() || yearSectionFilter.val() === '')) {
            yearSectionFilter.val(currentSectionYear);
        }

        const table = $('#kpi_section_table').DataTable({
            processing: true,
            ajax: {
                url: ajaxUrl,
                type: 'GET',
                data: function(d) {
                    d.year = yearSectionFilter.val() || currentSectionYear;
                }
            },
            scrollX: true,
            scrollCollapse: true,
            autoWidth: true,
            columns: [{
                    data: null,
                    title: 'Action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(row) {
                        return `<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="${row.id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="${row.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    `;
                    }
                },
                {
                    data: 'no',
                    title: 'No'
                },
                {
                    data: 'year',
                    title: 'Year',
                    className: 'text-center'
                },
                {
                    data: 'kpi_department',
                    title: 'KPI Department'
                },
                {
                    data: 'section',
                    title: 'Section'
                },
                {
                    data: 'section_goals',
                    title: 'KPI Section'
                },
                {
                    data: 'activities',
                    title: 'Section Activities'
                },
                {
                    data: 'target_section',
                    title: 'Target Section'
                },
                {
                    data: 'duration_days',
                    title: 'Duration (Days)'
                },
                {
                    data: 'schedule_start',
                    title: 'Schedule Start'
                },
                {
                    data: 'schedule_end',
                    title: 'Schedule End'
                },

                // months (contoh jan; lanjutkan sampai dec)
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
                    data: 'revenue_cost',
                    title: 'Revenue/Cost'
                },
                {
                    data: 'unit_id',
                    title: 'Unit ID'
                },
                {
                    data: 'description',
                    title: 'Description'
                }
            ]
        });

        function renderMonthCell(value, monthText, fieldName) {
            var color = value == 1 ? "background-color: #0099d6;" : "background-color: #e9ecef;";
            return `<span class="month-cell" style="${color}" data-field="${fieldName}">${monthText}</span>`;
        }

        function refreshTable() {
            table.ajax.reload(null, false); // keep paging
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

        $('#btnAddRow').on('click', function() {
            $('#kpiSectionForm')[0].reset();
            $('#kpi_section_id').val('');
            $('#btn-save-kpi-section').data('mode', 'create').text('Save');
            $('#modalKPISectionLabel').text('Add KPI Section');
            $('#modalKPISection').modal('show');
        });

        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            const url = resolveUrl(showUrlTemplate, id);

            $.get(url, function(res) {
                const d = res.data;
                const form = $('#kpiSectionForm');
                $('#kpi_section_id').val(d.id);

                $('select[name="year"]').val(d.year);
                $('select[name="kpi_department_id"]').val(d.kpi_department_id);
                $('select[name="section_id"]').val(d.section_id);

                $('textarea[name="section_goals"]').val(d.section_goals);
                $('textarea[name="activities"]').val(d.activities);
                $('input[name="target_section"]').val(d.target_section);
                $('input[name="duration_days"]').val(d.duration_days);
                $('input[name="schedule_start"]').val(d.schedule_start);
                $('input[name="schedule_end"]').val(d.schedule_end);

                const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep',
                    'oct', 'nov', 'dec'
                ];
                months.forEach(function(m) {
                    form.find('#plan_' + m).prop('checked', !!d[m]);
                });

                $('select[name="revenue_cost"]').val(d.revenue_cost);
                $('input[name="unit_id"]').val(d.unit_id);
                $('textarea[name="description"]').val(d.description);

                $('#btn-save-kpi-section').data('mode', 'edit');
                $('#modalKPISectionLabel').text('Edit KPI Section');

                if (window.applyKpiDateFromDb) {
                    window.applyKpiDateFromDb(
                        d.year,
                        formatToYMD(d.schedule_start),
                        d.duration_days,
                        formatToYMD(d.schedule_end)
                    );
                }

                $('#modalKPISection').modal('show');
            });
        });

        $('#btn-save-kpi-section').on('click', function() {
            const form = $('#kpiSectionForm');
            const mode = $(this).data('mode') || 'create';
            const id = $('#kpi_section_id').val();

            const year = form.find('[name="year"]').val();
            const kpiDepartmentId = form.find('[name="kpi_department_id"]').val();
            const sectionId = form.find('[name="section_id"]').val();

            const sectionGoals = (form.find('[name="section_goals"]').val() || '').trim();

            // basic validation (mirip KPI Department)
            if (!year || !kpiDepartmentId || !sectionId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: 'Year, KPI Department, and Section are required.'
                });
                return;
            }

            if (!sectionGoals) {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: 'KPI Section must not be empty.'
                });
                return;
            }

            const payload = {
                _token: csrfToken,
                year: year,
                kpi_department_id: kpiDepartmentId,
                section_id: sectionId,

                section_goals: sectionGoals,
                activities: (form.find('[name="activities"]').val() || '').trim(),
                target_section: (form.find('[name="target_section"]').val() || '').trim(),

                duration_days: form.find('[name="duration_days"]').val(),
                schedule_start: form.find('[name="schedule_start"]').val(),
                schedule_end: form.find('[name="schedule_end"]').val(),

                revenue_cost: (form.find('[name="revenue_cost"]').val() || '').trim(),
                unit_id: (form.find('[name="unit_id"]').val() || '').trim(),
                description: (form.find('[name="description"]').val() || '').trim(),
            };

            const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov',
                'dec'
            ];
            months.forEach(function(m) {
                payload[m] = form.find('#plan_' + m).is(':checked') ? 1 : 0;
            });

            let url = storeUrl;
            if (mode === 'edit' && id) {
                url = resolveUrl(updateUrlTemplate, id);
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
                        title: (mode === 'edit') ? 'Updated' : 'Saved',
                        text: res.message || 'Success',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    $('#modalKPISection').modal('hide');
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

        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Delete this data?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then((r) => {
                if (!r.isConfirmed) return;

                $.ajax({
                    url: resolveUrl(deleteUrlTemplate, id),
                    method: 'DELETE',
                    data: {
                        _token: csrfToken
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            timer: 1200,
                            showConfirmButton: false
                        });
                        refreshTable();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: xhr.responseJSON?.message || 'Error'
                        });
                    }
                });
            });
        });

        yearSectionFilter.on('change', function() {
            refreshTable();
        });

    });
</script>

<script>
    $(function() {
        const URL_BASE = `{{ url('kpisectioncompanypolicy') }}`;
        const URL_DT = `{{ route('kpisectioncompanypolicy.datatable') }}`;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ===== DataTable =====
        const cpDeptTable = $('#cp_kpisection_table').DataTable({
            scrollX: true,
            scrollCollapse: true,
            autoWidth: true,
            processing: true,
            ajax: {
                url: URL_DT,
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [{
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: (id) => `
          <button class="btn btn-sm btn-warning cpdept-edit" data-id="${id}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-danger cpdept-delete" data-id="${id}"><i class="bi bi-trash"></i></button>
        `
                },
                {
                    data: null,
                    render: (d, t, r, meta) => meta.row + 1
                },
                {
                    data: 'tahun',
                    className: 'text-center'
                },
                {
                    data: 'file',
                    orderable: false,
                    searchable: false,
                    defaultContent: '-'
                }
            ],
            columnDefs: [{
                targets: [0, 1, 2, 3],
                className: 'text-center'
            }]
        });

        // ===== Quill =====
        const quillOptions = {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{
                        header: [1, 2, 3, false]
                    }],
                    ['bold', 'italic', 'underline'],
                    [{
                        list: 'ordered'
                    }, {
                        list: 'bullet'
                    }],
                    [{
                        align: []
                    }],
                    ['link'],
                    ['clean']
                ],
                clipboard: {
                    matchVisual: false
                }
            }
        };

        let q = {};
        let quillInited = false;
        let pendingEditData = null;

        const modalEl = document.getElementById('cpSectionModal');
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

        function initQuillOnce() {
            if (quillInited) return;

            q.header = new Quill('#cp_header_editor', quillOptions);
            q.contents_en = new Quill('#cp_contents_en_editor', quillOptions);
            q.contents_id = new Quill('#cp_contents_id_editor', quillOptions);
            q.prologue_en = new Quill('#cp_prologue_en_editor', quillOptions);
            q.prologue_id = new Quill('#cp_prologue_id_editor', quillOptions);
            q.closing_en = new Quill('#cp_closing_en_editor', quillOptions);
            q.closing_id = new Quill('#cp_closing_id_editor', quillOptions);
            q.signature = new Quill('#cp_signature_editor', quillOptions);

            q.header.on('text-change', () => $('#cp_header_input').val(q.header.root.innerHTML));
            q.contents_en.on('text-change', () => $('#cp_contents_en_input').val(q.contents_en.root.innerHTML));
            q.contents_id.on('text-change', () => $('#cp_contents_id_input').val(q.contents_id.root.innerHTML));
            q.prologue_en.on('text-change', () => $('#cp_prologue_en_input').val(q.prologue_en.root.innerHTML));
            q.prologue_id.on('text-change', () => $('#cp_prologue_id_input').val(q.prologue_id.root.innerHTML));
            q.closing_en.on('text-change', () => $('#cp_closing_en_input').val(q.closing_en.root.innerHTML));
            q.closing_id.on('text-change', () => $('#cp_closing_id_input').val(q.closing_id.root.innerHTML));
            q.signature.on('text-change', () => $('#cp_signature_input').val(q.signature.root.innerHTML));

            quillInited = true;
        }

        function setQuill(quill, hiddenSel, html) {
            if (!quill) return;
            const v = html ?? '';
            quill.setContents([]);
            quill.clipboard.dangerouslyPasteHTML(v);
            $(hiddenSel).val(v);
        }

        function resetCpDeptFormToDefault() {
            $('#cpSectionForm')[0].reset();
            $('#cp_section_id').val('');
            $('#cpDeptFormError').addClass('d-none').html('');

            // default contoh (silakan ubah)
            const defaultHeader = `<h3>THE COMPANY POLICY</h3><p>{{ date('d F Y') }}</p>`;
            setQuill(q.header, '#cp_header_input', defaultHeader);

            setQuill(q.contents_en, '#cp_contents_en_input', '');
            setQuill(q.contents_id, '#cp_contents_id_input', '');
            setQuill(q.prologue_en, '#cp_prologue_en_input', '');
            setQuill(q.prologue_id, '#cp_prologue_id_input', '');
            setQuill(q.closing_en, '#cp_closing_en_input', '');
            setQuill(q.closing_id, '#cp_closing_id_input', '');
            setQuill(q.signature, '#cp_signature_input', '');
        }

        function resetCpFormtoDefault() {
            setQuill(
                q.header,
                '#cp_header_input',
                `<h3>THE COMPANY POLICY OF FY{{ date('Y') }}</h3>
                    <h3>PT PEROKSIDA INDONESIA PRATAMA</h3>
                    <p>=================================</p>
                    <h3>[FOR THE PREPARATION OF THE COMPANY BUDGET FOR FISCAL YEAR {{ date('Y') }}]</h3>
                    <p>Cikampek, {{ date('d F Y') }}</p>`
            );

            setQuill(q.contents_en, '#cp_contents_en_input', `<h3>REFER TO:</h3><br><br>
                                            <h3>CONSIDERING:</h3><br><br>
                                            <h3>DECISION:</h3><br><br>
                                            <h3>Background:</h3>`);
            setQuill(q.contents_id, '#cp_contents_id_input', `<h3>MENGACU PADA:</h3><br><br>
                                            <h3>MEMPERTIMBANGKAN:</h3><br><br>
                                            <h3>MEMUTUSKAN:</h3><br><br>
                                            <h3>Latar belakang:</h3>`);
            setQuill(q.prologue_en, '#cp_prologue_en_input', '');
            setQuill(q.prologue_id, '#cp_prologue_id_input', '');
            setQuill(q.closing_en, '#cp_closing_en_input', '');
            setQuill(q.closing_id, '#cp_closing_id_input', '');
            q.signature.clipboard.dangerouslyPasteHTML(`
                                            <div style="text-align:center; font-weight:700; font-size:18px; margin-bottom:8px;">
                                                THE BOARD OF DIRECTOR/DEWAN DIREKSI
                                            </div>

                                            <table>
                                                <tr>
                                                    <td>President Director</td>
                                                    <td>Operations and Production Director</td>
                                                    <td>Finance and General Affair Director</td>
                                                </tr>
                                                <tr>
                                                    <td><u><b>Yasuhiko Takaizumi</b></u></td>
                                                    <td><u><b>Daichi Ogawa</b></u></td>
                                                    <td><u><b>Yara Budhi Widowati</b></u></td>
                                                </tr>
                                            </table>
                                            `);

        }

        function fillEdit(d) {
            $('#cp_section_id').val(d.id);
            $('#cp_tahun').val(d.tahun);

            setQuill(q.header, '#cp_header_input', d.header);
            setQuill(q.contents_en, '#cp_contents_en_input', d.contents_en);
            setQuill(q.contents_id, '#cp_contents_id_input', d.contents_id);
            setQuill(q.prologue_en, '#cp_prologue_en_input', d.prologue_en);
            setQuill(q.prologue_id, '#cp_prologue_id_input', d.prologue_id);
            setQuill(q.closing_en, '#cp_closing_en_input', d.closing_en);
            setQuill(q.closing_id, '#cp_closing_id_input', d.closing_id);
            setQuill(q.signature, '#cp_signature_input', d.signature);
        }

        modalEl.addEventListener('shown.bs.modal', function() {
            initQuillOnce();

            if (pendingEditData) {
                fillEdit(pendingEditData);
                pendingEditData = null;
            } else {
                resetCpFormtoDefault();
            }
        });

        // ===== ADD =====
        $('#btnAddCpSection').on('click', function() {
            pendingEditData = null;
            resetCpFormtoDefault();
            bsModal.show();
        });

        // ===== EDIT =====
        $('#cp_kpisection_table').on('click', '.cpdept-edit', function() {
            const id = $(this).data('id');

            $.get(`${URL_BASE}/${id}/show`, function(res) {
                pendingEditData = res.data ?? res;
                bsModal.show();
            });
        });

        // ===== SUBMIT (create/update) =====
        $('#cpSectionForm').on('submit', function(e) {
            e.preventDefault();

            // sync hidden input (jaga-jaga)
            if (quillInited) {
                $('#cp_header_input').val(q.header.root.innerHTML);
                $('#cp_contents_en_input').val(q.contents_en.root.innerHTML);
                $('#cp_contents_id_input').val(q.contents_id.root.innerHTML);
                $('#cp_prologue_en_input').val(q.prologue_en.root.innerHTML);
                $('#cp_prologue_id_input').val(q.prologue_id.root.innerHTML);
                $('#cp_closing_en_input').val(q.closing_en.root.innerHTML);
                $('#cp_closing_id_input').val(q.closing_id.root.innerHTML);
                $('#cp_signature_input').val(q.signature.root.innerHTML);
            }

            const id = $('#cp_section_id').val();
            const isEdit = !!id;

            const url = isEdit ? `${URL_BASE}/${id}/update` : `${URL_BASE}`;
            const method = isEdit ? 'PUT' : 'POST';

            $('#btnSaveCpSection').prop('disabled', true);
            $('#cpDeptFormError').addClass('d-none').html('');

            $.ajax({
                url,
                type: method,
                data: $(this).serialize(),
                success: function() {
                    bsModal.hide();
                    cpDeptTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan.';
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $('#cpDeptFormError').removeClass('d-none').html(msg);
                },
                complete: function() {
                    $('#btnSaveCpSection').prop('disabled', false);
                }
            });
        });

        // ===== DELETE =====
        $('#cp_kpisection_table').on('click', '.cpdept-delete', function() {
            const id = $(this).data('id');
            if (!confirm('Hapus data ini?')) return;

            $.ajax({
                url: `${URL_BASE}/${id}`,
                type: 'DELETE',
                success: function() {
                    cpDeptTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message ?? 'Gagal menghapus data.');
                }
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const btnKpi1 = document.getElementById('btnAddCpSection');
        const btnKpi2 = document.getElementById('btnAddRow');

        // Dengarkan event tab bootstrap
        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                const target = event.target.getAttribute('href');

                if (target === '#demo-tab-5_home') {
                    btnKpi1.classList.remove('d-none');
                    btnKpi2.classList.add('d-none');
                }

                if (target === '#demo-tab-5_profile') {
                    btnKpi2.classList.remove('d-none');
                    btnKpi1.classList.add('d-none');
                }
            });
        });
    });
</script>
@endsection