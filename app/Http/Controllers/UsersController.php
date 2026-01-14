<?php

namespace App\Http\Controllers;

use App\Models\ModulMenu;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index()
    {
        $title = "Users Data";
        $roles = Role::all();
        $permissions = Permission::with('modul')->OrderBy('id', 'DESC')->get();
        $moduls = ModulMenu::orderBy('modul_name')->orderBy('menu_name')->get();
        return view('pages.settings.users', compact('title', 'roles', 'permissions', 'moduls'));
    }
}
