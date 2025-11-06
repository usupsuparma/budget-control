@extends('layouts.master')

@section('title', 'Company Policy | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Company Policy')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection
@section('content')

    <!-- Begin page -->
    <div id="layout-wrapper">

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex">
                        <div class="col-md-4 col-xl-3 col-xxl-2 me-2">
                            <select id="status-choice">
                                <option value="all">All Years</option>
                                <option value="uk1">2025</option>
                                <option value="uk2">2024</option>
                                <option value="uk3">2023</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-xl-3 col-xxl-2">
                            {{-- <select id="priority-choice">
                                <option value="javascript">High</option>
                                <option value="python">Medium</option>
                                <option value="java">Low</option>
                            </select> --}}
                        </div>
                        <div class="col-md-4 col-xl-6 col-xxl-8 text-end">
                            <a href="{{ route('company-policy.create') }}">
                                <button class="btn btn-primary"><i class="bi bi-plus-circle-dotted me-2"></i>Add Company Policy</button>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-box table-responsive">
                            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
                                rel="stylesheet">

                            <style>
                                /* Tambahan kecil agar panah animasi saat dibuka */
                                .accordion-toggle i.bi-chevron-down {
                                    transition: transform 0.2s ease-in-out;
                                }

                                .accordion-toggle[aria-expanded="true"] i.bi-chevron-down {
                                    transform: rotate(180deg);
                                }
                            </style>

                            <table class="table text-nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 5%;">No.</th>
                                        <th scope="col" style="width: 15%;">Year</th>
                                        <th scope="col" style="width: 70%;">Document</th>
                                        <th scope="col" style="width: 10%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Row utama -->
                                    <tr data-bs-toggle="collapse" data-bs-target="#accordionRow1" aria-expanded="false"
                                        aria-controls="accordionRow1" class="accordion-toggle">
                                        <td>1</td>
                                        <td>2025</td>
                                        <td>
                                            <h6 class="mb-1 d-flex align-items-center">
                                                <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                                Document Company Policy
                                            </h6>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-light-info btn-sm me-2">
                                                    <i class="bi bi-file-earmark-text text-primary me-2"></i> View Document
                                                </button>
                                                <a href="{{ route('company-policy.edit', 1) }}" class="me-1">
                                                    <button type="button" class="btn btn-light-primary icon-btn-sm">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                </a>
                                                <button type="button" class="btn btn-light-danger icon-btn-sm me-2">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                <!-- Panah accordion -->
                                                <i class="bi bi-chevron-down ms-auto text-secondary"></i>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Accordion Detail Row -->
                                    <tr>
                                        <td colspan="6" class="p-0">
                                            <div id="accordionRow1" class="accordion-collapse collapse"
                                                data-bs-parent=".table">
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
                                                                    <a href="{{ route('company-policy.edit', 1) }}">
                                                                        <button type="button"
                                                                            class="btn btn-light-primary icon-btn-sm"><i
                                                                                class="bi bi-pencil-square"></i></button>
                                                                    </a>
                                                                    <button type="button"
                                                                        class="btn btn-light-danger icon-btn-sm"><i
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
                                                                    <a href="{{ route('company-policy.edit', 1) }}">
                                                                        <button type="button"
                                                                            class="btn btn-light-primary icon-btn-sm"><i
                                                                                class="bi bi-pencil-square"></i></button>
                                                                    </a>
                                                                    <button type="button"
                                                                        class="btn btn-light-danger icon-btn-sm"><i
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
                                                                    <a href="{{ route('company-policy.edit', 1) }}">
                                                                        <button type="button"
                                                                            class="btn btn-light-primary icon-btn-sm"><i
                                                                                class="bi bi-pencil-square"></i></button>
                                                                    </a>
                                                                    <button type="button"
                                                                        class="btn btn-light-danger icon-btn-sm"><i
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
                                                                    <a href="{{ route('company-policy.edit', 1) }}">
                                                                        <button type="button"
                                                                            class="btn btn-light-primary icon-btn-sm"><i
                                                                                class="bi bi-pencil-square"></i></button>
                                                                    </a>
                                                                    <button type="button"
                                                                        class="btn btn-light-danger icon-btn-sm"><i
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
                                                                    <a href="{{ route('company-policy.edit', 1) }}">
                                                                        <button type="button"
                                                                            class="btn btn-light-primary icon-btn-sm"><i
                                                                                class="bi bi-pencil-square"></i></button>
                                                                    </a>
                                                                    <button type="button"
                                                                        class="btn btn-light-danger icon-btn-sm"><i
                                                                            class="ri-delete-bin-line"></i></button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-4 m-5">
                            <div class="fw-medium"> Showing 1 - 10 of 18 Entries</div>
                            <div class="ms-auto">
                                <nav aria-label="Page navigation example">
                                    <ul class="pagination pagination-primary mb-0">
                                        <li class="page-item">
                                            <a class="page-link" href="javascript:void(0)">
                                                <i class="ri-arrow-left-s-line fw-semibold"></i>
                                            </a>
                                        </li>
                                        <li class="page-item"><a class="page-link" href="javascript:void(0)">1</a></li>
                                        <li class="page-item active"><a class="page-link" href="javascript:void(0)">2</a>
                                        </li>
                                        <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                        <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                        <li class="page-item"><a class="page-link" href="javascript:void(0)">5</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="javascript:void(0)">
                                                <i class="ri-arrow-right-s-line fw-semibold"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
@endsection

@section('js')

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <script src="{{ asset('assets/js/app/project-list.init.js') }}"></script>
@endsection
