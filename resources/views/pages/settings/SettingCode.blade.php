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
                            <a class="nav-link active" data-bs-toggle="tab" href="#budgetCode" role="tab">
                                <i class="fas fa-user me-2"></i> Budget Code
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#stockCode" role="tab">
                                <i class="fas fa-user-tie me-2"></i> Stock Code
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#inventoryCode" role="tab">
                                <i class="fas fa-layer-group me-2"></i> Inventory Code
                            </a>
                        </li>



                    </ul>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="col-md-10">
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="budgetCode">
                            @include('pages.settings.employee')
                        </div>
                        <div class="tab-pane fade" id="stockCode">
                            @include('pages.settings.JobPosition')
                        </div>
                        <div class="tab-pane fade" id="inventoryCode">
                            @include('pages.settings.JobLevel')
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection