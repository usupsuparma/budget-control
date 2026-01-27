<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use App\Models\Permission;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\ModulMenu;
use Dotenv\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class AuthorizationController extends Controller
{
    public function showLogin()
    {
        return view('authorization.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            // Check if user is active
            if (Auth::user()->status === 'Inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.');
            }

            $userId = Auth::user()->id; // employee.id (PK)

            $employment2 = Employment::where('employee_id', $userId)->firstOrFail(); // employee_id is now FK to employee.id

            $uplinesTopDown = $employment2->uplineEmployeesTopDown([1,2,3,4]);

            $dam = array();
            foreach ($uplinesTopDown as $upline) {
                $dam[] = array(
                    "id" =>  $upline->id, 
                    "employee_id" =>  $upline->employee_id, 
                    "level" => $upline->upline_job_level_id, 
                    "fname" => $upline->first_name, 
                    "lname" => $upline->last_name
                );
            }
            session()->put('uplines_top_down', $dam);

            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->with('error', 'Email atau password salah.');
    }


    // ====== ROLE PAGE ======
    public function roles()
    {
        $roles = Role::orderBy('id', 'desc')->get();
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
    // ====== PERMISSIONS ======
    public function permissions()
    {
        $permissions = Permission::with('modul')->orderBy('id', 'desc')->get(); // Update eager load
        $moduls = ModulMenu::all(); // Ini sudah benar!

        return view('authorization.permissions', compact('permissions', 'moduls'));
    }

    public function permissionStore(Request $request)
    {
        try {
            Log::info('Permission Store Request:', $request->all());

            // Validasi dengan nama tabel yang benar: 'modul_menu' (bukan 'modul_menus')
            $validator = FacadesValidator::make($request->all(), [
                'modul_menu' => 'required|exists:modul_menu,id', // ← PERBAIKI DI SINI
                'name' => 'required|string|unique:permissions,name',
                'modul_menu_name' => 'required|string'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create permission
            $permission = Permission::create([
                'modul_menu'         => $request->modul_menu,
                'name'             => $request->name,
                'modul_menu_name'  => $request->modul_menu_name,
                'guard_name'       => 'web'
            ]);

            Log::info('Permission created successfully:', ['id' => $permission->id]);

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'permission' => $permission
            ]);
        } catch (Exception $e) {
            Log::error('Permission Store Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function permissionUpdate(Request $request, $id)
    {
        $request->validate([
            'modul_menu' => 'required|exists:moduls,id',
            'name' => 'required|string|unique:permissions,name,' . $id,
            'modul_menu_name' => 'required|string'
        ]);

        $permission = Permission::find($id);
        $permission->update([
            'modul_menu'         => $request->modul_menu,
            'name'             => $request->name,
            'modul_menu_name'  => $request->modul_menu_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully'
        ]);
    }

    public function permissionDelete($id)
    {
        $permission = Permission::find($id);

        if ($permission) {
            // Cek jika permission sedang digunakan
            if ($permission->roles()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete permission. It is assigned to roles.'
                ], 422);
            }

            $permission->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
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

    public function removeUserRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:employee,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = Employee::find($request->user_id);
        $role = Role::find($request->role_id);

        if ($user && $role) {
            $user->removeRole($role->name);
        }

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus dari role'
        ]);
    }
}
