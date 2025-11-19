<?php

namespace App\Http\Controllers;

use App\Models\JobLevel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class JobLevelController extends Controller
{
    public function getData()
    {
        $query = JobLevel::select(['id', 'job_level_id', 'job_level_name', 'status']);

        return DataTables::of($query)


            ->addColumn('status_badge', function ($row) {
                if ($row->status == 'Active') {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {

                return '
        <button class="btn btn-light-primary icon-btn-sm edit-btn" data-id="' . $row->id . '">
            <i class="bi bi-pencil-square"></i>
        </button>

        <button class="btn btn-light-danger icon-btn-sm delete-btn" data-id="' . $row->id . '">
            <i class="ri-delete-bin-line"></i>
        </button>
    ';
            })


            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jobLevel_name' => 'required|string|max:255',
        ]);

        // Pastikan model JobLevel memiliki fillable untuk job_level_name dan status
        JobLevel::create([
            'job_level_name' => $validated['jobLevel_name'],
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

        $jobLevel = JobLevel::findOrFail($id);
        $jobLevel->job_level_name = $validated['jobLevel_name'];
        $jobLevel->status = $validated['status'];
        $jobLevel->save();

        return redirect()->back()->with('success', 'Job Level berhasil diperbarui.');
    }

    public function edit($id)
    {
        $data = JobLevel::findOrFail($id);
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = JobLevel::findOrFail($id);
        $data->delete();

        return response()->json(['success' => true]);
    }
}
