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
                            <th width="25%">Remarks & Code</th>
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


                            {{-- Code Group --}}
                            <td>
                                @if($price->codes->isEmpty())
                                <span class="text-muted small fst-italic">No code assigned</span>
                                @else
                                @foreach($price->codes->groupBy('remarks') as $remark => $items)
                                <b>{{ $remark ?? '-' }}</b><br>
                                {{ $items->pluck('inchargecode')->implode(', ') }}
                                <hr class="my-1">
                                @endforeach
                                @endif
                            </td>


                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary editVerificator"
                                        data-id="{{ $price->id }}"
                                        data-name="{{ $price->verificator }}">
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
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Verificator</label>
                        <input type="text" name="verificator" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success" id="btnSaveVerificator">Save</button>
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
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Verificator</label>
                        <select name="price_verification_id" class="form-select">
                            <option value="" disabled selected>Select Verificator</option>
                            @foreach($priceVerificators as $pv)
                            <option value="{{ $pv->id }}">{{ $pv->verificator }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Remarks</label>
                        <select name="remarks" class="form-select">
                            <option value="" disabled selected>Select Remarks</option>
                            <option value="Consumption">Consumption</option>
                            <option value="Financial">Financial</option>
                            <option value="Investment">Investment</option>
                            <option value="Procurement">Procurement</option>
                            <option value="Sales">Sales</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Budget Code</label>
                        <input type="text" class="form-control" name="inchargecode">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" id="btnSaveAssignCode">Save</button>
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

        $('#formAddVerificator').on('submit', function(e) {
            e.preventDefault();

            $.post("{{ route('settingPriceVerificator.storeVerificator') }}", $(this).serialize())
                .done(function() {
                    $('#modalAddVerificator').modal('hide');
                    showSuccess('Verificator berhasil ditambahkan');
                })
                .fail(function(err) {
                    Swal.fire('Error', 'Gagal menyimpan verificator', 'error');
                    console.error(err);
                });
        });

        $('#formAssignCode').on('submit', function(e) {
            e.preventDefault();

            $.post("{{ route('settingPriceVerificator.assignCode') }}", $(this).serialize())
                .done(function() {
                    $('#modalAssignCode').modal('hide');
                    showSuccess('Budget code berhasil di-assign');
                })
                .fail(function(err) {
                    Swal.fire('Error', 'Gagal assign code', 'error');
                    console.error(err);
                });
        });

        $('#formAssignUser').on('submit', function(e) {
            e.preventDefault();

            $.post("{{ route('settingPriceVerificator.assignUser') }}", $(this).serialize())
                .done(function() {
                    $('#modalAssignUser').modal('hide');
                    showSuccess('User berhasil di-assign');
                })
                .fail(function(err) {
                    Swal.fire('Error', 'Gagal assign user', 'error');
                    console.error(err);
                });
        });

    });
</script>
@endpush