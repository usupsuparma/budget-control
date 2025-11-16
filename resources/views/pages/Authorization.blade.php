@extends('layouts.master')

@section('title', 'Authorization Management')
@section('pagetitle', 'Authorization')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Role Management</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRole">
            <i class="bi bi-plus-circle me-2"></i> Add Role
        </button>
    </div>

    <div class="card-body">
        <table id="roleTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Permissions</th>
                    <th width="18%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $r)
                <tr>
                    <td>{{ $r->name }}</td>
                    <td>{{ $r->permissions->pluck('name')->join(', ') ?: '-' }}</td>
                    <td>
                        <button class="btn btn-sm btn-secondary editRole"
                            data-id="{{ $r->id }}"
                            data-name="{{ $r->name }}">
                            <i class="bi bi-pencil-square"></i>
                        </button>

                        <button class="btn btn-sm btn-warning managePermission"
                            data-id="{{ $r->id }}">
                            <i class="bi bi-shield-lock"></i>
                        </button>

                        <button class="btn btn-sm btn-info assignRole"
                            data-id="{{ $r->id }}">
                            <i class="bi bi-people"></i>
                        </button>

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