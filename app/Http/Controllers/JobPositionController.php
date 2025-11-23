<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Director;
use App\Models\Division;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\Section;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class JobPositionController extends Controller
{
    public function getData()
    {
        $query = JobPosition::select([
            'id',
            'job_position_name',
            'job_level_id',
            'job_level_name',
            'structure_id',
            'structure_name',
            'status'
        ]);

        return DataTables::of($query)
            ->addColumn('organization', function ($row) {
                return $row->job_level_name ?? '-';
            })
            ->addColumn('structure', function ($row) {
                return $row->structure_name ?? '-';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                <button class="btn btn-light-primary icon-btn-sm jobPosition-edit-btn" data-id="' . $row->id . '">
                    <i class="bi bi-pencil-square"></i>
                </button>

                <button class="btn btn-light-danger icon-btn-sm jobPosition-delete-btn" data-id="' . $row->id . '">
                    <i class="ri-delete-bin-line"></i>
                </button>
            ';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function getOrganizationByLevel($level_id)
    {
        // Ambil data level
        $level = JobLevel::find($level_id);

        if (!$level) {
            return response()->json([]);
        }

        // Mapping nama untuk model
        switch (strtolower($level->job_level_name)) {
            case 'director':
                $data = Director::select('id', 'name')->get();
                break;

            case 'division':
                $data = Division::select('id', 'name')->get();
                break;

            case 'department':
                $data = Department::select('id', 'name')->get();
                break;

            case 'section':
                $data = Section::select('id', 'name')->get();
                break;

            default:
                $data = [];
        }

        return response()->json([
            'items' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_position_name' => 'required|string|max:255',
            'job_level_id'      => 'required',
            'structure_id'      => 'required',
        ]);

        JobPosition::create([
            'job_position_name' => $validated['job_position_name'],
            'job_level_id'      => $validated['job_level_id'],
            'job_level_name'    => JobLevel::find($validated['job_level_id'])->job_level_name,
            'structure_id'      => $validated['structure_id'],
            'status'            => 'Active'
        ]);

        return response()->json(['success' => true]);
    }


    public function edit($id)
    {
        $data = JobPosition::findOrFail($id);

        return response()->json($data);
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'job_position_name' => 'required|string|max:255',
            'job_level_id'      => 'required',
            'structure_id'      => 'required',
            'status'            => 'required|in:Active,Inactive'
        ]);

        $jp = JobPosition::findOrFail($id);
        $jp->job_position_name = $validated['job_position_name'];
        $jp->job_level_id      = $validated['job_level_id'];
        $jp->job_level_name    = JobLevel::find($validated['job_level_id'])->job_level_name;
        $jp->structure_id      = $validated['structure_id'];
        $jp->status            = $validated['status'];
        $jp->save();

        return response()->json(['success' => true]);
    }


    public function destroy($id)
    {
        JobPosition::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
