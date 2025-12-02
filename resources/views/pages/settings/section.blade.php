<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div class="col-md-12 text-end">

                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSection">
                            <i class="bi bi-plus-lg me-1"></i>Add Section
                        </button>

                    </div>
                </div>

                <div class="card-body">
                    <table id="sectionTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Section</th>
                                <th>Department (Parent)</th>
                                <th>Status</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSection" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Section</h5>
                <button type="button" class="btn-close icon-btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-large-line fw-semibold"></i>
                </button>
            </div>

            <div class="modal-body">
                <form id="sectionCreateForm" method="POST" action="{{ route('section.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Section Name</label>
                            <input type="text" name="section_name" class="form-control" required placeholder="Enter Section Name">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-control" required>
                            <option value="" disabled selected>-- Select Department --</option>
                            @foreach ($department as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnCreateSection">Save Data</button>

            </div>

        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSection" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="sectionEditForm" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Section Name</label>
                            <input type="text" name="section_name" id="edit_section_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Department</label>
                            <select name="department_id" id="edit_section_department_id" class="form-control" required>
                                <option value="" disabled>-- Select Department --</option>
                                @foreach ($department as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label>Status</label>
                            <select name="status" id="edit_status_section" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" form="sectionEditForm">Update</button>
            </div>

        </div>
    </div>
</div>


@push('scripts')

<!-- DATATABLES -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet"
    href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {

        $('#sectionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('section.data') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'department_name',
                    name: 'department_name'
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [0, 'desc']
            ]
        });
    });
</script>


@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "{{ session('success') }}",
        timer: 2000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
</script>
@endif

<script>
    // CREATE (AJAX)
    $('#btnCreateSection').click(function(e) {
        e.preventDefault();

        let form = $('#sectionCreateForm');
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {

                // Tutup modal
                $('#addSection').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                // Reload DataTable tanpa reload halaman
                $('#sectionTable').DataTable().ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Section added successfully",
                    timer: 1500,
                    showConfirmButton: false
                });

                // Reset form
                form.trigger('reset');
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to add data"
                });
            }
        });
    });
</script>
<!-- EDIT -->
<script>
    $(document).on('click', '.section-edit-btn', function() {
        var id = $(this).data('id');

        var editUrl = "{{ route('section.edit', ['id' => 0]) }}".replace('/0/edit', '/' + id + '/edit');

        $.get(editUrl, function(response) {

            $('#edit_section_name').val(response.name);
            $('#edit_section_department_id').val(response.department_id).change();
            $('#edit_status_section').val(response.status);

            var updateUrl = "{{ route('section.update', ['id' => 0]) }}".replace('/0', '/' + id);

            $('#sectionEditForm').attr('action', updateUrl);

            $('#editSection').modal('show');
        });
    });
</script>

<!-- UPDATE -->
<script>
    $('#sectionEditForm').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function() {
                $('#editSection').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#sectionTable').DataTable().ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Section updated successfully",
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });
</script>

<!-- DELETE -->
<script>
    $(document).on('click', '.section-delete-btn', function() {

        var id = $(this).data('id');

        Swal.fire({
            title: "Delete Section?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Delete",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {

                var deleteUrl = "/section/delete/" + id;

                $.ajax({
                    url: deleteUrl,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {

                        $('#sectionTable').DataTable().ajax.reload(null, false);

                        Swal.fire({
                            icon: "success",
                            title: "Deleted",
                            text: "Section deleted successfully",
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });

            }
        });
    });
</script>

@endpush