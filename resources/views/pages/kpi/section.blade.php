@extends('layouts.master')

@section('title', 'KPI Section | Budget Control')
@section('title-sub', 'KPI Section')
@section('pagetitle', 'Add Data')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        #kpi_section_table thead th {
            font-size: 11px;
            white-space: nowrap;
            text-align: center;
        }
        #kpi_section_table tbody td {
            font-size: 11px;
            vertical-align: middle;
            white-space: nowrap;
        }
        #kpi_section_table input,
        #kpi_section_table textarea,
        #kpi_section_table select {
            width: 100% !important;
            font-size: 11px !important;
        }
    </style>
@endsection

@section('content')
<div id="layout-wrapper">

    {{-- Table KPI Section --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">KPI Section</h6>
                    <button id="btnAddRow" type="button" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Add New KPI Section
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x:auto;">
                        <table id="kpi_section_table" class="display" style="width:100%;">
                            <thead>
                                <tr>
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1; @endphp
                                @foreach ($kpiSections as $kpi)
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
                                        data-unit_id="{{ $kpi->unit_id }}"
                                    >
                                        <td>{{ $i++ }}</td>
                                        <td class="editable" data-field="section_year">{{ $kpi->year }}</td>
                                        <td class="editable" data-field="department_goals">{{ $kpi->department_goals }}</td>
                                        <td class="editable" data-field="department_goals">{{ $kpi->section_id }}</td>
                                        <td class="editable" data-field="section_goals">{{ $kpi->section_goals }}</td>
                                        <td class="editable" data-field="activities">{{ $kpi->activities }}</td>
                                        <td class="editable" data-field="target_section">{{ $kpi->target_section }}</td>
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
                                        <td class="editable" data-field="unit_id">{{ $kpi->unit_id }}</td>
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
    const storeUrl  = "{{ route('kpisection.store', ['kpiDepartment' => 1, 'section' => 1]) }}";
    const inlineUrl = "{{ route('kpisection.inline', ['kpiDepartment' => 1, 'section' => 1, 'kpiSection' => '__ID__']) }}";
    const deleteUrl = "{{ route('kpisection.destroy', ['kpiDepartment' => 1, 'section' => 1, 'kpiSection' => '__ID__']) }}";
    const csrfToken    = "{{ csrf_token() }}";

    function buildInlineUrl(id) {
        if (!id) return null;
        return inlineUrlTpl.replace('__ID__', id);
    }
    function buildDeleteUrl(id) {
        if (!id) return null;
        return deleteUrlTpl.replace('__ID__', id);
    }

    $(document).ready(function () {
        @php
            $years = range(2023, date('Y') + 5);
        @endphp

        const table = $('#kpi_section_table').DataTable({
            scrollX: true,
            scrollCollapse: true,
            autoWidth: false,
            columnDefs: [
                { orderable: false, searchable: false, targets: -1 }
            ]
        });

        // --- ADD NEW ROW ---
        $('#btnAddRow').on('click', function () {
            if ($('#kpi_section_table tbody tr.adding').length > 0) {
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
            const kpideptOptionsHtml = `
                <option value="">Select KPI Department</option>
                @foreach ($kpiDepartment ?? [] as $buff)
                    <option value="{{ $buff->id }}">{{ $buff->department_goals }}</option>
                @endforeach
            `;
            const sectionOptionsHtml = `
                <option value="">Select Section</option>
                @foreach ($section ?? [] as $buff)
                    <option value="{{ $buff->id }}">{{ $buff->name }}</option>
                @endforeach
            `;
            const yesNoSelect = '<select class="form-select form-select-sm new-month"><option value="0">No</option><option value="1">Yes</option></select>';

            const newRowNode = table.row.add([
                no,
                '<select class="form-select new-year" style="width: 100px !important;">' +
                    yearOptionsHtml + '</select>',
                '<select class="form-select new-dept-goals" style="width: 100px !important;">' +
                    kpideptOptionsHtml + '</select>',
                '<select class="form-select new-section" style="width: 100px !important;">' +
                    sectionOptionsHtml + '</select>',
                '<textarea class="form-control form-control-sm new-sec-goals" rows="2"></textarea>',
                '<textarea class="form-control form-control-sm new-activities" rows="2"></textarea>',
                '<input type="text" class="form-control form-control-sm new-target-sec">',
                '<input type="number" class="form-control form-control-sm new-duration">',
                '<input type="date" class="form-control form-control-sm new-start">',
                '<input type="date" class="form-control form-control-sm new-end">',
                yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect,
                yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect, yesNoSelect,
                '<input type="text" class="form-control form-control-sm new-revenue">',
                '<input type="text" class="form-control form-control-sm new-unit">',
                '<textarea class="form-control form-control-sm new-desc" rows="2"></textarea>',
                '<button type="button" class="btn btn-sm btn-success btn-save-new">Save</button> ' +
                '<button type="button" class="btn btn-sm btn-secondary btn-cancel-new">Cancel</button>'
            ]).draw(false).node();

            $(newRowNode).addClass('adding');
        });

        // SAVE new row
        $('#kpi_section_table tbody').on('click', '.btn-save-new', function () {
            const $tr = $(this).closest('tr');
            const row = table.row($tr);
            const no  = row.data()[0];

            const deptGoals = $tr.find('.new-dept-goals').val().trim();
            const section = $tr.find('.new-section').val().trim();
            const secGoals  = $tr.find('.new-sec-goals').val().trim();
            const acts      = $tr.find('.new-activities').val().trim();
            const target    = $tr.find('.new-target-sec').val().trim();
            const duration  = $tr.find('.new-duration').val();
            const start     = $tr.find('.new-start').val();
            const end       = $tr.find('.new-end').val();
            const revenue   = $tr.find('.new-revenue').val().trim();
            const unit      = $tr.find('.new-unit').val().trim();
            const desc      = $tr.find('.new-desc').val().trim();

            if (!secGoals) {
                Swal.fire({ icon: 'error', title: 'Section Goals wajib diisi.' });
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
                    department_goals: deptGoals,
                    section_id: section,
                    section_goals: secGoals,
                    activities: acts,
                    target_section: target,
                    duration_days: duration,
                    schedule_start: start,
                    schedule_end: end,
                    revenue_cost: revenue,
                    unit_id: unit,
                    description: desc
                }, months),
                success: function (res) {
                    row.data([
                        no,
                        year,
                        deptGoals,
                        section,
                        secGoals,
                        acts,
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
                        unit,
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
        $('#kpi_section_table tbody').on('click', '.btn-cancel-new', function () {
            const $tr = $(this).closest('tr');
            table.row($tr).remove().draw(false);
        });

        // INLINE EDIT (double-click)
        $('#kpi_section_table tbody').on('dblclick', 'td.editable', function () {
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
            } else if (['department_goals','section_goals','activities','description'].includes(field)) {
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

                const url = buildInlineUrl(id);
                if (!url) {
                    cell.data(originalDisplay).draw(false);
                    Swal.fire({ icon: 'warning', title: 'Belum bisa disimpan', text: 'Baris belum tersimpan di database.' });
                    return;
                }

                $.ajax({
                    url: url,
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

        // DELETE row
        $('#kpi_section_table tbody').on('click', '.btn-delete', function () {
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

                const url = buildDeleteUrl(id);
                if (!url) {
                    Swal.fire({ icon: 'error', title: 'URL delete tidak valid.' });
                    return;
                }

                $.ajax({
                    url: url,
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
