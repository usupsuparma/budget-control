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

<!-- Create Modal -->
<div class="modal fade" id="addSection" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createSectionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="sectionCreateForm" method="POST" action="{{ route('section.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Section Name</label>
                            <input type="text" name="section_name" class="form-control" placeholder="Enter Section Name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Department</label>
                            <select name="department_id" id="department_id" class="form-control" required>
                                <option value="" selected disabled>-- Select Department --</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnCreateSection">Save Data</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
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
                            <select name="department_id" id="edit_department_section_id" class="form-control" required>
                                <option value="" disabled>-- Select Department --</option>
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

@push('page-scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi dropdown awal
        if (window.masterData && window.masterData.departments) {
            populateSelect('department_id', window.masterData.departments);
            populateSelect('edit_department_section_id', window.masterData.departments);
        }

        // Event listener untuk refresh data master
        $(document).on('masterDataRefreshed', function(e, data) {
            populateSelect('department_id', data.departments);
            populateSelect('edit_department_section_id', data.departments);
        });

        $('#sectionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('section.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'department', name: 'department' },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });
    });

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
                $('#addSection').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#sectionTable').DataTable().ajax.reload(null, false);
                refreshMasterOptions();

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Section added successfully",
                    timer: 1500,
                    showConfirmButton: false
                });

                form.trigger('reset');
            }
        });
    });

    // EDIT
    var $secEditModal = document.getElementById('editSection');
    var _secData = {};

    $(document).on('click', '.section-edit-btn', function() {
        var id = $(this).data('id');
        var url = "{{ url('/section') }}/" + id + "/edit";
        Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        $.get(url, function(data) {
            Swal.close();
            _secData = data;
            $('#edit_section_name').val(data.name);
            $('#edit_status_section').val(data.status || 'Active');
            $('#sectionEditForm').attr('action', "{{ url('/section/update') }}/" + data.id);
            new bootstrap.Modal($secEditModal).show();
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load data.' });
        });
    });

    $secEditModal.addEventListener('shown.bs.modal', function () {
        var depts = (window.masterData && window.masterData.departments) ? window.masterData.departments : [];
        populateSelect('edit_department_section_id', depts, _secData.department_id);
        if (!window.masterChoices['edit_department_section_id']) {
            initChoices('edit_department_section_id');
        }
    });

    $secEditModal.addEventListener('hidden.bs.modal', function () {
        if (window.masterChoices['edit_department_section_id']) {
            window.masterChoices['edit_department_section_id'].destroy();
            delete window.masterChoices['edit_department_section_id'];
        }
        _secData = {};
    });


    $('#sectionEditForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');

        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(),
            success: function(res) {
                $('#editSection').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                $('#sectionTable').DataTable().ajax.reload();
                refreshMasterOptions();

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

    // DELETE
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
                $.ajax({
                    url: "/section/delete/" + id,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        $('#sectionTable').DataTable().ajax.reload(null, false);
                        refreshMasterOptions();
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
