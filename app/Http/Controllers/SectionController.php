<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SectionController extends Controller
{
    public function getData()
    {
        $query = Section::with('department')->select(['id', 'name', 'department_id', 'status']);

        return DataTables::of($query)
            ->addColumn('department_name', function ($row) {
                return $row->department->name ?? '-';
            })

            ->addColumn('status_badge', function ($row) {
                return $row->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm section-edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-light-danger icon-btn-sm section-delete-btn" data-id="' . $row->id . '">
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
            'section_name' => 'required|string|max:255',
        ]);

        Section::create([
            'name' => $validated['section_name'],
            'department_id' => $request->department_id,
            'status' => 'Active',
        ]);


        return redirect()->back()->with('success', 'Section berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = Section::with('department')->findOrFail($id);

        return response()->json([
            'id' => $data->id,
            'name' => $data->name,
            'status' => $data->status,
            'department_id' => $data->department_id,
            'department_name' => $data->department?->name
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'section_name' => 'required|string|max:255',
            'status'       => 'required|in:Active,Inactive',
        ]);

        $section = Section::findOrFail($id);
        $section->name   = $validated['section_name'];
        $section->status = $validated['status'];
        $section->department_id = $request->department_id;
        $section->save();

        return redirect()->back()->with('success', 'Section berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Section::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
