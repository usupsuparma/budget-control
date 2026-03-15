<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Director;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\Section;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class MasterController extends Controller
{
    public function index()
    {
        $title = 'Master Data';

        $employees = Employee::where('status', 'Active')->get();
        $roles = Role::get();
        $jobPositions = JobPosition::where('status', 'Active')->get();
        $jobLevel = JobLevel::where('status', 'Active')->get();

        // Eager-load full org structure: directors -> divisions -> departments -> sections
        $directors = Director::where('status', 'Active')
            ->with(['divisions' => function ($q) {
                $q->where('status', 'Active')->orderBy('name');
            }, 'divisions.departments' => function ($q) {
                $q->where('status', 'Active')->orderBy('name');
            }, 'divisions.departments.sections' => function ($q) {
                $q->where('status', 'Active')->orderBy('name');
            }])
            ->orderBy('name', 'asc')
            ->get();

        // Backwards-compatible variable for select lists in existing partials
        $director = $directors;

        $division = Division::where('status', 'Active')->with('director')->orderBy('name', 'asc')->get();
        $department = Department::where('status', 'Active')->orderBy('name', 'asc')->get();
        $section = Section::where('status', 'Active')->orderBy('name', 'asc')->get();

        // provide plural aliases used across various blades for backward compatibility
        $divisions = $division;
        $departments = $department;
        $sections = $section;

        // dd($employee);


        return view('pages.settings.Settings', compact(
            'title',
            'employees',
            'roles',
            'jobPositions',
            'jobLevel',
            'directors',
            'director',
            // singular and plural names kept for compatibility
            'division',
            'divisions',
            'department',
            'departments',
            'section',
            'sections'
        ));
    }

    public function index2()
    {
        $title = 'Master Data';

        return view('pages.settings.Settings', compact('title'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = Employee::with('roles')->select(['id', 'email', 'first_name', 'last_name', 'status']);

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

            // 🧩 Return ke browser, baik AJAX maupun langsung
            if ($request->ajax()) {
                return $datatable;
            } else {
                return response()->json($datatable->getData());
            }
        }
    }
}
