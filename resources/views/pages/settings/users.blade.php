@extends('layouts.master')

@section('title', 'Setting | Users & Roles')
@section('title-sub', 'Users & Roles Management')
@section('pagetitle', 'Setting')

@section('css')
<!-- Choices.js Css -->
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" />
<style>
    /* Improve Choices.js integration with Bootstrap */
    .choices__inner {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        min-height: 38px;
        padding: 4px 10px;
    }
    .choices__list--single {
        padding: 4px 16px 4px 4px;
    }
    .choices[data-type*="select-one"] .choices__inner {
        padding-bottom: 4px;
    }
    .choices__input {
        background-color: transparent;
    }
</style>
@endsection

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
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#module">
                                <i class="bi bi-grid me-1"></i> Modul
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

                        <!-- TAB 3: Module Management -->
                        <div class="tab-pane fade" id="module">
                            @include('authorization.modules')
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<!-- Choices.js Js -->
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
@endpush
