<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SectionController extends Controller
{
    public function getData()
    {
        $query = Section::select(['id', 'name', 'status']);

        return DataTables::of($query)
            ->addColumn('status_badge', function ($row) {
                return $row->status == 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button type="button"
                            class="btn btn-light-danger icon-btn-sm delete-btn"
                            data-id="' . $row->id . '">
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
            'name' => 'required|string|max:255',
        ]);

        Section::create([
            'name' => $validated['name'],
            'status' => 'Active',
        ]);

        return redirect()->back()->with('success', 'Section berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = Section::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $section = Section::findOrFail($id);
        $section->name = $validated['name'];
        $section->status = $validated['status'];
        $section->save();

        return redirect()->back()->with('success', 'Section berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Section::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
