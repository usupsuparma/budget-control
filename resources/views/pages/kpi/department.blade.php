@extends('layouts.master')

@section('title', 'KPI Department | Budget Control')
@section('title-sub', 'KPI Department')
@section('pagetitle', 'Add Data')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        #kpi_department_table thead th {
            font-size: 11px;
            white-space: nowrap;
            text-align: center;
        }
        #kpi_department_table tbody td {
            font-size: 11px;
            vertical-align: middle;
            white-space: nowrap;
        }
        #kpi_department_table input,
        #kpi_department_table textarea,
        #kpi_department_table select {
            width: 100% !important;
            font-size: 11px !important;
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1; @endphp
                                @foreach ($kpiDepartments as $kpi)
                                    <tr
                                        data-id="{{ $kpi->id }}"
                                        data-jan="{{ (int) $kpi->jan }}"
                                        data-feb="{{ (int) $kpi->feb }}"
                                        data-mar="{{ (int) $kpi->mar }}"
                                        data-apr="{{ (int) $kpi->apr }}"
                                        data-may="{{ (int) $kpi->may }}"
                                        data-jun="{{ (int) $kpi->jun }}"
                                        data-jul="{{ (int) $kpi->jul }}"
                                        data-aug="{{ (int) $kpi->aug }}"
                                        data-sep="{{ (int) $kpi->sep }}"
                                        data-oct="{{ (int) $kpi->oct }}"
                                        data-nov="{{ (int) $kpi->nov }}"
                                        data-dec="{{ (int) $kpi->dec }}"
                                        data-revenue_cost="{{ $kpi->revenue_cost }}"
                                        data-pic="{{ $kpi->pic }}"
                                    >
                                        <td>{{ $i++ }}</td>
                                        <td class="editable" data-field="year">{{ $kpi->year }}</td>
                                        <td class="editable" data-field="division_goals">{{ $kpi->division_goals }}</td>
                                        <td class="editable" data-field="department_id">{{ $kpi->department_id }}</td>
                                        <td class="editable" data-field="department_goals">{{ $kpi->department_goals }}</td>
                                        <td class="editable" data-field="department_activities">{{ $kpi->department_activities }}</td>
                                        <td class="editable" data-field="target_department">{{ $kpi->target_department }}</td>
                                        <td class="editable" data-field="duration_days">{{ $kpi->duration_days }}</td>
                                        <td class="editable" data-field="schedule_start">{{ date("Y-m-d", strtotime($kpi->schedule_start)) }}</td>
                                        <td class="editable" data-field="schedule_end">{{ date("Y-m-d", strtotime($kpi->schedule_end)) }}</td>

                                        <td style="{{ $kpi->jan ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="jan">{{ $kpi->jan ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->feb ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="feb">{{ $kpi->feb ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->mar ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="mar">{{ $kpi->mar ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->apr ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="apr">{{ $kpi->apr ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->may ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="may">{{ $kpi->may ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->jun ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="jun">{{ $kpi->jun ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->jul ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="jul">{{ $kpi->jul ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->aug ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="aug">{{ $kpi->aug ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->sep ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="sep">{{ $kpi->sep ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->oct ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="oct">{{ $kpi->oct ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->nov ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="nov">{{ $kpi->nov ? 'Yes' : 'No' }}</td>
                                        <td style="{{ $kpi->dec ? 'background-color:limegreen' : '' }} ;" class="editable" data-field="dec">{{ $kpi->dec ? 'Yes' : 'No' }}</td>

                                        <td class="editable" data-field="revenue_cost">{{ $kpi->revenue_cost }}</td>
                                        <td class="editable" data-field="pic">{{ $kpi->pic }}</td>
                                        <td class="editable" data-field="description">{{ $kpi->description }}</td>

                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div> <!-- table-responsive -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const storeUrl  = "{{ route('kpidepartment.store', ['kpiDivision' => 1, 'department' => 1]) }}";
    const inlineUrl = "{{ route('kpidepartment.inline', ['kpiDivision' => 1, 'department' => 1, 'kpiDepartment' => '__ID__']) }}";
    const deleteUrl = "{{ route('kpidepartment.destroy', ['kpiDivision' => 1, 'department' => 1, 'kpiDepartment' => '__ID__']) }}";
    const csrfToken = "{{ csrf_token() }}";

    $(document).ready(function () {
        const table = $('#kpi_department_table').DataTable({
            scrollX: true,
            scrollCollapse: true,
            autoWidth: false,
            columnDefs: [
                { orderable: false, searchable: false, targets: -1 }
            ]
        });
        @php
            $years = range(2023, date('Y') + 5);
        @endphp

        // --- ADD NEW ROW ---
        $('#btnAddRow').on('click', function () {
            if ($('#kpi_department_table tbody tr.adding').length > 0) {
                Swal.fire({ icon: 'error', title: 'Selesaikan dulu baris yang sedang ditambah.' });
                return;
            }

            const no = table.rows().count() + 1;
            const yearOptionsHtml = `
                <option value="">Select Year</option>
                @foreach ($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            `;
            const departmentOptionsHtml = `
                <option value="">Select Department</option>
                @foreach ($department ?? [] as $departmen)
                    <option value="{{ $departmen->id }}">{{ $departmen->name }}</option>
                @endforeach
            `;
            const kpidivisionOptionsHtml = `
                <option value="">Select KPI Division</option>
                @foreach ($kpiDivisions ?? [] as $kpiDivision)
                    <option value="{{ $kpiDivision->id }}">{{ $kpiDivision->division_goals }}</option>
                @endforeach
            `;
            const yesNoSelect = '<select class="form-select form-select-sm new-month"><option value="0">No</option><option value="1">Yes</option></select>';

            const newRowNode = table.row.add([
                no,
                '<select class="form-select new-year" style="width: 100px !important;">' +
                    yearOptionsHtml + '</select>',
                '<select class="form-select department_id" style="width: 100px !important;">' +
                    departmentOptionsHtml + '</select>',
                '<select class="form-select new-division-goals" style="width: 100px !important;">' +
                    kpidivisionOptionsHtml + '</select>',
                '<textarea class="form-control form-control-sm new-dept-goals" rows="2"></textarea>',
                '<textarea class="form-control form-control-sm new-dept-act" rows="2"></textarea>',
                '<input type="text" class="form-control form-control-sm new-target-dept">',
                '<input type="number" class="form-control form-control-sm new-duration">',
                '<input type="date" class="form-control form-control-sm new-start">',
                '<input type="date" class="form-control form-control-sm new-end">',
                yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect,
                yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect,
                '<input type="text" class="form-control form-control-sm new-revenue">',
                '<input type="text" class="form-control form-control-sm new-pic">',
                '<textarea class="form-control form-control-sm new-desc" rows="2"></textarea>',
                '<button type="button" class="btn btn-sm btn-success btn-save-new">Save</button> ' +
                '<button type="button" class="btn btn-sm btn-secondary btn-cancel-new">Cancel</button>'
            ]).draw(false).node();

            $(newRowNode).addClass('adding');
        });

        // SAVE new row
        $('#kpi_department_table tbody').on('click', '.btn-save-new', function () {
            const $tr = $(this).closest('tr');
            const row = table.row($tr);
            const no  = row.data()[0];

            const year  = $tr.find('.new-year').val().trim();
            const deptId = $tr.find('.department_id').val();
            const deptText = $tr.find('.department_id option:selected').text();
            const divGoalsId = $tr.find('.new-division-goals').val();
            const divGoalsText = $tr.find('.new-division-goals option:selected').text();
            const deptGoals = $tr.find('.new-dept-goals').val().trim();
            const deptAct   = $tr.find('.new-dept-act').val().trim();
            const target    = $tr.find('.new-target-dept').val().trim();
            const duration  = $tr.find('.new-duration').val();
            const start     = $tr.find('.new-start').val();
            const end       = $tr.find('.new-end').val();
            const revenue   = $tr.find('.new-revenue').val().trim();
            const pic       = $tr.find('.new-pic').val().trim();
            const desc      = $tr.find('.new-desc').val().trim();

            if (!deptGoals) {
                Swal.fire({ icon: 'error', title: 'Department Goals wajib diisi.' });
                return;
            }

            const monthKeys = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
            const months = {};
            let startIndex = 8;
            monthKeys.forEach(function (m, idx) {
                months[m] = $tr.find('td').eq(startIndex + idx).find('select').val();
            });

            $.ajax({
                url: storeUrl,
                method: 'POST',
                dataType: 'json',
                data: Object.assign({
                    _token: csrfToken,
                    year: year,
                    depatment_id: deptId,
                    division_goals: divGoalsId,
                    department_goals: deptGoals,
                    department_activities: deptAct,
                    target_department: target,
                    duration_days: duration,
                    schedule_start: start,
                    schedule_end: end,
                    revenue_cost: revenue,
                    pic: pic,
                    description: desc
                }, months),
                success: function (res) {
                    row.data([
                        no,
                        divGoalsText,
                        deptGoals,
                        deptAct,
                        target,
                        duration,
                        start,
                        end,
                        months['jan'] === '1' ? 'Yes' : 'No',
                        months['feb'] === '1' ? 'Yes' : 'No',
                        months['mar'] === '1' ? 'Yes' : 'No',
                        months['apr'] === '1' ? 'Yes' : 'No',
                        months['may'] === '1' ? 'Yes' : 'No',
                        months['jun'] === '1' ? 'Yes' : 'No',
                        months['jul'] === '1' ? 'Yes' : 'No',
                        months['aug'] === '1' ? 'Yes' : 'No',
                        months['sep'] === '1' ? 'Yes' : 'No',
                        months['oct'] === '1' ? 'Yes' : 'No',
                        months['nov'] === '1' ? 'Yes' : 'No',
                        months['dec'] === '1' ? 'Yes' : 'No',
                        revenue,
                        pic,
                        desc,
                        '<button type="button" class="btn btn-sm btn-danger btn-delete">' +
                            '<i class="bi bi-trash"></i> Delete</button>'
                    ]).draw(false);

                    $(row.node())
                        .removeClass('adding')
                        .attr('data-id', res.id);

                    Swal.fire({ icon: 'success', title: 'Saved', timer: 1500, showConfirmButton: false });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Error' });
                }
            });
        });

        // Cancel new row
        $('#kpi_department_table tbody').on('click', '.btn-cancel-new', function () {
            const $tr = $(this).closest('tr');
            table.row($tr).remove().draw(false);
        });

        // --- INLINE EDIT (double-click) ---
        $('#kpi_department_table tbody').on('dblclick', 'td.editable', function () {
            const cell = table.cell(this);
            const $td  = $(this);
            const $tr  = $td.closest('tr');
            const id   = $tr.data('id');
            const field = $td.data('field');

            if (!id || !field) return;
            if ($td.find('input,textarea,select').length > 0) return;

            const originalDisplay = cell.data();
            const originalText    = $('<div>').html(originalDisplay).text().trim();

            let $input;

            if (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'].includes(field)) {
                $input = $('<select class="form-select form-select-sm">' +
                              '<option value="0">No</option>' +
                              '<option value="1">Yes</option>' +
                           '</select>');
                $input.val(originalText.toLowerCase() === 'yes' ? '1' : '0');
            } else if (['schedule_start','schedule_end'].includes(field)) {
                $input = $('<input type="date" class="form-control form-control-sm">').val(originalText);
            } else if (field === 'duration_days') {
                $input = $('<input type="number" class="form-control form-control-sm">').val(originalText);
            } else if (['division_goals','department_goals','department_activities','description'].includes(field)) {
                $input = $('<textarea class="form-control form-control-sm" rows="2"></textarea>').val(originalText);
            } else {
                $input = $('<input type="text" class="form-control form-control-sm">').val(originalText);
            }

            $td.empty().append($input);
            $input.focus().select();

            function saveInline() {
                const newVal = $input.val();
                if (newVal == originalText) {
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
                        value: newVal
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            cell.data(res.display_value).draw(false);
                            $tr.attr('data-' + field, res.value);
                        } else {
                            cell.data(originalDisplay).draw(false);
                            Swal.fire({ icon: 'error', title: 'Failed', text: res.message || 'Error' });
                        }
                    },
                    error: function (xhr) {
                        cell.data(originalDisplay).draw(false);
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Error' });
                    }
                });
            }

            $input.on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveInline();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cell.data(originalDisplay).draw(false);
                }
            });

            $input.on('blur', function () {
                saveInline();
            });
        });

        // --- DELETE row ---
        $('#kpi_department_table tbody').on('click', '.btn-delete', function () {
            const $tr = $(this).closest('tr');
            const row = table.row($tr);
            const id  = $tr.data('id');

            if (!id) {
                row.remove().draw(false);
                return;
            }

            Swal.fire({
                title: 'Yakin hapus?',
                text: 'Data tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: deleteUrl.replace('__ID__', id),
                    method: 'DELETE',
                    dataType: 'json',
                    data: { _token: csrfToken },
                    success: function (res) {
                        if (res.status === 'success') {
                            row.remove().draw(false);
                            Swal.fire({ icon: 'success', title: 'Terhapus', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Error' });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Error' });
                    }
                });
            });
        });

    });
</script>
@endsection
