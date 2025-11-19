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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jobPosition_name' => 'required|string|max:255',
        ]);

        // Pastikan model JobLevel memiliki fillable untuk job_level_name dan status
        JobPosition::create([
            'job_position_name' => $validated['jobPosition_name'],
            'job_level_name' => $request['job_level_name'],
            'status' => 'Active', // default aktif
        ]);

        return redirect()->back()->with('success', 'Job Level berhasil dibuat.');
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'jobLevel_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',

        ]);

        $jobLevel = JobPosition::findOrFail($id);
        $jobLevel->job_level_name = $validated['jobLevel_name'];
        $jobLevel->status = $validated['status'];
        $jobLevel->save();

        return redirect()->back()->with('success', 'Job Level berhasil diperbarui.');
    }

    public function edit($id)
    {
        $data = JobPosition::findOrFail($id);
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = JobPosition::findOrFail($id);
        $data->delete();

        return response()->json(['success' => true]);
    }
}
