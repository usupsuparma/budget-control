<div id="module-config"
     class="d-none"
     data-routes='@json([
        "store" => route("users.modules.store"),
        "base" => url("users/modules"),
     ])'>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Module Menu Management</h5>
            <small class="text-muted">Master data untuk pengelompokan permission berdasarkan modul dan menu.</small>
        </div>

        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddModule">
            <i class="bi bi-plus-circle me-2"></i>Add Modul
        </button>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="moduleTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Modul Name</th>
                        <th>Menu Name</th>
                        <th>Permissions</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($moduls as $modul)
                        <tr>
                            <td>{{ $modul->id }}</td>
                            <td>{{ $modul->modul_name }}</td>
                            <td>{{ $modul->menu_name ?: '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $modul->permissions_count ?? 0 }}</span>
                            </td>
                            <td>{{ optional($modul->updated_at)->format('d M Y H:i') ?: '—' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-warning edit-module"
                                            data-id="{{ $modul->id }}"
                                            data-modul-name="{{ $modul->modul_name }}"
                                            data-menu-name="{{ $modul->menu_name }}"
                                            data-permissions-count="{{ $modul->permissions_count ?? 0 }}"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger delete-module"
                                            data-id="{{ $modul->id }}"
                                            data-modul-name="{{ $modul->modul_name }}"
                                            data-menu-name="{{ $modul->menu_name }}"
                                            data-permissions-count="{{ $modul->permissions_count ?? 0 }}"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data modul.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddModule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Modul</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="moduleCreateForm">
                    @csrf
                    <div class="mb-3">
                        <label for="module_modul_name" class="form-label fw-semibold">Modul Name</label>
                        <input type="text" class="form-control" id="module_modul_name" name="modul_name" required>
                    </div>
                    <div class="mb-0">
                        <label for="module_menu_name" class="form-label fw-semibold">Menu Name</label>
                        <input type="text" class="form-control" id="module_menu_name" name="menu_name">
                        <div class="form-text">Kosongkan bila modul tidak memiliki menu spesifik.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" form="moduleCreateForm">
                    <i class="bi bi-save me-2"></i>Save Modul
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditModule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Modul</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="moduleEditForm">
                    @csrf
                    <input type="hidden" id="edit_module_id">
                    <div class="mb-3">
                        <label for="edit_modul_name" class="form-label fw-semibold">Modul Name</label>
                        <input type="text" class="form-control" id="edit_modul_name" name="modul_name" required>
                    </div>
                    <div class="mb-0">
                        <label for="edit_menu_name" class="form-label fw-semibold">Menu Name</label>
                        <input type="text" class="form-control" id="edit_menu_name" name="menu_name">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning" form="moduleEditForm">
                    <i class="bi bi-save me-2"></i>Update Modul
                </button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
    $(document).ready(function () {
        const routes = $('#module-config').data('routes');

        if (!$.fn.DataTable.isDataTable('#moduleTable')) {
            $('#moduleTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                responsive: true,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: true, targets: [0, 1, 2, 3, 4] },
                    { orderable: false, targets: [5] },
                ],
            });
        }

        function replaceRouteId(route, id) {
            return `${route}/${id}`;
        }

        function getValidationMessage(xhr, fallback) {
            if (xhr.responseJSON?.message) {
                return xhr.responseJSON.message;
            }

            if (xhr.responseJSON?.errors) {
                const firstError = Object.values(xhr.responseJSON.errors)[0];
                if (Array.isArray(firstError) && firstError.length) {
                    return firstError[0];
                }
            }

            return fallback;
        }

        function reloadPageAfterSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 1400,
                showConfirmButton: false,
            }).then(() => window.location.reload());
        }

        $(document).on('submit', '#moduleCreateForm', function (e) {
            e.preventDefault();
            const form = $(this);

            $.ajax({
                url: routes.store,
                type: 'POST',
                data: form.serialize(),
                beforeSend: function () {
                    Swal.fire({
                        title: 'Saving...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                },
                success: function (response) {
                    $('#modalAddModule').modal('hide');
                    reloadPageAfterSuccess(response.message || 'Modul berhasil ditambahkan.');
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: getValidationMessage(xhr, 'Gagal menambahkan modul.'),
                    });
                },
            });
        });

        $(document).on('click', '.edit-module', function () {
            $('#edit_module_id').val($(this).data('id'));
            $('#edit_modul_name').val($(this).data('modul-name'));
            $('#edit_menu_name').val($(this).data('menu-name'));
            $('#modalEditModule').modal('show');
        });

        $(document).on('submit', '#moduleEditForm', function (e) {
            e.preventDefault();
            const moduleId = $('#edit_module_id').val();

            $.ajax({
                url: replaceRouteId(routes.update, moduleId),
                type: 'POST',
                data: $(this).serialize() + '&_method=PUT',
                beforeSend: function () {
                    Swal.fire({
                        title: 'Updating...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                },
                success: function (response) {
                    $('#modalEditModule').modal('hide');
                    reloadPageAfterSuccess(response.message || 'Modul berhasil diperbarui.');
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: getValidationMessage(xhr, 'Gagal memperbarui modul.'),
                    });
                },
            });
        });

        $(document).on('click', '.delete-module', function () {
            const moduleId = $(this).data('id');
            const modulName = $(this).data('modul-name');
            const menuName = $(this).data('menu-name');
            const permissionsCount = parseInt($(this).data('permissions-count'), 10) || 0;
            const moduleLabel = menuName ? `${modulName} / ${menuName}` : modulName;

            Swal.fire({
                icon: 'warning',
                title: 'Delete Modul?',
                html: `
                    <div class="text-start">
                        <div><strong>Modul:</strong> ${moduleLabel}</div>
                        <div><strong>Permissions:</strong> ${permissionsCount}</div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: replaceRouteId(routes.destroy, moduleId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE',
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Deleting...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading(),
                        });
                    },
                    success: function (response) {
                        reloadPageAfterSuccess(response.message || 'Modul berhasil dihapus.');
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: getValidationMessage(xhr, 'Gagal menghapus modul.'),
                        });
                    },
                });
            });
        });
    });
</script>
@endpush
