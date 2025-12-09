@extends('layouts.master')

@section('title', 'Authorization Management')
@section('pagetitle', 'Authorization')

@section('content')

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Role & Permission Management</h4>


        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRole">
            <i class="bi bi-plus-circle me-2"></i> Add Role
        </button>
    </div>

    <div class="card-body">
        <table id="roleTable" class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th width="10%">ID</th>
                    <th width="25%">Role Name</th>
                    <th>Permissions</th>
                    <th width="22%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($roles as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td class="fw-bold text-capitalize">{{ $r->name }}</td>

                    {{-- Permission Badges --}}
                    <td>
                        @forelse($r->permissions as $perm)
                        <span class="badge rounded-pill bg-orange-light text-dark border px-2 py-1 me-1 mb-1">
                            {{ $perm->name }}
                        </span>
                        @empty
                        <span class="text-muted">No permissions</span>
                        @endforelse
                    </td>

                    {{-- Row Actions --}}
                    <td>

                        {{-- View / Manage Permissions --}}
                        <button class="btn btn-sm btn-warning managePermission"
                            data-id="{{ $r->id }}">
                            <i class="bi bi-shield-lock"></i>
                        </button>

                        {{-- Assign Role to Users --}}
                        <button class="btn btn-sm btn-info assignRole"
                            data-id="{{ $r->id }}">
                            <i class="bi bi-people"></i>
                        </button>

                        {{-- Edit Role Name --}}
                        <button class="btn btn-sm btn-secondary editRole"
                            data-id="{{ $r->id }}"
                            data-name="{{ $r->name }}">
                            <i class="bi bi-pencil-square"></i>
                        </button>

                        {{-- Delete --}}
                        <button class="btn btn-sm btn-danger deleteRole"
                            data-id="{{ $r->id }}">
                            <i class="bi bi-trash"></i>
                        </button>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@include('authorization.modals')

@endsection

@push('scripts')
@include('authorization.scripts')
@endpush