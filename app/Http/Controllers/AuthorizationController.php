<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Employee;
use Illuminate\Http\Request;

class AuthorizationController extends Controller
{
    // ====== ROLE PAGE ======
    public function roles()
    {
        $roles = Role::all();
        return view('authorization.index', compact('roles'));
    }

    public function roleStore(Request $request)
    {
        Role::create(['name' => $request->name]);
        return response()->json(['success' => true]);
    }

    public function roleUpdate(Request $request, $id)
    {
        Role::find($id)->update([
            'name' => $request->name,
        ]);
        return response()->json(['success' => true]);
    }

    public function roleDelete($id)
    {
        Role::find($id)->delete();
        return response()->json(['success' => true]);
    }


    // ====== PERMISSIONS ======
    public function permissions()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    public function permissionStore(Request $request)
    {
        Permission::create(['name' => $request->name]);
        return response()->json(['success' => true]);
    }

    public function permissionDelete($id)
    {
        Permission::find($id)->delete();
        return response()->json(['success' => true]);
    }


    // ====== ROLE PERMISSIONS ======
    public function rolePermissions($id)
    {
        $role = Role::find($id);
        $permissions = Permission::all();

        return response()->json([
            'role' => $role,
            'permissions' => $permissions,
            'selected' => $role->permissions->pluck('name')
        ]);
    }

    public function rolePermissionsUpdate(Request $request, $id)
    {
        $role = Role::find($id);
        $role->syncPermissions($request->permissions);
        return response()->json(['success' => true]);
    }


    // ASSIGN ROLE → USER
    public function assignRole(Request $request)
    {
        $user = Employee::find($request->user_id);
        $user->syncRoles([$request->role]);
        return response()->json(['success' => true]);
    }
}
