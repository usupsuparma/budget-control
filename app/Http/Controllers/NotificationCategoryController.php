<?php

namespace App\Http\Controllers;

use App\Models\NotificationCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class NotificationCategoryController extends Controller
{
    public function index()
    {
        return view('notifications.categories.index');
    }

    public function data()
    {
        $categories = NotificationCategory::query();

        return DataTables::of($categories)
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-sm btn-warning edit-category" data-id="' . $row->id . '" data-name="' . $row->name . '" data-icon="' . $row->icon . '"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-danger delete-category" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'icon' => 'nullable',
        ]);

        NotificationCategory::updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'icon' => $request->icon,
            ]
        );

        return response()->json(['success' => 'Category saved successfully.']);
    }

    public function destroy($id)
    {
        NotificationCategory::findOrFail($id)->delete();
        return response()->json(['success' => 'Category deleted successfully.']);
    }
}
