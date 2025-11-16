<?php

namespace App\Http\Controllers;

use App\Models\JobPosition;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class JobPositionController extends Controller
{
    public function getData()
    {
        $query = JobPosition::select(['id', 'job_position_id', 'job_position_name', 'status']);

        return DataTables::of($query)


            ->addColumn('status_badge', function ($row) {
                if ($row->status == 'Active') {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('organization', function ($row) {
                return '';
            })
            ->addColumn('action', function ($row) {

                $editUrl = route('jobPosition.edit', $row->id);

                return '

            <a href="' . $editUrl . '" class="btn btn-light-primary icon-btn-sm">
                <i class="bi bi-pencil-square"></i>
            </a>

            <button type="button" 
                    class="btn btn-light-danger icon-btn-sm delete-btn" 
                    data-id="' . $row->id . '">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    ';
            })

            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }
}
