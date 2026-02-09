<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .no-border {
            border: none !important;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 10px;
        }

        .xs {
            font-size: 9px;
        }

        .box {
            display: inline-block;
            width: 12px;
            height: 14px;
            border: 1px solid #000;
            margin-right: 2px;
        }

        .qr {
            width: 90px;
            height: 90px;
        }
        .qr-center{
            display:block;
            margin:0 auto;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <table>
        <tr>
            <td style="width:20%; border: 0 !important;" class="center">
                <img src="{{ public_path('assets/images/logo-dark.png') }}" style="max-width:80px;">
            </td>
            <td style="width:60%;" class="center no-border">
                <div class="bold"><br><br>PT PEROKSIDA INDONESIA PRATAMA</div>
                <div class="bold" style="font-size:16px;">BUDGET PROPOSAL</div>
            </td>
            <td style="width:20%;border: 0 !important;" class="center bold"><br>
                <div style="border: 1px solid #000000;">PROCUREMENT<br>PLAN DOCUMENT</div>
            </td>
        </tr>
    </table>

    <!-- META -->
    <table>
        <tr>
            <td style="width:50%;border: 0 !important;">
                Number : ........................................<br>
                Date : {{ date("d M Y", strtotime($transaction[0]->created_at)) }}<br>
                {{ $transaction[0]->jobPosition->job_level_name }} : {{ $transaction[0]->jobPosition->job_position_name }}
            </td>
            <td style="width:50%;border: 0 !important;">
                Ref. Number : ........................................<br>
                Date : ........................................<br>
                Initial : ........................................
            </td>
        </tr>
    </table>

    <!-- PROGRAM & BUDGET APPROVAL -->
    <table>
        <tr>
            <td class="center bold">PROGRAM AND BUDGET APPROVAL</td>
        </tr>
    </table>

    <table>
        <tr>
            <td style="width:30%;text-transform: uppercase;" class="bold">FROM {{ $transaction[0]->jobPosition->job_level_name }}</td>
            <td>{{ $transaction[0]->jobPosition->job_position_name }}</td>
        </tr>
        <tr>
            <td class="bold">ESTIMATED VALUE</td>
            <td>IDR {{ number_format($transaction[0]->estimated_amount, 0, ',', '.') }} ( {{ numberToWordsEn($transaction[0]->estimated_amount) }} ) Rupiah</td>
        </tr>
        <tr>
            <td class="bold">FOR THE PURPOSE OF / PROJECT TITLE</td>
            <td>{{ $transaction[0]->purpose }}</td>
        </tr>
    </table>

    <br>

    <!-- APPROVAL GRID -->
    <table>
        <tr class="center bold">
            <td style="width:40%;">PROGRAM APPROVAL</td>
            <td style="width:40%;">BUDGET APPROVAL</td>
            <td style="width:20%;">REMARK</td>
        </tr>

        <tr>
            <!-- PROGRAM APPROVAL -->
            <td>
                Proposed by:<br>
                Date: {{ date("d M Y H:i:s",strtotime($transaction[0]->created_at)) }}<br>
                By: {{ $transaction[0]->user_name }}<br><br>
                <div class="xs center">
                    <img src="data:image/png;base64,{{ $qrStaff ?? '' }}" class="qr">
                    <br>
                    {{ $transaction[0]->jobPosition->job_position_name }}
                </div>
                {{-- <div class="xs center">Approved / Not **</div> --}}
            </td>

            <!-- BUDGET APPROVAL -->
            <td>
                Date:<br>
                By:<br><br>
                <div class="xs center">
                    <img src="data:image/png;base64,{{ $qrFinance ?? '' }}" class="qr"><br>
                    Finance Staff
                </div>
            </td>

            <!-- REMARK -->
            <td>
                <div class="bold small">Budget Posting</div><br><br>
                <div class="bold small">VALUE:</div>
            </td>
        </tr>

        <tr>
            <!-- PROGRAM APPROVAL 2 -->
            <td>
                Date:<br>
                By:<br><br>
                <div class="xs center">
                    <img src="data:image/png;base64,{{ $qrDivision ?? '' }}" class="qr"><br>
                    Department/Division/Directorate
                </div>
                <div class="xs center">Approved / Not **</div>
            </td>

            <!-- BUDGET APPROVAL 2 -->
            <td>
                Date:<br>
                By:<br><br>
                <div class="xs center bold">
                    <img src="data:image/png;base64,{{ $qrManager ?? '' }}" class="qr"><br>
                    Dadan Hadiana
                </div>
                <div class="xs center">Finance Division Manager</div>
            </td>

            <td></td>
        </tr>
    </table>

    <br>

    <!-- BOARD OF DIRECTORS -->
    <table>
        <tr>
            <td colspan="2" class="center bold">BOARD OF DIRECTIONS APPROVAL</td>
        </tr>
        <tr>
            <td style="width:50%;">
                Date:<br>
                By:<br><br>
                <div class="xs center">
                    <img src="data:image/png;base64,{{ $qrPresident ?? '' }}" class="qr"><br>
                    President Director
                </div>
                <div class="xs center">Approved / Not **</div>
            </td>
            <td style="width:50%;">
                Date:<br>
                By:<br><br>
                <div class="xs center">
                    <img src="data:image/png;base64,{{ $qrFinanceDirector ?? '' }}" class="qr"><br>
                    Finance Director
                </div>
                <div class="xs center">Approved / Not **</div>
            </td>
        </tr>
    </table>

    <br>

    <!-- FOOTNOTE -->
    <table>
        <tr>
            <td class="no-border xs">
                *) Accompanied by the importance of supporting data attached.<br>
                **) Completed by the Finance Division.<br>
                ***) Cross the unnecessary ones.
            </td>
        </tr>
    </table>

</body>

</html>
