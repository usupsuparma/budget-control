@extends('layouts.master')

@section('title', 'KPI | Budget Control')

@section('title-sub', 'KPI')
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
                                <h5>KPI</h5>
                            </div>
                            <div class="col-12" style="display: flex;">
                                <div class="col-6">
                                    <div class="col-11 m-1">
                                        <div class="">
                                            <div class="tab-content" id="default-select">
                                                <div class="tab-pane fade show active" id="html-default-select"
                                                    role="tabpanel" aria-labelledby="html-default-select-tab"
                                                    tabindex="0">
                                                    <label class="form-label" for="product-size-add">Sasaran
                                                        Strategis</label>
                                                    <select class="form-select" id="form-select-01" name="form-select-01"
                                                        aria-label="Default select example">
                                                        <option selected>Select</option>
                                                        <option value="s1">Sasaran Strategis 1</option>
                                                        <option value="s2">Sasaran Strategis 2</option>
                                                        <option value="s3">Sasaran Strategis 3</option>
                                                        <option value="s4">Sasaran Strategis 4</option>
                                                        <option value="s5">Sasaran Strategis 5</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-11 m-1">
                                        <label class="form-label" for="product-cost-add">KPI</label>
                                        {{-- <input placeholder="Jenis" type="text" id="product-cost-add"
                                            class="form-control"> --}}

                                        <div class="col-xl-12" style="display: flex;">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="radio" name="flexRadioDefault"
                                                    id="flexRadioDefault1">
                                                <label class="form-check-label" for="flexRadioDefault1">
                                                    Baru
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="flexRadioDefault"
                                                    id="flexRadioDefault2" checked="">
                                                <label class="form-check-label" for="flexRadioDefault2">
                                                    Eksisting
                                                </label>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-xl-11 m-1">
                                        <label class="form-label" for="product-cost-add">&nbsp;</label>
                                        <input placeholder="Jenis" type="text" id="product-cost-add"
                                            class="form-control">
                                    </div>

                                    <div class="col-xl-11 m-1">
                                        <label class="form-label" for="product-description-add">Deskripsi</label>
                                        <input placeholder="Nama Anggaran" type="text" id="product-description-add"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="col-xl-12 m-1">
                                        <label class="form-label" for="product-cost-add">Target</label>
                                        <input placeholder="Jenis" type="text" id="product-cost-add"
                                            class="form-control">
                                    </div>

                                    <div class="col-xl-12 m-1">
                                        <label class="form-label" for="product-cost-add">Satuan</label>
                                        <input placeholder="Jenis" type="text" id="product-cost-add"
                                            class="form-control">
                                    </div>

                                    <div class="col-12 m-1">
                                        <label class="form-label" for="product-description-add">Bobot</label>
                                        <input placeholder="Nama Anggaran" type="text" id="product-description-add"
                                            class="form-control">
                                    </div>

                                    <div class="col-xl-12 m-1">
                                        <label class="form-label" for="product-cost-add">Catatan</label>
                                        <input placeholder="Nominal" type="text" id="product-cost-add"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <h5>ACTION PLAN</h5>
                            </div>

                            <div class="col-12" style="display: flex;">
                                <div class="col-6">
                                    <table class="table text-nowrap align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">Pelaksanaan</th>
                                                <th scope="col">Target</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value=""
                                                        checked>
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Jan
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="">
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Feb
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="">
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Mar
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="">
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Apr
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value=""
                                                        checked>
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Mei
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value=""
                                                        checked>
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Jun
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <table class="table text-nowrap align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">Pelaksanaan</th>
                                                <th scope="col">Target</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="">
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Jul
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="">
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Agu
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="">
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Sep
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value=""
                                                        checked>
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Okt
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value=""
                                                        checked>
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Nov
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="">
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Des
                                                    </label>
                                                </td>
                                                <td><input placeholder="Target" type="text"
                                                        class="form-control"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <div class="text-end">
                                    <a href="{{ route('kpi.index') }}" role="button"
                                        class="btn btn-light-primary">Batalkan</a>
                                    <a href="{{ route('kpi.index') }}" role="button" class="btn btn-primary">Edit
                                        Data</a>
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
