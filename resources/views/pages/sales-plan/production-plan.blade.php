@extends('layouts.master')

@section('title', 'Production Plan | Sales Plan')

@section('title-sub', 'Sales Plan')
@section('pagetitle', 'Production Plan')

@section('content')
    <div id="layout-wrapper">

        {{-- Table KPI Section --}}
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Production Plan</h6>
                        <button class="btn btn-primary btn-sm mb-3" id="btnAddProduction">
                            <i class="bi bi-plus-circle"></i> Add Production
                        </button>
                    </div>
                    <div class="card-body">
                        <div style="overflow-x:auto; width:100%;">
                            <table id="productionTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width:150px;">Action</th>
                                        <th>Type</th>
                                        <th>Production</th>
                                        <th>Year</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- @foreach ($rows as $r)
                                        <tr>
                                            <td>{{ $r->type }}</td>
                                            <td>{{ $r->production }}</td>
                                            <td>{{ $r->year }}</td>
                                            <td>{{ $r->details->count() }}</td>
                                            <td>
                                                <button class="btn btn-warning btn-sm btnEditProduction"
                                                    data-id="{{ $r->id }}">
                                                    Edit
                                                </button>
                                                <form class="d-inline" method="POST"
                                                    action="{{ route('production.destroy', $r) }}"
                                                    onsubmit="return confirm('Delete?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="showDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showDetailTitle">Production Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-2">
                        <div><b>Type:</b> <span id="sd_type">-</span></div>
                        <div><b>Production:</b> <span id="sd_production">-</span></div>
                        <div><b>Year:</b> <span id="sd_year">-</span></div>
                    </div>

                    <div class="table-responsive" style="overflow-x:auto;">
                        <table class="table table-bordered" id="sd_table" style="min-width:1400px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Detail</th>
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
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="sd_tbody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="productionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form id="productionForm">
                    @csrf
                    <input type="hidden" id="production_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productionModalTitle">Add Production</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label">Type</label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="" disabled selected>-- select --</option>
                                    <option value="MAXIMUM PRODUCTION AMOUNT">MAXIMUM PRODUCTION AMOUNT</option>
                                    <option value="PRODUCTION AND SALES BALANCE">PRODUCTION AND SALES BALANCE</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Production</label>
                                <input type="text" name="production" id="production" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Year</label>
                                <input type="number" name="year" id="year" class="form-control">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Details</h6>
                            <button type="button" class="btn btn-primary btn-sm" id="btnAddDetailRow">
                                <i class="bi bi-plus-circle"></i> Add Detail Row
                            </button>
                        </div>

                        <div class="table-responsive" style="overflow-x:auto;">
                            <table class="table table-bordered align-middle" id="detailTable" style="min-width:1400px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:90px;">Action</th>
                                        <th style="min-width:220px;">Detail</th>
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
                                        <th style="width:120px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="detailTbody"></tbody>
                            </table>
                        </div>

                        <div class="alert alert-danger d-none mt-3" id="formError"></div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-success" type="submit" id="btnSaveProduction">Save</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function() {
            const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

            const modalEl = document.getElementById('productionModal');
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let rowIndex = 0;

            function toNumber(v) {
                if (v === null || v === undefined) return 0;
                const s = String(v).trim().replace(',', '.');
                const n = parseFloat(s);
                return isNaN(n) ? 0 : n;
            }

            function calcTotalForRow(tr) {
                let sum = 0;
                months.forEach(m => {
                    sum += toNumber($(tr).find(`[data-month="${m}"]`).val());
                });
                $(tr).find('.row-total').val(sum.toFixed(2));
            }

            function makeRow(prefill = {}) {
                const idx = rowIndex++;
                const detail = (prefill.detail ?? '').replace(/"/g, '&quot;');

                const monthTds = months.map(m => {
                    const val = prefill[m] ?? 0;
                    return `<td>
        <input type="number" step="0.01" class="form-control form-control-sm month-input"
               name="details[${idx}][${m}]"
               data-month="${m}" value="${val}">
      </td>`;
                }).join('');

                const tr = document.createElement('tr');
                tr.innerHTML = `
      <td>
        <button type="button" class="btn btn-danger btn-sm btn-remove-row">
          <i class="bi bi-trash"></i>
        </button>
      </td>
      <td>
        <input type="text" class="form-control form-control-sm"
               name="details[${idx}][detail]" value="${detail}" required>
      </td>
      ${monthTds}
      <td>
        <input type="number" step="0.01" class="form-control form-control-sm row-total"
               name="details[${idx}][total]" value="${prefill.total ?? 0}" readonly>
      </td>
    `;
                $('#detailTbody').append(tr);
                calcTotalForRow(tr);
            }

            function safeResetForm(formSelector) {
                const form = document.querySelector(formSelector);
                if (!form) {
                    console.error('Form not found:', formSelector);
                    return;
                }
                form.reset();
            }


            function resetModalForm() {
                // $('#productionModalForm')[0].reset();
                safeResetForm('#productionModalForm');
                $('#production_id').val('');
                $('#form_method').val('POST');
                $('#productionModalTitle').text('Add Production');
                $('#btnSaveProduction').text('Save');
                $('#modalError').addClass('d-none').html('');
                $('#detailTbody').html('');
                rowIndex = 0;
                makeRow({
                    detail: ''
                }); // minimal 1 row
            }

            // Add
            $('#btnAddProduction').on('click', function() {
                resetModalForm();
                bsModal.show();
            });

            // Add row
            $('#btnAddDetailRow').on('click', function() {
                makeRow({
                    detail: ''
                });
            });

            // Remove row
            $('#detailTable').on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Recalc total when month changes
            $('#detailTable').on('input', '.month-input', function() {
                calcTotalForRow($(this).closest('tr')[0]);
            });

            // Edit
            $('.btnEditProduction').on('click', function() {
                const id = $(this).data('id');
                resetModalForm();

                $.get(`{{ url('production') }}/${id}/json`, function(res) {
                    const d = res.data;

                    $('#production_id').val(d.id);
                    $('#form_method').val('PUT');
                    $('#productionModalTitle').text('Edit Production');
                    $('#btnSaveProduction').text('Update');

                    $('#type').val(d.type);
                    $('#production').val(d.production);
                    $('#year').val(d.year ?? '');

                    $('#detailTbody').html('');
                    rowIndex = 0;

                    if (d.details && d.details.length) {
                        d.details.forEach(item => {
                            makeRow({
                                detail: item.detail,
                                jan: item.jan,
                                feb: item.feb,
                                mar: item.mar,
                                apr: item.apr,
                                may: item.may,
                                jun: item.jun,
                                jul: item.jul,
                                aug: item.aug,
                                sep: item.sep,
                                oct: item.oct,
                                nov: item.nov,
                                dec: item.dec,
                                total: item.total
                            });
                        });
                    } else {
                        makeRow({
                            detail: ''
                        });
                    }

                    bsModal.show();
                });
            });

            // Submit (Create / Update) via AJAX
            $('#productionModalForm').on('submit', function(e) {
                e.preventDefault();

                // minimal 1 detail row
                if ($('#detailTbody tr').length === 0) {
                    $('#modalError').removeClass('d-none').html('Minimal 1 detail row.');
                    return;
                }

                const id = $('#production_id').val();
                const isEdit = !!id;

                const url = isEdit ?
                    `{{ url('production') }}/${id}/update` :
                    `{{ route('production.store') }}`;

                const method = isEdit ? 'PUT' : 'POST';

                $('#btnSaveProduction').prop('disabled', true);
                $('#modalError').addClass('d-none').html('');

                $.ajax({
                    url: url,
                    type: method,
                    data: $(this).serialize(),
                    success: function() {
                        bsModal.hide();
                        location.reload(); // simple: reload list
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan.';
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        $('#modalError').removeClass('d-none').html(msg);
                    },
                    complete: function() {
                        $('#btnSaveProduction').prop('disabled', false);
                    }
                });
            });

        });
    </script>

    <script>
        $(function() {
            const URL_BASE = `{{ url('production') }}`;
            const URL_DT = `{{ route('production.datatable') }}`;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ===== DataTable =====
            const table = $('#productionTable').DataTable({
                processing: true,
                serverSide: false,
                autoWidth: false,
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
          <button class="btn btn-info btn-sm btn-show" data-id="${id}">Detail</button>
          <button class="btn btn-warning btn-sm btn-edit" data-id="${id}">Edit</button>
          <button class="btn btn-danger btn-sm btn-delete" data-id="${id}">Delete</button>
        `
                    },
                    {
                        data: 'type'
                    },
                    {
                        data: 'production'
                    },
                    {
                        data: 'year',
                        defaultContent: '-'
                    },
                    {
                        data: 'details_count',
                        defaultContent: 0
                    }
                ]
            });

            // ===== Modal instances =====
            const editModalEl = document.getElementById('productionModal');
            const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);

            const showModalEl = document.getElementById('showDetailModal');
            const showModal = bootstrap.Modal.getOrCreateInstance(showModalEl);

            // ===== Dynamic Detail Rows =====
            const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
            let rowIndex = 0;

            function toNumber(v) {
                const n = parseFloat(String(v ?? '0').replace(',', '.'));
                return isNaN(n) ? 0 : n;
            }

            function calcTotalForRow(tr) {
                let sum = 0;
                months.forEach(m => sum += toNumber($(tr).find(`[data-month="${m}"]`).val()));
                $(tr).find('.row-total').val(sum.toFixed(2));
            }

            function addRow(prefill = {}) {
                const idx = rowIndex++;
                const detail = (prefill.detail ?? '').replace(/"/g, '&quot;');

                const monthTds = months.map(m => {
                    const val = prefill[m] ?? 0;
                    return `<td>
        <input type="number" step="0.01" class="form-control form-control-sm month-input"
          name="details[${idx}][${m}]" data-month="${m}" value="${val}">
      </td>`;
                }).join('');

                const tr = document.createElement('tr');
                tr.innerHTML = `
      <td>
        <button type="button" class="btn btn-danger btn-sm btn-remove-row">
          <i class="bi bi-trash"></i>
        </button>
      </td>
      <td>
        <input type="text" class="form-control form-control-sm"
          name="details[${idx}][detail]" value="${detail}" required>
      </td>
      ${monthTds}
      <td>
        <input type="number" step="0.01" class="form-control form-control-sm row-total"
          name="details[${idx}][total]" value="${prefill.total ?? 0}" readonly>
      </td>
    `;
                $('#detailTbody').append(tr);
                calcTotalForRow(tr);
            }

            function resetForm() {
                // $('#productionForm')[0].reset();
                safeResetForm('#productionForm');
                $('#production_id').val('');
                $('#productionModalTitle').text('Add Production');
                $('#btnSaveProduction').text('Save');
                $('#formError').addClass('d-none').html('');
                $('#detailTbody').html('');
                rowIndex = 0;
                addRow({
                    detail: ''
                });
            }

            // Add button
            $('#btnAddProduction').on('click', function() {
                resetForm();
                editModal.show();
            });

            // Add detail row
            $('#btnAddDetailRow').on('click', function() {
                addRow({
                    detail: ''
                });
            });

            // Remove row
            $('#detailTable').on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Recalc total
            $('#detailTable').on('input', '.month-input', function() {
                calcTotalForRow($(this).closest('tr')[0]);
            });

            // ===== SHOW DETAIL (modal) =====
            $('#productionTable').on('click', '.btn-show', function() {
                const id = $(this).data('id');

                $.get(`${URL_BASE}/${id}/json`, function(res) {
                    const d = res.data;

                    $('#sd_type').text(d.type ?? '-');
                    $('#sd_production').text(d.production ?? '-');
                    $('#sd_year').text(d.year ?? '-');

                    const tbody = $('#sd_tbody');
                    tbody.html('');

                    (d.details || []).forEach(item => {
                        tbody.append(`
          <tr>
    <td>${item.detail ?? ''}</td>
    <td>${formatIDRNumber(item.jan)}</td>
    <td>${formatIDRNumber(item.feb)}</td>
    <td>${formatIDRNumber(item.mar)}</td>
    <td>${formatIDRNumber(item.apr)}</td>
    <td>${formatIDRNumber(item.may)}</td>
    <td>${formatIDRNumber(item.jun)}</td>
    <td>${formatIDRNumber(item.jul)}</td>
    <td>${formatIDRNumber(item.aug)}</td>
    <td>${formatIDRNumber(item.sep)}</td>
    <td>${formatIDRNumber(item.oct)}</td>
    <td>${formatIDRNumber(item.nov)}</td>
    <td>${formatIDRNumber(item.dec)}</td>
    <td><b>${formatIDRNumber(item.total)}</b></td>
  </tr>
        `);
                    });

                    showModal.show();
                });
            });

            // ===== EDIT (modal) =====
            $('#productionTable').on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                resetForm();

                $.get(`${URL_BASE}/${id}/json`, function(res) {
                    const d = res.data;

                    $('#production_id').val(d.id);
                    $('#productionModalTitle').text('Edit Production');
                    $('#btnSaveProduction').text('Update');

                    $('#type').val(d.type);
                    $('#production').val(d.production);
                    $('#year').val(d.year ?? '');

                    $('#detailTbody').html('');
                    rowIndex = 0;

                    if (d.details && d.details.length) {
                        d.details.forEach(item => {
                            addRow({
                                detail: item.detail,
                                jan: item.jan,
                                feb: item.feb,
                                mar: item.mar,
                                apr: item.apr,
                                may: item.may,
                                jun: item.jun,
                                jul: item.jul,
                                aug: item.aug,
                                sep: item.sep,
                                oct: item.oct,
                                nov: item.nov,
                                dec: item.dec,
                                total: item.total
                            });
                        });
                    } else {
                        addRow({
                            detail: ''
                        });
                    }

                    editModal.show();
                });
            });

            // ===== SUBMIT (AJAX create/update) =====
            $('#productionForm').on('submit', function(e) {
                e.preventDefault();

                if ($('#detailTbody tr').length === 0) {
                    $('#formError').removeClass('d-none').html('Minimal 1 detail row.');
                    return;
                }

                const id = $('#production_id').val();
                const isEdit = !!id;
                const url = isEdit ? `${URL_BASE}/${id}` : `${URL_BASE}`;
                const method = isEdit ? 'PUT' : 'POST';

                $('#btnSaveProduction').prop('disabled', true);
                $('#formError').addClass('d-none').html('');

                $.ajax({
                    url,
                    type: method,
                    data: $(this).serialize(),
                    success: function() {
                        editModal.hide();
                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: isEdit ? 'Updated.' : 'Created.',
                            timer: 1200,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan.';
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        $('#formError').removeClass('d-none').html(msg);
                    },
                    complete: function() {
                        $('#btnSaveProduction').prop('disabled', false);
                    }
                });
            });

            // ===== DELETE (SweetAlert) =====
            $('#productionTable').on('click', '.btn-delete', function() {
                const id = $(this).data('id');

                Swal.fire({
                    icon: 'warning',
                    title: 'Delete?',
                    text: 'Data akan dihapus permanen.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `${URL_BASE}/${id}`,
                        type: 'DELETE',
                        success: function() {
                            table.ajax.reload(null, false);

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: xhr.responseJSON?.message ??
                                    'Gagal menghapus data.'
                            });
                        }
                    });
                });
            });

        });

        function formatIDRNumber(value, decimals = 2) {
            if (value === null || value === undefined || value === '') return '0,00';

            const num = Number(value);
            if (isNaN(num)) return '0,00';

            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(num);
        }
    </script>
@endpush
