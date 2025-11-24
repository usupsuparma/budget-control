@extends('layouts.master')

@section('title', 'Settings | Approval')

@section('title-sub', 'Settings')
@section('pagetitle', 'Approval')
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
                    <div class="col-md-12 text-end">

                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDivision">
                            <i class="bi bi-plus-lg me-1"></i>Add Approval
                        </button>

                    </div>
                </div>

                <div class="card-body">
                    <table id="approvalTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 37%;">Approval Layer</th>
                                <th style="width: 10%;">Layer 1</th>
                                <th style="width: 10%;">Layer 2</th>
                                <th style="width: 10%;">Layer 3</th>
                                <th style="width: 10%;">Layer 4</th>
                                <th style="width: 10%;">Layer 5</th>
                                <th style="width: 10%;">Actions</th>
                            </tr>
                        </thead>

                    </table>

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
@push('page-scripts')
<script>
    $(document).ready(function() {
        $('#approvalTable').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
        });
    });
</script>



@endpush