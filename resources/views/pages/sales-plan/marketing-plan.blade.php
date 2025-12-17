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

    .stat-card {
        border-radius: 14px;
        min-height: 120px;
        /* ⬅️ dari 140 → 100 */
    }

    .stat-card .card-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        /* ⬅️ diperkecil */
    }

    .stat-title {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
        line-height: 1.25;
    }

    .stat-value {
        font-size: 18px;
        /* ⬅️ sedikit lebih kecil */
        font-weight: 700;
        margin-top: 4px;
        color: #212529;
    }

    .stat-icon {
        font-size: 24px;
        /* ⬅️ seimbang dengan tinggi card */
        opacity: 0.9;
    }
</style>




<div id="layout-wrapper">
    <div class="container-fluid">
        <div class="d-flex align-items-center gap-2">
            <div>
                <select id="yearFilter" class="form-select form-select-sm">
                    @php
                    $currentYear = date('Y');
                    @endphp
                    @for ($y = $currentYear; $y >= $currentYear - 5; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div id="customGridSearch"></div>
        </div>
        <br>
        <div class="row mb-3">
            <div class="col-md-6 col-xl-2">
                <div class="card stat-card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div>
                            <div class="stat-title">Total Sales<br>Quantity</div>
                            <div class="stat-value">21,766,075</div>
                        </div>
                        <i class="ri-bar-chart-fill stat-icon text-primary"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card stat-card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div>
                            <div class="stat-title">Total Sales<br>Amount</div>
                            <div class="stat-value">21,766,075</div>
                        </div>
                        <i class="ri-money-dollar-circle-line stat-icon text-warning"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card stat-card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div>
                            <div class="stat-title">Total Margin<br>Amount</div>
                            <div class="stat-value">21,766,075</div>
                        </div>
                        <i class="ri-funds-line stat-icon text-success"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card stat-card shadow-sm border-start border-secondary border-4">
                    <div class="card-body">
                        <div>
                            <div class="stat-title">Total Full<br>Cost</div>
                            <div class="stat-value">21,766,075</div>
                        </div>
                        <i class="ri-calculator-line stat-icon text-secondary"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card stat-card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div>
                            <div class="stat-title">Total Transport<br>Amount</div>
                            <div class="stat-value">21,766,075</div>
                        </div>
                        <i class="ri-truck-line stat-icon text-info"></i>
                    </div>
                </div>
            </div>


            <div class="col-md-6 col-xl-2">
                <div class="card stat-card shadow-sm border-start border-dark border-4">
                    <div class="card-body">
                        <div>
                            <div class="stat-title">Packing<br>Amount</div>
                            <div class="stat-value">21,766,075</div>
                        </div>
                        <i class="ri-box-3-line stat-icon text-dark"></i>
                    </div>
                </div>
            </div>



        </div>

        <!-- DATATABLE -->
        <div class="card">
            <div class="card-body">

                <div class="grid-header-wrapper mb-3">
                    <div id="customGridSearch"></div>

                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadExcelModal">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Upload Excel
                    </button>

                </div>


                <div style="overflow-x:auto; width:100%;">
                    <div id="salesGridTable"></div>
                </div>



            </div>
        </div>


    </div>
</div>


<!-- ========== MODAL UPLOAD EXCEL ========== -->
<div class="modal fade" id="uploadExcelModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Upload Marketing Plan Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="uploadExcelForm" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel (.xlsx)</label>
                        <input type="file" name="file" accept=".xlsx,.xls" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('marketing.downloadTemplate') }}" class="btn btn-success">
                            <i class="bi bi-download"></i> Download Template
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Upload Data
                        </button>
                    </div>
                </form>

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

<script>
    $("#btnUploadExcel").click(function() {

        let formData = new FormData($("#uploadExcelForm")[0]);

        $.ajax({
            url: "{{ route('marketing.upload_excel') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,

            success: function(response) {
                Swal.fire("Success", "Excel berhasil di-upload!", "success");
                $("#uploadExcelModal").modal("hide");
                grid.updateConfig({}).forceRender();
            },

            error: function(xhr) {
                Swal.fire("Error", xhr.responseJSON?.message ?? "Gagal upload file", "error");
            }
        });

    });
</script>


@endpush