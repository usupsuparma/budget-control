@extends('layouts.master')

@section('title', 'Marketing Plan | Sales Plan')
@section('title-sub', 'Sales Plan')
@section('pagetitle', 'Marketing Plan')

@section('content')


<style>
    .gridjs-th,
    .gridjs-td {
        white-space: nowrap !important;
    }

    .gridjs-table {
        width: auto !important;
        min-width: 100% !important;
    }
</style>
<style>
    /* Hilangkan icon sorting Grid.js */
    .gridjs-sort,
    .gridjs-sort-desc,
    .gridjs-sort-asc {
        display: none !important;
    }

    /* Agar teks tidak terpotong */
    .gridjs-th,
    .gridjs-td {
        white-space: nowrap !important;
    }
</style>
<style>
    /* Search box Grid.js – buat lebarnya auto, bukan 100% */
    .gridjs-search {
        width: auto !important;
        flex: none !important;
        display: inline-block !important;
    }

    .grid-header-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
</style>




<div class="container">



    <!-- DATATABLE -->
    <div class="card">
        <div class="card-body">

            <div class="grid-header-wrapper mb-3">
                <div id="customGridSearch"></div>

                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#salesPlanningModal">
                    + Tambah Sales Planning
                </button>
            </div>


            <div style="overflow-x:auto; width:100%;">
                <div id="salesGridTable"></div>
            </div>



        </div>
    </div>



</div>


<!-- ========== MODAL FORM ========== -->
<div class="modal fade" id="salesPlanningModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Tambah Sales Planning</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="salesPlanningForm">
                    @csrf

                    <div class="row g-4">

                        <!-- LEFT COLUMN -->
                        <div class="col-md-6">

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">No</label>
                                <div class="col-8">
                                    <select class="form-control" name="no">
                                        <option>Select</option>
                                        @for ($i=1; $i<=100; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Actual Delivery & Prognosis</label>
                                <div class="col-8">
                                    <input type="text" class="form-control" name="actual_delivery_prognosis">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Sales Per Segment</label>
                                <div class="col-8">
                                    <select class="form-control" name="sales_segment">
                                        <option>Select</option>
                                        <option>B2B</option>
                                        <option>Retail</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Brand</label>
                                <div class="col-8">
                                    <input type="text" class="form-control" name="brand">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Conc %</label>
                                <div class="col-8">
                                    <input type="text" class="form-control" name="conc">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Packing 1</label>
                                <div class="col-8">
                                    <select class="form-control" name="packing1">
                                        <option>Select</option>
                                        <option>Bag</option>
                                        <option>Box</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Packing 2</label>
                                <div class="col-8">
                                    <select class="form-control" name="packing2">
                                        <option>Select</option>
                                        <option>Bag</option>
                                        <option>Box</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-md-6">

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Segmen</label>
                                <div class="col-8">
                                    <select class="form-control" name="segment">
                                        <option>Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Area 1</label>
                                <div class="col-8">
                                    <select class="form-control" name="area1">
                                        <option>Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Area 2</label>
                                <div class="col-8">
                                    <select class="form-control" name="area2">
                                        <option>Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Product Cost/kg</label>
                                <div class="col-8">
                                    <input type="number" class="form-control" name="production_cost">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Price</label>
                                <div class="col-8">
                                    <input type="number" class="form-control" name="price">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Increase/Decrease Price</label>
                                <div class="col-8">
                                    <input type="number" class="form-control" name="increase_decrease_price">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-4 col-form-label">Status</label>
                                <div class="col-8">
                                    <select class="form-control" name="status">
                                        <option>Active</option>
                                        <option>Inactive</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                    </div>

                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="btnSave">Simpan</button>
            </div>

        </div>
    </div>
</div>


@endsection


@push('scripts')
<link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
<script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    function computeTextWidth(text, font = "14px Arial") {
        const canvas = computeTextWidth.canvas || (computeTextWidth.canvas = document.createElement("canvas"));
        const context = canvas.getContext("2d");
        context.font = font;
        return context.measureText(text).width;
    }

    function autoFitColumns(gridElement) {
        const table = gridElement.querySelector("table");
        if (!table) return;

        const headers = table.querySelectorAll("th");
        const rows = table.querySelectorAll("tr");

        headers.forEach((header, colIndex) => {
            let maxWidth = computeTextWidth(header.innerText);

            rows.forEach((row) => {
                const cell = row.children[colIndex];
                if (cell) {
                    const cellWidth = computeTextWidth(cell.innerText);
                    maxWidth = Math.max(maxWidth, cellWidth);
                }
            });

            // Sedikit padding biar rapi
            header.style.width = (maxWidth + 30) + "px";
        });
    }
</script>
<script>
    const grid = new gridjs.Grid({
        columns: [
            "No",
            "Actual Delivery & Prognosis",
            "Sales Segment",
            "Brand",
            "Conc %",
            "Packing 1",
            "Packing 2",
            "Segment",
            "Area 1",
            "Area 2",
            "Prod Cost",
            "Price",
            "Increase/Decrease Price",
            "Status",
            "Action",
        ],

        sort: false, // SORTING DIMATIKAN TOTAL agar icon tidak muncul
        search: true,
        pagination: {
            enabled: true,
            limit: 10,
        },

        server: {
            url: "{{ route('marketing.data') }}",
            then: res =>
                res.data.map(item => [
                    item.no,
                    item.actual_delivery_prognosis,
                    item.sales_segment,
                    item.brand,
                    item.conc,
                    item.packing1,
                    item.packing2,
                    item.segment,
                    item.area1,
                    item.area2,
                    item.production_cost,
                    item.price,
                    item.increase_decrease_price,
                    item.status,
                    item.id,
                ]),
        },
    }).render(document.getElementById("salesGridTable"));

    grid.on("ready", () => {
        const gridContainer = document.querySelector("#salesGridTable");
        autoFitColumns(gridContainer);
    });
</script>

<script>
    grid.on("ready", () => {

        // Auto-fit kolom
        const gridContainer = document.querySelector("#salesGridTable");
        autoFitColumns(gridContainer);

        // Pindahkan search box ke wrapper custom
        let searchBox = document.querySelector(".gridjs-search");
        if (searchBox) {
            document.getElementById("customGridSearch").appendChild(searchBox);
        }
    });
</script>

@endpush