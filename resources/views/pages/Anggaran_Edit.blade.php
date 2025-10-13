@extends('layouts.master')

@section('title', 'Anggaran | Budget Control')

@section('title-sub', 'Anggaran')
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
                                <h5>ANGGARAN</h5>
                            </div>
                            <div class="col-12">
                                <div class="">
                                    <div class="tab-content" id="default-select">
                                        <div class="tab-pane fade show active" id="html-default-select" role="tabpanel"
                                            aria-labelledby="html-default-select-tab" tabindex="0">
                                            <label class="form-label" for="product-size-add">Program Kerja</label>
                                            <select class="form-select" id="form-select-01" name="form-select-01"
                                                aria-label="Default select example">
                                                <option selected>Select</option>
                                                <option value="p1">Program Kerja 1</option>
                                                <option value="p2">Program Kerja 2</option>
                                                <option value="p3">Program Kerja 3</option>
                                                <option value="p4">Program Kerja 4</option>
                                                <option value="p5">Program Kerja 5</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <label class="form-label" for="product-cost-add">Jenis</label>
                                <input placeholder="Jenis" type="text" id="product-cost-add" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="product-description-add">Nama Anggaran</label>
                                <input placeholder="Nama Anggaran" type="text" id="product-description-add"
                                    class="form-control">
                            </div>

                            <div class="col-xl-12">
                                <label class="form-label" for="product-cost-add">Nominal</label>
                                <input placeholder="Nominal" type="text" id="product-cost-add" class="form-control">
                            </div>

                            <div class="col-12">
                                <h5>RENCANA REALISASI</h5>
                            </div>

                            <div class="col-12" style="display: flex;">
                                <div class="col-6">
                                    <table class="table text-nowrap align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">Pelaksanaan</th>
                                                <th scope="col">Nilai Anggaran</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" checked>
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Jan
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" >
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Feb
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" >
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Mar
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" >
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Apr
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" checked>
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Mei
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" checked>
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Jun
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <table class="table text-nowrap align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">Pelaksanaan</th>
                                                <th scope="col">Nilai Anggaran</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" >
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Jul
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" >
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Agu
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" >
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Sep
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" checked>
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Okt
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" checked>
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Nov
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" value="" >
                                                    <label class="form-check-label" for="CustomflexCheck01">
                                                        Des
                                                    </label>
                                                </td>
                                                <td><input placeholder="Nilai Anggaran" type="text" class="form-control"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <div class="text-end">
                                    <a href="{{ route('anggaran.index') }}" role="button"  class="btn btn-light-primary">Batalkan</a>
                                    <a href="{{ route('anggaran.index') }}" role="button" class="btn btn-primary">Update Data</a>
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
