@extends('layouts.master')

@section('title', 'Company Policy | Budget Control')

@section('title-sub', 'Company Policy')
@section('pagetitle', 'Edit Data')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection
@section('content')

    <!-- Begin page -->
    <div id="layout-wrapper">

        <div class="row">
            
            <div class="col-xl-12">
                <div class="card card-h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Edit Document</h6>
                    </div>
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
                                <label class="form-label" for="product-cost-add">Upload Document Company Policy</label>
                                <input class="form-control" type="file" id="formFile">
                            </div>

                        </div>
                    </div>
                </div>
            </div><!--End col-->
            
        </div><!--End row-->
        <div class="row">
            
            <div class="col-xl-12">
                <div class="card card-h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Add Detail Document</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-5">

                            <div class="col-12">
                                <label class="form-label" for="product-description-add">Company Policy (Eng)</label>
                                <textarea class="form-control" rows="3" placeholder="Deskripsi" id="floatingTextarea"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="product-description-add">Company Policy (Ina)</label>
                                <textarea class="form-control" rows="3" placeholder="Deskripsi" id="floatingTextarea"></textarea>
                            </div>

                            <div class="col-xl-12">
                                <div class="text-end">
                                    <a href="#" role="button" class="btn btn-primary">Add Detail</a>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <div class="p-3">
                                    <h6 class="fw-bold mb-2">Detail Company Policy (2025)</h6>
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th scope="col">No.</th>
                                                <th scope="col">Company Policy (Eng)</th>
                                                <th scope="col">Company Policy (Ina)</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="width: 5%;"">1</td>
                                                <td
                                                    style="width: 45%; white-space: normal; word-wrap: break-word; text-align: justify;">
                                                    <h6 class="mb-1">Safety first in all
                                                        activities and keep a stable Plant operation
                                                        (Target: Zero accidents,
                                                        plant troubles by internal causes: max 3
                                                        troubles).</h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        PIP consistently maintains plant operational
                                                        stability by
                                                        ensuring WS conditions, raw material
                                                        quality, and utilities meet
                                                        established standards, along with regulatory
                                                        compliance, in
                                                        order to achieve production targets and zero
                                                        customer
                                                        complaints. <br>PIP will seek
                                                        alternative suppliers for AAQ,
                                                        PSC, DIBC, and other materials to ensure
                                                        the continuity of plant
                                                        operations in the event of supply
                                                        disruptions from current
                                                        suppliers.
                                                    </p>
                                                </td>
                                                <td
                                                    style="width: 45%; white-space: normal; word-wrap: break-word; text-align: justify;">
                                                    <h6 class="mb-1">Mengutamakan keselamatan
                                                        dalam semua aktivitas dan menjaga kestabilan
                                                        operasi pabrik
                                                        (Target: Zero accidents, plant troubles by
                                                        internal causes: max 3
                                                        troubles).</h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        PIP senantiasa menjaga stabilitas
                                                        operasional pabrik dengan
                                                        memastikan kondisi WS, kualitas bahan
                                                        baku, dan utilitas sesuai
                                                        standar, serta kepatuhan terhadap
                                                        regulasi, guna mencapai target
                                                        produksi dan zero customer complaint.
                                                        <br>PIP akan mencari
                                                        alternatif pemasok untuk AAQ, PSC, DIBC,
                                                        dan bahan lainnya guna
                                                        memastikan keberlangsungan operasional
                                                        pabrik apabila terjadi
                                                        gangguan pasokan dari pemasok yang ada
                                                        saat ini.
                                                    </p>
                                                </td>
                                                <td>
                                                    <a href="{{ route('realisasi.edit', 1) }}">
                                                        <button type="button" class="btn btn-light-primary icon-btn-sm"><i
                                                                class="bi bi-pencil-square"></i></button>
                                                    </a>
                                                    <button type="button" class="btn btn-light-danger icon-btn-sm"><i
                                                            class="ri-delete-bin-line"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 5%;"">2</td>
                                                <td
                                                    style="width: 45%; white-space: normal; word-wrap: break-word; text-align: justify;">
                                                    <h6 class="mb-1">Safety first in all
                                                        activities and keep a stable Plant operation
                                                        (Target: Zero accidents,
                                                        plant troubles by internal causes: max 3
                                                        troubles).</h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        PIP consistently maintains plant operational
                                                        stability by
                                                        ensuring WS conditions, raw material
                                                        quality, and utilities meet
                                                        established standards, along with regulatory
                                                        compliance, in
                                                        order to achieve production targets and zero
                                                        customer
                                                        complaints. <br>PIP will seek
                                                        alternative suppliers for AAQ,
                                                        PSC, DIBC, and other materials to ensure
                                                        the continuity of plant
                                                        operations in the event of supply
                                                        disruptions from current
                                                        suppliers.
                                                    </p>
                                                </td>
                                                <td
                                                    style="width: 45%; white-space: normal; word-wrap: break-word; text-align: justify;">
                                                    <h6 class="mb-1">Mengutamakan keselamatan
                                                        dalam semua aktivitas dan menjaga kestabilan
                                                        operasi pabrik
                                                        (Target: Zero accidents, plant troubles by
                                                        internal causes: max 3
                                                        troubles).</h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        PIP senantiasa menjaga stabilitas
                                                        operasional pabrik dengan
                                                        memastikan kondisi WS, kualitas bahan
                                                        baku, dan utilitas sesuai
                                                        standar, serta kepatuhan terhadap
                                                        regulasi, guna mencapai target
                                                        produksi dan zero customer complaint.
                                                        <br>PIP akan mencari
                                                        alternatif pemasok untuk AAQ, PSC, DIBC,
                                                        dan bahan lainnya guna
                                                        memastikan keberlangsungan operasional
                                                        pabrik apabila terjadi
                                                        gangguan pasokan dari pemasok yang ada
                                                        saat ini.
                                                    </p>
                                                </td>
                                                <td>
                                                    <a href="{{ route('realisasi.edit', 1) }}">
                                                        <button type="button" class="btn btn-light-primary icon-btn-sm"><i
                                                                class="bi bi-pencil-square"></i></button>
                                                    </a>
                                                    <button type="button" class="btn btn-light-danger icon-btn-sm"><i
                                                            class="ri-delete-bin-line"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 5%;"">3</td>
                                                <td
                                                    style="width: 45%; white-space: normal; word-wrap: break-word; text-align: justify;">
                                                    <h6 class="mb-1">Safety first in all
                                                        activities and keep a stable Plant operation
                                                        (Target: Zero accidents,
                                                        plant troubles by internal causes: max 3
                                                        troubles).</h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        PIP consistently maintains plant operational
                                                        stability by
                                                        ensuring WS conditions, raw material
                                                        quality, and utilities meet
                                                        established standards, along with regulatory
                                                        compliance, in
                                                        order to achieve production targets and zero
                                                        customer
                                                        complaints. <br>PIP will seek
                                                        alternative suppliers for AAQ,
                                                        PSC, DIBC, and other materials to ensure
                                                        the continuity of plant
                                                        operations in the event of supply
                                                        disruptions from current
                                                        suppliers.
                                                    </p>
                                                </td>
                                                <td
                                                    style="width: 45%; white-space: normal; word-wrap: break-word; text-align: justify;">
                                                    <h6 class="mb-1">Mengutamakan keselamatan
                                                        dalam semua aktivitas dan menjaga kestabilan
                                                        operasi pabrik
                                                        (Target: Zero accidents, plant troubles by
                                                        internal causes: max 3
                                                        troubles).</h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        PIP senantiasa menjaga stabilitas
                                                        operasional pabrik dengan
                                                        memastikan kondisi WS, kualitas bahan
                                                        baku, dan utilitas sesuai
                                                        standar, serta kepatuhan terhadap
                                                        regulasi, guna mencapai target
                                                        produksi dan zero customer complaint.
                                                        <br>PIP akan mencari
                                                        alternatif pemasok untuk AAQ, PSC, DIBC,
                                                        dan bahan lainnya guna
                                                        memastikan keberlangsungan operasional
                                                        pabrik apabila terjadi
                                                        gangguan pasokan dari pemasok yang ada
                                                        saat ini.
                                                    </p>
                                                </td>
                                                <td>
                                                    <a href="{{ route('realisasi.edit', 1) }}">
                                                        <button type="button"
                                                            class="btn btn-light-primary icon-btn-sm"><i
                                                                class="bi bi-pencil-square"></i></button>
                                                    </a>
                                                    <button type="button" class="btn btn-light-danger icon-btn-sm"><i
                                                            class="ri-delete-bin-line"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 5%;"">4</td>
                                                <td
                                                    style="width: 45%; white-space: normal; word-wrap: break-word; text-align: justify;">
                                                    <h6 class="mb-1">Safety first in all
                                                        activities and keep a stable Plant operation
                                                        (Target: Zero accidents,
                                                        plant troubles by internal causes: max 3
                                                        troubles).</h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        PIP consistently maintains plant operational
                                                        stability by
                                                        ensuring WS conditions, raw material
                                                        quality, and utilities meet
                                                        established standards, along with regulatory
                                                        compliance, in
                                                        order to achieve production targets and zero
                                                        customer
                                                        complaints. <br>PIP will seek
                                                        alternative suppliers for AAQ,
                                                        PSC, DIBC, and other materials to ensure
                                                        the continuity of plant
                                                        operations in the event of supply
                                                        disruptions from current
                                                        suppliers.
                                                    </p>
                                                </td>
                                                <td
                                                    style="width: 45%; white-space: normal; word-wrap: break-word; text-align: justify;">
                                                    <h6 class="mb-1">Mengutamakan keselamatan
                                                        dalam semua aktivitas dan menjaga kestabilan
                                                        operasi pabrik
                                                        (Target: Zero accidents, plant troubles by
                                                        internal causes: max 3
                                                        troubles).</h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        PIP senantiasa menjaga stabilitas
                                                        operasional pabrik dengan
                                                        memastikan kondisi WS, kualitas bahan
                                                        baku, dan utilitas sesuai
                                                        standar, serta kepatuhan terhadap
                                                        regulasi, guna mencapai target
                                                        produksi dan zero customer complaint.
                                                        <br>PIP akan mencari
                                                        alternatif pemasok untuk AAQ, PSC, DIBC,
                                                        dan bahan lainnya guna
                                                        memastikan keberlangsungan operasional
                                                        pabrik apabila terjadi
                                                        gangguan pasokan dari pemasok yang ada
                                                        saat ini.
                                                    </p>
                                                </td>
                                                <td>
                                                    <a href="{{ route('realisasi.edit', 1) }}">
                                                        <button type="button"
                                                            class="btn btn-light-primary icon-btn-sm"><i
                                                                class="bi bi-pencil-square"></i></button>
                                                    </a>
                                                    <button type="button" class="btn btn-light-danger icon-btn-sm"><i
                                                            class="ri-delete-bin-line"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 5%;"">5</td>
                                                <td
                                                    style="width: 45%; white-space: normal; word-wrap: break-word; text-align: justify;">
                                                    <h6 class="mb-1">Safety first in all
                                                        activities and keep a stable Plant operation
                                                        (Target: Zero accidents,
                                                        plant troubles by internal causes: max 3
                                                        troubles).</h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        PIP consistently maintains plant operational
                                                        stability by
                                                        ensuring WS conditions, raw material
                                                        quality, and utilities meet
                                                        established standards, along with regulatory
                                                        compliance, in
                                                        order to achieve production targets and zero
                                                        customer
                                                        complaints. <br>PIP will seek
                                                        alternative suppliers for AAQ,
                                                        PSC, DIBC, and other materials to ensure
                                                        the continuity of plant
                                                        operations in the event of supply
                                                        disruptions from current
                                                        suppliers.
                                                    </p>
                                                </td>
                                                <td
                                                    style="width: 45%; white-space: normal; word-wrap: break-word; text-align: justify;">
                                                    <h6 class="mb-1">Mengutamakan keselamatan
                                                        dalam semua aktivitas dan menjaga kestabilan
                                                        operasi pabrik
                                                        (Target: Zero accidents, plant troubles by
                                                        internal causes: max 3
                                                        troubles).</h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        PIP senantiasa menjaga stabilitas
                                                        operasional pabrik dengan
                                                        memastikan kondisi WS, kualitas bahan
                                                        baku, dan utilitas sesuai
                                                        standar, serta kepatuhan terhadap
                                                        regulasi, guna mencapai target
                                                        produksi dan zero customer complaint.
                                                        <br>PIP akan mencari
                                                        alternatif pemasok untuk AAQ, PSC, DIBC,
                                                        dan bahan lainnya guna
                                                        memastikan keberlangsungan operasional
                                                        pabrik apabila terjadi
                                                        gangguan pasokan dari pemasok yang ada
                                                        saat ini.
                                                    </p>
                                                </td>
                                                <td>
                                                    <a href="{{ route('realisasi.edit', 1) }}">
                                                        <button type="button"
                                                            class="btn btn-light-primary icon-btn-sm"><i
                                                                class="bi bi-pencil-square"></i></button>
                                                    </a>
                                                    <button type="button" class="btn btn-light-danger icon-btn-sm"><i
                                                            class="ri-delete-bin-line"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--End col-->
            
        </div><!--End row-->
        <div class="row">
            
            <div class="col-xl-12">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row g-5">

                            <div class="col-xl-12">
                                <div class="text-end">
                                    <a href="{{ route('company-policy.index') }}" role="button"
                                        class="btn btn-light-primary">Cancel</a>
                                    <a href="{{ route('company-policy.index') }}" role="button"
                                        class="btn btn-primary">Submit</a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!--End col-->
            
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
