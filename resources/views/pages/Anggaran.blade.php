@extends('layouts.master')

@section('title', 'Anggaran | Budget Control')

@section('title-sub', 'Budget Control')
@section('pagetitle', 'Anggaran')
@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<style>
    .budget-table {
        font-size: 11px;
    }
    .budget-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
        vertical-align: middle;
    }
    .budget-table td {
        vertical-align: middle;
    }
    .division-header {
        background-color: #e9ecef;
        font-weight: bold;
    }
    .activity-cell {
        padding: 2px !important;
        min-width: 40px;
    }
    .activity-bar {
        height: 20px;
        border-radius: 3px;
        margin: 1px 0;
    }
    .activity-plan {
        background-color: #93c5fd;
    }
    .activity-realization {
        background-color: #fca5a5;
    }
    .balance-label {
        font-size: 10px;
        color: #6c757d;
    }
    .text-end {
        text-align: right;
    }
</style>
@endsection
@section('content')

<!-- Begin page -->
<div id="layout-wrapper">

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div class="col-md-3 col-xl-2 me-2">
                        <form method="GET" action="{{ route('anggaran.index') }}" id="yearFilterForm">
                            <select id="year-choice" name="year" class="form-select" onchange="document.getElementById('yearFilterForm').submit()">
                                <option value="2025" {{ $year == 2025 ? 'selected' : '' }}>2025</option>
                                <option value="2024" {{ $year == 2024 ? 'selected' : '' }}>2024</option>
                                <option value="2023" {{ $year == 2023 ? 'selected' : '' }}>2023</option>
                                <option value="2022" {{ $year == 2022 ? 'selected' : '' }}>2022</option>
                            </select>
                        </form>
                    </div>
                    <div class="ms-auto text-end">
                        <button class="btn btn-success btn-sm me-2"><i class="bi bi-file-earmark-excel me-1"></i>Export to Excel</button>
                        <a href="{{ route('anggaran.create') }}">
                            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-circle-dotted me-1"></i>Input Anggaran</button>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm budget-table mb-0">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="text-center">DIVISI</th>
                                    <th rowspan="2" class="text-center">BUDGET NAME</th>
                                    <th rowspan="2" class="text-center">BUDGET</th>
                                    <th rowspan="2" class="text-center">REALIZATION</th>
                                    <th rowspan="2" class="text-center">BALANCE</th>
                                    <th rowspan="2" class="text-center">BALANCE</th>
                                    <th colspan="12" class="text-center">Activities</th>
                                </tr>
                                <tr>
                                    <th class="text-center activity-cell">Jan</th>
                                    <th class="text-center activity-cell">Feb</th>
                                    <th class="text-center activity-cell">Mar</th>
                                    <th class="text-center activity-cell">Apr</th>
                                    <th class="text-center activity-cell">Mei</th>
                                    <th class="text-center activity-cell">Jun</th>
                                    <th class="text-center activity-cell">Jul</th>
                                    <th class="text-center activity-cell">Agu</th>
                                    <th class="text-center activity-cell">Sep</th>
                                    <th class="text-center activity-cell">Okt</th>
                                    <th class="text-center activity-cell">Nov</th>
                                    <th class="text-center activity-cell">Des</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($budgetData as $divisionName => $items)
                                    @php
                                        $divisionTotal = $items->sum('total');
                                        $divisionRealization = 0; // TODO: Calculate from actual realization data
                                        $divisionBalance = $divisionTotal - $divisionRealization;
                                    @endphp
                                    
                                    @foreach($items as $index => $item)
                                        <tr>
                                            @if($index === 0)
                                                <td rowspan="{{ $items->count() * 2 }}" class="text-start fw-bold division-header">
                                                    {{ $divisionName }}
                                                </td>
                                            @endif
                                            
                                            <td rowspan="2" class="text-start">
                                                {{ $item->category?->name ?? 'N/A' }}
                                                
                                            </td>
                                            
                                            <td rowspan="2" class="text-end">
                                                {{ number_format($item->total, 0, ',', '.') }}
                                            </td>
                                            
                                            <td rowspan="2" class="text-end">
                                                {{ number_format($divisionRealization, 0, ',', '.') }}
                                            </td>
                                            
                                            <td rowspan="2" class="text-end">
                                                {{ number_format($item->total - $divisionRealization, 0, ',', '.') }}
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="balance-label">Plan</span>
                                            </td>
                                            
                                            @foreach(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'] as $month)
                                                <td class="activity-cell">
                                                    @if($item->{"activity_$month"} == 1)
                                                        <div class="activity-bar activity-plan"></div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td class="text-center">
                                                <span class="balance-label">Realization</span>
                                            </td>
                                            
                                            @foreach(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'] as $month)
                                                <td class="activity-cell">
                                                    {{-- TODO: Add realization data --}}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="18" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                No budget data available for year {{ $year }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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

<script>
    // Initialize Choices.js for year selector
    if (document.getElementById('year-choice')) {
        new Choices('#year-choice', {
            searchEnabled: false,
            itemSelectText: '',
        });
    }
</script>
@endsection