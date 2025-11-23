<?php

namespace App\Http\Controllers;

use App\Models\Director;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DirectorController extends Controller
{
    public function getData()
    {
        $query = Director::select(['id', 'name', 'status']);

        return DataTables::of($query)
            ->addColumn('status_badge', function ($row) {
                return $row->status == 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm director-edit-btn" data-id="' . $row->id . '">
                   

                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button type="button"
                            class="btn btn-light-danger icon-btn-sm director-delete-btn"
                            data-id="' . $row->id . '">
                        <i class="ri-delete-bin-line"></i>
                    </button>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'director_name' => 'required|string|max:255',
        ]);

        Director::create([
            'name' => $validated['director_name'],
            'status' => 'Active', // default
        ]);

        return redirect()->back()->with('success', 'Director berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = Director::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'director_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $director = Director::findOrFail($id);
        $director->name = $validated['director_name'];
        $director->status = $validated['status'];
        $director->save();

        return response()->json([
            'success' => true,
            'message' => 'Director updated'
        ]);
    }


    public function destroy($id)
    {
        // dd('$id');
        Director::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
