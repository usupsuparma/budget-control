<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class OrganizationController extends Controller
{
    public function getData()
    {
        $query = Organization::select(['id', 'organization_id', 'organization_name', 'job_level_id', 'job_level_name', 'status']);

        return DataTables::of($query)


            ->addColumn('status_badge', function ($row) {
                if ($row->status == 'Active') {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {

                $editUrl = route('organization.edit', $row->id);

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
