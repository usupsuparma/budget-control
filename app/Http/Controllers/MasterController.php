<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MasterController extends Controller
{
    public function index()
    {
        $title = 'Master Data';

        $employee = Employee::where('status', 1)->get();


        return view('pages.master', compact('title', 'employee'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = Employee::select(['id', 'email', 'first_name', 'last_name', 'role_id', 'status']);

            return DataTables::of($query)
                ->addColumn('fullname', fn($row) => $row->first_name . ' ' . $row->last_name)
                ->addColumn('action', function ($row) {
                    return '
                        <div class="btn-group">
                            <button class="btn btn-sm btn-warning edit" data-id="' . $row->id . '">Edit</button>
                            <button class="btn btn-sm btn-danger delete" data-id="' . $row->id . '">Delete</button>
                        </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
}
