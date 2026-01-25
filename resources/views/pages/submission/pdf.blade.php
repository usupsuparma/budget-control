<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Budget Proposal</title>
    <style>
        /* ===== PDF/A4 setup ===== */
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
        }

        * {
            box-sizing: border-box;
        }

        .sheet {
            width: 190mm;
        }

        /* approx A4 width - margins */
        .b {
            border: 1px solid #000;
        }

        .bb {
            border-bottom: 1px solid #000;
        }

        .bt {
            border-top: 1px solid #000;
        }

        .bl {
            border-left: 1px solid #000;
        }

        .br {
            border-right: 1px solid #000;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: 700;
        }

        .small {
            font-size: 10px;
        }

        .xs {
            font-size: 9px;
        }

        .mt6 {
            margin-top: 6px;
        }

        .mt10 {
            margin-top: 10px;
        }

        .p6 {
            padding: 6px;
        }

        .p8 {
            padding: 8px;
        }

        .p10 {
            padding: 10px;
        }

        .lh12 {
            line-height: 1.2;
        }

        .lh14 {
            line-height: 1.4;
        }

        /* Header */
        .header {
            display: grid;
            grid-template-columns: 40mm 1fr 45mm;
            gap: 6mm;
            align-items: start;
        }

        .logo {
            width: 18mm;
            /* height: 18mm; */
            border: 0px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            margin: 0 auto;
        }

        .doc-box {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
        }

        .title h1 {
            margin: 0;
            font-size: 14px;
            letter-spacing: .2px;
        }

        .title h2 {
            margin: 2px 0 0;
            font-size: 16px;
            letter-spacing: .3px;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6mm;
            margin-top: 6px;
        }

        .meta .row {
            display: flex;
            gap: 6px;
        }

        .meta .lbl {
            width: 36mm;
        }

        .dots {
            border-bottom: 1px dotted #000;
            flex: 1;
            height: 12px;
        }

        /* Section heading bar */
        .bar {
            border: 1px solid #000;
            padding: 5px;
            font-weight: 700;
            text-align: center;
        }

        /* Generic table */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        th {
            font-weight: 700;
        }

        /* Top info block */
        .info td {
            padding: 6px;
        }

        .info .lbl {
            width: 45%;
        }

        .info .val {
            width: 55%;
        }

        /* Approval block layout */
        .grid-approvals {
            width: 100%;
        }

        .grid-approvals th {
            text-align: center;
        }

        .subhead {
            font-weight: 700;
            text-align: center;
        }

        /* Small check boxes */
        .boxes {
            display: inline-flex;
            gap: 2px;
            vertical-align: middle;
        }

        .box {
            width: 10px;
            height: 12px;
            border: 1px solid #000;
            display: inline-block;
        }

        /* QR placeholder */
        .qr {
            width: 30mm;
            height: 30mm;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            margin: 6px auto;
        }

        .sign-line {
            margin-top: 6px;
        }

        .sign-row {
            display: flex;
            gap: 6px;
            margin-top: 2px;
        }

        .sign-row .lbl {
            width: 20mm;
        }

        .sign-row .line {
            border-bottom: 1px dotted #000;
            flex: 1;
            height: 12px;
        }

        .footnotes {
            margin-top: 6px;
            font-size: 9px;
        }

        .footnotes div {
            margin-top: 2px;
        }

        /* Keep borders crisp in PDF engines */
        .b,
        td,
        th {
            border-color: #000;
        }
    </style>
</head>

<body>
    <div class="sheet">

        <!-- HEADER -->
        <div class="header">
            <div class="logo">
                <img src="{{ public_path('assets/images/logo-dark.png') }}" style="max-width:100%;max-height:100%;" />
            </div>

            <div class="title center">
                <div class="bold">PT PEROKSIDA INDONESIA PRATAMA</div>
                <h2>BUDGET PROPOSAL</h2>

                <div class="meta">
                    <div>
                        <div class="row">
                            <div class="lbl">Number</div>
                            <div class="dots"></div>
                        </div>
                        <div class="row">
                            <div class="lbl">Date</div>
                            <div class="dots"></div>
                        </div>
                        <div class="row">
                            <div class="lbl">Sec/Dept/Division/Dir.</div>
                            <div class="dots"></div>
                        </div>
                    </div>
                    <div>
                        <div class="row">
                            <div class="lbl">Ref. Number</div>
                            <div class="dots"></div>
                        </div>
                        <div class="row">
                            <div class="lbl">Date</div>
                            <div class="dots"></div>
                        </div>
                        <div class="row">
                            <div class="lbl">Initial</div>
                            <div class="dots"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="doc-box">
                PROCUREMENT<br />PLAN DOCUMENT
            </div>
        </div>

        <!-- PROGRAM AND BUDGET APPROVAL -->
        <div class="bar mt10">PROGRAM AND BUDGET APPROVAL</div>

        <table class="info">
            <tr>
                <td class="lbl">
                    <div class="bold">FROM SEC/DEPT/DIVISION/DIRECTORATE</div>
                </td>
                <td class="val">
                    <!-- isi -->
                    ............................................................
                </td>
            </tr>
            <tr>
                <td class="lbl">
                    <div class="bold">ESTIMATED VALUE</div>
                </td>
                <td class="val">
                    IDR ............. ( ................................................. ) Rupiah
                </td>
            </tr>
            <tr>
                <td class="lbl">
                    <div class="bold">FOR THE PURPOSE OF / PROJECT TITLE</div>
                </td>
                <td class="val">
                    ............................................................
                </td>
            </tr>
        </table>

        <!-- APPROVALS GRID -->
        <table class="grid-approvals mt6">
            <tr>
                <th style="width: 38%;">PROGRAM APPROVAL</th>
                <th style="width: 38%;">BUDGET APPROVAL</th>
                <th style="width: 24%;">REMARK</th>
            </tr>

            <tr>
                <!-- PROGRAM APPROVAL - TOP -->
                <td>
                    <div class="sign-row">
                        <div class="lbl">Proposed by:</div>
                        <div class="line"></div>
                    </div>
                    <div class="sign-row">
                        <div class="lbl">Date:</div>
                        <div class="boxes">
                            <span class="box"></span><span class="box"></span><span class="box"></span>
                        </div>
                    </div>
                    <div class="sign-row">
                        <div class="lbl">By:</div>
                        <div class="line"></div>
                    </div>

                    <div class="qr">QR</div>
                    <div class="center xs">Section/Staff/Department/Division/Directorate</div>
                    <div class="center xs">Approved/Not **</div>
                </td>

                <!-- BUDGET APPROVAL - TOP -->
                <td>
                    <div class="sign-row">
                        <div class="lbl">Date:</div>
                        <div class="boxes">
                            <span class="box"></span><span class="box"></span><span class="box"></span>
                        </div>
                    </div>
                    <div class="sign-row">
                        <div class="lbl">By:</div>
                        <div class="line"></div>
                    </div>

                    <div class="qr">QR</div>
                    <div class="center xs">Finance Staff / ................</div>
                </td>

                <!-- REMARK -->
                <td>
                    <div class="bold small">Budget Posting</div>
                    <div class="mt6">
                        <div class="boxes">
                            <span class="box"></span><span class="box"></span><span class="box"></span><span
                                class="box"></span>
                            <span class="box"></span><span class="box"></span><span class="box"></span><span
                                class="box"></span>
                            <span class="box"></span><span class="box"></span><span class="box"></span><span
                                class="box"></span>
                        </div>
                    </div>
                    <div class="mt10 bold small">VALUE:</div>
                    <div class="dots" style="height:14px;"></div>
                </td>
            </tr>

            <tr>
                <!-- PROGRAM APPROVAL - MID -->
                <td>
                    <div class="sign-row">
                        <div class="lbl">Date:</div>
                        <div class="boxes">
                            <span class="box"></span><span class="box"></span><span class="box"></span>
                        </div>
                    </div>
                    <div class="sign-row">
                        <div class="lbl">By:</div>
                        <div class="line"></div>
                    </div>

                    <div class="qr">QR</div>
                    <div class="center xs">Department/Division/Directorate</div>
                    <div class="center xs">Approved/Not **</div>
                </td>

                <!-- BUDGET APPROVAL - MID -->
                <td>
                    <div class="sign-row">
                        <div class="lbl">Date:</div>
                        <div class="boxes">
                            <span class="box"></span><span class="box"></span><span class="box"></span>
                        </div>
                    </div>
                    <div class="sign-row">
                        <div class="lbl">By:</div>
                        <div class="line"></div>
                    </div>

                    <div class="qr">QR</div>
                    <div class="center xs bold">Dadan Hadiana</div>
                    <div class="center xs">Finance Division Manager</div>
                </td>

                <!-- REMARK filler -->
                <td></td>
            </tr>
        </table>

        <!-- BOARD OF DIRECTIONS APPROVAL -->
        <div class="bar mt10">BOARD OF DIRECTIONS APPROVAL</div>

        <table class="mt6">
            <tr>
                <td style="width:50%;">
                    <div class="sign-row">
                        <div class="lbl">Date:</div>
                        <div class="boxes">
                            <span class="box"></span><span class="box"></span><span class="box"></span>
                        </div>
                    </div>
                    <div class="sign-row">
                        <div class="lbl">By:</div>
                        <div class="line"></div>
                    </div>

                    <div class="qr">QR</div>
                    <div class="center xs">President Director / Director / ...</div>
                    <div class="center xs">Approved/Not **</div>
                </td>

                <td style="width:50%;">
                    <div class="sign-row">
                        <div class="lbl">Date:</div>
                        <div class="boxes">
                            <span class="box"></span><span class="box"></span><span class="box"></span>
                        </div>
                    </div>
                    <div class="sign-row">
                        <div class="lbl">By:</div>
                        <div class="line"></div>
                    </div>

                    <div class="qr">QR</div>
                    <div class="center xs">Finance Director</div>
                    <div class="center xs">Approved/Not **</div>
                </td>
            </tr>
        </table>

        <div class="footnotes">
            <div>*) Accompanied by the importance of supporting data attached.</div>
            <div>**) Completed by the Finance Division.</div>
            <div>***) Cross the unnecessary ones.</div>
        </div>

    </div>
    <!-- =========================
     PAGE 2 (HALAMAN KEDUA)
     Tempel setelah halaman 1
========================== -->
    <div style="page-break-before: always;"></div>

    <style>
        /* Page 2 styles (aman untuk PDF) */
        .p2-wrap {
            width: 100%;
        }

        .p2-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 260mm;
        }

        .p2-title {
            font-weight: 700;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .p2-subtitle {
            font-weight: 700;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .p2-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .p2-table th,
        .p2-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .p2-table th {
            font-weight: 700;
            text-align: center;
        }

        .p2-gray {
            background: #d9d9d9;
        }

        .p2-right {
            text-align: right;
        }

        .p2-center {
            text-align: center;
        }

        .p2-small {
            font-size: 9px;
        }

        .p2-mt12 {
            margin-top: 12px;
        }

        .p2-mt18 {
            margin-top: 18px;
        }

        .sig-area {
            margin-top: 26px;
            text-align: right;
        }

        .sig-line {
            display: inline-block;
            width: 80mm;
            border-bottom: 1px dotted #000;
            height: 14px;
        }

        .sig-sub {
            margin-top: 8px;
        }
    </style>

    <div class="p2-wrap">
        <div class="p2-box">

            <div class="p2-title">COST ALLOCATION DETAILS</div>
            <div class="p2-subtitle">PROCUREMENT OF : .......................................................</div>

            <!-- TABLE: COST ALLOCATION -->
            <table class="p2-table">
                <thead>
                    <tr class="p2-gray">
                        <th style="width: 10%;">Number</th>
                        <th style="width: 35%;">Description of Goods/Services</th>
                        <th style="width: 10%;">Unit</th>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 12.5%;">Price per Unit<br />(IDR)</th>
                        <th style="width: 12.5%;">Total<br />(IDR)</th>
                        <th style="width: 10%;">REMARK</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- baris contoh (ubah jadi foreach Blade kalau perlu) -->
                    <tr>
                        <td class="p2-center">1</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="p2-center">2</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="p2-center">3</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="p2-center">4</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="p2-center">5</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <!-- TOTAL -->
                    <tr class="p2-gray">
                        <td colspan="5" class="p2-center bold" style="font-weight:700;">TOTAL ( IDR )</td>
                        <td class="p2-center">-</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <!-- URGENCY / IMPORTANCE -->
            <div class="p2-mt18 p2-title">URGENCY / IMPORTANCE OF THE PROGRAM / ANALYSIS RESULTS</div>

            <table class="p2-table">
                <thead>
                    <tr class="p2-gray">
                        <th style="width: 10%;">Number</th>
                        <th>Urgency / Importance of The Program / Analysis Result</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="height: 55mm;">
                        <td class="p2-center">1</td>
                        <td></td>
                    </tr>
                    <tr style="height: 55mm;">
                        <td class="p2-center">2</td>
                        <td></td>
                    </tr>
                    <tr style="height: 55mm;">
                        <td class="p2-center">3</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <!-- Prepared By -->
            <div class="sig-area">
                <div class="p2-small">Prepared By</div>
                <div class="p2-mt12"><span class="sig-line"></span></div>
                <div class="sig-sub p2-small">......Section/......Department/......Division/......Directorate</div>
            </div>

        </div>
    </div>

</body>

</html>
