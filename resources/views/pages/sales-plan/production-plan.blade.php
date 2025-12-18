@extends('layouts.master')

@section('title', 'Production Plan | Sales Plan')

@section('title-sub', 'Sales Plan')
@section('pagetitle', 'Production Plan')

@section('content')
    {{-- <a class="btn btn-primary btn-sm" href="{{ route('production.create') }}">Add</a> --}}

    <a class="btn btn-outline-success btn-sm" href="{{ route('production.template') }}">Download Template</a>

    <form action="{{ route('production.import') }}" method="POST" enctype="multipart/form-data" class="d-inline">
        @csrf
        <input type="file" name="file" required>
        <button class="btn btn-success btn-sm" type="submit">Import Excel</button>
    </form>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <button class="btn btn-primary btn-sm mb-3" id="btnAddProduction">
        <i class="bi bi-plus-circle"></i> Add Production
    </button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Production</th>
                <th>Year</th>
                <th>Details</th>
                <th style="width:150px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r->type }}</td>
                    <td>{{ $r->production }}</td>
                    <td>{{ $r->year }}</td>
                    <td>{{ $r->details->count() }}</td>
                    <td>
                        <button class="btn btn-warning btn-sm btnEditProduction" data-id="{{ $r->id }}">
                            Edit
                        </button>
                        <form class="d-inline" method="POST" action="{{ route('production.destroy', $r) }}"
                            onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="modal fade" id="productionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <form id="productionModalForm">
                    @csrf
                    <input type="hidden" id="production_id" value="">
                    <input type="hidden" id="form_method" value="POST">

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
                                <select class="form-select" id="year" name="year" required>
                                    <option value="">Select</option>
                                    @for ($year = 2023; $year <= date('Y') + 1; $year++)
                                        <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
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
                                        <th style="width:90px;">Jan</th>
                                        <th style="width:90px;">Feb</th>
                                        <th style="width:90px;">Mar</th>
                                        <th style="width:90px;">Apr</th>
                                        <th style="width:90px;">May</th>
                                        <th style="width:90px;">Jun</th>
                                        <th style="width:90px;">Jul</th>
                                        <th style="width:90px;">Aug</th>
                                        <th style="width:90px;">Sep</th>
                                        <th style="width:90px;">Oct</th>
                                        <th style="width:90px;">Nov</th>
                                        <th style="width:90px;">Dec</th>
                                        <th style="width:110px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="detailTbody"></tbody>
                            </table>
                        </div>

                        <div class="alert alert-danger d-none mt-3" id="modalError"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="btnSaveProduction">Save</button>
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

            function resetModalForm() {
                $('#productionModalForm')[0].reset();
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
                    `{{ url('production') }}/${id}` :
                    `{{ url('production') }}`;

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
@endpush
