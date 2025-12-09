<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index()
    {
        $title = "Users Data";
        $roles = Role::all();
        $permissions = Permission::OrderBy('id', 'DESC')->get();
        return view('pages.settings.users', compact('title', 'roles', 'permissions'));
    }
}
