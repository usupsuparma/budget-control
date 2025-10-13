@extends('layouts.master')

@section('title', 'Sasaran Strategis | Budget Control')

@section('title-sub', 'Sasaran Strategis')
@section('pagetitle', 'Edit Data')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection
@section('content')

    <!-- Begin page -->
    <div id="layout-wrapper">

        <div class="row">
            <div class="col-xl-3"></div>
            <div class="col-xl-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">
                            <div class="col-12">
                                <div class="">
                                    <div class="tab-content" id="default-select">
                                        <div class="tab-pane fade show active" id="html-default-select" role="tabpanel"
                                            aria-labelledby="html-default-select-tab" tabindex="0">
                                            <label class="form-label" for="product-size-add">Tahun</label>
                                            <select class="form-select" id="form-select-01" name="form-select-01"
                                                aria-label="Default select example">
                                                <option selected>Select</option>
                                                <option value="2025">2025</option>
                                                <option value="2024">2024</option>
                                                <option value="2023">2023</option>
                                                <option value="2022">2022</option>
                                                <option value="2021">2021</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <label class="form-label" for="product-cost-add">Sasaran Strategis</label>
                                <input placeholder="Sasaran Strategis" type="text" id="product-cost-add" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="product-description-add">Deskripsi</label>
                                <textarea class="form-control" rows="3" placeholder="Deskripsi" id="floatingTextarea"></textarea>
                            </div>

                            <div class="col-xl-12">
                                <label class="form-label" for="product-cost-add">Target</label>
                                <input placeholder="Target" type="text" id="product-cost-add" class="form-control">
                            </div>

                            <div class="col-xl-12">
                                <label class="form-label" for="product-cost-add">Satuan</label>
                                <input placeholder="Satuan" type="text" id="product-cost-add" class="form-control">
                            </div>

                            <div class="col-xl-12">
                                <label class="form-label" for="product-cost-add">Catatan</label>
                                <input placeholder="Catatan" type="text" id="product-cost-add" class="form-control">
                            </div>

                            <div class="col-xl-12">
                                <div class="text-end">
                                    <a href="{{ route('sasaran-strategis.index') }}" role="button"  class="btn btn-light-primary">Batalkan</a>
                                    <a href="{{ route('sasaran-strategis.index') }}" role="button" class="btn btn-primary">Update Data</a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!--End col-->
            <div class="col-xl-3"></div>
        </div><!--End row-->
    </div>
    </main>
@endsection

@section('js')

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <script src="{{ asset('assets/js/app/project-list.init.js') }}"></script>
@endsection
