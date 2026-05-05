<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Company Policy - {{ $policy->tahun }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.4;
        }

        h1,
        h2,
        h3,
        h4 {
            margin: 0 0 5px 0;
        }
        #cp_detail p{
            margin-bottom: 0px;
            margin-top: 0px;
        }

        .section-title {
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }

        .mt-10 {
            margin-top: 10px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .policy-table {
            width: 100%;
            border-collapse: collapse;
        }

        .policy-table td,
        .policy-table th {
            border: 1px solid #000;
            vertical-align: top;
            padding: 6px;
        }

        .policy-table .col-50 {
            width: 50%;
        }

        .policy-table th,
        .policy-table td {
            border: 0px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .w-50 {
            width: 50%;
        }

        /* TABEL DI DALAM SIGNATURE */
        .signature table {
            width: 100% !important;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        }

        /* Border tabel dan sel */
        .signature table,
        .signature table td,
        .signature table th {
            border: 1px solid #000 !important;
            text-align: center;
        }

        .signature tr:first-child td { 
            background-color:#eaeaea;
            font-weight: bold;
        }

        .signature table tr:nth-child(2) {
            height: 100px !important;
            vertical-align: bottom;
        }

        /* Tambahan: padding agar teks lebih rapi */
        .signature table td,
        .signature table th {
            padding: 6px;
        }
    </style>
</head>

<body>
    {{-- HEADER --}}
    <div style="text-align: center;">
        {!! $policy->header !!}
    </div>

    {{-- CONTENTS --}}
    <table class="policy-table">
        <tr>
            <td class="col-50">{!! $policy->contents_en !!}</td>
            <td class="col-50">{!! $policy->contents_id !!}</td>
        </tr>
    </table>

    {{-- COMPANY POLICY DETAILS (dynamic rows) --}}
    <table class="policy-table" id="cp_detail">
        <tbody>
            <tr>
                <td class="col-50"><b>Company Policy FY{{ $policy->tahun }}: </b></td>
                <td class="col-50"><b>Company Policy Tahun {{ $policy->tahun }}: </b></td>
            </tr>
            <tr>
                <td class="col-50">{!! $policy->prologue_en !!}</td>
                <td class="col-50">{!! $policy->prologue_id !!}</td>
            </tr>
            @forelse($policy->details as $i => $detail)
            <tr>
                <td class="col-50">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="15" valign="top"><p>{{ $i + 1 }}.</p></td>
                            <td valign="top">{!! $detail->strategic_goal !!}</td>
                        </tr>
                    </table>
                </td>

                <td class="col-50">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="15" valign="top"><p>{{ $i + 1 }}.</p></td>
                            <td valign="top">{!! $detail->strategic_goal_id !!}</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td class="col-50">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="15" valign="top">&nbsp;</td>
                            <td valign="top">{!! $detail->description !!}</td>
                        </tr>
                    </table>
                </td>

                <td class="col-50">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="15" valign="top">&nbsp;</td>
                            <td valign="top">{!! $detail->description_id !!}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            @empty
            @endforelse
        </tbody>
    </table>

    {{-- CLOSING --}}
    <table class="policy-table">
        <tr>
            <td class="col-50">{!! $policy->closing_en !!}</td>
            <td class="col-50">{!! $policy->closing_id !!}</td>
        </tr>
    </table>

    {{-- SIGNATURE --}}
    <div style="text-align: center; margin-top: 20px;" class="signature">
        {!! $policy->signature !!}
    </div>

</body>

</html>
