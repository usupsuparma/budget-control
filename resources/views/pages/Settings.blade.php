@extends('layouts.master')

@section('title', 'Setting | Master')
@section('title-sub', 'Master')
@section('pagetitle', 'Setting')

@section('content')

<div class="col-12 col-lg-12">
    <!-- ✅ CARD PEMBUNGKUS UTAMA -->
    <div class="card card-h-100 shadow-sm border">


        <div class="card-body">
            <div class="row">
                <!-- LEFT SIDEBAR (Tab) -->
                <div class="col-md-2 border-end">
                    <ul class="nav nav-pills flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#employee" role="tab">
                                <i class="fas fa-user me-2"></i> Employee
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#job_position" role="tab">
                                <i class="fas fa-user-tie me-2"></i> Job Position
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#job_level" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Job Level
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#section" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Section
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#department" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Department
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#division" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Division
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#director" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Director
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#organization" role="tab">
                                <i class="fas fa-building me-2"></i> Organization
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#coa" role="tab">
                                <i class="fas fa-history me-2"></i> COA
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#authorization" role="tab">
                                <i class="fas fa-lock me-2"></i> Authorization
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="col-md-10">
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="employee">
                            @include('pages.employee')
                        </div>
                        <div class="tab-pane fade" id="job_position">
                            @include('pages.JobPosition')
                        </div>
                        <div class="tab-pane fade" id="job_level">
                            @include('pages.JobLevel')
                        </div>
                        <div class="tab-pane fade" id="section">
                            @include('pages.section')
                        </div>
                        <div class="tab-pane fade" id="department">
                            @include('pages.department')
                        </div>
                        <div class="tab-pane fade" id="division">
                            @include('pages.division')
                        </div>
                        <div class="tab-pane fade" id="director">
                            @include('pages.director')
                        </div>
                        <div class="tab-pane fade" id="organization">
                            @include('pages.Organization')
                        </div>
                        <div class="tab-pane fade" id="coa">
                            <p>History content...</p>
                        </div>
                        <div class="tab-pane fade" id="authorization">
                            <p>Authorization content...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection