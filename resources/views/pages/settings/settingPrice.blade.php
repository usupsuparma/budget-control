@extends('layouts.master')

@section('title', 'Setting | Master')
@section('title-sub', 'Master')
@section('pagetitle', 'Setting')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection

@section('content')

<div class="col-12 col-lg-12">
    <!-- ✅ CARD PEMBUNGKUS UTAMA -->
    <div class="card card-h-100 shadow-sm border">


        <div class="card-body">
            <div class="row">
                <!-- LEFT SIDEBAR (Tab) -->
                <div class="col-md-3 border-end">
                    <ul class="nav nav-pills flex-column" role="tablist">

                        <!-- <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#budgetCode" role="tab">
                                <i class="fas fa-user me-2"></i> Price
                            </a>
                        </li> -->
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#priceVerificator" role="tab">
                                <i class="fas fa-user-tie me-2"></i> Price Verificator
                            </a>
                        </li>



                    </ul>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="col-md-9">
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="priceVerificator">
                            @include('pages.settings.priceVerificator')
                        </div>



                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
@endpush