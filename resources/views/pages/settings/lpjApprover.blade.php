@extends('layouts.master')

@section('title', 'LPJ Approver Management | Budget Control')

@section('title-sub', 'Settings')
@section('pagetitle', 'LPJ Approver Management')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ri-user-settings-line me-2"></i>LPJ Approver Management</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLpjApproverModal">
                <i class="ri-add-line me-1"></i> Add Approver
            </button>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                <i class="ri-information-line me-1"></i>
                Configure employees who can approve LPJ (Laporan Pertanggungjawaban) submissions. 
                The approval sequence determines the order in which approvers receive the LPJ for review.
            </div>
            
            <table id="lpjApproverTable" class="table table-bordered table-striped w-100">
                <thead class="table-light">
                    <tr>
                        <th width="60px">Sequence</th>
                        <th>Employee Name</th>
                        <th>Job Position</th>
                        <th width="100px">Status</th>
                        <th width="150px">Action</th>
                    </tr>
                </thead>
                <tbody id="lpjApproverBody">
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================= ADD MODAL ================= --}}
<div class="modal fade" id="addLpjApproverModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-user-add-line me-2"></i>Add LPJ Approver</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="addLpjApproverForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employment_id" id="addEmploymentId" class="form-select" required>
                            <option value="" selected disabled>-- Select Employee --</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Approval Sequence</label>
                        <input type="number" name="approval_sequence" class="form-control" min="1" required 
                            placeholder="Enter sequence number (1, 2, 3, ...)">
                        <small class="text-muted">Lower numbers approve first</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="addIsActive" checked>
                            <label class="form-check-label" for="addIsActive">Active</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= EDIT MODAL ================= --}}
<div class="modal fade" id="editLpjApproverModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-edit-line me-2"></i>Edit LPJ Approver</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editLpjApproverForm">
                @csrf
                <input type="hidden" name="id" id="editApproverId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" id="editEmployeeName" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Approval Sequence</label>
                        <input type="number" name="approval_sequence" id="editApprovalSequence" class="form-control" min="1" required>
                        <small class="text-muted">Lower numbers approve first</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive">
                            <label class="form-check-label" for="editIsActive">Active</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    loadLpjApprovers();
    loadAvailableEmployees();
});

function loadLpjApprovers() {
    $.ajax({
        url: '{{ route("lpjApprovalMaster.data") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderApproversTable(response.data);
            }
        },
        error: function() {
            $('#lpjApproverBody').html('<tr><td colspan="5" class="text-center text-danger">Error loading data</td></tr>');
        }
    });
}

function renderApproversTable(approvers) {
    let html = '';
    if (approvers.length === 0) {
        html = '<tr><td colspan="5" class="text-center text-muted">No approvers configured</td></tr>';
    } else {
        approvers.forEach((approver) => {
            html += `
                <tr>
                    <td class="text-center"><span class="badge bg-primary">${approver.approval_sequence}</span></td>
                    <td>${approver.employee_name}</td>
                    <td>${approver.job_position}</td>
                    <td>
                        <span class="badge ${approver.is_active ? 'bg-success' : 'bg-secondary'}">
                            ${approver.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editApprover(${approver.id}, '${approver.employee_name}', ${approver.approval_sequence}, ${approver.is_active})">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-${approver.is_active ? 'warning' : 'success'}" onclick="toggleApproverStatus(${approver.id})">
                            <i class="ri-${approver.is_active ? 'pause' : 'play'}-circle-line"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteApprover(${approver.id})">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    $('#lpjApproverBody').html(html);
}

function loadAvailableEmployees() {
    $.ajax({
        url: '{{ route("lpjApprovalMaster.availableEmployees") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let options = '<option value="" selected disabled>-- Select Employee --</option>';
                response.data.forEach((emp) => {
                    options += `<option value="${emp.id}">${emp.name} - ${emp.job_position}</option>`;
                });
                $('#addEmploymentId').html(options);
            }
        }
    });
}

$('#addLpjApproverForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("lpjApprovalMaster.store") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            employment_id: $('#addEmploymentId').val(),
            approval_sequence: $('input[name="approval_sequence"]').val(),
            is_active: $('#addIsActive').is(':checked')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('addLpjApproverModal')).hide();
                loadLpjApprovers();
                loadAvailableEmployees();
                $('#addLpjApproverForm')[0].reset();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Error adding approver', 'error');
        }
    });
});

function editApprover(id, name, sequence, isActive) {
    $('#editApproverId').val(id);
    $('#editEmployeeName').val(name);
    $('#editApprovalSequence').val(sequence);
    $('#editIsActive').prop('checked', isActive);
    
    const modal = new bootstrap.Modal(document.getElementById('editLpjApproverModal'));
    modal.show();
}

$('#editLpjApproverForm').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#editApproverId').val();
    
    $.ajax({
        url: '{{ route("lpjApprovalMaster.update", ":id") }}'.replace(':id', id),
        type: 'PUT',
        data: {
            _token: '{{ csrf_token() }}',
            approval_sequence: $('#editApprovalSequence').val(),
            is_active: $('#editIsActive').is(':checked')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('editLpjApproverModal')).hide();
                loadLpjApprovers();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Error updating approver', 'error');
        }
    });
});

function toggleApproverStatus(id) {
    $.ajax({
        url: '{{ route("lpjApprovalMaster.toggleActive", ":id") }}'.replace(':id', id),
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.success) {
                loadLpjApprovers();
            }
        }
    });
}

function deleteApprover(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will remove this approver from the LPJ approval flow.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, remove'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("lpjApprovalMaster.destroy", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted', response.message, 'success');
                        loadLpjApprovers();
                        loadAvailableEmployees();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error removing approver', 'error');
                }
            });
        }
    });
}
</script>
@endsection
