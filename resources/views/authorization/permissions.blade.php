<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Permission Management</h5>

        <button class="btn btn-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#modalAddPermission">
            <i class="bi bi-plus-circle me-2"></i> Add Permission
        </button>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="permissionTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Permission Name</th>
                        <th>Route</th>
                        <th>Module/Menu</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $p)
                    <tr>
                        <td>{{ $p->id }}</td>
                        <td>{{ $p->modul_menu_name }}</td>
                        <td>
                            <code>{{ $p->name }}</code>
                        </td>
                        <td>
                            @if($p->modul) {{-- GANTI: modulMenu menjadi modul --}}
                            {{ $p->modul->modul_name ?? 'No Module' }}<br>
                            {{ $p->modul->menu_name ?? 'No Menu' }}
                            @else
                            <span class="text-muted">No Module/Menu</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-warning btn-sm edit-permission"
                                    data-id="{{ $p->id }}"
                                    data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-danger btn-sm delete-permission"
                                    data-id="{{ $p->id }}"
                                    data-bs-toggle="tooltip" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No permissions found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD PERMISSION MODAL -->
<div class="modal fade" id="modalAddPermission" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add New Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="permissionCreateForm" method="POST"
                    action="{{ route('authorization.permissions.create') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Modul / Menu</label>
                        <select name="modul_menu" id="modul_menu" class="form-select" required>
                            <option value="">-- Select Module/Menu --</option>
                            @if(isset($moduls) && $moduls->count() > 0)
                            @foreach($moduls as $modul)
                            <option value="{{ $modul->id }}">
                                @if($modul->modul_name && $modul->menu_name)
                                {{ $modul->modul_name }} - {{ $modul->menu_name }}
                                @elseif($modul->modul_name)
                                {{ $modul->modul_name }}
                                @elseif($modul->menu_name)
                                {{ $modul->menu_name }}
                                @else
                                ID: {{ $modul->id }}
                                @endif
                            </option>
                            @endforeach
                            @else
                            <option value="" disabled>No modules/menus available</option>
                            @endif
                        </select>
                        <div class="form-text">Select the module/menu for this permission</div>

                        <!-- DEBUG: Show moduls data -->
                        @if(isset($moduls) && $moduls->count() == 0)
                        <div class="alert alert-warning mt-2 p-2">
                            <small>No module data found. Check database table 'modul_menus'</small>
                        </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Permission Display Name</label>
                        <input type="text" name="modul_menu_name" class="form-control"
                            placeholder="e.g., Dashboard View" required>
                        <div class="form-text">This will be displayed as permission name</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Route/Key Name</label>
                        <input type="text" name="name" class="form-control"
                            placeholder="e.g., dashboard.view" required>
                        <div class="form-text">Unique key for permission (used in middleware)</div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" form="permissionCreateForm">
                    <i class="bi bi-save me-2"></i> Save Permission
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#permissionTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100],
            "responsive": true,
            "order": [], // ← INI YANG PERLU DIPERBAIKI
            "columnDefs": [{
                    "orderable": true,
                    "targets": [0, 1, 2, 3]
                },
                {
                    "orderable": false,
                    "targets": [4]
                }
            ],
            // Optional: Atur default sorting ke kolom ID descending
            "order": [
                [0, 'desc']
            ] // Kolom 0 = ID, 'desc' = descending
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Form submission with AJAX
        $(document).on('submit', '#permissionCreateForm', function(e) {
            e.preventDefault();

            let form = $(this);
            let url = form.attr('action');
            let submitBtn = form.find('button[type="submit"]');

            // Show loading state
            let originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass me-2"></i> Saving...');

            $.ajax({
                url: url,
                type: "POST",
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message dengan Bootstrap alert
                        $('#modalAddPermission').modal('hide');

                        // Tampilkan alert sukses
                        showAlert('success', 'Success!', response.message || 'Permission created successfully!');

                        // Reload page after 1.5 seconds
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        // Show validation errors
                        let errorMsg = response.message || 'Failed to create permission';
                        if (response.errors) {
                            errorMsg = '';
                            for (let field in response.errors) {
                                errorMsg += response.errors[field][0] + '\n';
                            }
                        }

                        showAlert('error', 'Error!', errorMsg);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to save permission';

                    try {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            // Show validation errors
                            let errors = xhr.responseJSON.errors;
                            errorMessage = '';
                            for (let field in errors) {
                                errorMsg += errors[field][0] + '\n';
                            }
                        } else if (xhr.status === 500) {
                            errorMessage = 'Internal server error. Check Laravel logs.';
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }

                    showAlert('error', 'Error!', errorMessage);
                    console.log('Error details:', xhr.responseText);
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Delete permission
        $(document).on('click', '.delete-permission', function() {
            let permissionId = $(this).data('id');

            if (confirm('Are you sure you want to delete this permission? This action cannot be undone.')) {
                let deleteBtn = $(this);
                deleteBtn.prop('disabled', true).html('<i class="bi bi-hourglass"></i>');

                $.ajax({
                    url: "{{ url('authorization/permissions/delete') }}/" + permissionId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', 'Success!', 'Permission deleted successfully!');
                            location.reload();
                        } else {
                            showAlert('error', 'Error!', response.message || 'Failed to delete permission');
                            deleteBtn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                        }
                    },
                    error: function(xhr) {
                        console.log('Delete error:', xhr.responseText);
                        showAlert('error', 'Error!', 'Failed to delete permission');
                        deleteBtn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    }
                });
            }
        });

        // Fungsi untuk menampilkan alert (ganti toastr)
        function showAlert(type, title, message) {
            // Buat elemen alert Bootstrap
            let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            let icon = type === 'success' ? '<i class="bi bi-check-circle me-2"></i>' : '<i class="bi bi-exclamation-triangle me-2"></i>';

            let alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3" 
                     style="z-index: 9999; min-width: 300px;" role="alert">
                    <div class="d-flex">
                        <div>${icon}</div>
                        <div>
                            <strong>${title}</strong>
                            <div class="small">${message}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            // Hapus alert sebelumnya
            $('.alert-position-fixed').remove();

            // Tambahkan alert baru
            $('body').append(alertHtml);

            // Auto remove setelah 5 detik
            setTimeout(function() {
                $('.alert-position-fixed').alert('close');
            }, 5000);
        }
    });
</script>
@endpush