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
                            <a class="nav-link active" data-bs-toggle="tab" href="#transaction" role="tab">
                                <i class="fas fa-user me-2"></i> Transactions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#addBudget" role="tab">
                                <i class="fas fa-user-tie me-2"></i> Add Budget
                            </a>
                        </li>


                    </ul>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="col-md-9">
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="transaction">
                            @include('pages.settings.authorizationTransaction')
                        </div>
                        <div class="tab-pane fade" id="addBudget">
                            @include('pages.settings.authorizationAddBudget')
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection