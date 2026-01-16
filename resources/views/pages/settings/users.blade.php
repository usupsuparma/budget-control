@extends('layouts.master')

@section('title', 'Setting | Users & Roles')
@section('title-sub', 'Users & Roles Management')
@section('pagetitle', 'Setting')

@section('content')

<div class="col-12 col-lg-12">
    <div class="card card-h-100 shadow-sm border">

        <div class="card-body">
            <div class="row">

                <!-- LEFT TAB -->
                <div class="col-md-2 border-end">
                    <ul class="nav nav-pills flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#user">
                                <i class="bi bi-people me-1"></i> Users & Roles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#permission">
                                <i class="bi bi-shield-lock me-1"></i> Permissions
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="col-md-10">
                    <div class="tab-content pt-3">

                        <!-- TAB 1: Users & Roles Management -->
                        <div class="tab-pane fade show active" id="user">
                            @include('authorization.assignUser')
                        </div>

                        <!-- TAB 2: Permissions Management -->
                        <div class="tab-pane fade" id="permission">
                            @include('authorization.permissions')
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection