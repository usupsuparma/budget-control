@extends('layouts.master')

@section('title', 'Authorization Management')
@section('title-sub', 'Settings')
@section('pagetitle', 'Authorization Management')

@section('content')

<div class="row">
    <div class="col-12">

        <div class="card shadow-sm border">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Role & Permission Management</h5>

                <div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddRole">
                        <i class="bi bi-plus-circle me-1"></i> Add Role
                    </button>

                    <button class="btn btn-secondary btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#modalAssignRole">
                        <i class="bi bi-person-check me-1"></i> Assign Role to User
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table id="roleTable" class="table table-bordered table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">ID</th>
                            <th width="30%">Role Name</th>
                            <th width="40%">Permissions</th>
                            <th width="20%">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>

                            <td>
                                <span class="fw-semibold">{{ $role->name }}</span>
                            </td>

                            <td>
                                @php
                                $perms = $role->permissions->pluck('name')->toArray();
                                @endphp

                                @if(count($perms) == 0)
                                <span class="badge bg-light text-muted">No Permission</span>
                                @else
                                @foreach($perms as $p)
                                <span class="badge bg-primary-subtle text-primary border">{{ $p }}</span>
                                @endforeach
                                @endif
                            </td>

                            <td>
                                <div class="btn-group">

                                    <!-- Manage Permission -->
                                    <button
                                        class="btn btn-sm btn-light-secondary managePermission"
                                        data-id="{{ $role->id }}">
                                        <i class="bi bi-list-check"></i>
                                    </button>

                                    <!-- Edit Role -->
                                    <button
                                        class="btn btn-sm btn-light-primary editRole"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->name }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Delete -->
                                    @if($role->name != 'Super Admin')
                                    <button
                                        class="btn btn-sm btn-light-danger deleteRole"
                                        data-id="{{ $role->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif

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

@include('authorization.modals')
@include('authorization.scripts')

@endsection