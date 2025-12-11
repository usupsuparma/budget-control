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
                <div class="col-md-3 border-end">
                    <ul class="nav nav-pills flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#customer" role="tab">
                                <i class="fas fa-user me-2"></i> Customer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#supplier" role="tab">
                                <i class="fas fa-user-tie me-2"></i> Supplier
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#segmen" role="tab">
                                <i class="fas fa-layer-group me-2"></i>Segmen Customer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#area" role="tab">
                                <i class="fas fa-layer-group me-2"></i>Area
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#unit" role="tab">
                                <i class="fas fa-layer-group me-2"></i>Unit
                            </a>
                        </li>

                    </ul>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="col-md-9">
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="customer">
                            @include('pages.settings.customer')
                        </div>
                        <div class="tab-pane fade" id="supplier">
                            @include('pages.settings.supplier')
                        </div>
                        <div class="tab-pane fade" id="segmen">
                            @include('pages.settings.segmen')
                        </div>
                        <div class="tab-pane fade" id="area">
                            @include('pages.settings.area')
                        </div>
                        <div class="tab-pane fade" id="area">
                            @include('pages.settings.unit')
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection