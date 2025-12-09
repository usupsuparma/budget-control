<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Permission Management</h5>

        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddPermission">
            <i class="bi bi-plus-circle me-2"></i> Add Permission
        </button>
    </div>

    <div class="card-body">
        <table id="permissionTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>Permission</th>
                    <th>Module</th>
                    <th width="20%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($permissions as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ explode('.', $p->name)[0] }}</td>

                    <td>
                        <!-- Edit -->
                        <button
                            class="btn btn-sm btn-secondary editPermission"
                            data-id="{{ $p->id }}"
                            data-name="{{ $p->name }}">
                            <i class="bi bi-pencil-square"></i>
                        </button>

                        <!-- Delete -->
                        <button
                            class="btn btn-sm btn-danger deletePermission"
                            data-id="{{ $p->id }}">
                            <i class="bi bi-trash"></i>
                        </button>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@include('authorization.modals.permissions')



@push('scripts')
@include('authorization.scripts.permissions')
@endpush