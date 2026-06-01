<div class="row">
    <div class="col-12">

        <div class="card shadow-sm border">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Price Verificator Settings</h5>

                <div>
                    <button class="btn btn-success btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalAddVerificator">
                        <i class="bi bi-plus-circle me-1"></i> Add Verificator
                    </button>

                    <button class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalAssignCode">
                        <i class="bi bi-tags me-1"></i> Assign Budget Type
                    </button>

                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalAssignUser">
                        <i class="bi bi-person-check me-1"></i> Assign Users Verificator
                    </button>
                </div>
            </div>


            <div class="card-body">
                <table id="userRoleTable" class="table table-bordered table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">ID</th>
                            <th width="15%">Verificator</th>
                            <th width="25%">Description</th>
                            <th width="25%">Users Verificator</th>
                            <th width="25%">Remarks & Incharge Code</th>
                            <th width="5%">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($priceVerificators as $price)
                        <tr>
                            <td>{{ $price->id }}</td>
                            <td class="fw-semibold">{{ $price->verificator }}</td>
                            <td>{{ $price->description }}</td>

                            {{-- Users Verificator --}}
                            <td>
                                @if($price->users->isEmpty())
                                <span class="text-muted small fst-italic">No users assigned</span>
                                @else
                                @foreach($price->users as $u)
                                <span class="badge bg-info text-dark me-1 mb-1 d-inline-flex align-items-center">
                                    {{ $u->jobPosition->job_position_name ?? '-' }}
                                    <button
                                        class="btn btn-sm btn-link text-danger ms-1 p-0 removeUserFromVerificator"
                                        data-id="{{ $u->id }}"
                                        title="Remove user">
                                        <i class="bi bi-x-lg" style="font-size: 10px;"></i>
                                    </button>
                                </span>
                                @endforeach
                                @endif
                            </td>


                            {{-- Code List --}}
                            <td>
                                @if($price->codes->isEmpty())
                                <span class="text-muted small fst-italic">No code assigned</span>
                                @else
                                @foreach($price->codes as $code)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div>
                                        <span class="badge bg-secondary">{{ $code->remarks }}</span>
                                        <strong>{{ $code->inchargecode }}</strong>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm editCode"
                                            data-id="{{ $code->id }}"
                                            data-verificator-id="{{ $code->price_verification_id }}"
                                            data-remarks="{{ $code->remarks }}"
                                            data-inchargecode="{{ $code->inchargecode }}"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm deleteCode"
                                            data-id="{{ $code->id }}"
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            </td>


                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary editVerificator"
                                        data-id="{{ $price->id }}"
                                        data-name="{{ $price->verificator }}"
                                        data-description="{{ $price->description }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <button class="btn btn-outline-danger deleteVerificator"
                                        data-id="{{ $price->id }}"
                                        data-name="{{ $price->verificator }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modalAddVerificator">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Add Verificator</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAddVerificator">
                @csrf
                <input type="hidden" name="verificator_id" id="verificatorId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Verificator</label>
                        <input type="text" name="verificator" id="verificatorName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" id="verificatorDescription" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="btnSaveVerificator">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssignCode">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Assign Budget Type</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAssignCode">
                @csrf
                <input type="hidden" name="code_id" id="codeId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Verificator</label>
                        <select name="price_verification_id" id="selectVerificatorForCode" class="form-select" required>
                            <option value="" disabled selected>Select Verificator</option>
                            @foreach($priceVerificators as $pv)
                            <option value="{{ $pv->id }}">{{ $pv->verificator }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Remarks</label>
                        <select name="remarks" id="selectRemarks" class="form-select" required>
                            <option value="" disabled selected>Select Remarks</option>
                            <option value="Consumption">Consumption</option>
                            <option value="Financial">Financial</option>
                            <option value="Investment">Investment</option>
                            <option value="Procurement">Procurement</option>
                            <option value="Sales">Sales</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Incharge Code <span class="text-muted small">(filtered by remarks)</span></label>
                        <select name="inchargecode[]" id="selectInchargeCode" class="form-select" required multiple>
                            <option value="">Select Incharge Code</option>
                        </select>
                        <small class="text-muted" id="inchargeCodeHint">Select Remarks first, then pick one or more codes</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveAssignCode">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssignUser">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Assign User Verificator</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAssignUser">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Verificator</label>
                        <select name="price_verification_id" class="form-select">
                            <option value="" disabled selected>-- Select Verificator --</option>
                            @foreach($priceVerificators as $pv)
                            <option value="{{ $pv->id }}">{{ $pv->verificator }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Job Position</label>
                        <select name="job_position_id" class="form-select">
                            <option value="" disabled selected>-- Select Division --</option>
                            @foreach($jobPositions as $jp)
                            <option value="{{ $jp->id }}">{{ $jp->job_position_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-warning" id="btnSaveAssignUser">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('scripts')
<script>
    $(function() {

        // Initialize Choices.js for Incharge Code select
        let inchargeCodeChoices;
        let allBudgetCodes = @json($budgetCodes);
        
        function initChoices(filteredCodes = null, allowMultiple = true) {
            if (inchargeCodeChoices) {
                inchargeCodeChoices.destroy();
            }
            
            // Toggle multiple attribute based on mode
            const selectEl = document.getElementById('selectInchargeCode');
            if (allowMultiple) {
                selectEl.setAttribute('multiple', 'multiple');
                selectEl.name = 'inchargecode[]';
                $('#inchargeCodeHint').text('Select Remarks first, then pick one or more codes');
            } else {
                selectEl.removeAttribute('multiple');
                selectEl.name = 'inchargecode';
                $('#inchargeCodeHint').text('Select Remarks first to filter options');
            }
            
            const codesToUse = filteredCodes || allBudgetCodes;
            const choices = codesToUse.map(bc => ({
                value: bc.inchargecode,
                label: `${bc.inchargecode} - ${bc.remarks}`,
                customProperties: {
                    remarks: bc.remarks,
                    name: bc.name
                }
            }));
            
            inchargeCodeChoices = new Choices('#selectInchargeCode', {
                searchEnabled: true,
                searchPlaceholderValue: 'Search incharge code...',
                itemSelectText: 'Click to select',
                removeItemButton: allowMultiple,
                choices: choices,
                shouldSort: false
            });
        }
        
        initChoices();
        
        // Filter Incharge Code when Remarks is selected
        $('#selectRemarks').on('change', function() {
            const selectedRemarks = $(this).val();
            const isEditMode = !!$('#codeId').val();
            
            if (selectedRemarks) {
                // Filter budget codes by remarks
                const filteredCodes = allBudgetCodes.filter(bc => bc.remarks === selectedRemarks);
                initChoices(filteredCodes, !isEditMode);
            } else {
                // Show all codes if no remarks selected
                initChoices(null, !isEditMode);
            }
        });

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        }

        // Reset modal when opening for add new
        $('#modalAddVerificator').on('show.bs.modal', function() {
            $('#verificatorId').val('');
            $('#verificatorName').val('');
            $('#verificatorDescription').val('');
            $('#modalAddVerificator .modal-title').text('Add Verificator');
        });

        $('#modalAssignCode').on('show.bs.modal', function() {
            $('#codeId').val('');
            $('#selectVerificatorForCode').val('');
            $('#selectRemarks').val('');
            
            // Reset to multi-select add mode
            initChoices(null, true);
            
            $('#modalAssignCode .modal-title').text('Assign Budget Type');
        });

        // VERIFICATOR CRUD
        $('#formAddVerificator').on('submit', function(e) {
            e.preventDefault();
            const id = $('#verificatorId').val();
            const url = id ? "{{ url('setting-price-verificator/verificator') }}/" + id : "{{ route('settingPriceVerificator.storeVerificator') }}";
            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    $('#modalAddVerificator').modal('hide');
                    showSuccess(response.message || 'Verificator berhasil disimpan');
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal menyimpan verificator';
                    showError(message);
                }
            });
        });

        $(document).on('click', '.editVerificator', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const description = $(this).data('description') || '';
            
            $('#verificatorId').val(id);
            $('#verificatorName').val(name);
            $('#verificatorDescription').val(description);
            $('#modalAddVerificator .modal-title').text('Edit Verificator');
            $('#modalAddVerificator').modal('show');
        });

        $(document).on('click', '.deleteVerificator', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            Swal.fire({
                title: 'Hapus Verificator?',
                text: `Yakin ingin menghapus verificator "${name}"? Semua code dan user yang terkait juga akan dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('setting-price-verificator/verificator') }}/" + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            showSuccess(response.message || 'Verificator berhasil dihapus');
                        },
                        error: function(xhr) {
                            showError(xhr.responseJSON?.message || 'Gagal menghapus verificator');
                        }
                    });
                }
            });
        });

        // CODE CRUD
        $('#formAssignCode').on('submit', function(e) {
            e.preventDefault();
            const id = $('#codeId').val();
            const url = id ? "{{ url('setting-price-verificator/code') }}/" + id : "{{ route('settingPriceVerificator.assignCode') }}";
            const method = id ? 'PUT' : 'POST';

            Swal.showLoading();

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    $('#modalAssignCode').modal('hide');
                    showSuccess(response.message || 'Budget code berhasil disimpan');
                },
                error: function(xhr) {
                    Swal.close();
                    const message = xhr.responseJSON?.message || 'Gagal menyimpan code';
                    showError(message);
                }
            });
        });

        $(document).on('click', '.editCode', function() {
            const id = $(this).data('id');
            const verificatorId = $(this).data('verificator-id');
            const remarks = $(this).data('remarks');
            const inchargecode = $(this).data('inchargecode');
            
            $('#codeId').val(id);
            $('#selectVerificatorForCode').val(verificatorId);
            $('#selectRemarks').val(remarks);
            
            // Filter codes by remarks and init as single-select (edit mode)
            const filteredCodes = allBudgetCodes.filter(bc => bc.remarks === remarks);
            initChoices(filteredCodes, false);
            
            setTimeout(() => {
                if (inchargeCodeChoices) {
                    inchargeCodeChoices.setChoiceByValue(inchargecode);
                }
            }, 50);
            
            $('#modalAssignCode .modal-title').text('Edit Budget Type');
            $('#modalAssignCode').modal('show');
        });

        $(document).on('click', '.deleteCode', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Hapus Code?',
                text: 'Yakin ingin menghapus code ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('setting-price-verificator/code') }}/" + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            showSuccess(response.message || 'Code berhasil dihapus');
                        },
                        error: function(xhr) {
                            showError(xhr.responseJSON?.message || 'Gagal menghapus code');
                        }
                    });
                }
            });
        });

        // USER CRUD
        $('#formAssignUser').on('submit', function(e) {
            e.preventDefault();

            $.post("{{ route('settingPriceVerificator.assignUser') }}", $(this).serialize())
                .done(function(response) {
                    $('#modalAssignUser').modal('hide');
                    showSuccess(response.message || 'User berhasil di-assign');
                })
                .fail(function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal assign user';
                    showError(message);
                });
        });

        $(document).on('click', '.removeUserFromVerificator', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Hapus User?',
                text: 'Yakin ingin menghapus user dari verificator ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('setting-price-verificator/user') }}/" + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            showSuccess(response.message || 'User berhasil dihapus');
                        },
                        error: function(xhr) {
                            showError(xhr.responseJSON?.message || 'Gagal menghapus user');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush