<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ModulMenu;
use App\Models\Permission;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index()
    {
        $title = "Users Data";
        $roles = Role::all();
        $employees = Employee::select('id', 'first_name', 'email')
            ->orderBy('first_name')
            ->get();
        $permissions = Permission::with('modul')->OrderBy('id', 'DESC')->get();
        $moduls = ModulMenu::orderBy('modul_name')->orderBy('menu_name')->get();
        return view('pages.settings.users', compact('employees', 'title', 'roles', 'permissions', 'moduls'));
    }
}
