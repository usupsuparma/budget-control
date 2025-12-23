@extends('layouts.master')

@section('title', 'Setting | Master')
@section('title-sub', 'Master')
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
                            <a class="nav-link active" data-bs-toggle="tab" href="#user">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#authorization">Roles</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#permission">Permissions</a>
                        </li>
                    </ul>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="col-md-10">
                    <div class="tab-content pt-3">

                        <div class="tab-pane fade show active" id="user">
                            @include('authorization.users')
                        </div>

                        <div class="tab-pane fade" id="authorization">
                            @include('authorization.index') <!-- ROLE PAGE -->
                        </div>

                        <div class="tab-pane fade" id="permission">
                            @include('authorization.permissions') <!-- PERMISSION PAGE -->
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection


{{-- ========== FIX: MODAL DITARUH DI LUAR TAB-PANE ========== --}}
@include('authorization.modals.permissions')
@include('authorization.modals')
@include('authorization.modals.assign')

@push('scripts')
@include('authorization.scripts.permissions')
@endpush