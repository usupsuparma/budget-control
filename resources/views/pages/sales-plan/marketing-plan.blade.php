@extends('layouts.master')

@section('title', 'Marketing Plan | Sales Plan')

@section('title-sub', 'Sales Plan')
@section('pagetitle', 'Marketing Plan')


<div class="container">

    <h4 class="mb-4">Sales Planning</h4>

    {{-- FORM INPUT --}}
    <div class="card mb-4">
        <div class="card-body">

            <form id="salesPlanningForm">

                @csrf

                <div class="row">

                    <div class="col-md-3">
                        <label>No</label>
                        <select class="form-control" name="no">
                            <option value="">Select</option>
                            @for ($i=1; $i<=100; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Actual Delivery & Prognosis</label>
                        <input type="text" class="form-control" name="actual_delivery_prognosis">
                    </div>

                    <div class="col-md-3">
                        <label>Sales Per Segment</label>
                        <select class="form-control" name="sales_segment">
                            <option>Select</option>
                            <option>B2B</option>
                            <option>Retail</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Brand</label>
                        <input type="text" class="form-control" name="brand">
                    </div>

                    <div class="col-md-3 mt-3">
                        <label>Conc %</label>
                        <input type="text" class="form-control" name="conc">
                    </div>

                    <div class="col-md-3 mt-3">
                        <label>Packing 1</label>
                        <select class="form-control" name="packing1">
                            <option>Select</option>
                            <option>Bag</option>
                            <option>Box</option>
                        </select>
                    </div>

                    <div class="col-md-3 mt-3">
                        <label>Packing 2</label>
                        <select class="form-control" name="packing2">
                            <option>Select</option>
                            <option>Bag</option>
                            <option>Box</option>
                        </select>
                    </div>

                    <div class="col-md-3 mt-3">
                        <label>Segment</label>
                        <select class="form-control" name="segment">
                            <option>Select</option>
                        </select>
                    </div>

                    <div class="col-md-3 mt-3">
                        <label>Area 1</label>
                        <select class="form-control" name="area1">
                            <option>Select</option>
                        </select>
                    </div>

                    <div class="col-md-3 mt-3">
                        <label>Area 2</label>
                        <select class="form-control" name="area2">
                            <option>Select</option>
                        </select>
                    </div>

                    <div class="col-md-3 mt-3">
                        <label>Product Cost/Kg</label>
                        <input type="number" class="form-control" name="production_cost">
                    </div>

                    <div class="col-md-3 mt-3">
                        <label>Price</label>
                        <input type="number" class="form-control" name="price">
                    </div>

                    <div class="col-md-3 mt-3">
                        <label>Increase Decrease Price</label>
                        <input type="number" class="form-control" name="increase_decrease_price">
                    </div>

                    <div class="col-md-3 mt-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option>Active</option>
                            <option>Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="text-center mt-4">
                    <button type="button" id="btnSave" class="btn btn-primary px-4">Simpan</button>
                    <button type="reset" class="btn btn-secondary px-4">Reset</button>
                </div>

            </form>

        </div>
    </div>

    {{-- DATATABLE --}}
    <div class="card">
        <div class="card-body">

            <table id="salesPlanningTable" class="table table-bordered w-100">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Actual</th>
                        <th>Sales Segment</th>
                        <th>Brand</th>
                        <th>Conc</th>
                        <th>Packing1</th>
                        <th>Packing2</th>
                        <th>Segment</th>
                        <th>Area1</th>
                        <th>Area2</th>
                        <th>Prod Cost</th>
                        <th>Price</th>
                        <th>Increase/Decrease Price</th>
                        <th>Status</th>
                        <th width="10%">Action</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>

</div>

@endsection

@push('scripts')

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(function() {
        let table = $('#salesPlanningTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('sales.planning.data') }}",
            columns: [{
                    data: 'no',
                    name: 'no'
                },
                {
                    data: 'actual_delivery_prognosis',
                    name: 'actual_delivery_prognosis'
                },
                {
                    data: 'sales_segment',
                    name: 'sales_segment'
                },
                {
                    data: 'brand',
                    name: 'brand'
                },
                {
                    data: 'conc',
                    name: 'conc'
                },
                {
                    data: 'packing1',
                    name: 'packing1'
                },
                {
                    data: 'packing2',
                    name: 'packing2'
                },
                {
                    data: 'segment',
                    name: 'segment'
                },
                {
                    data: 'area1',
                    name: 'area1'
                },
                {
                    data: 'area2',
                    name: 'area2'
                },
                {
                    data: 'production_cost',
                    name: 'production_cost'
                },
                {
                    data: 'price',
                    name: 'price'
                },
                {
                    data: 'increase_decrease_price',
                    name: 'increase_decrease_price'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Save button
        $('#btnSave').click(function() {
            $.ajax({
                url: "{{ route('sales.planning.store') }}",
                method: "POST",
                data: $('#salesPlanningForm').serialize(),
                success: function(res) {
                    Swal.fire("Success", "Saved successfully", "success");
                    table.ajax.reload();
                    $('#salesPlanningForm')[0].reset();
                },
                error: function(err) {
                    Swal.fire("Error", "Failed to save data", "error");
                }
            });
        });

    });
</script>

@endpush